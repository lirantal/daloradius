# UNIT-040 — Chilli PayPal callbacks

## Scope and coexistence

Migrates the ten inventoried PayPal callback/persistence/provisioning blocks:

- `portal1/signup-paypal/paypal-ipn.php`;
- `portal2/signup-paypal/paypal-ipn.php` and `include/common/provisionUser.php`;
- `portal3/signup-paypal/paypal-ipn.php`.

The callbacks now delegate to `contrib/chilli/common/paypalPdo.php`. Portal2's historical provisioning include is a loader for that provider; the old `provisionUser`, `enableUser`, `disableUser`, `updateBilling`, `saveToDb` and raw-log routines are retired. Repository caller inspection found no remaining consumers of those old APIs. Do not call the old three-argument helpers from custom integrations; use the complete verified callback rather than a separate mutation/connection.

Checkout pages, initial registration and their PEAR wrappers are **not** migrated by this unit. 2Checkout is outside scope and retains its separate, same-named helpers under `signup-2checkout/`; these do not load the PayPal provisioning module. Keep UNIT-038 `contrib/chilli/common/database.php` and UNIT-001 `app/common/includes/pdo_connection.php` available; copying only an individual example directory is insufficient. PHP cURL, OpenSSL, PDO MySQL and a current CA trust store are required. Monetary arithmetic assumes 64-bit PHP.

## Verification boundary

Accept POST only. Read the original form-encoded body with a 64-KiB limit, reject duplicate keys, bracket/array controls, malformed percent encoding, more than 200 fields, oversized values and a caller-supplied `cmd`. Parse this same body, not PHP's potentially truncated or key-mangled `$_POST` projection.

Before SQL, retransmit `cmd=_notify-validate&` plus the original bytes to the fixed HTTPS IPN endpoint selected by administrator configuration. Require trusted certificate and hostname validation, no redirects, HTTP 200 and the exact `VERIFIED` response token after trimming surrounding whitespace. `INVALID` is rejected. A verification transport/status/unexpected-response failure returns a generic HTTP 503 without opening SQL. No notification can choose a verifier URL or switch sandbox/live mode.

Supported notifications also require the configured receiver, correct environment marker, a known unambiguous local order and plan, matching supplied account/item descriptors, and a valid provider identity. Completed/pending payment events require a payment date and an exact amount/currency/quantity match to the locked plan. Use integer minor-unit calculations, not floating-point comparisons. Portal1/3 retain absolute plan tax; Portal2 retains percentage tax rounded to cents. This implementation deliberately supports only USD, EUR, GBP, CAD, AUD, NZD, CHF, SGD and HKD, quantity one and amounts with at most two decimal places. Other currencies, negative/refund amounts, unsupported statuses, shipping discounts or multiple-item carts are not implemented as new workflows.

References for the provider boundary and variables:

- https://developer.paypal.com/api/nvp-soap/ipn/IPNImplementation/
- https://developer.paypal.com/api/nvp-soap/ipn/IPNandPDTVariables/

## Configuration and deployment prerequisites

**Apply the SQL migration before deploying callbacks:**

```sh
mariadb <database> < contrib/db/migrations/2026-09-30-chilli-paypal-events.sql
```

Use the site's normal protected authentication mechanism; do not put credentials in the command or logs. `contrib/db/mariadb-daloradius.sql` contains the identical journal definition for fresh installations. The additive migration is replayable and preserves existing journal rows. An existing incompatible table is not silently repaired by `CREATE TABLE IF NOT EXISTS`.

Configure in each portal's existing `library/daloradius.conf.php`:

```php
// Choose explicitly; historical defaults are live for Portal1, sandbox for 2/3.
$configValues['CONFIG_PAYPAL_SANDBOX'] = false;

// Pin the actual receiving PayPal account; placeholders are not production values.
$configValues['CONFIG_PAYPAL_RECEIVER_EMAIL'] = '<configured receiving email>';
// Alternatively, pin receiver_id. A nonempty configured ID takes precedence.
// $configValues['CONFIG_PAYPAL_RECEIVER_ID'] = '<configured receiving account ID>';

// Optional identifier override; do not share a journal across unrelated databases.
// $configValues['CONFIG_DB_TBL_CHILLI_PAYPAL_EVENTS'] = 'chilli_paypal_events';

// Optional administrator-managed CA bundle. Normally use the system trust store.
// $configValues['CONFIG_PAYPAL_CA_FILE'] = '/path/to/trusted-ca-bundle.pem';
```

