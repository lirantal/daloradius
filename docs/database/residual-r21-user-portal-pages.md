# R21 — user-portal pages, preferences and invoices

## Scope and coexistence

Base: `f41baed8eb6a086764cabe9991ef5814e8fab2c1` (R20).
Covers RES-191/192/193/194/195/196/211/216/217/218, all ten inventoried
blocks in these nine `app/users` files:

- `pref-userinfo-edit.php`: user information permission/read/update.
- `bill-invoice-report.php`, `bill-invoice-show.php`: invoice report and details.
- `include/common/notificationsUserInvoice.php`: native invoice PDF context/download.
- `acct-date.php`: session-scoped accounting report and CSV descriptor producer.
- `login.php`, `home-main.php`, `help-main.php`: configured/purified message reads.
- `include/menu/sidebar/bill/default.php`: actual invoice-status form producer.

`library/portal_pages_pdo.php` is a portal-only provider. It does not import the
operator copy of user reports, rendering functions or authorization. It reuses the
existing selected-location connection factory, the fixed UNIT-004 export-query
builder and R01's PDO message dispatch. Configured identifiers are allowlisted and
validated; data values are bound. Reads are fully fetched before table/document
rendering or export-descriptor publication. Ownership comes from the authenticated
portal session, never from a posted/query `username`.

There are no PEAR query/open/close/error API calls left in these nine files. This
is not a statement that the entire portal is PDO: the separate
`app/users/include/management/userReports.php` accordions called by home remain
PEAR for R22. Their three traced functions alone are permitted through the native
fixture's PEAR tripwire. A dashboard message failure now skips those widgets,
rather than continuing into dependent reads after an unavailable message backend.
PEAR remains installed; the common message compatibility branch is retained until
R29. Its stale caller comment and R01 message regression are updated without
changing that branch's behavior. No schema, live configuration/data, persistent
service, push, PR or deployment is changed.

## Preserved contracts

- Preferences retain their thirteen fields, POST/CSRF form, permission requirement,
  session account, success/refusal notices and missing-field clearing policy.
  Ordinary authorized duplicate rows still update together. Password columns,
  login flags, notes and audit fields are not edited by this page.
- Accounting keeps exclusive start/end bounds, original pre-hotspot-join counting,
  configured page size, all ten sort columns, formatters and optional-hotspot
  fallback. Invoice reports keep inclusive date bounds, status filtering, item
  and payment sums, balance presentation and five sort columns. The full CSV
  remains unpaginated, generated from a typed descriptor and current session
  identity by the existing UNIT-004 exporter; raw session SQL is not revived.
- Invoice details retain first billing-row resolution, customer/form fields,
  joined statuses/types, aggregate totals, ordered items, missing-plan NULLs and
  no-item behavior. All header/item reads complete before customer/detail output.
- The callable `getInvoiceDetails()` retains its public signature and complete
  customer/contact/header/item HTML map. The existing portal PDF processor and
  invoice template are unchanged. Native download headers, filename, complete PDF
  bytes (apart from creation metadata/generated IDs) and documents without items
  are exercised. There is no SMTP/email feature added to this download endpoint.
- Login/dashboard/support messages still use the first message ID and existing
  HTMLPurifier policy, language/form markup, blank/default behavior and login
  CSRF flow. Read failure is an unavailable message (503), not fabricated emptiness.
- Explicit text casts preserve PEAR monetary wire formatting. Monetary ORDER BY
  expressions remain numeric, not lexical text ordering; a fixture crosses the
  lexical/numeric ordering boundary and checks actual SQL key ranks.

## Intentional, demonstrated repairs and fail-closed differences

- The actual sidebar posted `invoice_status_id`, whereas the report reads
  `invoice_status`; the field is corrected and its selection is normalized to the
  integer-valued sidebar formatter. Invoice sort/pagination links previously
  concatenated `orderType` and `username` without an ampersand. Links now preserve
  filters with URL-encoded values. Literal `0` and quote/percent/plus/Unicode
  identities are not treated as empty or HTML-escaped database identities.
- Native baseline LIMIT queries with nonunique sort keys were observed duplicating
  or omitting rows across separately executed pages. A final identity sort makes
  candidate pagination deterministic. Differential checks retain ordered key
  ranks and first-page controls, compare complete candidate page membership with
  the unpaginated native baseline, and require unique candidate identities/counts.
  This is characterized baseline behavior, not claimed identical tied row order.
- The baseline PDF endpoint silently refuses the valid session identity `0`.
  Candidate zero-account PDFs are tested as an intentional repair, not counted as
  PEAR/PDO PDF parity. Missing, foreign or orphan invoice downloads now return 404
  before loading the PDF processor. Malformed/overflow/array invoice IDs or invalid
  destination controls return 400; complete SQL/PDF failure returns generic 503.
  No empty or partially loaded invoice is converted into a plausible PDF.
- Scalar filters/sort controls cannot reach string functions as PHP arrays. Safe
  date validation and sort defaults remain. Invalid calendar filters retain the
  existing empty-filter fallback, not a new date-policy rejection.
