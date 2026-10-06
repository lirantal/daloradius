# Chilli PDO-only closures — lot 2, stage 3

Base: `refactor/pdo` at `931e9c0c0c1d532e949b614d92fc9fd8537fa87e`. This stage builds on the uncommitted stage-2 openings; validation concerns the working tree, not a new commit.

## Changes and contract

The six retained `library/closedb.php` wrappers continue to use the existing shared `dalo_chilli_database_close(&$socket)` provider. Their implementation was already centralized; the functional conversion therefore belongs in that provider rather than duplicated wrapper code. Each wrapper now documents its PDO-only contract.

The shared provider no longer calls `disconnect()` or `DB::isError()`. It accepts PDO or null only:

- Null is a no-op, including a repeated close.
- PDO with an active transaction is explicitly rolled back; close never commits.
- PDO without an active transaction is released without changing committed data.
- The caller's variable is always set to null in `finally`, including invalid-handle, transaction-state and rollback failures.
- Non-PDO handles are rejected without invoking their methods; no legacy fallback or adapter remains in closure.
- Failures expose only `Database close failed`, without an attached driver exception. Wrappers preserve the historical fixed `<b>Database close error</b><br/>` response.

Explicit rollback protects pending work even when a PDO alias or statement still references the connection. Setting one variable to null does **not** physically disconnect a connection retained by other references; callers must release those references themselves. If rollback fails, the generic exception and cleared variable do not prove that the transaction ended or that database state is unchanged.

The existing PDO page/provider callers are unchanged. Their guarded close calls already supply PDO handles. No routing, configuration, signup, payment or transaction policy is changed. The retired 2Checkout endpoints stay retired.

## Test changes

`tests/chilli_connections_http.py` now checks every retained wrapper for null/double close, close/reopen, persistence of an explicitly committed row, and native rollback with both a live statement and a PDO alias. These run against disposable real PHP/HTTP/MariaDB fixtures, including the existing options and nondefault-port routing matrix.

Clearly separate synthetic PDO-subclass tests inject `inTransaction()` exceptions, `rollBack()` exceptions and false returns. They assert fixed redacted errors, nulling after failure and no implicit commit. Non-PDO inputs are rejected without calling a fake `disconnect()`. Every wrapper's fixed error rendering and nulling are also tested through HTTP with synthetic rollback failure. These are failure-injection tests, not evidence of a real server disconnect or failed native rollback.

The query-error probe now uses PDO rather than passing a PEAR handle to the PDO-only close provider. Historical PEAR wrappers and pages remain pinned to their original providers in separate baseline fixtures; production compatibility is not restored.

## Scope and remaining work

- The PEAR factory `dalo_chilli_pear_open` and its callback remain for the next removal stage; they are not called by the converted close path.
- PEAR DB is still installed. This stage does not claim a package-free production installation. PEAR Mail is untouched.
- No real configuration or application data, live scheduler or persistent stack was changed.
- No real PayPal service, external mailbox, FreeRADIUS, NAS hardware or PostgreSQL workflow was tested.
- No commit, push, PR or deployment is performed. Earlier uncommitted changes and the preexisting `tests/__pycache__/` directory are preserved.
- Generated fixture connection material is ephemeral and removed by fixture cleanup; result artifacts contain only source hashes and sanitized checked summaries.

## Executed validation

Eight checked executions completed successfully, each with exit code 0, empty stderr and native fixture-resource cleanup verified:

| Suite | Result |
|---|---|
| chilli_connections_http.py — candidate | Six PDO wrappers; native rollback/retained references, double close, committed data, reopen, no PEAR load, synthetic failure/error redaction, sequential includes and port routing — PASS |
| chilli_connections_http.py — historical baseline | Six pinned PEAR wrappers, ordinary persisted row projections — PASS |
| chilli_signup_http.py | Three free-signup families, complete late-error rollback, gates, special inputs and concurrent sessions — PASS |
| chilli_paypal_http.py | Three callback families, transaction/retry/replay controls, recurring payments and local HTTPS verification emulator — PASS |
| chilli_2checkout_residual_http.py | 8 baseline/candidate comparisons, 6 control families; retirement, not PDO parity — PASS |
| portal1_paypal_signup_http.py | 9 paired comparisons, 10 candidate/characterization families — PASS |
| portal2_paypal_signup_http.py | 13 paired comparisons, 11 candidate/characterization families — PASS |
| portal3_paypal_signup_http.py | 15 paired comparisons, 11 candidate/characterization families — PASS |

PHP lint passed for the shared helper, six closure wrappers and both generated connection endpoint templates. Python AST checks and `git diff --check` passed. Tested source hashes were verified unchanged after execution; the R26 test hash was updated before its successful rerun following its readiness correction.

Two initial connection-test attempts failed only because the fixture-added newline preceded the fixed close-error response. The assertion now ignores surrounding whitespace while preserving the exact response content and shutdown nulling marker. The initial matrix runner then stopped on the callback suite's different cleanup marker despite that suite returning 0 and verifying cleanup. Its existing precise marker was read and checked; no test result was fabricated or broadened to arbitrary success. The first R26 attempt failed on SQL error 2002 during fixture initialization. Its readiness probe now requires the final TCP server instead of the temporary bootstrap Unix socket; the complete suite passed on rerun. No application behavior was changed for these fixture corrections.

Sanitized checked results and source hashes are retained outside the repository at `/home/kevin/daloradius-pdo-chilli-close-stage3-validation/` on docker-services. All execution claims concern isolated native fixtures; synthetic failure injection and locally emulated payment verification are identified above.
