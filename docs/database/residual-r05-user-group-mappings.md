# R05 — residual user/group mappings

## Scope

Pinned baseline: `fb33d38a505bf8d3a1cfcd3d987b3f67d939932b` (R04d).
Original lot: RES-064/067/069/075 and RES-173–178; five operator pages,
`include/management/functions.php` and the shared `groups.php` widget.

- R05a: `mng-rad-usergroup-new.php` now uses the existing opt-in PDO mapping
  provider on a selected-location connection and a page-owned transaction.
  The missing lazy PDO branch of `delete_user_group_mappings()` is added;
  shared insert/batch/priority PDO dispatch from UNIT-037 is reused.
- R05b: editing validates parent/destination and locks the current mappings on
  one transaction before changing group/priority; its form read is PDO too.
- R05c: deletion supports pair selections, one named pair and all mappings for
  one username, with complete validation/recheck before the first delete.
- R05d: both list pages use PDO, including catalog/fullname join, mapped groups,
  sorting/pagination and raw-name producing controls; the shared widget retains
  both handle types and safe caller ownership.

`library/user_group_pages_pdo.php` coordinates page-local transactions, validated
identifiers, bound statements and full deletion selections. Shared providers keep
their borrowed-handle contract: no connection open/close, no commit, and failure
propagation so dependent caller work can roll back. PDO deletion dispatch is tested
with a real earlier caller INSERT and an explicit rollback. There is no schema DDL.

## Contracts and intentional repairs

Ordinary PEAR/PDO create, rename and delete requests produce the same physical
mapping rows, including IDs and priorities. Editing preserves the legacy behavior
of updating every duplicate row; it does **not** silently adopt the separate
priority helper's delete/reinsert duplicate-collapse policy. Unchanged updates
succeed after a locked existence check. Nonreserved negative priorities normalize
to zero; the disabled-users group normalizes to -1; malformed/overflow priorities
are rejected before writes.

Creation/editing validate existing authentication parent rows and group definitions
using the existing provider. Competing page mutations share its parent locking;
two real PHP sessions create, rename or delete a mapping with one winner. Without
universal uniqueness/foreign keys/cooperation from external writers, this is not
an all-writer global concurrency guarantee.

Deletion validates the entire selection, deduplicates it, locks existing parents
and mappings and removes the exact locked IDs in one transaction. A malformed,
stale or ambiguous later selection or a later DELETE error restores earlier rows.
Duplicate physical rows contribute to the actual affected-row count. Cleanup of
orphaned mappings remains allowed; a parent need not exist, but transactional
engines are required. Near-limit PHP input batches fail closed rather than applying
a truncated subset.

Characterized repairs, not claimed baseline parity:

- The legacy username-only deletion path uses uninitialized/local variables and
  does not implement its intended all-groups selection. The pinned native fixture
  leaves those rows unchanged; the candidate deletes all of that user's mappings.
- Per-user lists counted distinct groups but selected physical rows. With duplicate
  mappings this can understate pagination and make rows unreachable. Counts now
  match the physical row SELECT, while duplicates themselves remain visible.
- Exact identities preserve percent/plus, quotes, ampersands, Unicode and scalar
  `0`; HTTP URLs encode raw names, not HTML-escaped strings. The global LIKE search
  retains historical removal of `%` from its filter; the per-user exact lookup does
  not strip characters from the stored identity.
- Ordinary checkbox values keep `username||group`. Names containing the delimiter,
  or a username starting with the encoded prefix, use a canonical `mapping:` token
  with separately percent-encoded components. Legacy ambiguous tokens fail closed;
  literal percent characters in ordinary tokens are never decoded a second time.
- The shared widget escapes server-rendered names, hex-escapes script JSON, keeps
  string identity `0`, assigns new group names through the DOM value property and
  uses a monotonic ID counter. Removing a row can no longer reuse another row's ID.

PDO queries/debug logs contain templates rather than bound values. Fixed HTTP
error notices do not expose driver details. A successful mutation followed by a
failed display read remains a committed mutation; reads do not share ownership of
its completed write transaction.

## Coexistence

PEAR branches in `functions.php` and `groups.php` remain available for unmigrated
callers, including remaining POS/other page reads. They are not converted implicitly
and the dependency is not removed. Shared insert/priority/batch dispatch already
existed and is verified, not duplicated. The previous provider fixture now pins the
old PEAR creation page, because its current production counterpart is PDO after R05;
that preserves genuinely PEAR compatibility coverage rather than relabeling PDO.

## Executed evidence

`tests/user_group_pages_http.py` performs actual HTTP requests to PHP workers and
MariaDB 11.8 on an isolated internal Docker network with separate deterministic
baseline/candidate schemas. No production configuration/data is copied.

Verified:

- ordinary create/rename/deletion state, edit controls, and 16 list sort/direction/page
  projections with actual page boundaries;
- real PDO and PEAR widget rendering and borrowed transaction/earlier-write rollback;
- unknown parents/destinations, existing destinations, malformed/non-scalar controls,
  stale selections, PHP-truncated batches and accurate duplicate deletion counts;
- later UPDATE and later DELETE failure invariance using full physical mapping rows;
- duplicate-preserving and idempotent edits, signed priority bounds, disabled-group
  priority, nontransactional-engine refusal and repaired all-groups deletion;
- raw special-character/delimiter/zero names through real form values and URLs;
- concurrent independent-session creation, rename and deletion;
- authentication/ACL/CSRF gates, default versus selected alternate schemas, configured
  authentication/group/mapping/userinfo table names, late read error redaction and
  candidate script logs without warnings/fatals (the deliberate PHP input-limit
  startup warning is expected and distinct);
- throwing candidate legacy-open/close tripwires throughout migrated page execution.

`tests/group_widget_js.py` additionally executes the actual widget JavaScript using
Node and a small synthetic DOM: safe name assignment, string `0`, reserved priority,
and delete/add ID uniqueness. This is **not** a real-browser visual/interaction test.

Focused regression evidence includes the shared mapping suite (including the pinned
PEAR page and real second-connection parent-lock contention), R03 creation/import,
user editing and user-action flows. The executed regression commands are:

- `tests/group_mappings_http.py`
- `tests/user_create_import_http.py`
- `tests/user_edit_http.py`
- `tests/user_actions_http.py`
- `tests/pos_provision_http.py`
- `tests/pos_update_http.py`
- `tests/group_profiles_residual_http.py`

Static verification includes PHP lint, Python
syntax and diff whitespace checks. Test containers/networks and disposable data are
removed; no live stack, deployment, external authentication or RADIUS test is claimed.
No PEAR dependency removal, push or PR is part of this lot.
