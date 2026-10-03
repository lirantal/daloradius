# R06 — dictionary pages and attribute metadata

## Boundary and slices

Pinned pre-R06 baseline: `603a33bdc24258d822d63d560d649c6881d16a5a`.
Original inventory: RES-056, RES-108/109, RES-114, RES-143–148.

- **R06a:** dictionary creation and metadata editing use a selected-location PDO
  handle, bound values, the existing validated dictionary identifier and InnoDB
  preflight. The page-owned transaction and a schema/table-scoped advisory lock
  encompass collision/existence rechecks and writes. An existing caller transaction
  is rejected without committing or rolling it back.
- **R06b:** deletion fully validates and deduplicates the complete vendor/attribute
  selection before writes, rechecks every key under the transaction, and deletes
  all physical rows of each selected key atomically. A later malformed/stale key,
  an input-limit truncated request, or a later SQL error cannot leave earlier
  deletions committed. Notices distinguish physical row and selected-key counts.
- **R06c:** both lists/search use PDO counts, bound LIKE filters and integer-bound
  LIMIT/OFFSET. Sort columns/directions remain allowlisted. Pagination counts and
  selected physical rows use the same historic type predicate.
- **R06d:** attribute/vendor JSON producers use PDO while retaining response shapes,
  metadata-row predicates, enum ordering, helper descriptors, cleartext-password
  filtering, operator permission gates and allowed methods. The existing parent
  allowlist now includes the actual quick-create/import/POS/group-edit callers,
  with each mapped to its own permission rather than bypassing authorization.

`library/dictionary_pages_pdo.php` coordinates the page-local provider operations.
`include/management/attributes.php` (RES-056) was already migrated indirectly by
UNIT-023/R02 to `get_attributes()`; its current PDO getter is reused, not duplicated.
Its actual rendered widget, including the selected alternate schema and configured
physical dictionary table, is exercised with throwing legacy-open/close tripwires.
There is no schema migration or PEAR dependency removal in this lot.

## Preserved behavior

- Creation refuses an attribute name already present **anywhere in the dictionary**,
  not just in the submitted vendor. This is the legacy global collision policy.
- Types retain valid structural/exotic forms and flags. Unsupported scalar types,
  operators, recommended tables or helpers still normalize to the empty string.
- Editing updates metadata of every matching vendor/attribute row, while preserving
  each row's `Value`, `Format` and ID, including enumeration rows. An idempotent
  update succeeds after the locked existence check. The display selects the first
  physical row, consistent with the old edit query, now ordered explicitly by ID.
- The list/search `(Type <> '' OR Type IS NOT NULL)` predicate is retained, including
  non-NULL empty types. LIKE filters retain historic percent removal and underscore
  wildcard semantics; stored identities are not stripped or HTML-transformed.
- JSON metadata excludes nonempty enumeration `Value` rows and selects the first
  structural row by ID. Enum values and vendor/attribute options remain distinct
  and sorted. Existing helper option/initial-value contracts are unchanged.
- AJAX permission denial remains a bodyless HTTP 403 from the shared ACL bootstrap;
  a JSON parser must not interpret that empty body as JSON. PDO/query errors use
  fixed JSON error bodies without driver details. SQL debug output uses templates,
  not interpolated bound values.

The advisory lock serializes cooperating dictionary **page** creators/editors/deleters.
It is not a universal uniqueness guarantee: the schema has no unique attribute key,
and independent imports/external SQL writers do not participate in this page lock.
Existing import row locks still coordinate its own operations; no new all-writer
concurrency guarantee is claimed.

## Characterized repairs / intentional differences

1. The actual edit form emits lowercase recommendation controls while the old POST
   parser only accepted uppercase names, silently clearing submitted metadata.
   Both compatible spellings are now accepted; conflicting spellings are rejected.
   The native baseline proves the old lowercase submission clears the operator.
2. Legacy list checkbox values were URL-encoded (after HTML escaping), but deletion
   did not decode them and removed percent characters. Real raw names with quotes,
   ampersands, Unicode, plus/percent and scalar `0` now round-trip through form values
   and URLs. Ordinary tokens remain `urlencode(vendor)__urlencode(attribute)`;
   delimiter-bearing names use a canonical `dict:` JSON token. Ambiguous or invalid
   tokens fail closed, before mutation.
