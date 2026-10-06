# Chilli PDO-only boundary — lot 2 delivery

This closes the five-stage Chilli wrapper lot, not the complete repository-wide PEAR DB retirement. Base: `refactor/pdo` at `931e9c0c0c1d532e949b614d92fc9fd8537fa87e`.

## Final disposition

| Family | Opening | Port policy | Closure |
|---|---|---|---|
| portal1/signup-paypal | Existing PDO provider | Standard port | Existing PDO-only close provider |
| portal2/signup-2checkout | Existing PDO provider | Standard port | Existing PDO-only close provider |
| portal2/signup-free | Existing PDO provider | Configured port | Existing PDO-only close provider |
| portal2/signup-paypal | Existing PDO provider | Configured port | Existing PDO-only close provider |
| portal3/signup-free | Existing PDO provider | Standard port | Existing PDO-only close provider |
| portal3/signup-paypal | Existing PDO provider | Standard port | Existing PDO-only close provider |

All six retained pairs preserve their configuration loader path, exported `$dbSocket`, fixed redacted connection/closure responses and historical applicable port policy. The opener normalizes a copy of configuration rather than overwriting `$configValues`. Every connection uses the existing shared PDO settings: utf8mb4 on MySQL, exceptions, associative fetch, native prepares, nonpersistent connections and the existing empty MySQL session sql_mode. No extra database-driver portability is claimed.

`dalo_chilli_database_close` accepts PDO or null, explicitly rolls back active transactions, never commits and clears the by-reference variable even on failure. Non-PDO handles are rejected without calling their methods. A retained PDO or statement reference can keep the physical connection alive; clearing one variable is not a guarantee of disconnection, and a rollback failure is not proof that database state is unchanged.

The PEAR opening factory and its callback/registration are removed rather than adapted. There is no legacy fallback or mixed-client transaction in this boundary. The supported current signup/payment flows remain; retained wrappers do not restore the retired 2Checkout signup, callback or public receipt routes.

## Stage-5 acceptance checks

A dedicated native two-connection fixture now runs for every wrapper: it starts independent transactions on the wrapper connection and a separate PDO, closes/rolls back only the wrapper, proves that the other transaction remains active, explicitly commits the other transaction and reopens the wrapper to verify the independent write survived while the wrapper write did not. Both statements stay alive during the rollback. This complements existing double-close, retained-reference, explicit-commit and sequential-include tests.

The candidate connection matrix removes only the DB.php entrypoint in its disposable web container, asserts it cannot be resolved and that the removed factory/callback are undefined, then executes all six wrapper paths. This is not a production package uninstall. Historical PEAR baselines retain their original coherent pages, wrappers, configuration loaders and shared providers in separate fixture environments.

Synthetic PDO-subclass failures separately cover transaction-state exceptions, rollback exceptions/false returns, fixed generic errors, nulling on failure and non-PDO rejection. They are not evidence of native failed rollback or server disconnect behavior.

## Delivery scope and limits

- Local delivery is split into two commits: one for the complete code/test boundary and one for the stage reports and consolidated disposition. Exact commit IDs are recorded outside the repository after read-back; they are not predicted here.
- No push, PR, merge or deployment is authorized by this stage. No hosted CI status is claimed.
- Installation/package files and the live lab's packages are unchanged. Other mixed helper cleanup, full PEAR DB dependency removal, fresh installation and existing-data upgrade validation remain separate later lots. PEAR Mail is untouched.
- Live configuration and real application data are neither read for testing nor modified. No real PayPal service, external mailbox, FreeRADIUS or NAS hardware is tested. Native database validation here is MariaDB; PostgreSQL is untested.
- Fixture credentials, factors, configuration, keys and sessions are generated only inside ephemeral fixtures and removed on cleanup. Only checked summaries, source hashes and dispositions are retained as validation artifacts.
- The preexisting untracked `tests/__pycache__/` directory is preserved and excluded from commits.

The stage-2, stage-3 and stage-4 reports are historical snapshots of their respective working-tree steps. This consolidated report supersedes their then-current statements about retained providers and pending local delivery; it does not change their historical test claims.

## Executed final validation

The complete stage-5 matrix was rerun after adding two-connection isolation coverage, with the additional retired SDK/provider fail-closed suite also exercised. All nine executions returned 0, with empty captured suite stderr and fixture/config/container/network cleanup confirmed:

| Suite | Checked result |
|---|---|
| chilli_connections_http.py — PDO candidate | Six wrapper families with DB.php unavailable; connection/options/redaction, queries, close/reopen, native rollback, retained references, independent active-transaction isolation, sequential includes and nondefault-port routing — PASS |
| chilli_connections_http.py — pinned PEAR baseline | Six historical wrapper families and ordinary persisted projections — PASS |
| chilli_signup_http.py | Three free-signup families, complete late-error rollback, gates, input/config/engine checks and concurrent sessions — PASS |
| chilli_paypal_http.py | Three callback families, replay/retry/concurrency, transaction rollback and recurring events with trusted local HTTPS verifier — PASS |
| chilli_2checkout_residual_http.py | 8 historical/candidate comparisons, 6 control families; retirement, not PDO parity — PASS |
| chilli_2checkout_retirement_http.py | Retired SDK/provider reject before use; HTTP 410 and unchanged full fixture state across signed, invalid, malformed and concurrent requests — PASS |
| portal1_paypal_signup_http.py | 9 paired comparisons, 10 candidate/characterization families — PASS |
| portal2_paypal_signup_http.py | 13 paired comparisons, 11 candidate/characterization families — PASS |
| portal3_paypal_signup_http.py | 15 paired comparisons, 11 candidate/characterization families — PASS |

Static validation passed: PHP lint for the shared provider and 12 wrappers, both generated PHP connection templates, Python AST checks, `git diff --check`, and explicit six-family opening/closure/port-policy/baseline-dependency structure checks. The PHP lexical scan covered 1,037 tracked PHP files excluding six configuration files without reading them; no non-comment identifier/string references to the removed factory/callback remain, and the shared module has no checked PEAR API/include markers. Static scanning does not prove absence of arbitrary dynamically assembled calls or external custom integrations.

Tested Chilli/PDO/test source hashes were frozen before the matrix and verified unchanged afterward and again before committing. Sanitized results, source hashes, wrapper dispositions and commit read-back are retained outside the repository at `/home/kevin/daloradius-pdo-chilli-delivery-stage5-validation/` on docker-services. No connection material or raw request/driver snapshot is included in those artifacts.

Acceptance criterion met for this lot: retained wrappers use PDO exclusively, cannot open PEAR DB, preserve their contracts and do not interfere with a separate PDO transaction; the current Chilli workflows pass isolated native regression validation. Repository-wide dependency retirement/release readiness is not claimed.

Code/test delivery commit: `2f2d0628d94a9b24c2bee9c8a13a3b7e3d4b499b`. The subsequent documentation commit is recorded in the external verified delivery artifact.
