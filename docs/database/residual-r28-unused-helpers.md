# R28 — unused helpers and obsolete compatibility branches

## Scope and disposition

Source baseline: `2753c9d1c2fb922e64cf978d889f49626042c25b` (R27b),
branch `refactor/pdo`. This lot closes **35 original inventory entries**, not a
whole-repository production-consumer count. No package removal is authorized here.

| Disposition | Entries | Change |
| --- | ---: | --- |
| Remove unused function | 21 | `add_invoice_items` and ten obsolete `populate_*` functions in each selector provider |
| Remove unloaded copy | 6 | Portal-specific `library/errorHandling.php` copies; active common callback remains |
| Keep tested PDO contract; remove obsolete PEAR branch | 7 | Three mapping wrappers, three NAS lock helpers, duplicate-error predicate |
| Retain already-PDO provider | 1 | `userInvoiceAdd`; `userBilling.php` remains byte-identical to R27 |

The complete ID/file/function disposition is in
[`residual-r28-dispositions.json`](residual-r28-dispositions.json).

PHP tokenization distinguishes definitions, executable function calls and quoted
callback/include strings from comments. The structural gate checks current
tracked application/contrib PHP (excluding vendored PEAR/mailer/vendor code),
requires removed names to have no declaration, direct call or string reference,
and checks the exact retained function-name sets. A same-name callback in the
common Chilli connection bootstrap or third-party mailer is **not** a caller of
a copied portal file. Repository tracing cannot rule out custom external
includes or custom deployments; those must adapt before using this cleanup.

## Implementation and preserved contracts

- Keep `drawTables`, `drawOptions`, `drawTypes`, `drawRecommendedHelper` and all
  current `get_*` selectors; remove only the unused legacy HTML populators.
- Keep mapping wrappers exercised by existing HTTP fixtures. They now accept
  `PDO` explicitly and delegate to the existing caller-owned transaction
  providers; no new connection, locking policy or commit boundary is introduced.
- Keep the exact database/table-scoped NAS advisory lock name, timeout clamping,
  failure reporting and release semantics. Only the unused PEAR dispatch is
  removed. Duplicate recognition is now the native PDO SQLSTATE/vendor-code
  predicate rather than a PEAR/message fallback.
- Keep shared Chilli open/close compatibility and the actual common error
  callback. Remove only six unloaded copied handlers. PayPal signup/receipt and
  unchanged IPN workflows remain active; 2Checkout stays retired with HTTP 410.
- Keep `userInvoiceAdd` and its complete provider unchanged; it was already PDO
  after R20. The similarly named unused `add_invoice_items` is not its provider.
- Do not remove PEAR DB, connection bootstraps, other compatibility branches,
  configuration, installation scripts or data. R28 is not R29/release readiness.

Earlier fixtures must not accidentally run a new PDO-only wrapper on a legacy
PEAR page. The mapping test uses a separate pinned `functions-legacy.php` for
its PEAR calls and injected historical page. NAS import similarly pins the
matching historical lock helper for its legacy create route. Current PDO calls
still load current providers. Baseline Chilli fixture assembly retains its
historical copied handler while candidate assembly no longer requires it.
Historical providers exist only in disposable fixture trees, not production.
The two changed application-copy builders explicitly exclude ignored real
`daloradius.conf.php`; generated fixture configuration is cleaned up.

## Verified validation

[`residual-r28-validation.json`](residual-r28-validation.json) records the exact
suite/mode/exit matrix. **19 successful HTTP fixture runs across 16 suites**:

| Suite | Successful modes |
| --- | --- |
| `group_mappings_http.py` | candidate/regression; GROUP_MAPPINGS_BASELINE=1 |
| `nas_import_http.py` | candidate/regression; NAS_IMPORT_BASELINE=1 |
| `chilli_connections_http.py` | candidate/regression; CHILLI_CONNECTION_BASELINE=1 |
| `selectbox_reads_http.py` | candidate/regression |
| `user_group_pages_http.py` | candidate/regression |
| `shared_context_http.py` | candidate/regression |
| `nas_management_http.py` | candidate/regression |
| `operator_acl_reads_http.py` | candidate/regression |
| `nas_reads_maintenance_http.py` | candidate/regression |
| `portal1_paypal_signup_http.py` | candidate/regression |
| `portal2_paypal_signup_http.py` | candidate/regression |
| `portal3_paypal_signup_http.py` | candidate/regression |
| `chilli_signup_http.py` | candidate/regression |
| `chilli_paypal_http.py` | candidate/regression |
| `chilli_2checkout_retirement_http.py` | candidate/regression |
| `invoice_create_http.py` | candidate/regression |

These include ordinary PEAR/PDO state/projection comparisons, caller-owned
rollback and later-write failures, real second-connection contention, NAS
import/CRUD/list/export, ACL, selectors, user/plan mappings, invoice contexts,
PDF and local SMTP, all PayPal portal signups/receipts, unchanged IPN callbacks,
free signup and 2Checkout refusal. Each earlier suite retains its own pinned
baseline/characterized legacy defects; successful baseline runs do not mean
full output parity for intentionally repaired historical behavior.

Separate structural/native-bootstrap gate: `python3 tests/residual_cleanup_audit.py`
passes all 35 dispositions, exact symbol inventories, native isolated PHP
includes/reflection, the native duplicate-code predicate and unchanged
`userBilling.php`. It runs without network and does not execute inventory source
while tokenizing it. This is not SQL workflow proof; the HTTP suites supply that.

Four changed PHP files pass native lint. Four changed/new Python tests pass
in-memory syntax compilation without creating caches. Four native unit/contract
scripts pass: `pdo-connection.test.php`, `portal-password-storage.test.php`,
`report_export_descriptor.test.php`, `user_report_export.test.php`.
`git diff --check` passes.

An initial structural-test-only assertion used an integer synthetic exception
code instead of PDO's string SQLSTATE. The fixture was corrected with a native
reflection-set string code; no production behavior was changed to accommodate
that mistake. A long regression transport timed out; only persisted successful
records were counted and the unrecorded tail was re-run in shorter batches.

## Limits and next gate

All runtime validation uses synthetic, disposable PHP/MariaDB/HTTP fixtures.
The payment verifier is local HTTPS, not PayPal; maintenance uses a fixture
executable, not a real NAS/radclient exchange; SMTP is an isolated receiver.
No real FreeRADIUS, payment-provider, NAS, full-browser or PostgreSQL validation
is claimed. Fixtures are removed; no persistent test service or production-data
mutation is introduced. The pre-existing `tests/__pycache__/` stays untracked.
No push, PR or deployment is part of R28.

Next: refresh the **complete current** PEAR inventory, distinguish active calls,
handle-dispatched compatibility, bootstrap/vendor code and historical fixtures.
R29 package/provider removal remains gated on no active/indirect consumer and
fresh Docker/classic installation plus existing-data upgrade validation. Do not
subtract these 35 historical entries to invent a live remaining count.
