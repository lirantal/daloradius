# R08 — residual huntgroup pages

## Scope and baseline

R08 closes RES-157, RES-158, RES-159 and RES-160 from the frozen residual
inventory. The branch remains `refactor/pdo` in `retho-p/daloradius`.
Pinned unmodified PEAR page baseline: `71bb1985b1b449525b93197fb6a71657e0bab2d0`.

- R08a: `mng-rad-hunt-new.php`, `mng-rad-hunt-edit.php`, and the explicit
  `library/huntgroup_pages_pdo.php` provider.
- R08b: `mng-rad-hunt-del.php`, `mng-rad-hunt-list.php`,
  `tests/huntgroup_pages_http.py`, this report and the progress update.

No shared PEAR dispatch/provider or dependency is removed. Existing migrated
authorization, selected-location PDO connection and selector contracts are reused
without turning still-PEAR callers into PDO implicitly. No schema/configuration
migration or live FreeRADIUS change is part of this slice.

## Contracts and connection boundary

The configured `CONFIG_DB_TBL_RADHG` identifier and list sort columns/direction are
allowlisted; data and pagination values are bound. Each mutation acquires a
schema/table-scoped advisory lock on the same nonpersistent PDO connection as its
complete InnoDB transaction. A metadata lock precedes engine preflight; an already
active caller transaction is refused rather than committed/rolled back implicitly.

Creation/edit retain the global NAS-address/port pair collision rule across group
names, while an edit excludes its own locked ID. The same address on a different
port and the same port on a different address are permitted. Numeric port comparison
is explicit even though the stock column is varchar: an existing zero-padded port
still collides numerically with the corresponding canonical submitted number.
SQL NULL is not implicitly converted to zero by the pair predicate.

Blank/missing port defaults to zero and leading zeroes are normalized. Ports are
validated nonnegative decimal strings, preserving numeric values without integer
formatting/overflow; negative, signed, fractional, exponent, suffix-bearing and
array inputs are refused instead of the old `intval()` coercion. Actual configured
column character capacities and exact stored-value readback are checked before
commit, protecting against truncation and charset conversion in permissive SQL mode.
Stock columns are group name varchar(64), NAS address varchar(15), and nullable
port varchar(15). Existing unedited NULL values remain unchanged in reads/state.

Deletion parses the whole scalar/flat-array selection, requires canonical stock
unsigned `huntgroup-ID` tokens, deduplicates/sorts it and locks/rechecks every
selected row before the first DELETE. A stale/malformed later item or a selection
at PHP's input truncation boundary rejects the complete operation. Later SQL failure
rolls back earlier deletes; success reports the actual number of distinct rows.
The post-write catalog has the existing getter's option label/order but propagates
read failures to the page. Shared `get_huntgroups()` keeps its original empty-on-error
contract for other callers.

The list preserves column order, tooltips, edit/delete producers, sort/page defaults,
NULL display and paging through explicit COUNT/fetched rows. Array-typed sorting
controls safely default. Creation's actual edit link is followed in the test, and
maximum stock unsigned IDs round-trip through real edit/list/delete controls.
Names containing literal percent, zero, quotes and Unicode remain data instead of
being damaged by historical percent stripping. HTML escaping remains separate from
IDs/transport; the original form and browser-side address pattern are retained.

## Native validation and characterized legacy defects

Command: `PYTHONDONTWRITEBYTECODE=1 python3 -u tests/huntgroup_pages_http.py`.
Runtime: PHP **8.4.24**, MariaDB **11.8.9**. Separate equivalent disposable
baseline/candidate schemas use the unmodified pinned PEAR pages and the real PHP
HTTP routes. Operator sessions are seeded only inside the fixture: these tests
exercise actual session/expiry/ACL/CSRF gates, not a real operator credential login.

- Ordinary create/edit/delete match complete persisted state. **35** sort/page/form/
  default comparisons pass across all four sort columns, both directions and
  four pages, including NULL rows and actual form controls.
- The unmodified edit rejects an unchanged pair, including a group-name-only
  rename, because its duplicate query includes its own row. That exact legacy
  defect is characterized separately; the candidate succeeds on unchanged edits
  and rename-only edits, and still refuses another row's pair.
- The unmodified delete ignores stale later selections and preserves an earlier
  deletion when a later trigger fails. The candidate instead rejects/rolls back
  the whole selection. A separate connection physically removes a later row
  between preview and POST to exercise the real stale-confirmation path.
- Native INSERT/UPDATE/later-DELETE triggers, non-InnoDB engines, borrowed caller
  transactions, malformed/nested/truncated controls and unsigned-ID overflow are
  tested against unchanged complete state. Successful cross-group multi-delete
  preserves all other rows; duplicate IDs count once.
- Port default, canonicalization, capacity, maximum stock-width decimal,
  numeric collision with existing zero-padded storage and NULL-versus-zero pair
  behavior are exercised. Legacy acceptance/coercion of a suffix-bearing number
  is characterized; candidate refusal is an intentional validation improvement.
- The unmodified permissive insert reports success for an over-capacity IPv6
  address after truncating it. The candidate refuses it on the stock column and
  preserves full IPv6 on an explicitly widened disposable column; this is not
  a schema upgrade or a browser/FreeRADIUS IPv6 support claim.
- Charset coercion rolls back; a 64-character multibyte name round-trips and an
  over-capacity edit is refused without mutation. Special names round-trip through
  actual edit/list/delete controls without corrupting their values.
- Separate sessions exercise concurrent creation (one winner), serialized edits
  (a coherent winning tuple) and deletion (one winner). An independent SQL
  connection holds the actual advisory lock; the page waits and succeeds after
  release. Locks coordinate these pages, not arbitrary external SQL writers.
- Selected backends and custom tables cover all four pages, with default-backend
  invariance. Invalid/missing tables, a failing page SELECT after successful COUNT,
  empty catalogs, SQL-debug redaction, provider direct-access refusal and candidate
  legacy-open/close tripwires are tested. Candidate PHP diagnostic checks pass.

Focused native regression suites passed:

- `tests/selectbox_reads_http.py`
- `tests/operator_common_reads_http.py`
- `tests/operator_sidebars_catalogs_http.py`
- `tests/ip_pool_pages_http.py`

PHP lint, Python syntax compilation without cache output, the scoped residual
PEAR-call scan and `git diff --check` are static checks required before commit,
not substitutes for the native evidence above. No browser JavaScript, actual
FreeRADIUS huntgroup matching, primary credential login, installation/upgrade,
engine matrix or live deployment is claimed. SQL trigger rollback does not prove
crash recovery or an unambiguous outcome after connection loss during commit.

## Cleanup and coexistence

Fixture databases/app copies/configuration/sessions/logs are temporary. MariaDB
storage is tmpfs, networks are private/internal, and exact test container/network
removal is checked. No live application configuration or data is copied; connection
and session material is not retained in reports/comparison files. The pre-existing
`tests/__pycache__/` directory is preserved. No push, PR or deployment is included.
PEAR remains installed until the other active/indirect families are migrated and
the final dependency/installer gate is explicitly validated.
