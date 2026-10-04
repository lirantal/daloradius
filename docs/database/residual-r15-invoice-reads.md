# R15 — invoice list, report and form reads

## Scope and baseline

Base: `63a7a88eb6f5d5a66c34e793e47df73528c832c5`; branch: `refactor/pdo`.
Implementation was performed without subagents. The five original members were
rechecked against the R15 row of `finalization-lots.csv`:

| Slice | Members |
|---|---|
| R15a | `bill-invoice-list.php` (RES-022), `bill-invoice-report.php` (RES-024), strict invoice read provider and shared invoice CSV builder |
| R15b | `bill-invoice-new.php` (RES-023), `bill-invoice-edit.php` (RES-021): status/type/user/plan/customer/detail/item reads |
| R15c | `bill-invoice-del.php` (RES-020): remaining invoice selector, complete differential fixture and progress/report documents |

The invoice sidebar already uses the R02 PDO selectors. It needed no source change;
its actual form controls and strict selection behavior are exercised through the
real five page prologues, including PEAR connection tripwires. Seven production
files are changed: the five pages, one explicit read provider and the existing
invoice-export builder. No schema or write-provider implementation is changed.

## Implementation and retained contracts

`library/invoice_reads_pdo.php` opens the session-selected backend explicitly and
validates/quotes the invoice-family table identifiers. Its prepared row reader
borrows its supplied PDO, fetches numeric rows, closes every cursor and never
begins, commits or rolls back a caller transaction. Data filters and numeric
identities are bound; sorting stays on the pages' existing allowlists and pagination
uses integer offsets/limits.

The report display shares the established invoice CSV SQL builder. The invoice
list keeps exact username-to-billing-ID lookup and its separate user/status filters.
Both count the actual grouped/joined dataset before a paginated SELECT; they do not
use PDO SELECT rowCount. The inner billing/status joins, separately aggregated
items/payments, COALESCE totals, decimal display and paid-minus-billed balance are
preserved. Multiple children and payments cannot multiply each other's sums.
A missing type still has its old report/list behavior; the edit detail keeps its
additional mandatory type join.

New/edit status, type, user and active-plan choices retain their original source
and ordering. New-form statuses retain the shared selector's value ordering;
edit selectors keep prefixed keys. Native PDO integers are converted to strings
for the edit header IDs because the shared sidebar's strict selection historically
receives PEAR string values. Existing item IDs, selected plans, amounts, taxes,
notes and notification/payment buttons stay intact. All render-dependent customer
and item reads finish before emitting the form. NULL display values and previously
uninitialized blank new-item fields render as empty strings without PHP warnings.
Dynamic plan markup is JSON-encoded when embedded into the form's JavaScript.

UNIT-005/006/007 write providers retain their existing owned PDO transactions.
Preparatory/display reads are not inserted into those transactions and do not
commit another handle. A display failure after a successfully committed create
or delete retains its success message alongside the read failure; the already
committed mutation is not reported as rolled back. Delete options are loaded before
printing action messages to avoid duplicating a successful-delete notice on error.

## Scoped repairs and failure behavior

- An absent explicit list username yields no invoices instead of falling through
  to every invoice. The pinned PEAR page's unfiltered result is characterized.
- Literal zero and percent filters are retained; the shared invoice CSV builder
  applies zero-string username/status filters just as the displayed report does.
  The report's percent-containing search remains SQL LIKE, not a new literal-LIKE policy.
- Raw identities are URL-encoded in real customer links. Sort/pagination links keep
  active filters and correctly prefix their appended query fragment with `&`.
- The report accepts the actual sidebar's `invoice_status_id` name in addition to
  its historical `invoice_status` control, with an explicit old-name value winning.
- Regex date/ID validation checks a match (`=== 1`), not merely a non-error result.
  The existing previous-month date fallback and midnight date bounds remain intact.
- Array-typed list/report controls and CSRF inputs cannot reach scalar PHP functions.
- Missing mandatory joined invoice/customer data fails closed with no phantom edit form.
- Read errors use `Unable to read invoice data`, not driver messages or SQLSTATE.
  Logs contain a class/query template, not bound values or connection factors.
- The report installs its CSV descriptor only after both count and data reads
  succeed. Empty/error reports and the nonexporting invoice list clear stale state.

