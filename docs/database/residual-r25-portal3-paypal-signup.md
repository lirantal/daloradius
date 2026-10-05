# R25 — Chilli Portal3 PayPal pending signup and receipt

## Scope and baseline

Baseline `218393e0fafa07b7f335abdf8802052300dd29b2` (R24b).
RES-244/248 are Portal3 `signup-paypal/index.php` and `success.php`.
`contrib/chilli/common/portal3Paypal.php` owns Portal3 registration policy. It
reuses the unchanged Portal1 checked reads/capacity/legacy receipt primitives
and Portal2 checked insert/merchant URL primitives, not their signup policies.
The existing Chilli PDO connector supplies one nonpersistent connection.

Portal1/Portal2 providers and pages, the shared callback and Portal3 listener,
provisioning wrappers, free signup, PEAR compatibility/dependency and example
helpers are unchanged. No schema migration is required. No live configuration,
service or data changed; no merchant or connection material is recorded here.

## Preserved Portal3 contracts

- Plans are selected by logical `billing_plans.planId`, including literal `0`,
  not by Portal2's numeric `billing_plans.id`. The list retains `planType='PayPal'`
  without inventing an active/recurrence filter. Ambiguous and non-exact keys
  cannot create a pending order.
- All six historical controls are required: first/last name, address, city,
  state and plan. Only first/last name and creation metadata are stored in
  userinfo. Address/city/state are deliberately **not** stored, matching Portal3
  rather than adopting Portal2's additional userinfo/userbillinfo policy.
- The second insert creates a billing_paypal order containing username, txnId,
  planName and logical planId. No authentication/group/event journal, merchant
  history or userbillinfo write occurs during pending signup.
- PIN length is `CONFIG_PASSWORD_LENGTH`, **not CONFIG_USERNAME_LENGTH** and not
  Portal1's fixed eight characters. The configured alphanumeric pool is used
  with secure random selection; length is canonical and bounded to 1–64 by the
  unchanged callback account limit. An absent length defaults to eight.
- Checkout is `_xclick` with amount, quantity, **flat tax amount**, currency,
  item number/name and on0/os0 correlation. No Portal2 percentage calculation or
  recurring/subscription checkout policy is imported. Monetary/currency values
  must satisfy the unchanged listener's supported contract.
- Signup displays the pending PIN only after checked commit. It remains inactive
  until the callback authorizes it. Success reads billing_paypal only and shows
  the PIN only for the exact persisted `Completed` state, not posted/query status.
  Pending/Denied/Failed/empty/missing rows keep polling as before.
- Existing stylesheet, static sidebar/layout and trusted administrator success
  message markup are retained. Dynamic plan/form/PIN values are escaped.

## Ownership and intentional repairs

One connection handles options, selected plan and both dependent inserts.
Registration refuses a caller-owned transaction. A schema-scoped advisory lock
coordinates cooperating Chilli allocators; six distinct configured sources
(plan, userinfo, billing_paypal, RADIUS check/reply and mappings) are metadata
locked and checked for InnoDB before provisioning. Default Portal3 mapping is
radusergroup, aligned with the unchanged listener.

Lock the selected plan and check collisions in info/order/check/reply/mapping,
plus order correlation. Allocation is bounded to 20 attempts. Physical string
capacities are verified before writes. Both inserts check execution and affected
rows, including PDO silent mode. Each row is read back on the same connection
before commit to catch coerced/trigger-rewritten fields. Any first/last insert or
stored-value failure rolls back all pending rows. No false checkout is rendered.
The reader never commits/rolls back a borrowed transaction.

Deliberate deviations from the pinned PEAR pages, separately characterized:

- A late baseline insert error leaves userinfo committed and still displays a
  PIN/checkout. PDO returns a generic 503 with complete pending-row rollback.
- Session CSRF, consumed POST token, method/scalar/bound validation, secure
  allocation, finite retry and ambiguous-receipt rejection are added. No pending
  resume feature is introduced; a fresh GET still renders a new signup form.
- Repair the malformed legacy currency_code name attribute. Native baseline
  parsing confirms it did not produce a correctly named currency control.
- Escape dynamic values in options, echoed input, hidden checkout and receipt.
  Preserve trusted configured message HTML. Missing receipt HEADER is tolerated.
- Replace the nonexistent index.html navigation destination with index.php.
  Fixed relative form action avoids reflecting PHP_SELF. Declare UTF-8 and add
  no-store/no-referrer headers. The two original files retain CRLF and final EOL;
  the new provider/test/report use LF.
- Require coherent credential-free HTTPS merchant return/cancel/notify URLs and
  environment. A configured merchant business must match the callback receiver;
  an explicitly configured receiver ID may coexist with its configured email
  alias. Misconfiguration is refused before writes, never silently redirected.

## Deployment configuration (not changed)