3. Legacy deletion required exactly one physical row and silently skipped dictionary
   keys with enum/duplicate rows. Selecting a key now removes all of its physical
   rows, counts them accurately, and refuses a stale later key rather than applying
   a partial selection. This full-selection atomicity is intentional, not claimed
   legacy failure/partial-success parity.
4. The old AJAX allowlist omitted existing shared-widget callers. Those callers now
   work only when their own existing source-page ACL is allowed, with denied cases
   verified separately. This does not remove the parent-page requirement.

Required names and schema-limited fields reject non-scalar, malformed UTF-8/NUL or
oversized inputs before writes. No value truncation is used to silently accept them.
The success notices and descriptors HTML-escape raw identities; URL construction
encodes raw data exactly once for each transport layer.

## Executed validation

`tests/dictionary_pages_http.py` uses actual PHP HTTP workers and MariaDB 11.8 on a
private internal Docker network with separate deterministically seeded baseline and
candidate schemas plus alternate-location schemas. Sessions are synthetic; this is
not a password-login, browser-interaction, production or RADIUS authentication test.
No live configuration/data is copied.

- Ordinary creation/edit/deletion matches full physical database rows, including IDs.
- **66 native comparisons** cover sort/direction/page projections, filters, edit
  controls, attribute/vendor JSON, helper/enum/unknown outputs and the actual widget.
- Full physical-state rollback is checked after later multi-row UPDATE and later
  multi-key DELETE trigger failures. INSERT failure and a succeeding retry are
  checked separately, including fixed error text and advisory-lock release.
- Invalid/scalar-array/oversized controls, global name collisions, stale edits,
  malformed/stale/nested selections, real PHP input truncation and nontransactional
  engines cannot mutate earlier rows. Borrowed caller INSERT/transaction is preserved
  and can still be rolled back after a rejected page-owned provider call.
- Raw special/delimiter/zero identities pass through actual success links, rendered
  options/checkboxes and HTTP actions. Duplicate enum deletion reports physical counts.
- Concurrent independent PHP sessions exercise creation (one insert, one collision),
  deletion (one delete, one stale refusal) and edits (both succeed, one complete final
  metadata tuple, with every enum value/format retained). Tokens are fetched from
  current forms before each operation; unrelated renders rotate the session token.
- ACL/CSRF, old/new parent allowlist gates, selected alternate schemas, configured and
  invalid dictionary tables, late missing-table reads, SQL debug redaction, and
  legacy-open/close tripwires are exercised. Candidate PHP script logs have no warnings,
  deprecations or fatals; the deliberate PHP input-limit startup warning is distinct.

Executed focused runtime regressions:

- `tests/dictionary_import_http.py`
- `tests/user_create_import_http.py`
- `tests/group_profiles_residual_http.py`
- `tests/user_group_pages_http.py`
- `tests/pos_provision_http.py`
- `tests/pos_update_http.py`
- `tests/attribute_engine_cli.py`

`tests/rad_attributes_new.test.py` retains its stateful **synthetic PEAR** fixture by
pinning the pre-R06 page pair explicitly; it passes and labels itself as legacy
characterization. Production PDO coverage is the native R06 suite, not this fixture.
The actual frontend JavaScript is also exercised with Node's synthetic DOM tests:
`tests/dynamic-attributes.test.cjs` and `tests/readonly-info.test.cjs`, **23 passed**.
These are not real-browser visual tests. PHP lint, Python AST syntax and diff whitespace
checks are part of the final verification.

### Pre-existing regression-harness limitation

`tests/readonly_info_http.py` is an older synthetic DB fixture, not the native R06
harness. Its preliminary production `db_open.php` fixture omits the now-required
`pdo_connection.php`; PHP fails before its HTTP endpoint checks. This same missing
required-file failure was reproduced with both the pinned pre-R06 baseline and the
candidate. It is **not counted as a passed regression** or disguised by deleting its
assertions. Real attribute/vendor HTTP contracts are covered by the native R06 suite.

All disposable fixture containers/networks and temporary data are removed after runs.
Pre-existing `tests/__pycache__/` is preserved. No push, PR, live-stack rebuild,
deployment, production credentials or dependency retirement is part of R06.
