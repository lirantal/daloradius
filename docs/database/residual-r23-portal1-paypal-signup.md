# R23 — Chilli Portal1 pending PayPal signup and receipt

## Scope

Pinned baseline: `226365a8bac7a185643fd2df1dd45a25bc7d8d00` (R22c).
RES-223 and RES-227 cover `contrib/chilli/portal1/signup-paypal/index.php`
and `success.php`. The new `contrib/chilli/common/portal1Paypal.php` is a
Portal1-specific provider over the existing Chilli PDO connector. Existing
`paypal-ipn.php`, shared callback implementation, Portal2/3 flows, legacy
open/close wrappers and PEAR dependency are unchanged.

## Preserved behavior

- List PayPal plans from the configured billing-plan table, retaining zero
  identities and the historical absence of an active-plan filter.
- Keep the original firstName/lastName/address/city/state/planId controls.
  As before, address/city/state are accepted but **not persisted**. No new
  address semantics are introduced.
- Generate an eight-character account PIN and a 64-character order correlation.
  Persist only a userinfo row (creation metadata included) and a pending
  billing_paypal row with username, txnId, planId and planName. Do not create
  authorization attributes, profile mappings, payment events or Completed state.
  The existing callback remains the only activation/payment writer.
- Preserve the Standard single-purchase projection: amount, tax, currency,
  item_number, item_name, quantity, notify_url, return and on0/os0 correlation.
  The tested IPN carries the correlation back as option_selection1.
- Receipt lookup binds the original txnId correlation. Only the persisted exact
  `Completed` value shows the escaped username/PIN and stops five-second polling.
  Missing/pending/denied/failed rows keep waiting; submitted payment_status is
  ignored. Administrator-configured message markup remains trusted markup.

## Transaction and deliberate repairs

Signup uses one nonpersistent PDO connection for options, schema/plan checks,
allocation, both inserts, exact stored-value verification and commit. It owns
its transaction and refuses an already-active caller transaction. Table names
are validated, values bound, actual column capacities checked, and participating
InnoDB engines verified after metadata-lock acquisition. A database-scoped
advisory lock coordinates allocation with the existing free-signup allocator.
Allocation checks userinfo, pending orders, radcheck and group mappings, with
20 bounded retries. It uses secure random selection from the configured alphabet
and a separate cryptographic correlation. Neither PIN nor checkout is rendered
before successful commit. Checked errors roll back earlier writes; errors expose
no driver details or generated values. Receipt reads do not change or close a
borrowed connection/transaction, including silent PDO error mode.

Intentional deviations from the pinned PEAR page are asserted separately:

- PEAR can keep the first userinfo insert and still display PIN/checkout after
  the later order insert fails. PDO returns a generic 503 and rolls both back.
- Fresh session CSRF, one-use submitted token, scalar/method/storage validation,
  duplicate/ambiguous plan rejection, exact plan/receipt identity checks and
  escaped HTML replace unguarded input/interpolation.
- The malformed legacy currency input becomes a valid hidden currency_code.
  Historical HTTP/hardcoded merchant/host checkout destinations are replaced
  with the configured HTTPS business/destinations. Cancel returns to the actual
  signup page rather than a nonexistent cancelled.php.
- Signup and receipt responses use no-store and no-referrer; session startup
  does not overwrite the explicit no-store header.

## Required deployment configuration (no live configuration changed)

Keep the existing database/table configuration and callback receiver binding.
Set `CONFIG_PAYPAL_RECEIVER_ID`, or `CONFIG_PAYPAL_RECEIVER_EMAIL` (the legacy
`CONFIG_MERCHANT_BUSINESS_ID` remains an email fallback), consistently with the
unchanged callback. `CONFIG_PAYPAL_SANDBOX` must be a PHP boolean, not a string.
Set **`CONFIG_PAYPAL_PORTAL_BASE_URL`** to the absolute HTTPS directory containing
this Portal1 index, success and paypal-ipn endpoint. Do not include credentials,
query or fragment. Missing/invalid receiver/base settings refuse registration
before any write. The URL plus generated receipt query is bounded. No merchant
identifier, real secret or configured deployment URL is added to this report.

Package the already-required `contrib/chilli/common` helpers and common PDO
connector with this legacy example; copying only the signup directory is not a
self-contained deployment. Recheck configured tables are InnoDB before rollout.

## Executed validation

`python3 -u tests/portal1_paypal_signup_http.py` passed on native PHP 8.4.24,
HTTP and disposable MariaDB 11.8 fixtures:

- **9 keyed paired comparisons:** initial plan controls, ordinary/zero/quoted
  signup projections, and five persisted receipt statuses. Generated values
  are checked in memory rather than retained in comparison artifacts.
- **10 negative/characterization families:** first/later INSERT failure,
  complete non-sensitive state invariance, fresh CSRF/replay/malformed controls,
  engine/capacity/ambiguous-plan rejection, quote/Unicode/XSS output, allocation
  collisions in four sources, and two dedicated independent HTTP workers
  observed waiting on the actual native advisory lock (exactly one allocation).
- Actual signup-generated checkout/order passes through the unchanged callback
  and separate trusted **local HTTPS** provider emulator. Check exact raw body
  verification, rejected invalid verification, Completed authorization/profile
  activation, successful receipt and duplicate-event state invariance.
- Configured nondefault tables, a measurably separate database, sandbox checkout,
  SELECT-only receipt grants, missing/invalid tables/configuration and borrowed
  transaction ownership on success/failure/refused registration. Legacy-open
  tripwires, direct-provider rejection and clean candidate PHP diagnostics pass.

Adjacent native regressions passed:

| Suite | Result |
| --- | --- |
| `tests/chilli_paypal_http.py` | All three unchanged callbacks, local TLS/raw body, payment/subscription replay, engines, rollback/retry and cleanup |
| `tests/chilli_signup_http.py` | Three free-signup families, real form/CAPTCHA, rollback, input gates and concurrent allocation |
| `tests/chilli_connections_http.py` | Six PEAR/PDO wrappers, coexistence, ownership/redaction, actual port 3307 and delimiter-bearing connection data |

The first parallel full-callback regression exceeded its execution budget;
its disposable resources were explicitly cleaned and the full standalone rerun
passed. This is not reported as a successful timed-out run.

All three production PHP files linted; the harness parsed/compiled; existing
PDO-connection, portal-password storage, portal-password and notification-render
PHP test scripts passed; Git whitespace checks passed. Source LF conventions and
pre-existing `tests/__pycache__/` are preserved. Disposable SQL/HTTP/TLS/network,
session/config and certificate resources were removed. No live service changed.

## Limits

These are native tests with synthetic data, not a real PayPal sandbox/live
exchange, completed financial payment, full browser rendering, FreeRADIUS
protocol authentication or NAS/hotspot hardware. PostgreSQL is not tested.
No installation, deployment or performance claim.

Eight-character PINs and legacy bearer receipts remain historical policy;
legacy correlations remain readable. Receipt query strings can still appear in
upstream access/proxy logs: deployments must handle those logs appropriately.
Advisory locks coordinate cooperating signup writers, not arbitrary external
inserts; absent universal uniqueness/FKs, uncoordinated external writers can
still race. No schema or callback rewrite is introduced. Remaining consumers,
R28/R29 cleanup and dependency removal remain separate. No push or PR.