Preserve the configured Chilli database/table keys and PASSWORD_LENGTH. Merchant
root plus relative SUCCESS/FAILURE/DIR keys still select destinations; the shared
validated root fallback is supported. `CONFIG_PAYPAL_SANDBOX` is a real boolean
and defaults to true, matching the Portal3 listener. A configured merchant web
payment URL must exactly match the official HTTPS endpoint for that environment.
The configured merchant business must identify the receiver expected by IPN.

Ship the already-required Chilli common providers and common PDO connector;
the signup directory alone is not a standalone application. Unsupported money,
currency, identifiers, inadequate storage and nontransactional sources fail
closed. No merchant/deployment URL or login/secret value is saved in this report.

## Executed evidence

`python3 -u tests/portal3_paypal_signup_http.py` passed on native HTTP/PHP 8.4.24
and disposable MariaDB 11.8 fixtures:

- **15 keyed PEAR/PDO comparisons**: plan controls, three signup SQL/checkout
  projections (ordinary, logical zero, quoted/Unicode names), five visible
  receipt state projections, and six empty-required-control state comparisons.
  Baseline empty controls return 200 while PDO returns 400; both preserve rows.
  Dynamic correlations/PINs are checked only in disposable state/in memory.
- **11 candidate/characterization families** include first/last insertion and
  stored-field mutation rollback, strict controls/CSRF/replay/method gates, all
  six engine checks, physical capacities and ambiguous/foreign/quoted plans.
- Configured PIN length distinct from USERNAME_LENGTH, merchant ID/email alias,
  correct complete checkout destinations and repaired currency control. Dynamic
  plan/form/receipt XSS, including an invalid form redisplay, are escaped.
- Five existing-identity collision sources and two dedicated HTTP workers
  actually observed waiting on the native advisory lock; exactly one full
  deterministic PIN allocation survives.
- A newly signup-generated order runs through the **unchanged actual callback**
  with a trusted **local HTTPS** verifier. Assert exact verified raw body,
  rejected invalid verification, Completed authentication/profile/receipt and
  duplicate callback invariance. No actual PayPal exchange occurs.
- Read-only grants, alternate schema/custom tables, invalid/missing backend and
  merchant configuration; borrowed silent-mode successful/failed receipt reads
  preserve caller writes/transaction. Borrowed registration is refused. Owned
  silent-mode first/last native INSERT failures leave no active transaction and
  restore all rows. Legacy connection tripwires and direct-provider 404 pass.
  Candidate logs have no PHP warning/fatal/notice/deprecation; baseline diagnostic
  allowance is specific to historical random seed and missing-row behavior.

Five adjacent native suites passed:

| Suite | Result |
| --- | --- |
| `tests/chilli_paypal_http.py` (UNIT-040) | Complete three-listener payment/TLS/rollback/replay matrix, subscription cases and cleanup |
| `tests/portal1_paypal_signup_http.py` (R23) | Nine paired comparisons, ten control families and actual locally verified signup/IPN/receipt |
| `tests/portal2_paypal_signup_http.py` (R24) | Thirteen paired comparisons, eleven families and native pending/resume/receipt/callback |
| `tests/chilli_signup_http.py` (UNIT-039) | All three free-signup families, form/CAPTCHA, rollback and concurrent independent sessions |
| `tests/chilli_connections_http.py` (UNIT-038) | All six wrappers, borrowed connections, redaction, actual alternate port and special connection routing |

Three production PHP lints and Python AST/compile passed. Existing PHP contract
scripts pdo-connection, portal-password, portal-password-storage and
notifications-render passed in a network-isolated container; these are contract/
static/unit checks, distinct from the native MariaDB integration suites.
CRLF-aware Git whitespace checks passed.

An initial batched regression invocation exceeded its runner budget. Its exact
abandoned UNIT-040 containers/network and bound scratch directory were verified
and removed, without touching other resources. UNIT-040 was rerun successfully
with the full terminal budget; all four other regressions were rerun to recover
verifiable results. Final R25 fixtures/config/session/TLS artifacts were removed.
Pre-existing tests/__pycache__/ is preserved; no generated access material remains.

## Limits and continuation

Native HTTP/PHP/SQL use synthetic data and local verifier emulation. This is not
browser rendering, real PayPal sandbox/live financial exchange, FreeRADIUS
protocol authentication, hotspot/NAS hardware or PostgreSQL execution. No install,
deployment or performance claim is made.

Short PINs remain configured historical policy. Receipt correlations remain
bearer references and may appear in upstream access/proxy logs despite these
response headers. Cooperating advisory locks do not serialize arbitrary external
inserts or prove safety of unrelated concurrent plan-policy changes. A historical
Completed receipt must not be used to infer current authorization after reversal.

R25's two consumers are closed. R26–R29 remain separate, including compatibility
and dependency removal. No push, PR or deployment.
