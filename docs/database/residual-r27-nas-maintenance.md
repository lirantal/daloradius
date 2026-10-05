# R27 — NAS list/export and maintenance reads use PDO

## Scope and baseline

Baseline `a87b9a3ec29505a568192e1ff7e3e5fae9e433f9` (R26b), branch
`refactor/pdo`. This closes RES-045/046/118/165/166 in the five files
listed by `finalization-lots.csv`:

- R27a: `mng-rad-nas-list.php`, `mng-rad-nas-export.php`.
- R27b: `config-maint-disconnect-user.php`, `config-maint-test-user.php`,
  `library/extensions/maintenance_radclient.php`.

Existing PDO connection/read providers and R01 authorization are reused.
No generic adapter, schema change, new write transaction, provider removal,
push, PR or deployment is included.

## Contracts preserved

The NAS list keeps COUNT-based pagination, the same allowlisted sort fields,
directions, out-of-range-page fallback, row projection, controls and secret
visibility policy. SELECT results are buffered and counted with `count`, never
PDO SELECT `rowCount`. NULL presentation values become empty escaped text.
The historical tooltip/link encoding and nonunique-sort tie policy are not
redesigned by this lot.

The full privileged JSON export is independent of current list pagination.
It retains source-page ACL, version 1/2, complete fields, integer/NULL ports,
UTF-8/binary HEX decoding, Base64 descriptors, encoded_fields metadata,
includes_secrets, generated filename, MIME and no-store/nosniff headers.
Shared secrets are intentionally included in this administrator backup as before;
they are not added to logs or saved as test artifacts. Missing tables/connections
now return a redacted 500 JSON error instead of leaking driver details or treating
failure as an empty successful export.

Disconnect's selector retains (nasname, shortname, nas-ID) identity projection.
`RadClient::get_nas` binds its ID, fetches numeric nasname/secret fields on a
private selected-location PDO handle and releases only that handle. A caller's
PDO transaction is never opened, closed or replaced. Lookup errors become a
redacted RuntimeException; a genuinely missing row still reports NAS not found.

The connectivity form still checks existence in **radcheck**, not optional
userinfo/billing records. Its private read handle is closed before the unchanged
configuration-write and radclient invocation. Database failures prevent both
steps and render a distinct unavailable message.

No command-building, shell execution, packet type, custom-attribute, dictionary,
retry/count/timeout or password-validation policy is changed. The CoA/PoD port
remains its dedicated parameter (default 3799), not nas.ports. Existing simulated
command output retains its historical administrator-visible values; R27 does not
claim a credential-redaction redesign of the maintenance UI.

Deliberate robustness changes: array-shaped scalar maintenance controls are
rejected before trim/token/render operations, and array-shaped list orderType
falls back to default. SQL errors display unavailable, not empty/offline success.
These negative cases are candidate checks, not mislabeled legacy output parity.

## Native validation executed

`PYTHONDONTWRITEBYTECODE=1 python3 -u tests/nas_reads_maintenance_http.py`
passed against real PHP HTTP/CLI and MariaDB on an internal disposable Docker
network. The suite pins all five baseline files together and rewrites only the
fixture copy with OPcache disabled. Actual authentication, sessions, CSRF and
ACL providers run on both sides.

**89 keyed PEAR/PDO comparisons** are asserted programmatically:

- empty list/export and populated version-1 export;
- 80 ordered list projections/checkbox sets across all nine sort columns plus
  invalid-sort fallback, both directions and four page requests;
- exact in-memory simulated disconnect/auth commands and executable disconnect
  command/output, missing user and password mismatch;
- version-2 invalid-UTF-8/NUL payload descriptors and NULL fields.

Only exported_at is removed from JSON parity. Secret-bearing HTML/JSON/commands
are retained in process memory only, never printed or written as snapshots.
The test radclient executable consumes stdin, records only an invocation marker
and returns fixed output. It cannot send a RADIUS packet. Simulate is checked
not to invoke it; ordinary disconnect and auth invoke it successfully.

Additional native candidate checks passed:

- full GET pages and real maintenance POSTs under a throwing legacy db_open
  tripwire; actual username preflight and private NAS lookup remain PDO-only;
- malformed username/NAS/CSRF controls and sort arrays, SQL-shaped username,
  invalid NAS selection, hidden/visible list policy and unchanged NAS state plus
  secret equality asserted inside SQL;
- selected alternate schema with real ACL tables, renamed NAS table, successful
  alternate disconnect/auth, missing table failures and later list projection
  failure after COUNT succeeds; no config-write invocation after user-read error;
- source ACL revoked after earlier page rendering: export returns 403;
- caller-owned PDO transaction with a real uncommitted UPDATE survives the
  class-local lookup; caller rollback removes that update; missing ID and
  private lookup SQL failure have distinct redacted outcomes;
- both PHP log streams checked for warnings/fatals/notices/deprecations and
  generated sensitive values; a harmless HTTP log marker proves capture is live;
- containers, internal network, temporary configuration and sessions verified
  absent after teardown; pre-existing tests/__pycache__/ remains untouched.

### Focused regressions

All passed on the combined candidate:

| Native suite | Coverage |
| --- | --- |
| `nas_import_http.py` | preview/confirm, duplicate policies, single-use token, retained PEAR creation, late rollback, binary v2 import, lock wait, nontransactional refusal |
| `nas_management_http.py` | create/edit/delete, no-op/legacy type, malformed controls, stale selection, late rollback, advisory locking, engine checks |
| `operator_acl_reads_http.py` | allow/deny/redirect/403, named location, caller ownership, form catalog parity, redacted failures and direct guards |

Four PHP contract scripts also passed: `pdo-connection.test.php`,
`operator-auth-provider.test.php`, `operator-identity.test.php` and
`operator-management-identity.test.php` (static/unit contracts, not live LDAP).

The five changed PHP files pass native lint. The new Python test passes AST and
in-memory compile; Git whitespace checks pass. Tests introduce no persistent
service or production-data change.

## Limits and remaining work

This is isolated synthetic native runtime validation, not a real NAS disconnect,
FreeRADIUS authentication, full browser interaction or live installation test.
The retained config writer is replaced on both fixtures by an invocation-only
marker to avoid saving posted secrets; its real filesystem persistence behavior
is not certified by R27. Command invocation is genuine shell-to-fixture-binary,
not a real radclient executable or remote server. No PostgreSQL portability or
stable tied-order redesign is claimed.

The five active R27 PEAR consumers are closed. Compatibility branches and
providers remain for separately scoped R28/R29; a fresh complete inventory and
release/installation gates are still required before removing PEAR DB.
