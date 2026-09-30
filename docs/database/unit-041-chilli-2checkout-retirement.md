# UNIT-041 — retire legacy Portal2 2Checkout callbacks

## Disposition and scope

UNIT-041 takes the inventory's **deprecate**, not **migrate**, disposition, explicitly selected by the user after the local authentication audit. This is a deliberate breaking change: this legacy 2Checkout signup path no longer accepts payments or activates accounts. It is not a PDO payment implementation and has no replacement payment provider, new database table or schema migration.

Affected family: `contrib/chilli/portal2/signup-2checkout/`.

- `2co_ipn.php` is an HTTP 410 retirement endpoint, not a payment acknowledgement.
- `2co_start.php` is HTTP 410 and cannot emit a gateway purchase form or redirect.
- `index.php` returns HTTP 410 **before** configuration, signup processing or any database access. Its previous implementation is retained below the unconditional return; those signup SQL blocks are not represented as PDO-migrated.
- `include/common/retired.php` emits one fixed, untrusted-input-independent plain-text response with `Cache-Control: no-store` and `X-Content-Type-Options: nosniff`.
- `include/common/provisionUser.php` removes the dependent PEAR mutations. Its old function name/signature is retained as a fail-fast compatibility tombstone: `provisionUser(...)` throws `LogicException` before touching its supplied connection. Direct HTTP access is also 410.
- `include/merchant/TwoCo.php::validateIpn()` throws `LogicException` before copying, logging or accepting any notification. Its form-building SDK surface remains historical code, not a supported replacement integration.

The endpoints read neither the request fields nor merchant configuration, start no session, open no PEAR/PDO connection and make no external request. They never turn a callback into a payment, account activation, group assignment or billing-profile update. Other Chilli families, including PayPal, free signup and the separate bundled merchant examples under `signup-paypal/include/merchant/`, are not changed by this unit.

## Evidence and why a PDO conversion is insufficient

The native legacy verifier calculates a digest over the signing word, merchant ID, provider order number and total. Its check does not bind `custom` (the local order), `cart_order_id` (the selected plan) or `credit_card_processed` (the payment flag).

A runtime PHP probe loads the **unmodified** `TwoCo`/`PaymentGateway` classes from the pre-UNIT-041 commit. With SDK payload logging disabled through its existing public option, it checks a generated valid signature, changes those three unsigned fields, and checks the same signature again. Both are accepted. No signer, gateway or signature response is mocked; the signing word is generated at runtime and never retained. This proves the authorization primitive's missing field binding, not a completed real-world activation exploit.

The separate unmodified HTTP baseline on the test PHP runtime returns HTTP 500 at `PaymentGateway.php:193` (`Path must not be empty`) because the SDK log path is not set. Fixture database state remains unchanged. This is a pre-existing baseline defect; the test does **not** describe that response as a successful legacy payment, a candidate regression or a parity result. Repairing the logger would not repair the demonstrated signature-field gap.

Atomic PDO writes or an event-deduplication table alone cannot authenticate which local order and plan a payment authorizes. Retiring the incomplete verifier and its consumer avoids preserving that trust boundary in another database API. A future payment implementation must separately choose and validate the actual merchant-account protocol, verify the provider-authenticated order identity, amount/currency, receiver and payment state, and only then apply idempotent mutations using one PDO transaction. That work is not implemented or claimed here.

## Compatibility and deployment

- GET, POST, HEAD and other methods return 410; no redirect, session cookie, credentials or request values are returned.
- The initial signup and checkout producer are also blocked so users cannot create new pending accounts or pay through a return endpoint that can no longer activate them.
- Existing accounts, billing records and group mappings are **not** deleted, disabled, migrated or otherwise changed. Existing authentication records remain intact.
- `success.php` remains the existing historical read path for previously completed records. It is outside this retirement's mutation scope and is not an authorization primitive or new payment confirmation.
- Before deploying, withdraw public signup links to this retired family and coordinate disabling its merchant callback/checkout configuration with the responsible operator. Returning 410 is intentional, not a provider-specific successful acknowledgement; provider retry behavior is not tested.
- Pending or partially processed historical orders require operator reconciliation against authoritative payment records. This unit performs no automatic activation, refund, failure marking or replay of historical data.
- Re-enabling this path requires a separately reviewed authenticated replacement, not removal of the guard or reinstatement of MD5 validation.
- This change does not alter any merchant credential or live service configuration, and does not itself deploy anything.

**Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.**

## Verification

Run from the repository root:

```sh
CHILLI_2CHECKOUT_BASELINE=1 PYTHONDONTWRITEBYTECODE=1 python3 tests/chilli_2checkout_retirement_http.py
PYTHONDONTWRITEBYTECODE=1 python3 tests/chilli_2checkout_retirement_http.py
```

The test uses isolated Docker containers running real PHP HTTP and MariaDB on an internal network, with disposable fixture tables and no real gateway or payment:

- native baseline verifier accepts the altered unsigned fields;
- unmodified baseline HTTP failure is characterized separately;
- candidate validator and provisioning shim reject calls explicitly, with zero use of the supplied socket;
- all five retired routes return the fixed 410 response;
- signed, invalid, empty, array/malformed, Unicode, oversized and JSON payloads are refused;
- concurrent callbacks leave `billing_merchant`, `userbillinfo`, `userinfo`, `radcheck`, `radusergroup` and `billing_history` unchanged, including existing active accounts and mappings;
- configuration tripwires are never loaded; the retired routes still work after removing configuration/SDK files and stopping the fixture database;
- combined PHP stdout and stderr contain no candidate warnings/fatals, input/signature/signing-word logs or legacy application payload log files;
- containers, network, environment and fixture directories are removed.

Only synthetic fixture signing material is used, generated at runtime and held in memory/the disposable container environment. The fixture configuration references `getenv()` rather than persisting that material. Nothing is printed or retained as a signature/payload snapshot. No live merchant verification, live RADIUS authentication, production database access or deployment is claimed.
