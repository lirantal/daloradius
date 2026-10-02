# PDO residual migration — progress

Reference inventory: `daloradius-pdo-finalization-2026-10-01`, frozen at
`eaecd81773c95603824825e399b83cbe833e233f`. Its reports remain a baseline, not a
claim that historical counts are still current after each commit.

| Slice | State | Residual scope | Evidence |
|---|---|---|---|
| R01a | Implemented and validated | RES-117 permission gate; RES-080 ACL form renderer | [R01a report](residual-r01a-operator-acl-reads.md), differential HTTP/PHP/MariaDB and targeted regression scripts |
| R01b1 | Implemented and validated | RES-048 MFA configuration | [MFA report](residual-r01b1-operator-mfa.md), native baseline/candidate HTTP/PHP/MariaDB, error and concurrent enrollment tests |
| R01b2 | Implemented and validated | RES-047 editor; RES-005 PDO write; RES-006 PDO read dispatch | [Message report](residual-r01b2-messages.md), native baseline/candidate, rollback and lock contention; PEAR portal reader stays pending R21/R29 |
| R01c | Pending | Operator catalog/list reads and residual AJAX operator lookup | Keep existing PDO business writes; use configured table names and named locations |
| R02–R27 | Pending | Functional read/write consumers from `finalization-lots.csv` | Split according to the original commit slices and test equivalent fixtures |
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
- [ ] Entire migration: refreshed residual inventory after all functional slices.
- [ ] Entire migration: dependency removal, installer/Docker validation and transverse tests.
- [ ] Entire migration: installed external legacy cron callers verified before deployment.
- [ ] Entire migration: release/PR/push/deployment separately authorized and verified.

R01a, R01b1 and the R01b2 operator editor are complete here. The message PEAR reader
still serves the three user-portal callers and must close with R21/R29. In particular,
permission-bootstrap migration does not
mean every calling page is entirely PDO: its own remaining provider/reads must still
be reviewed. Do not subtract historic block counts to invent a current remainder.