If receiver email is not explicitly set, the existing `CONFIG_MERCHANT_BUSINESS_ID` is usable only when it contains the receiving email. There is no hardcoded fallback account and no acceptance of an unconfigured receiver. The checkout's receiving business field must match the callback configuration. The environment setting must be a PHP boolean, not a string; sandbox requires `test_ipn=1`, live accepts absence or `test_ipn=0`. The configured database port is honored. Table identifiers must be simple names; every participating table must be InnoDB, including group-definition sources and Portal2 plan profiles/billing tables.

**Historical cutover requires reconciliation.** The old callbacks saved INVALID notifications and partial workflows; their persisted records are not proof of authenticated or fully applied payment events. A previously touched order with no new journal history returns HTTP 503 unchanged. Cancellation/end-of-term also requires an enrolled subscription in the journal. There is no automatic historical backfill or unsafe inference from raw IPN logs. Reconcile historical orders/subscriptions and PayPal deliveries under a controlled operator-reviewed cutover before relying on their callbacks. Do not erase payment status or invent journal rows to bypass this gate. Fresh untouched registration rows can be processed normally. Keep receiving-account identity configuration stable across a journal's lifetime.

## Single connection and durable once-only processing

After verification, open one nonpersistent PDO handle. Hold a database-scoped advisory lock on that handle, preflight transactional engines, begin one transaction, and lock local order/plan rows. All journal, merchant/payment, RADIUS, mapping and billing mutations use this same handle; no PEAR query or independently committed helper is invoked.

`chilli_paypal_events` stores hash identifiers/fingerprints, event type/status/date and processing time, **not** raw callback bodies, signing fields or credentials. The primary key distinguishes provider ID/type/status within the configured receiver/environment scope. A unique nonnull completed-payment hash prevents reusing a PayPal payment for another event or order. The fingerprint binds immutable local order/account/plan and payment semantics; a conflicting replay fails rather than rebinding an existing event. Hashes are operational deduplication identifiers, not anonymization guarantees.

A duplicate acknowledged event performs no writes. A pending-to-completed transition is a separate event and can fulfill once. One-shot orders cannot fulfill a second distinct completed payment. Recurring orders retain a payment/event row per new notification; an already-enrolled subscription cannot be reassigned to another order or replaced by another subscription under the same correlation token.

Insert the journal entry, update/append payment persistence, provision authorization/profile/time attributes when appropriate, and write Portal2 billing dates/history within one transaction. A later profile, authorization, billing UPDATE or history INSERT failure rolls back all earlier writes including the journal. A retry can then succeed. HTTP 200 is emitted only after commit, or for a duplicate/unsupported-event no-op. HTTP 400 is a generic validation rejection; HTTP 503 is a generic processing/verification failure. No exception details, SQL values or callback fields are rendered or logged. Releasing a lock after commit is best-effort; close never commits.

## Preserved contract and deliberate corrections

- Preserve the selected RADIUS `Auth-Type := Accept`, profile priority zero, time attributes and configured historical three-column `usergroup` support. Inserts name columns explicitly. No password/credential generation is performed here.
- Portal1 uses a nonempty legacy `pin` when available and otherwise the actual checkout's `username`; its old callback read only `pin`, while its checkout populated `username`.
- Portal1's old group INSERT used the literal string `planGroup`; the new provider binds the selected real group. Profile definitions must exist in check/reply sources. Portal2 processes all selected profiles and deduplicates mappings rather than reusing a result variable as SQL state.
- Old callbacks could repeat authorization/group/billing side effects, persist INVALID notifications and match the substring VERIFIED inside NOTVERIFIED or HTTP headers. The new provider does not preserve these failures.
- Portal1/3 now enable only a completed supported payment, not an arbitrary verified notification. Portal2 subscription signup enrolls but does not grant access before payment; completed subscription/recurring payments grant access and book history once.
- Cancellation and end-of-term set one consistent `Auth-Type := Reject`, rather than appending contradictory Accept/Reject rows. A terminated order cannot be reactivated by any later payment notification. New payments can still be booked once; older dates cannot move billing dates backwards. A new subscription needs a new local order. Events without provider event dates use receipt time; this is not a universal event-ordering guarantee.
- Fix Portal2 billing on the same handle using the actual plan and event date. The unmodified baseline passes an undefined `$row` to date calculation, omits a statement terminator while constructing UPDATE, then calls `query($sql)` before `$sql` has been assigned. With PHP/mysqli in the fixture this produces a `ValueError`/HTTP 500 **after earlier payment/activation writes have committed**, before billing history is inserted. Candidate billing history/date correction is an intentional divergence, not claimed full legacy parity.
- Plan/current price changes while a checkout is outstanding can cause mismatch rejection; the legacy registration does not contain an immutable checkout price snapshot. This unit does not silently add a pricing/checkout redesign.

