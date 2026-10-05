# R24 — Chilli Portal2 PayPal pending signup, resume and receipt

## Scope and baseline

Baseline `9c2925b88f5cfe3678dac62a3656f58acaab8612` (R23b).
RES-236/240 are the two planned Portal2 `signup-paypal/index.php` and
`success.php` consumers. `contrib/chilli/common/portal2Paypal.php` implements
Portal2 policy, reusing only the checked SQL/capacity primitives from the
unchanged Portal1 provider and the existing Chilli PDO connector.

The shared PayPal callback, actual Portal2 `paypal-ipn.php`/provision wrapper,
Portal1 implementation, Portal3 signup, PEAR wrappers/dependency and merchant
example helpers are unchanged. No schema migration is required. Those helpers
and remaining consumers retain their separately scheduled dispositions.

## Preserved Portal2 contracts

- Plans use the **numeric primary key `billing_plans.id`**, not Portal1's
  logical `planId`. Options still select PayPal plans without a new active-plan
  filter. Actual numeric zero is supported; malformed or noncanonical IDs are
  rejected rather than numerically coerced or interpolated into SQL.
- All six historical controls are required. First/last name, address/city/state
  and creation metadata are stored in userinfo; plan name, combined contact
  person, address/city/state and creation metadata are stored in userbillinfo.
  The third insert creates a PayPal billing_merchant pending order with username,
  correlation, numeric planId and payment_date. No authentication, group, payment
  ledger or billing-history mutation occurs at signup.
- Respect configured PIN length, bounded by the unchanged callback's 64-character
  account limit. Use the configured alphanumeric pool and secure random selection;
  the distinct order correlation is a 64-character cryptographic random value.
- Match the callback's integer percentage-tax rounding, producing two-decimal
  cost and tax. Ordinary form retains `_xclick`, amount and on0/os0/on1/os1.
  Subscription form retains `_xclick-subscriptions`, a3/p3/t3/src/sra/custom,
  supporting the four legacy Daily/Weekly/Monthly/Yearly periods.
- Preserve the stylesheet, static layout/navigation, receipt messages and legacy
  hotspot prelogin link. Administrator message markup remains trusted; dynamic
  PINs, plan labels and form values are escaped. The return URL needs only txnId;
  the unused username query is omitted, while callback option_selection2 remains
  aligned with the generated account.

## Ownership and deliberate deviations

One nonpersistent PDO connection handles plan/options and all provisioning.
The provider refuses a borrowed active transaction. It uses the schema-scoped
signup advisory lock shared with cooperating free/Portal1 allocators, acquires
metadata locks before InnoDB preflight, locks the selected plan, and checks
collisions in info, billing info, merchant orders, RADIUS check/reply and mappings.
There are 20 allocation attempts, never an unbounded loop.

All three inserts use explicit columns and checked execution; actual string
capacities are checked before writing, including the combined contact person.
Read back each stored row on the same handle before commit to detect silent
coercion/truncation. A first, second or third insert error restores all prior
transactional rows. No PIN or checkout appears before checked commit; generic
errors do not expose SQL/driver details or generated values.

Intentional repairs, not baseline parity:

- The pinned third-insert failure leaves two earlier rows and still renders a
  checkout. PDO rolls everything back and returns 503 without PIN/checkout.
- Add session CSRF, one-use submitted token, scalar/method/length validation,
  checked merchant/environment destinations and escaped dynamic output.
- Add **session-bound pending resume**: for 30 minutes, reload can reconstruct
  the same untouched pending PIN/order/checkout without another insert. The
  private session keeps only the correlation/expiry, not a copied PIN. Resume
  checks the pending order and both account rows. Query-supplied PIN/username/
  txnId cannot select a pending account. Processed/ambiguous/expired orders are
  not offered as untouched checkouts; inconsistent account chains fail closed.
- Receipt accepts legitimate multiple payment rows only with exact common
  correlation/account/plan and PayPal vendor. Any persisted Completed payment
  wins over an earlier Pending row, fixing the pinned reader's endless polling
  in that case. A receipt is evidence of historical payment, **not current
  subscription authorization**: cancellation remains callback-owned.
- Unsupported recurrence/type, invalid money/currency, inadequate physical
  capacity and nontransactional sources are refused before provisioning.
  In particular, unsupported periods no longer silently emit a monthly checkout.
- Sensitive responses use no-store/no-referrer; UTF-8 replaces the old declared
  ISO charset. Source LF conventions are retained.

## Deployment configuration (none changed in the lab)

