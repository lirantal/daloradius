# R01b2 — Operator message editor and mixed read provider

Base: `b59b278cdefc9a66313746914a20dfb6523d3748`, branch `refactor/pdo`.
Closes RES-047 (operator editor) and the PEAR write in RES-005. RES-006 is now
mixed: the operator editor uses PDO, while the three known user-portal readers
retain PEAR until their R21 migration. This is not removal of all message-related
PEAR code or closure of the whole R01 family.

## Production scope

- `app/operators/config-messages.php`: one explicit PDO handle and transaction.
- `app/common/includes/messages_pdo.php`: bound reads, selected-row locks,
  InnoDB/storage validation and caller-owned writes.
- `app/common/includes/functions.php`: `update_message` is PDO-only with a
  caller-owned transaction; `get_message` dispatches PDO versus the still-active
  PEAR reader. Actual repository callers were checked before changing the writer.

The editor validates and purifies the complete selected batch before beginning its
transaction. Selected types are bound and locked by row ID; all their existing
rows are updated on the same handle before commit. Duplicate type rows remain
updated together, and read/display keeps `ORDER BY id ASC LIMIT 1`. Absent flags
remain unselected; an explicitly selected empty string clears the message.

Preserved: login/ACL/CSRF, three tabs/control names, HTMLPurifier policy, quotes/
Unicode/literal percent handling, created metadata, modified actor/SQL timestamp,
non-default configured table name and selected database location. An unchanged
save remains successful; reported types are successful selections, not a claimed
count of physically changed rows. No DDL migration, package or vendor update.

Deliberate differences:

- A later selected-type failure rolls back earlier updates. The unmodified PEAR
  baseline was actually executed and retained the earlier login-message writes
  when a support-message trigger failed. Candidate errors return the form with
  a generic failure rather than exposing driver details or retaining that partial batch.
- Reject malformed later controls, missing selected rows, MyISAM and insufficient
  actual column capacity before writing. A read of an absent message returns an
  empty descriptor rather than producing undefined/NULL caption warnings.
- Log parameter templates, not message text or actor values. A display error is
  distinct from update completion and does not claim an already committed save
  rolled back. A lost commit acknowledgement/connection remains an uncertain outcome,
  not a promise that no database change could have occurred.

`should_update_message` is left intact for compatibility, not automatically removed
just because the editor's new whole-batch parser replaces its call. The PEAR read
branch remains for `users/login.php`, `users/help-main.php`, `users/home-main.php`.
These pages are not represented as completely PDO.

## Actual native verification

```sh
MESSAGES_BASELINE=1 PYTHONDONTWRITEBYTECODE=1 python3 tests/messages_http.py
PYTHONDONTWRITEBYTECODE=1 python3 tests/messages_http.py
```

Both exited 0 on real disposable HTTP/PHP 8.4.24/PDO MySQL/MariaDB 11.8.9.
The baseline pins both editor and shared functions to the base commit, not an old
editor on an incompatible rewritten helper. Both use equivalent schemas/inputs.
Normalized stored message state excludes only dynamic modification timestamps.

Passed: authentication/ACL/CSRF, no selection, clearing a message, multi-type update,
HTML purification, quotes/Unicode, metadata, unchanged save, named location isolation,
duplicate-type semantics, baseline partial-write characterization, candidate later-type
rollback, malformed arrays, missing rows, short storage, MyISAM, redacted SELECT errors.
A real second MariaDB connection holds the selected row locks; the fixture observes
its process-list state before the competing HTTP write and verifies the final update.

The real user login and help pages, plus the dashboard PEAR helper dispatch, were
exercised successfully. A candidate legacy-provider tripwire rejects any hidden
PEAR open by `config-messages.php` while allowing those deliberately retained readers.
The standalone helper fixture explicitly loads validation/language/configuration;
its initial missing message-type initialization was a fixture defect, corrected
without changing production behavior.

Both new R01b fixture runners collect Docker stdout **and stderr**. A positive PHP
`error_log` marker proves the log channel is captured before asserting absence of
fatal errors/warnings and, for MFA, factor leakage. The MFA baseline/candidate tests
were rerun with this strengthened guard and also exited 0.

Existing `portal_login_http.py`, `operator_acl_reads_http.py`, `operator_otp_http.py`
and `operator_edit_http.py` were rerun on the combined candidate and exited 0.
PHP syntax, Python AST and `git diff --check` passed. These are isolated native tests,
not a production deployment, PostgreSQL/SQLite proof or full user-dashboard validation.

No live `daloradius.conf.php` is copied. Containers use an internal network and tmpfs
SQL, with ephemeral config/session files. Container-created cache ownership is restored
only inside the disposable copy before teardown; all fixture resources are removed.
No push, deployment, installed cron, real data or external mail destination is changed.
PEAR DB remains installed until supported legacy callers are closed.

**Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.**
