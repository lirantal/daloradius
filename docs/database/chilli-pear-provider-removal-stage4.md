# Chilli PEAR opening-provider removal — lot 2, stage 4

Base: `refactor/pdo` at `931e9c0c0c1d532e949b614d92fc9fd8537fa87e`. This stage builds on the uncommitted PDO openings and closures from stages 2 and 3.

## Changes

`contrib/chilli/common/database.php` no longer defines `dalo_chilli_pear_open` or its sole error callback `dalo_chilli_database_error`. Their `DB.php` include, array DSN construction, `DB::connect`/`DB::isError`, `PEAR_ERROR_CALLBACK` registration and PEAR disconnect-on-failure path are removed, with no replacement adapter, alias or fallback.

The shared module now contains only configuration normalization, the existing PDO opening provider and the PDO-only close provider. Obsolete coexistence comments were updated. The PDO implementation, port policy, charset/session options, connection-error redaction and rollback/nulling behavior are preserved. No additional production file changes are introduced by this stage.

`tests/chilli_connections_http.py` no longer calls the removed factory in its candidate failure probe. Before candidate requests, it resolves and removes only `DB.php` inside the disposable web container's writable image layer, then checks that the entrypoint is unavailable and the factory/callback are undefined. Each wrapper probe independently checks these conditions. The pinned historical PEAR baseline runs in a separate unchanged container; no historical production compatibility is restored.

This is a native targeted unavailable-DB.php connection-boundary test, **not** a production package uninstall or a fresh installation without PEAR DB. Other regression fixtures retain their existing isolated runtimes and legacy tripwires.

## Reference audit

Before removal, tracked-source search found only the factory/callback definition and registration in the shared module, plus the candidate test's obsolete direct factory call. No production caller remained after stage 2. PHP lexical validation after removal checks non-comment identifier and string tokens for the two removed symbols, covering direct calls, definitions, aliases and callback-name strings. Six tracked `daloradius.conf.php` files are deliberately excluded without reading their contents. Arbitrary dynamically assembled names and external custom integrations are not proven absent by static scanning.

The shared module is additionally checked for PEAR-specific classes/constants/methods and the DB.php include. Historical documents and pinned test source may still describe PEAR; they are not production compatibility.

## Scope boundaries

- Installation scripts, package manifests and the live lab's installed packages are unchanged. PEAR DB dependency removal remains a later stage.
- PEAR Mail is unrelated and untouched.
- No live configuration, application data, persistent service or scheduler was changed. No real payment service, external mailbox, FreeRADIUS or NAS hardware was contacted; PostgreSQL runtime was not tested.
- No commit, push, PR or deployment is performed. Earlier changes and the preexisting `tests/__pycache__/` directory are preserved.
- Generated fixture connection material is ephemeral; only sanitized checked results and source hashes are retained outside the repository.

## Executed validation

The lexical audit passed on 1,037 tracked PHP files, excluding the six configuration files described above. It found no non-comment identifier/string token referencing either removed symbol, and no checked PEAR API/include marker in the shared provider.

Eight checked native executions completed with exit code 0, empty suite stderr and resource cleanup verified:

| Suite | Result |
|---|---|
| chilli_connections_http.py — candidate | Six wrappers with DB.php unavailable; removed functions undefined; options, queries, rollback/close/reopen, references, synthetic error injection and port 3307 routing — PASS |
| chilli_connections_http.py — pinned baseline | Six historical PEAR wrappers and ordinary persisted projections — PASS |
| chilli_signup_http.py | Three free-signup families, full late-error rollback, gates and concurrent sessions — PASS |
| chilli_paypal_http.py | Three callback families, payment/profile projections, replay/retry/rollback and recurring events with trusted local HTTPS emulator — PASS |
| chilli_2checkout_residual_http.py | 8 comparisons and 6 control families; intentional retirement, not PDO parity — PASS |
| portal1_paypal_signup_http.py | 9 paired comparisons, 10 candidate/characterization families — PASS |
| portal2_paypal_signup_http.py | 13 paired comparisons, 11 candidate/characterization families — PASS |
| portal3_paypal_signup_http.py | 15 paired comparisons, 11 candidate/characterization families — PASS |

PHP lint passed on the shared provider, six opening and six closing wrappers, and both generated connection-endpoint templates. Python AST and `git diff --check` passed. Frozen source hashes were verified unchanged after testing; comparison with stage-4 preflight hashes proves only the shared provider and connection test changed among preexisting tracked files.

The initial foreground matrix exceeded the tool transport deadline. Its first four checked results had been persisted with exit code 0 and cleanup confirmation. After verifying the original runner and child had ended, the remaining four suites were executed through a tracked background job. No unrecorded or merely started execution is counted, and no suite needed relaxed acceptance. The background shell emitted job-control notices, distinct from the empty captured stderr of each native suite.

Checked result summaries and source hashes are retained outside the repository at `/home/kevin/daloradius-pdo-chilli-pear-removal-stage4-validation/` on docker-services. These tests are isolated native HTTP/PHP/MariaDB validation, with synthetic failure injection and local payment-verification emulation explicitly distinguished. They are not evidence of a production install or hardware/provider integration.
