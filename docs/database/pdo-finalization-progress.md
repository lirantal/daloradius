# PDO residual migration — progress

Reference inventory: `daloradius-pdo-finalization-2026-10-01`, frozen at
`eaecd81773c95603824825e399b83cbe833e233f`. Its reports remain a baseline, not a
claim that historical counts are still current after each commit.

| Slice | State | Residual scope | Evidence |
|---|---|---|---|
| R01a | Implemented and validated | RES-117 permission gate; RES-080 ACL form renderer | [R01a report](residual-r01a-operator-acl-reads.md), differential HTTP/PHP/MariaDB and targeted regression scripts |
| R01b1 | Implemented and validated | RES-048 MFA configuration | [MFA report](residual-r01b1-operator-mfa.md), native baseline/candidate HTTP/PHP/MariaDB, error and concurrent enrollment tests |
| R01b2 | Implemented and validated | RES-047 editor; RES-005 PDO write; RES-006 PDO read dispatch | [Message report](residual-r01b2-messages.md), native baseline/candidate, rollback and lock contention; PEAR portal reader stays pending R21/R29 |
| R01c | Implemented and validated | RES-049 catalog; RES-112 unused AJAX legacy open; required `get_operators` selector dependency | [Catalog report](residual-r01c-operator-catalog.md), native PEAR/PDO parity, selected locations, caller preservation and legacy-open tripwire |
| R02a | Implemented and validated | RES-081–RES-087 selectors and indirect getter callers; R01c operator selector unchanged | [Selector report](residual-r02a-selectors.md), native PEAR/PDO parity, selected locations, NULL/empty/duplicate options, caller preservation and legacy-open tripwire; producing-form regression suites |
| R02b | Implemented and validated, PEAR compatibility retained | RES-059/061/062/063/065/073/074 common read providers | [Borrowed-reader report](residual-r02b-common-reads.md), native PEAR/PDO/compatibility parity, actual caller INSERT/rollback ownership, named/custom tables and error redaction; legacy page callers remain scheduled |
| R02c | Implemented and validated | RES-106/107 sidebars; RES-171/172 realm/proxy catalogs | [Sidebar/catalog report](residual-r02c-sidebars-catalogs.md), native option/descriptor and row/control parity, sort/pagination, caller preservation, late SELECT failure and no-legacy-open tripwire |
| R03a–d | Implemented and validated | Standard/quick creation, multiline import, associated providers and page-local/AJAX reads | [R03 report](residual-r03-user-create-import.md), native A/B, whole-batch rollback, concurrent collisions, configured tables/locations and PEAR compatibility; R06/R20 indirect families retain their separate scope |
| R04a–d | Implemented and validated, PEAR compatibility retained | Eleven residual profile/group pages and existing shared attribute dispatch | [R04 report](residual-r04-group-profiles.md), native PEAR/PDO state and 132 list/search projections, late rollback, ACL bootstrap repair, concurrent creation, selected/configured tables and no-legacy-open tripwire |
| R05–R27 | Pending | Functional read/write consumers from `finalization-lots.csv` | Split according to the original commit slices and test equivalent fixtures |
| R28 | Pending | Helpers without proven callers and obsolete compatibility branches | Recheck function names, includes/callbacks, tests and known external usages before removing |
| R29 | Blocked by remaining consumers | Legacy providers, PEAR DB package and installation paths | Remove only after a fresh tokenized inventory finds no active/indirect PEAR consumers |

## Per-slice completion rule

- [x] R01a: branch, HEAD, remotes and modifications checked before writes.
- [x] R01a: permission aliases/direct callers and new/edit render callers traced.
- [x] R01a: native differential test and focused regressions passed; SQL failures are redacted.
- [x] R01a: documented behavior preserved versus characterized pre-existing defect.
- [x] R01a: no live configuration/data copied; isolated fixtures destroyed.
- [x] R01b1: MFA baseline/candidate, rejected writes and concurrent enrollment validated.
- [x] R01b2: editor baseline/candidate, full-batch rollback and actual lock contention validated.
- [x] R01b: Docker stderr log channel positively verified; legacy portal reader compatibility tested.
- [x] R01c: native catalog/selector parity, independent caller transaction, AJAX contracts and full legacy-open tripwire passed.
- [x] R02a: nineteen getter contracts plus retained string-SQL helper, custom tables, selected locations, NULL/empty/duplicate options and caller-owned transaction preservation validated.
- [x] R02a: producing-form native regressions passed; no legacy opens in selector fixture; resources removed.
- [x] R02b: native dual dispatch, exact PEAR branch preservation and real caller INSERT/rollback ownership verified.
- [x] R02c: real sidebar/catalog parity, late SELECT failure, configured tables/locations and no-legacy-open tripwire verified; invoice caller-disposal defect characterized separately.
- [x] R03a–d: all four planned slices implemented and validated on isolated native PHP/HTTP/MariaDB; caller ownership, complete import rollback, real concurrent creation, selected locations and configured tables checked.
- [x] R04a–d: all planned residual pages migrated and validated; shared attribute PDO dispatch reused and PEAR compatibility preserved; late rollback, edit ACL bootstrap, sorting/pagination, concurrent creates and fixture cleanup verified.
- [ ] Entire migration: refreshed residual inventory after all functional slices.
- [ ] Entire migration: dependency removal, installer/Docker validation and transverse tests.
- [ ] Entire migration: installed external legacy cron callers verified before deployment.
- [ ] Entire migration: release/PR/push/deployment separately authorized and verified.

R01a, R01b1, the R01b2 operator editor and R01c are complete here. The message PEAR reader
still serves the three user-portal callers and must close with R21/R29. In particular,
permission-bootstrap migration does not
mean every calling page is entirely PDO: its own remaining provider/reads must still
be reviewed. Do not subtract historic block counts to invent a current remainder.