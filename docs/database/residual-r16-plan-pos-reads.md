# R16 — residual plan, POS and batch reads

## Frozen scope and local implementation

- Branch: `refactor/pdo`; pinned PEAR baseline: `2148769ec72bc42cfc4b179516d6f434b6a08639` (completed R15).
- Original R16 blocks: RES-034/035/036/037/038/039/131/132, covering exactly the eight planned entry points.
- Implemented directly without subagents. No push, PR, production data access or deployment.
- No schema changes and no edits to the existing transactional plan/POS/batch write providers.

| Slice | Entry points | Disposition |
|---|---|---|
| R16a | `bill-plans-list.php`, `bill-plans-edit.php`, `bill-plans-del.php` | PDO page count/bounded rows, existence/detail/profile reads, ordered delete options |
| R16b | `bill-pos-list.php`, `bill-pos-edit.php`, `bill-pos-del.php` | PDO grouped count/bounded rows, RADIUS existence and portal presence, user/bill detail reads, existing PDO group widget dispatch, delete options |
| R16c | `mng-batch-add.php`, `mng-batch-del.php` | PDO hotspot map and ordered history names; shared R02 group/plan selectors retained; unused legacy handle scopes removed |

`app/operators/library/catalog_reads_pdo.php` selects the explicit session backend and reuses the existing PDO factory and identifier validator. Data values, including LIMIT/OFFSET integers, are bound. Selector columns are allowlisted, and configured table names are validated/quoted. List order keys/directions retain their page allowlists. SELECT row counts never use PDO `rowCount()`.

Page-local handles are private and released in `finally`; they do not begin/commit/roll back a write transaction. The shared group widget borrows the page's PDO, with buffered rendering and a redacted failure envelope. The read helper similarly borrows its handle and closes its result cursor without taking ownership of an existing transaction. An SQL failure does not disclose bound values or driver errors in HTML/log action messages.

All list rows are fetched before row rendering. A failed plan/profile or POS detail read suppresses the partial edit form. A failed batch hotspot read prevents batch creation/export. A successfully committed write remains successful if a later display read fails; it is not presented as a rolled-back write.

## Preserved contracts and characterized repairs

- Ordered plan/history delete selectors remain ordered; the POS delete selector retains its legacy unsorted DISTINCT username projection.
- The plan profile selector remains DISTINCT by profile name. POS listing keeps the original grouped account query, disabled-group join, password-hidden display, and every offered sort key.
- Existence for POS edit is still checked against RADIUS `radcheck`, **not** optional user/billing metadata. Missing optional metadata remains editable/creatable by the existing write provider.
- Empty/NULL display strings, inactive plans, absent targets, special raw identities, disabled accounts, default/named backend isolation and post-mutation forms were exercised.
- Existing plans/POS/batch transactions and their rechecks, rollback semantics and selected-location routing remain intact.

Intentional differences from pinned PEAR, asserted separately rather than counted as parity:

1. Scalar name `0` is no longer mistaken for an empty plan/POS edit identity. The POS substring filter also honors `0`.
2. POS filter/sort and both top/bottom pagination links carry `planname`, not the unrelated historical `vendor` parameter, and use the original identity rather than its HTML-escaped presentation. Plan/POS action URLs likewise encode raw names. Quotes, percent, plus, ampersand and Unicode are covered. A percent character in a plan filter is literal rather than being deleted; existing underscore wildcard behavior remains.
3. Plan list pagination counts the actual rendered rows. Duplicate plan names previously understated the number of pages because COUNT(DISTINCT name) accompanied a non-DISTINCT row query. The last duplicate-containing page is now reachable.
4. Auth-Type account badges use the fetched value, not the nonexistent associative `auth` key on a numeric row. The page now includes the existing validation constants required by PIN/MAC classification; a native PIN rendering request verifies the branch.
5. Missing password/optional metadata rows render safe defaults instead of invalid numeric offsets. Late errors are handled without phantom forms, partial group markup or SQL detail disclosure.

The mixed historical CRLF/LF plan source was preserved on unchanged lines; this is not a whole-file normalization.

## Actual verification

