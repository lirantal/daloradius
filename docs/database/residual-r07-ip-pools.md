# R07 — residual IP pool pages

## Scope and baseline

R07 closes RES-161, RES-162, RES-163 and RES-164 from the frozen residual
inventory. It does not close unrelated providers or authorize removing PEAR DB.
The worktree remains on `refactor/pdo` in `retho-p/daloradius`.

Pinned unmodified PEAR page baseline: `8c9a749649806929eb1fc63d1a7048c7a1555f6e`.

- R07a: `mng-rad-ippool-new.php`, `mng-rad-ippool-edit.php`, and the explicit
  `library/ip_pool_pages_pdo.php` provider.
- R07b: `mng-rad-ippool-del.php`, `mng-rad-ippool-list.php`, the differential
  HTTP test and this report/progress update.

## Connection and mutation boundary

All page-local business reads/writes use the selected location's PDO factory.
Configured pool identifiers and sort identifiers are allowlisted; data and
pagination values are bound. Creation, duplicate checks, row rechecks, insert ID,
write verification and commit use the same nonpersistent PDO handle. Mutations
hold a database/table-scoped advisory lock on that handle, acquire a metadata
lock, require InnoDB and refuse an already-active caller transaction.

Creation/edit preserve the global text-address collision policy across pool
names. Only `pool_name` and `framedipaddress` are changed by an edit; lease owner,
NAS/station fields, expiry and pool key remain untouched. An unchanged edit is
successful after locked-row/readback verification rather than an affected-row
count requirement. Actual configured column capacity and exact stored field
readback protect against truncation and charset coercion under permissive mode.

Deletion parses the complete scalar/flat-array selection, requires canonical
unsigned `ippool-ID` tokens, deduplicates/sorts them, then rechecks/locks every row
before the first DELETE. A malformed or stale later token, an input selection at
PHP's truncation boundary, or a later SQL failure rejects/rolls back the complete
batch. The reported deletion count is the actual number of distinct deleted
rows. The post-transaction catalog uses the same option projection/order as the
existing selector but propagates errors to the page instead of treating a failed
query as an empty catalog; the shared `get_ippools()` contract is unchanged.

The list uses explicit COUNT and fetched page rows, preserving column order,
expiry presentation, sorting, paging, edit/delete producers and username actions.
It reuses the existing borrowed-PDO `user_exists()` dispatch on raw usernames.
HTML escaping and URL encoding are separate; filter state survives sorting and
pagination. Literal zero and percent are data in stored names. Search retains the
historical policy of removing percent signs from the LIKE input (underscore is
still a LIKE wildcard); searching a literal percent-containing name is therefore
not an exact-name search. No schema or FreeRADIUS configuration is changed.

## Native evidence and intentional differences

`PYTHONDONTWRITEBYTECODE=1 python3 -u tests/ip_pool_pages_http.py` passed using
PHP **8.4.24** and MariaDB **11.8.9**, with separate equivalent disposable schemas
and a pinned baseline in the same app fixture. These are real PHP/HTTP/database
executions, not driver mocks or production traffic.

- Ordinary create/edit/delete match full physical pool/lease state. **77**
  sort/page/filter/form comparisons pass, including all nine sort columns, both
  directions, populated/empty filters, and real edit/delete controls.
- Unmodified PEAR characterization confirms that a stale later selection is
  silently ignored and that an earlier DELETE persists when a later DELETE
  trigger fails. The candidate instead preserves the complete pre-write state.
- Real later-DELETE, UPDATE and INSERT triggers test rollback; an independently
  deleted row between preview and confirmation tests the stale selection path.
  Successful cross-pool multi-delete preserves every other row and lease field.
- Malformed/nested/truncated controls, oversized strings, duplicate addresses,
  non-InnoDB tables and active borrowed transactions are refused without mutation.
  The maximum stock unsigned ID round-trips through actual edit/list/delete forms.
- The stock `framedipaddress varchar(15)` causes the permissive PEAR IPv6 insert
  to report success after truncation. This exact defect is characterized, not
  reported as parity. The candidate refuses over-capacity input, accepts a short
  IPv6 value fitting stock capacity, and preserves full IPv6 values on an
  explicitly widened **disposable** column. Browser-side IP patterns are unchanged;
  these POST tests do not claim an end-to-end IPv6 browser or RADIUS allocation flow.
- Charset coercion is detected and rolled back. Special/Unicode/percent/zero
  stored names round-trip through real forms; raw filter links are checked.
- Independent sessions concurrently create, edit and delete. A separate SQL
  connection holds the actual advisory lock, and the page waits before succeeding.
- Authentication/expiry redirects, ACL/CSRF, selected database locations,
  configured/invalid/missing tables and later username-action reads are exercised.
  SQL-debug output contains templates, not bound sentinel data. Candidate legacy
  open/close tripwires and PHP diagnostic checks pass.

Focused native regression suites also passed:

- `tests/selectbox_reads_http.py`
- `tests/operator_common_reads_http.py`
- `tests/operator_sidebars_catalogs_http.py`
- `tests/user_group_pages_http.py`
- `tests/dictionary_pages_http.py`

PHP lint, Python syntax compilation without cache output and `git diff --check`
are required before commit. These are static checks, distinct from native evidence.
No JavaScript browser, actual FreeRADIUS lease allocation, supported-engine matrix,
installation/upgrade or live deployment was tested for this slice.

## Coexistence, limits and cleanup

Shared PEAR dispatch/providers and the dependency remain for other callers; no
legacy package or installer is removed. Advisory locks serialize these cooperating
pages, not arbitrary external SQL writers. The native tests prove the stated SQL
failure/rollback cases, not connection loss during commit or process-crash recovery.
Existing browser validation patterns, IP textual-equivalence rules and dynamic
lease allocation policy remain unchanged.

All fixture databases, app configuration, sessions, runtime logs, tmpfs containers
and private networks are disposable; container/network removal is verified. No
live data/configuration or credentials were copied. Fixture connection/session
material is not retained in reports or comparison artifacts. Any pre-existing
`tests/__pycache__/` directory is left untouched. No push, PR or deployment is part
of R07 completion.
