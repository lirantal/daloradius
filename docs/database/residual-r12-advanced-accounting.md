# R12 — Advanced accounting reports and scoped purge

## Scope and coexistence

Baseline: `64c46d949c1bf1e8e4f927c7bf06a0d96a7196b9` (R11c).
The three planned residuals are RES-010 (`acct-custom-query.php`), RES-015
(`acct-maintenance-delete.php`) and RES-017 (`acct-plans-usage.php`).

- R12a: schema-derived custom projection, bounded sorting/pagination, prepared
  predicates and PDO username tooltips, via `accounting_advanced_pdo.php`.
- R12b: PDO form reads and a caller-independent, owned purge transaction.
- R12c: plan usage display/count on the existing plan CSV query builder,
  structured export state, isolated differential tests and this report.

Selected locations use the existing PDO resolver. Configured accounting,
RADIUS-user, billing-user and plan table identifiers remain allowlisted.
Metadata borrows the PDO handle through the already-migrated R02 helper; its
PEAR branch is unchanged. No legacy connection opens/closes or PEAR result
methods remain in these three page-local paths.
The independent plan/subscription/connection accordions in `userReports.php`
remain on PEAR for R20. Other consumers and the PEAR package are not removed.

## Selection and retention policy

These pages deliberately have different boundaries; they are **not unified**:

| Path | Start | End |
| --- | --- | --- |
| Custom query | strictly after start midnight | strictly before end midnight |
| User/period purge | strictly after start midnight | before midnight following end day |
| Plan usage / CSV | from start midnight, inclusive | before midnight following end day |

Purge still targets only the exact selected visible username and submitted
period, using the database's historical collation for DELETE matching. Existing
min/max dates remain form hints, not a new retention cap. There is no automatic
age policy, no change to accounting cleanup, and no deletion of billing,
RADIUS or plan records. Zero matching rows remain a successful valid operation.
All reversed or missing/invalid intervals are refused before mutation.

The purge refuses a borrowed transaction and non-InnoDB accounting tables,
locks target metadata before engine inspection, then rechecks the exact identity
on the same PDO transaction. Commit is checked before reporting success; errors
roll back and expose only a generic message and exception class.
Concurrent inserts after commit can create newly eligible accounting rows:
this is not an immutable preview-snapshot purge or a universally coordinated
retention service. No live purge or scheduler change was performed.

## Deliberate corrections, separate from ordinary parity

- Scalar/flat-column validation rejects malformed requests and unknown schema
  columns rather than silently replacing the selection. Duplicate columns are
  deduplicated. Real additional columns, including mixed-case names, are usable.
- Literal zero and percent-bearing identities/values survive binding; raw URL
  parameters are not built from HTML-escaped labels. Equality is literal data;
  contains remains a bound SQL LIKE pattern, with its surrounding wildcards.
- SQL debug logs contain templates, not bound values; driver messages are not
  displayed or copied into application error logs.
- Custom/purge pages clear stale export state and expose no new custom CSV
  facility. Empty or failed plan reads clear export state and suppress CSV.
- The unused plan percentage calculation is removed: the pinned PEAR page
  reproducibly throws `DivisionByZeroError` with a zero plan time bank.
- NULL traffic aggregates are normalized for arithmetic: the pinned PEAR page
  reproducibly throws `Unsupported operand types: string + string` otherwise.
  Normal non-NULL display and full CSV output remain differential comparisons.

## Verified execution

`python3 tests/accounting_advanced_http.py` passed against disposable internal
Docker fixtures: native PHP **8.4.24**, MariaDB **11.8.9**. Authentication sessions
and all data are synthetic; baseline page sources are pinned to the commit above.
SQL NOW() is frozen **only in fixture copies** to stabilize the still-legacy
live-session accordions. No production provider clock is altered.

- **52** PEAR/PDO display comparisons: custom/plan filters, every offered sort,
  full result sets and sort-key sequences for undefined ties, pagination,
  narrow projections and plan CSV comparisons covering complete unpaginated rows.
- Explicit strict/inclusive midnight tests; complete accounting-state parity
  after the same PEAR/PDO purge; unrelated usernames and outside-period rows retained.
- Later-row native trigger failure after an earlier DELETE, with nontransactional
  fixture instrumentation proving visit order and full InnoDB state restoration.
- Two independent real HTTP workers/PDO transactions observed simultaneously
  in native `INNODB_LOCK_WAITS`; release produces one committed purge and one
  stale-identity refusal, with all unrelated rows unchanged.
- MyISAM refusal, borrowed-transaction preservation, stale identity, malformed
  controls, ACL/CSRF and injection probes, literal zero/quote/Unicode/percent names.
- Named backend reads/purge and configured table names; SELECT-only report/form
  reads and write denial; actual additional schema columns and empty datasets.
- Fixture-only late schema failure after successful COUNT: generic errors,
  no CSV control and cleared export descriptor. Hook never enters production source.
- Positive legacy-open/close tripwires on custom/purge and unfiltered plan reads;
  clean candidate PHP diagnostics and sanitized database failures.
- Test containers, private network and temporary configuration/session files removed.

Five native regression suites passed:
`accounting_pages_http.py` (221 R11 comparisons), `report_export_http.py`,
`selectbox_reads_http.py`, `operator_common_reads_http.py`,
`acct_maintenance_http.py` (separate open-session maintenance workflow).
PHP lint, Python compilation and `git diff --check` passed.

These are real HTTP/PHP/MariaDB executions on synthetic disposable data, **not**
a complete browser, production-data, FreeRADIUS, NAS/hotspot hardware or multi-engine
validation. No push, PR, deployment, live configuration read or persistent test
service is part of R12. The pre-existing Python cache directory is preserved.