## Executed validation

`python3 tests/invoice_reads_http.py` returned **exit 0** with **71 complete
PEAR/PDO comparisons** on isolated native PHP/MariaDB. The original five pages
and invoice CSV builder are pinned at the base commit in an otherwise equivalent
application copy; schemas are initialized identically in independent databases.
No live configuration, records or sessions are copied.

Coverage includes:

- Full rows, footer totals and form inputs/textareas/options/selected values,
  not just page statuses. Random default new-item names and CSRF are excluded
  deliberately, but existing edit item IDs and values are preserved in comparison.
- Every offered sort key in both directions on complete results; undefined ordering
  within contact/status ties is compared as row sets with checked monotone sort keys.
  Separate two-row pagination tests retain ordered results.
- User/status/date filters, all invoice child/payment aggregates, empty invoices,
  NULL aggregates/labels, nonexistent invoices/users and empty datasets.
- Three complete parsed invoice CSV parity cases, plus a candidate zero-username
  CSV with exactly the expected single invoice. Real generated sort/customer URLs
  are followed or decoded with special quote/percent/plus/ampersand/Unicode identities.
- Default and distinct named backends with configured invoice-family table names;
  the selected edit response contains a named-only invoice note. Actual SELECT-only
  accounts execute all five routes and a distinct named edit route.
- Login redirects, denied source ACLs, malformed controls, bound injection attempts,
  stale descriptors, invalid configured identifiers and no-PEAR connection tripwires
  on all five routes, including their actual sidebar.
- Real native later SELECT failures on **each** route: after a successful count on
  list/report, after header reads on edit, after options on new, and during the
  delete selector. Fixture-only synchronization renames a real column immediately
  before the targeted SELECT. No hook or schema change remains in production.
- Native successful create/delete followed by a forced display SELECT error, with
  persisted committed state checked independently from the read-failure message.
- A caller-owned transaction remains active after both a successful strict read
  and a native SQL error; only the fixture caller rolls it back.
- Complete invoice/item/payment state invariance across read-only requests;
  candidate logs contain no PHP warnings, deprecations, fatals or SQLSTATE output.
- Actual native PDF preview and download through the existing notification endpoint
  for both baseline and candidate: HTTP 200, PDF content type and `%PDF-` payload.
  This exercises the still-PEAR notification consumer; it does **not** migrate R20.

All six actual regression suites returned checked exit 0:

1. `tests/invoice_create_http.py` (UNIT-005), including late-child rollback.
2. `tests/invoice_edit_http.py` (UNIT-006), including full replacement rollback.
3. `tests/invoice_delete_http.py` (UNIT-007), including later-parent rollback.
4. `tests/report_export_http.py`.
5. `tests/operator_common_reads_http.py`.
6. `tests/report_export_differential.py` with `DALO_SOURCE_ROOT` set to this worktree:
   18 native export cases, all export statuses 200 and a nonempty invoice CSV.
   This regression run is candidate execution, not a new global historical CSV
   byte-comparison claim. R15's three pinned invoice CSV comparisons are separate.

`tests/report_export_descriptor.test.php` also passed in native PHP. A historical
`tests/report_export_matrix.py` command was found absent; it was not counted as
passing and the actual checked-in differential/export scripts above were used.
PHP lint passed for all seven production files; Python compilation and Git diff
whitespace checks passed. Test connection material is generated only in the
short-lived fixture and never returned/snapshotted. Fixtures/containers/networks
are removed; the pre-existing untracked `tests/__pycache__/` is preserved.

## Limits and delivery

These are synthetic-data native HTTP/PHP/SQL/PDF executions, not a full browser
session, PDF visual inspection, external SMTP delivery or production deployment.
No new performance or cross-query consistent-snapshot guarantee is claimed.
Read errors after committed writes do not undo those writes. The existing write
providers' documented concurrency and referential-integrity limits remain intact.
Notifications, user-portal invoices and other active PEAR consumers remain for
their respective lots; PEAR dependencies are not removed here.

Three local slice commits only. No push, PR, service deployment or persistent
fixture. R16 has not been started by this lot; no historical residual count is
subtracted to invent a current migration remainder.