Keep the configured Chilli database/table keys and username settings. The legacy
`CONFIG_MERCHANT_IPN_URL_ROOT` plus relative SUCCESS/FAILURE/DIR settings still
construct return/cancel/notify destinations. They must form credential-free
HTTPS PHP endpoint URLs with bounded length and safe relative paths. A new
`CONFIG_PAYPAL_PORTAL_BASE_URL` can serve as the root fallback; no existing
working merchant root is rewritten.

`CONFIG_PAYPAL_SANDBOX` is a real PHP boolean and defaults to true, matching this
Portal2 listener. If `CONFIG_MERCHANT_WEB_PAYMENT` is provided, it must exactly
match the official HTTPS checkout URL for that configured environment. Receiver
ID/email selection matches the callback (merchant business email is the legacy
fallback). Do not leave the checkout business and callback receiver inconsistent.
No merchant identity, deployment URL or live connection material is saved here.
Package all already-required Chilli common helpers and the common PDO connector;
the signup directory alone is not a standalone application.

## Executed evidence

`python3 -u tests/portal2_paypal_signup_http.py` passed on native HTTP/PHP 8.4.24
and disposable MariaDB 11.8 fixtures:

- **13 keyed PEAR/PDO comparisons**: plan controls, seven signup SQL/checkout
  projections (ordinary, numeric zero, quotes, four recurrence periods), and
  five complete receipt body/status projections. Generated credentials and
  correlations are checked only in memory/disposable state, never reported.
- **11 negative/characterization families** covering all three insert failures,
  exact pending state/no activation, consumed CSRF and malformed/missing controls,
  session resume/replay/stranger/expiry/corrupt-account cases, seven InnoDB source
  checks, physical address/contact bounds, exact percentage midpoint rounding,
  invalid/quoted/Unicode plans and escaped persisted PINs.
- Six collision sources, configured PIN length, and two dedicated HTTP workers
  observed waiting on the real native advisory lock; exactly one complete
  three-row allocation for their deterministic shared candidate.
- Both a newly generated ordinary checkout and monthly subscription run through
  the **unchanged actual callback** with a separate trusted **local HTTPS** verifier.
  Assert exact verified raw body, invalid verification, enrollment without
  activation, Completed authorization/profile/billing/receipt, duplicate replay,
  cancellation and next-billing dates. No actual provider request/payment.
- Repeated historical payment receipts with conflict rejection; SELECT-only
  reads; configured nondefault tables and a separate measured database;
  merchant/sandbox isolation; native source failures; borrowed silent-mode
  transaction preservation and refused borrowed registration; legacy-open
  tripwires and direct-provider rejection. Candidate PHP logs are clean.
  Baseline diagnostics are limited to characterized legacy random-seed conversion
  and empty-result receipt warnings, not arbitrary baseline errors.

Four adjacent native suites passed:

| Suite | Result |
| --- | --- |
| `tests/chilli_paypal_http.py` (UNIT-040) | Complete three-callback TLS/payment/subscription/rollback/replay matrix and cleanup |
| `tests/portal1_paypal_signup_http.py` (R23) | Nine paired comparisons, ten native negative families and actual signup/IPN/receipt |
| `tests/chilli_signup_http.py` | All three free-signup families and real form/CAPTCHA/rollback/concurrency |
| `tests/chilli_connections_http.py` | Six coexistence wrappers, transaction/redaction rules, real port 3307 and special connection-data routing |

Three production PHP lints, Python AST/compilation, four existing PDO/password/
notification PHP scripts and Git whitespace checks passed. Disposable database,
HTTP/TLS workers, networks, configuration/session/certificate resources were
removed. Pre-existing `tests/__pycache__/` is preserved. No live service changed.

## Limits and continuation

This is real PHP/HTTP/SQL with synthetic data and local provider emulation, not
browser painting, a real PayPal sandbox/live financial exchange, FreeRADIUS
protocol authentication, NAS/hotspot hardware or PostgreSQL execution. Subscription
provider-side tax behavior is not established by local emulation; the form fields
and server calculation remain the historical contract and require separate actual
provider validation before deployment. No performance or installation claim.

Configured short PINs remain historical policy. Legacy receipt tokens remain
bearer references and can appear in upstream access/proxy logs despite no-referrer.
Advisory locking coordinates these signup writers, not arbitrary external inserts;
uncoordinated writers and concurrent plan-policy changes remain outside this
proof. Receipt payment history must not be used to infer current authorization.

R24's two inventoried consumers are closed. R25–R29 remain separate work, including
legacy compatibility/dependency removal. No push, PR or deployment.