- `acct-date.php` redundantly included validation after its language loader had
  already included it. Candidate uses `include_once`; unchanged baseline warnings
  are characterized by exact constant name/source line from the pinned validation
  file. Candidate PHP logs have no warning/fatal/notice/deprecation exemptions.
- Preference writes require actual InnoDB storage, valid UTF-8 scalar fields and
  physical character/octet capacities. One owned transaction locks/rechecks all
  matching user rows and `changeuserinfo`, performs the UPDATE and verifies every
  stored editable field before committing. Mixed authorized/denied duplicates,
  missing accounts, later row-trigger failure, silent value coercion, oversized
  configured columns, NUL/array controls and unsupported engines fail unchanged.
  A borrowed transaction is rejected without commit/rollback/attribute changes.
- Reads on borrowed ERRMODE_SILENT handles detect false prepare/execute/fetch and
  leave the caller's earlier write and transaction intact. Page failures are
  sanitized; a committed preference update followed by an injected display-read
  failure remains visibly committed/unavailable, not falsely reported as rollback.
  Bound private values do not appear in the native SQL debug section.

## Executed native validation

`python3 -u tests/portal_pages_http.py` passed **274 PEAR/PDO comparisons**:
270 native page/context/PDF/CSV comparisons and four canonical preference-state
comparisons. The baseline is pinned to R20, including shared message and portal
widget dependencies; baseline SQL is not modified. For tied pagination only, a
second unpaginated baseline request supplies the full native row projection.

Fixtures use real PHP HTTP servers, MariaDB 11.8 with stock schemas renamed to
configured table names, independent base/candidate/default/named schemas, actual
Dompdf downloads and native session/CSRF producers. Candidate cases additionally
cover:

- Authentication gates, cross-user/orphan invoice refusal, exact IDs/destination,
  array-shaped filters, actual sidebar selection and following filtered links.
- Full unpaginated CSV identities, one-shot descriptors, numeric/NULL totals,
  default and named location reads, zero/raw identities and ordinary empty reports.
- Authorized field/state parity, missing-field clears, bad CSRF/arrays, missing or
  denied account, exact bounded/narrowed storage and preservation of every
  non-sensitive physical userinfo column, including notes/flags/audit fields.
- Actual later duplicate-row trigger rollback, trigger-coercion verification,
  all-authorized duplicate success and mixed-permission duplicate invariance.
- Borrowed silent handle preservation, named-location UPDATE isolation, and an
  independent PHP blocker that revokes permission under a row lock. A second HTTP
  worker is observed in INNODB_LOCK_WAITS before release; its stale permission
  write is refused after revocation commits. This is observed native contention,
  not a synthetic sleep or stale-CSRF substitute.
- Committed UPDATE plus later page-read failure; unsupported engine; absent
  messages; actual SELECT-only SQL grants and write refusal; later table/query
  errors across reports/details/PDF/messages; unsafe identifiers and debug redaction.
- Both stdout and stderr PHP diagnostics. Baseline-only validation redeclarations
  are precisely characterized; candidate paths are warning/fatal-free.

Adjacent regressions also passed, using real isolated PHP/MariaDB execution:

| Script | Executed modes |
|---|---|
| `shared_context_http.py` | R20, including 133 comparisons, native PDF/isolated SMTP/tickets |
| `portal_login_http.py` | Current login, pinned login baseline, PORTAL_CHANGE_TEST, RADIUS_CHANGE_TEST |
| `invoice_reads_http.py` | R15, 71 comparisons, native PDF/CSV and late reads |
| `messages_http.py` | Current R01 and pinned baseline; user login/help dependency pinned on baseline and PDO tripwired on candidate |
| `operator_common_reads_http.py` | R02b borrowed/retained compatibility readers |
| `user_report_export_http.py` | Pre-UNIT-004 pinned baseline and current candidate; actual baseline CSV trace supplied via an in-memory memfd |

These are eleven distinct regression execution configurations across six scripts;
repeated diagnostic runs are not counted as extra coverage. CSV regressions include
principal changes with stale descriptors, raw-SQL tampering, rejected extra filters,
quoted comma fields and spreadsheet-formula neutralization.

PHP lint on all eleven changed PHP files, Python parse/compile, the four PHP
PDO/password/template test scripts and git whitespace checks passed before final
commits. Temporary config/session/SQL/document data
and fixture containers/networks are removed. No credential values, password/hash
snapshots, session/CSRF values, PDF contents or driver messages are retained in
reports/artifacts. The pre-existing `tests/__pycache__/` directory is preserved.

## Boundaries

Validation is isolated native HTTP/PHP/MariaDB/Dompdf execution, not browser
interaction, a real RADIUS authentication, production delivery, a physical
NAS/hotspot, or proof of PostgreSQL/other legacy-engine compatibility. No portal
widgets/graphs from R22, legacy compatibility/dependency removal, installer change
or production operation is included. External writers not taking these row locks
are not promised a new universal account-deduplication policy.

## Local commit slices

- R21a: portal provider and preference read/write consumer.
- R21b: invoice report/detail/PDF and actual invoice-status sidebar.
- R21c: accounting report/descriptor producer.
- R21d: three message pages, compatibility comment, differential/regression tests
  and this report/progress update.
