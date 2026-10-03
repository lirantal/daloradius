# R04 — residual profiles and group attributes

## Scope and coexistence

Baseline: `527cebeeae351cbd074283e192419a3509e0a869` (R03d).
The frozen R04 lot contains RES-115/116, RES-149–156 and RES-167–170.
Its original file list is eleven operator pages plus `library/attributes.php`.

- R04a: profile creation/editing, including both check/reply families, use one
  explicit, selected-location PDO connection and one atomic write transaction.
  The existing UNIT-023 `handleAttributes()`/exact-lookup PDO dispatch is reused;
  `attributes.php` does not need another migration or a duplicate provider.
- R04b: groupcheck/groupreply attribute selection, validation, lock and deletion
  use PDO; the whole selection is checked before the first deletion.
- R04c: groupcheck list/search and remaining edit-form read use PDO.
- R04d: groupreply list/search/edit-form reads and profile catalog/edit rendering
  use PDO, retaining the dictionary join, controls, filter and pagination contracts.

`library/group_profiles_pdo.php` centralizes configured-table validation, prepared
queries, profile transaction coordination and attribute batch deletion. Values are
bound; identifiers and sorting are allowlisted; debug output contains SQL templates,
not bound values. Read/write errors expose fixed messages rather than driver errors.

The shared attribute PEAR branches remain intentionally available for compatibility
and are exercised separately by `attribute_engine_cli.py`. Retaining these branches
is not a claim that every old production consumer remains active: the R04 profile
callers are now PDO, the standard/quick callers were migrated in R03, and the group
create callers already pass PDO. No PEAR package or installation dependency is removed.
No unrelated group-create option reads, R06 pages or release work is folded into R04.

## Atomicity and limits

Profile create/edit validates all attribute controls first, preflights the three
configured group tables as InnoDB, takes a database-scoped advisory lock, rechecks
existence and executes the shared attribute engine within the same transaction.
A later reply INSERT/UPDATE failure restores earlier check writes. Foreign/stale
attribute IDs are rejected by the shared engine. Empty creation and colliding names
are rejected without writes. Lock release after commit is best-effort and cannot
convert a committed success into a reported rollback.

Standalone attribute deletion validates the complete scalar/flat-array selection,
deduplicates/sorts IDs, preflights InnoDB, locks/rechecks every ID, deletes and commits
as one transaction. A malformed/stale later ID or later DELETE failure preserves all
selected rows. The actual affected-row count is reported after commit. This path
removes attribute records only, as before; whole-profile/user/plan cleanup remains
the separate existing profile deletion workflow.

The advisory lock serializes cooperating R04 profile creates/edits. There is no
universal cross-table uniqueness constraint or coordination with every external
writer/other group workflow; this is not a guarantee against arbitrary external
concurrent inserts. No schema migration is required. MySQL/MariaDB is the runtime
validated engine; no PostgreSQL or production/RADIUS protocol claim is made.

## Preserved behavior and intentional repairs

- Ordinary check/reply profile creation and editing produce the same physical row
  state as the pinned PEAR pages. Scalar attribute deletion has matching final state.
- Lists/search preserve selected rows, values, checkbox identities, sorting directions,
  page boundaries and the historical removal of `%` from search input. Search values,
  including SQL-looking/quoted strings, are now bound instead of interpolated.
- The profile catalog still unions the two attribute families and counts distinct
  mapped usernames; a usergroup-only name is not suddenly added to that catalog.
  Legacy `ORDER BY users` has no secondary key: tied identities may land on different
  LIMIT boundaries. Tests compare count ranks and full membership across all pages,
  not an invented deterministic order within those ties.
- Literal attribute `0` is data. Profile names keep `%`, `+`, quotes, ampersands and
  Unicode; encoded edit/delete URLs use the raw name, not HTML-escaped bytes. The
  valid scalar name `0` is handled consistently by create and edit rather than
  being rejected by PHP `empty()` in the editor.
- The pinned profile edit page constructs its permission include from `$configValues`
  **before loading configuration**. A denied fixture operator could reach its edit
  form. Configuration now loads before path-based permission inclusion; the candidate
  redirects that same denied operator. This characterized bootstrap defect is a
  security repair, not a claim of baseline ACL parity.
- Legacy partial application/invalid-selection skipping is not preserved: malformed
  profile controls or a stale later deletion invalidate the entire operation.
  Error notices and affected-row counts are corrected rather than copying legacy
  misleading success messages.

## Executed verification

`tests/group_profiles_residual_http.py` runs actual PHP HTTP requests and MariaDB
11.8 in an isolated internal Docker network, using deterministic synthetic data,
separate baseline/candidate schemas and PHP workers for concurrent requests.

Verified:

- ordinary PEAR/PDO profile creation/edit state and scalar deletion state;
- 132 list/search sort/direction/page projections, plus quoted/empty/no-match and
  SQL-looking searches, edit-form values and complete page membership;
- forced later reply INSERT/UPDATE and later attribute DELETE failures, with exact
  before/after physical rows (IDs retained, password-like values excluded);
- malformed/empty/colliding profile controls, stale/foreign IDs, zero values and
  literal special-character names/links;
- non-InnoDB refusal, two independent concurrent creates yielding one profile;
- authentication/ACL/CSRF gates, the characterized legacy edit ACL omission,
  selected alternate locations and configured/renamed tables including edit writes;
- fixed-message late read failure and candidate logs without PHP warnings/fatals;
- throwing candidate `db_open.php` and `db_close.php` tripwires throughout the suite,
  proving these migrated HTTP routes no longer open/close PEAR connections.

Focused native regression suites passed:

- `group_attributes_http.py`
- `profile_delete_http.py`
- `profile_duplicate_http.py`
- `attribute_engine_cli.py` (PDO and retained PEAR dispatch, password hashing)
- `user_create_import_http.py`
- `group_mappings_http.py`

PHP lint for all twelve changed/new PHP files, Python AST validation and
`git diff --check` passed. Disposable fixture containers/networks are removed;
no live configuration, application data or persistent services are changed.
This is real isolated PHP/HTTP/database validation, not production deployment,
external authentication or RADIUS hardware validation. No push or PR is performed.