`python3 -u tests/catalog_reads_http.py` returned **exit 0** on the final source:

- **77 complete native PEAR/PDO comparisons** of noncredential table rows/footer and form controls/options/selections; generated authentication display is checked only in memory, never emitted as a snapshot.
- All nine offered list sort keys in both directions, every seeded pagination boundary, matching/nonmatching/injection-looking filters, selected/deleted/inactive/absent targets, configured physical table names, a separate named schema, NULL and empty datasets.
- Native SELECT-only SQL grants on both candidate backends, login/ACL rejection, scalar/array input rejection, unchanged noncredential read-only domain state.
- Real late SQL errors on all eight page-local families (fixture-only column renames), plus a real failed PDO query in the copied group widget. HTTP responses remain complete with domain-level errors rather than driver information or half-rendered rows/forms.
- Committed plan edit and POS delete followed by selector/display failure, plus hotspot discovery failure before any batch/account creation.
- Real caller-owned transaction containing an earlier UPDATE remains active after a successful read and a failed SQL read, then rolls back correctly.
- Throwing legacy open/close tripwires on the seven routes without R20 dependencies. Full POS edit permits only the explicit existing R20 report functions (including transitive `checkUserOnline`), and must reach the HTML epilogue; status 200 alone is not treated as proof of success.
- stdout **and** stderr PHP log capture with a positive harmless channel marker; no authentication factor appears in captured logs. Known R20 no-invoice warnings are isolated and checked against the pinned baseline, not called clean logs.
- Temporary configs/sessions, tmpfs database, PHP/MariaDB containers and internal network were removed; resource absence was checked independently.

All eight required UNIT-008–015 regression suites were rerun on the final production source and returned **exit 0**:

| Suite | Existing workflow verified |
|---|---|
| `tests/plan_create_http.py` | plan creation, rejected inputs, later profile rollback, named backend |
| `tests/plan_edit_http.py` | plan/profile edit, later mapping rollback, special names, named backend |
| `tests/plan_delete_http.py` | single/multiple deletion, stale selection, later parent rollback, named backend |
| `tests/pos_provision_http.py` | provisioning, invalid plan/profiles, later invoice-item rollback, portal hashes, named backend |
| `tests/pos_update_http.py` | info/billing/groups edit, later group rollback, reassignment, portal preservation, missing optional rows |
| `tests/pos_delete_http.py` | account/dependent deletion, later account rollback, accounting choice, missing billing/invoice rows |
| `tests/batch_create_http.py` | batch creation, duplicate/generated-account collision, second-user rollback, PIN/portal cases, named backend |
| `tests/batch_delete_http.py` | batch deletion, stale/shared-identity selection, later batch rollback, unrelated batch preservation |

Adjacent regressions also returned exit 0: `tests/operator_common_reads_http.py`, `tests/operator_reports_http.py` (**197** existing PEAR/PDO display comparisons and CSV producer/export checks), and `tests/invoice_reads_http.py` (**71** existing comparisons plus native PDF/CSV). One parallel R15 invocation failed its HTML/control parity assertion; its isolated rerun passed all 71 comparisons. The reason for that first parallel failure was not established, and no R15 production/test source was changed to hide it.

Nine affected production PHP files passed native PHP lint in the project web runtime. The new Python test compiled, and `git diff --check` passed.

## Explicit remaining dependency and limits

`bill-pos-edit.php` still invokes `userInvoicesStatus`, `userPlanInformation`, `userSubscriptionAnalysis`, `userConnectionStatus` and transitive `checkUserOnline`. These independent PEAR report reads are scheduled in **R20**; their source is unchanged. The no-invoice branch in `userBilling.php` still emits its four historical NULL-offset warnings at lines 213–216, characterized on both native baseline and candidate. R16 does not claim this full page is completely PDO-only or its retained reports warning-free. PEAR therefore remains installed.

Validation is real isolated HTTP/PHP/MariaDB execution on synthetic fixtures, not a full interactive browser, live FreeRADIUS/hotspot/NAS validation, live administrator account validation or production deployment. It does not prove PostgreSQL equivalence. No connection factor, token or sensitive credential snapshot is retained.
