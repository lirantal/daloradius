# R11 — Standard operator accounting reports

## Scope and baseline

Pinned PEAR pages: `1160e4bfb6f1744c6eb5c3b8ac0fae8d154dc898`.
Branch: `refactor/pdo`. No push, PR, service rebuild or deployment.

| Slice | Pages | Historical residuals |
|---|---|---|
| R11a | `acct-active.php`, `acct-all.php`, `acct-date.php`; shared accounting PDO provider | RES-008, RES-009, RES-011 |
| R11b | `acct-ipaddress.php`, `acct-nasipaddress.php`, `acct-username.php` | RES-014, RES-016, RES-018 |
| R11c | `acct-hotspot-accounting.php`, `acct-hotspot-compare.php`; differential tests and documentation | RES-012, RES-013 |

All eight page-local accounting queries and tooltip existence lookups now use
an explicit, selected-location PDO handle. The existing lazy connection policy
and CSV builders remain shared; untouched pages are not implicitly converted.
`accounting_pages_pdo.php` shares generic SQL/filter construction with the six
accounting CSV producers, uses explicit COUNT queries rather than SELECT
rowCount(), fetches bounded numeric projections, and binds pagination values.
Existing numbering/rendering functions stay shared with other pages.

## Preserved contracts

- The six generic pages preserve their LEFT JOIN on the stored hotspot MAC,
  column order, displayed time/byte formatting, NULL stop times, and zero
  termination mapped to `Unknown` only in HTML. CSV retains its raw value.
- Date accounting requires a username, defaults to the current month, and
  includes the whole end day through an exclusive next-midnight boundary.
- IP and NAS filters retain their historical substring LIKE semantics.
- Hotspot comparison retains its INNER JOIN, distinct users, hit count,
  sums/averages, and existing chart producers migrated in R09.
- Active accounting retains lifetime totals and permissive GROUP BY behavior.
  Its username/date controls historically do not constrain this query, and the
  NAS page's `only-active` flag historically does not constrain its SQL either.
  R11 does not silently change these policies. Multiple eligible attributes
  for one username remain subject to the historical permissive aggregation.
- ASC/DESC allowlists, ordinary pagination, empty-result messages and ACL gates
  remain in place. Read queries do not start or commit a write transaction.
  Concurrent accounting writers can still make count/data observations differ;
  R11 does not introduce a snapshot contract or guarantee order within ties.

## Explicit repairs and intentional differences

- Literal percent signs and the identity `0` are no longer removed or treated
  as missing. Nested/non-scalar controls fail with HTTP 400 before rendering.
  Hotspot selections are deduplicated; at most 256 values are accepted, each
  scalar request field is bounded to 4096 bytes, and configured page size must
  be a positive decimal in the range 1–10000. Invalid scalar dates retain the
  form's fallback, with actual calendar validation and no undefined captures.
- Raw usernames/hotspot names are kept separately from HTML-escaped labels
  when checking existence and composing links. Pagination parameters are URL
  encoded rather than reusing escaped HTML. NAS accounting links use the
  endpoint's actual `nasipaddress` parameter, not the old `ipaddress` typo.
- Total counts reflect the exact joined dataset, including multiple hotspot
  rows sharing a MAC. Previously some pages counted only the accounting table
  while rendering joined rows, producing inconsistent totals.
- All entries clear previous export state. Empty, invalid or failed reads also
  discard their descriptor; active/comparison pages cannot replay an unrelated
  report. Generic pages do not emit CSV controls after a data-query failure.
  Existing fileExport still checks the source ACL and reconstructs its query.
- Driver errors are caught and reported generically, including failures after
  a successful count. Logged SQL consists of templates, not bound parameters;
  exception logs contain the exception class only. NULL HTML values and the
  active page's tooltip counter no longer cause PHP diagnostics.

## Native isolated validation

Command: `python3 tests/accounting_pages_http.py`.
Runtime observed: PHP **8.4.24**, MariaDB **11.8.9**.

- **221 display comparisons** against pinned, unmodified PEAR pages, covering
  all eight reports, every allowlisted sort in both directions, pagination,
  filters, inclusive dates, NULL values, empty matches and named locations.
- Full unpaginated CSV bytes are compared for all six export-producing pages
  whenever the producing page has an exportable dataset. Empty-source
  descriptor removal is tested as an intentional candidate change.
- SQL has no secondary tie-breaker. Sort comparisons use complete result sets
  and ordered sort-key sequences when equal keys change internal row order;
  pagination checks use unique default keys rather than assuming a legacy
  tie order. No unexplained mismatch is accepted.
- Equivalent custom accounting/hotspot/check/billing tables are used on both
  backends. The named backend has exclusive rows, proving actual routing.
- Real SELECT-only grants, complete accounting-state invariance, malformed
  controls, denied ACLs, zero/quoted/percent/Unicode identities, correct raw
  producer links, large BIGINT IDs, joined-row counts and stale exports pass.
- Missing/invalid/empty sources are tested. For all eight pages, a fixture-only
  synchronization hook renames a relevant column after the real count succeeds;
  the real subsequent SELECT fails and the complete HTTP error/descriptor
  contract is checked. No driver, response or SQL result is mocked, and no hook
  remains in production source.
- Candidate direct-report pages pass legacy-open/close tripwires. Date/username
  pages are deliberately excluded from this *whole-page* tripwire because
  their independent R20 accordion provider remains PEAR; their local queries
  and complete ordinary HTTP/CSV workflows are nevertheless tested natively.
- The NULL termination-cause case explicitly widens only the disposable stock
  schema. Credentials/session identifiers are generated only in disposable
  fixtures; no credentials, hashes or response snapshots are retained.
- Candidate PHP logs are checked for warnings/fatal/deprecation diagnostics.
  Disposable databases, containers, network, app/config/session trees are
  removed and cleanup verified; no live service/data is used.

Five native regression suites also pass:

1. `tests/report_export_http.py` (PDO security assertions enabled).
2. `tests/selectbox_reads_http.py`.
3. `tests/operator_common_reads_http.py`.
4. `tests/hotspot_pages_http.py` (55 R09 comparisons).
5. `tests/geo_heartbeat_http.py`.

PHP lint, in-memory Python compilation and `git diff --check` also pass.
These tests exercise native HTTP/PHP/SQL in disposable Docker fixtures and
synthetic authenticated sessions, not browser interaction, live authentication,
FreeRADIUS accounting delivery, NAS hardware or deployment/performance.

## Retained boundary

`include/management/userReports.php` still opens its own PEAR connection for
independent date/username accordions and belongs to R20. It is not called from
within a PDO mutation. Advanced accounting/maintenance/plan reports remain R12.
Other active consumers and compatibility providers still require PEAR until
R29's refreshed executable-callsite audit and installation/release gates.
This lot completes the eight specified residual blocks, not the entire
migration or PEAR dependency removal.