Portal2 records supported signup, modify and failed lifecycle notifications without granting access. Unknown event types and payment statuses outside Completed/Pending/Denied/Failed are acknowledged as ignored without mutations; refunds, reversals and disputes need separate manual/operator reconciliation. This unit is not a complete subscription/refund accounting product or production security certification.

## Verified isolated execution

```sh
CHILLI_PAYPAL_BASELINE=1 PYTHONDONTWRITEBYTECODE=1 python3 -u tests/chilli_paypal_http.py
PYTHONDONTWRITEBYTECODE=1 python3 -u tests/chilli_paypal_http.py
PYTHONDONTWRITEBYTECODE=1 python3 tests/chilli_signup_http.py
PYTHONDONTWRITEBYTECODE=1 python3 tests/chilli_connections_http.py
```

The baseline is pinned to `58b410ab47f4b598dedd2564248d9c781ebd8f03`. Its callbacks and provisioning file remain unmodified in the fixture; all modes use equivalent disposable MariaDB schemas/configuration. Tests compare shared persisted projections against the same expected contract and explicitly characterize the Portal1 literal-group defect, repeated effects, and Portal2 partial writes/HTTP 500. A baseline characterization pass does **not** mean its callback works successfully or that billing state is identical to PDO.

The real callback pages execute in PHP workers against MariaDB. A private Docker network routes the official live/sandbox verifier hostnames to an ephemeral HTTPS emulator with a trusted certificate: **no real PayPal network call, sandbox integration or payment occurs**. The PDO verification function is not stubbed. The harness checks original body bytes, INVALID, NOTVERIFIED, redirect/status/transport failures, untrusted CA rejection and configured receiver/environment binding. Fixtures substitute local database/log/CA settings only; root-owned emulator artifacts are cleaned up.

Candidate cases include malformed/duplicate/oversized controls, wrong receiver/order/item/account/amount/currency/quantity/date, nonzero percentage/absolute taxes, pending/completed and late-pending states, quoted/Unicode identifiers, obsolete-pin fallback, multiple profiles, missing groups, every participating MyISAM table, invalid configuration, historical journal gaps, sequential and parallel redelivery, cross-order payment/subscription rebinding and durable migration replay. Triggers force late mapping, second-profile, billing-date, history and cancellation failures; tests verify full pre/post persisted state and successful retry. Subscription tests cover enrollment without access, payment, cancellation replay, older/later payments without reactivation, monotonic billing dates, failed and end-of-term events. Both stdout/stderr PHP logs are inspected; candidate raw IPN log files must remain absent. Container, network and temporary-file cleanup is asserted.

PHP lint, Python AST and `git diff --check` supplement these runtime tests. UNIT-038/039 regression suites are also run. No live portal deployment, real PayPal exchange or FreeRADIUS authorization exchange is asserted.

## Residual limits

The advisory lock coordinates these callbacks only, not legacy checkout/external writers or concurrent DDL. Legacy tables lack universal identity uniqueness/foreign keys. Keep the journal durable; removing or changing its scope destroys historical deduplication assumptions. Journal retention, HA/database failover, external-writer discipline, callback request limits/rate limiting, HTTPS ingress, monitoring and safe reconciliation of ignored/failed notifications remain operator responsibilities. Payment verification holds a PHP worker until its bounded timeout. An interrupted connection during commit can have an uncertain outcome; a generic failure is not a promise that nothing committed.

**Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.**
