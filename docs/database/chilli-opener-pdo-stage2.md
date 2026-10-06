# Chilli PDO-only openings — lot 2, stage 2

Base: `refactor/pdo` at `931e9c0c0c1d532e949b614d92fc9fd8537fa87e`. Validation applies to the modified working tree, not a new commit.

## Changes

The six retained `library/opendb.php` wrappers now export a PDO as `$dbSocket`, via the existing `dalo_chilli_pdo_open` provider. Each normalizes a local copy of settings with its original port flag; `$configValues` is not overwritten. Loader paths, configuration selection and the fixed wrapper connection-error response remain unchanged. No new adapter, PEAR fallback or implicit transaction was introduced.

| Family | Port flag passed to settings normalization |
|---|---|
| portal1/signup-paypal | false: historical standard port |
| portal2/signup-2checkout | false: historical standard port |
| portal2/signup-free | true: configured port |
| portal2/signup-paypal | true: configured port |
| portal3/signup-free | false: historical standard port |
| portal3/signup-paypal | false: historical standard port |

The provider remains unchanged: MySQL charset utf8mb4, exception mode, associative fetch, native prepares, nonpersistent connections and empty MySQL session sql_mode. These are explicit PDO-provider choices, not a claim that every PEAR default was identical. Drivers other than those supported by the existing PDO provider fail explicitly; no extra portability support is claimed.

A fresh PHP-tokenized scan of 522 tracked application/contrib files finds no executable call to `dalo_chilli_pear_open`. No tracked application caller needed conversion beyond these wrappers. Custom integrations that consume `$dbSocket` must now use PDO; compatibility with PEAR result/error methods is intentionally ended for the converted wrappers.

## Tests and historical baselines

`tests/chilli_connections_http.py` keeps the pinned historical PEAR endpoint and exercises the current wrappers with PDO prepared statements/fetch APIs. It checks native PDO type/options, no DB.php load, query/close/reopen, wrapper rollback with a live statement reference, sequential inclusion of all families in one process, real configured-port/omitted-port routing on a MariaDB server listening on 3307, and delimiter-bearing generated authentication material. The existing direct PEAR-provider checks remain separate while that factory is retained for stage 4; they are not a wrapper fallback.

Log checks collect stdout and stderr and require a harmless native HTTP error_log marker before asserting absence of warnings, driver/query details and generated authentication material.

The three PayPal signup suites and the 2Checkout residual suite formerly copied current wrapper dependencies into pinned historical pages. Their baseline now also pins `opendb.php`, `closedb.php`, `config_read.php` and `contrib/chilli/common/database.php` to the suite's existing BASE revision. No legacy production code was restored, and the candidate stays on current source/tripwires.

## Executed validation

All six checked executions returned 0, with empty stderr and fixture cleanup confirmed:

| Suite | Mode / result |
|---|---|
| chilli_connections_http.py | PDO candidate: six wrappers, options, queries, close/reopen/rollback, no PEAR load, port routing, error redaction and positive log capture — PASS |
| chilli_connections_http.py | Pinned PEAR baseline: six historical wrappers and ordinary expected row projections — PASS |
| chilli_2checkout_residual_http.py | 8 historical/candidate comparisons, 6 native control families; retirement remains intentional, not PDO parity — PASS |
| portal1_paypal_signup_http.py | 9 paired comparisons, 10 candidate/characterization families; native signup/receipt/IPN — PASS |
| portal2_paypal_signup_http.py | 13 paired comparisons, 11 candidate/characterization families; pending signup/resume/receipt/IPN — PASS |
| portal3_paypal_signup_http.py | 15 paired comparisons, 11 candidate/characterization families; signup/receipt/IPN — PASS |

PHP lint passed for the six modified wrappers and both generated endpoint templates. Python AST checks passed for all five changed suites. `git diff --check` passed. Source hashes were frozen before the final matrix and checked afterward.

Validation used disposable real PHP/HTTP/MariaDB fixtures and, for IPN, a trusted local HTTPS verification emulator. It did not contact a real PayPal service, SMTP mailbox, FreeRADIUS or NAS hardware. PostgreSQL native workflows and a production runtime without PEAR DB were not tested here.

## Scope deliberately left for later stages

- The six `closedb.php` wrappers and shared close helper are unchanged; their PDO branch was exercised, but the remaining PEAR branch is not removed in stage 2.
- `dalo_chilli_pear_open` and its callback still exist, now without tracked production callers; removal belongs to stage 4.
- PEAR DB remains installed for now. PEAR Mail is unrelated and untouched.
- 2Checkout public endpoints remain HTTP 410; no signup, activation, account/history or payment-policy restoration.
- No live configuration, real application data or persistent stack was changed.
- No commit, push, PR or deployment was performed. The preexisting `tests/__pycache__/` directory is preserved.

No keys, passwords, tokens, secrets or connection identities are retained in result artifacts; generated fixture material is removed with fixture cleanup. Reports contain only source paths/hashes, dispositions and checked test summaries.
