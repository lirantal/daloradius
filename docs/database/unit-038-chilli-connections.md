# UNIT-038 — Chilli shared connection boundary

## Scope / disposition

The inventory classifies this legacy unit as `audit/deprecate-or-migrate`: six copied open/close families, twelve blocks, dependent on UNIT-001. Retain and consolidate their compatibility entry points; add an explicit PDO provider rather than silently substituting PDO for PEAR in unmigrated signup/payment code.

Covered library directories under `contrib/chilli/`:

- `portal1/signup-paypal/library`;
- `portal2/signup-2checkout/library`;
- `portal2/signup-free/library`;
- `portal2/signup-paypal/library`;
- `portal3/signup-free/library`;
- `portal3/signup-paypal/library`.

Each `opendb.php` still loads its own local `config_read.php` and assigns **PEAR DB** to `$dbSocket`; each `closedb.php` delegates to the shared close helper. Connection/error policy is consolidated in `contrib/chilli/common/database.php`. Includes use file-relative paths, not the process working directory. Old `errorHandling.php` copies remain on disk for compatibility but are no longer loaded by these wrappers; the shared callback renders only a generic database error.

Audited indirect consumers include free signup index pages, merchant index/success pages, both PayPal callback styles, the 2Checkout callback, and `provisionUser`, `enableUser`, `disableUser`, `updateBilling` helpers. They still require PEAR `escapeSimple`, `query`, `fetchRow`, `numRows` and `getCol`; replacing their socket would break them or split dependent writes. No business SQL, callback protocol or provisioned credentials is changed here. Portal1's standalone free-signup script has its own inline connection and belongs to UNIT-039, not this twelve-block unit.

## New PDO entry point

Migrated workflows load local configuration and the shared module, then call:

```php
$pdo = dalo_chilli_pdo_open($configValues);
try {
    if (!$pdo->beginTransaction()) { throw new RuntimeException('Transaction unavailable'); }
    // Every dependent query uses this exact handle, with bound data values.
    // Validate identifiers and transactional engines in the workflow itself.
    $pdo->commit();
} catch (Throwable $error) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    // Render only a generic failure; driver messages can contain sensitive values.
} finally {
    dalo_chilli_database_close($pdo);
}
```

The function neither creates PEAR nor replaces an existing `$dbSocket`. Do not call legacy `opendb.php` inside a PDO transaction. Conversion of whole free-signup, PayPal and 2Checkout workflows is reserved for UNIT-039/040/041 respectively.

PDO delegates to UNIT-001 `app/common/includes/pdo_connection.php` with the **portal-local default configuration**, not an operator session location. This reuses utf8mb4, native prepares, exception mode, nonpersistent handles and the legacy MySQL session SQL mode. No transaction is started automatically. Missing ports default to 3306 for MySQL/MariaDB and 5432 for PostgreSQL; invalid ports or unsupported PDO engines fail explicitly without retaining a driver exception as the cause. PostgreSQL defaults are validated only; real runtime coverage is MariaDB/mysqli.

## Compatibility and intentional differences

- PEAR opens still preserve historical port selection: only `portal2/signup-free` and `portal2/signup-paypal` honor `CONFIG_DB_PORT`. The other four omit the PEAR port option, as before. PDO always honors the configured port. This is a deliberate coexistence boundary, not a silent change of legacy backend.
- PEAR uses a structured DSN instead of constructing a credential-bearing URI. This also makes URI delimiter characters in credentials safe. No DSN or connection credential variable is exported into the caller's scope.
- Connection failures and query callbacks no longer reveal driver diagnostics, SQL, usernames or credentials. Existing pages receive a generic connection error, and compatibility queries retain their PEAR error-result behavior. This does not repair their existing partial-write/error-handling policy.
- Close never commits. For PDO it explicitly rolls back an active transaction before dropping the caller's handle, even if a statement still references the connection. Idle or already-null handles can be closed safely. A close failure is propagated as a generic exception; legacy wrappers terminate with a generic close error. Other aliases/statements are still owned by the caller and must not be reused after close.
- **Packaging requirement:** these retained examples now share a file. Deploy the `contrib/chilli/common/` sibling directory alongside any selected portal tree, preserving the repository-relative layout. For PDO adoption also retain `app/common/includes/pdo_connection.php` at its repository-relative location. Copying only a standalone `signup-*/` directory is no longer sufficient. There is no guessed absolute-path fallback or duplicate connection implementation. No installed portal or production deployment is modified by this unit.

## Validation

```sh
CHILLI_CONNECTION_BASELINE=1 PYTHONDONTWRITEBYTECODE=1 python3 tests/chilli_connections_http.py
PYTHONDONTWRITEBYTECODE=1 python3 tests/chilli_connections_http.py
```

Baseline wrappers/config reader/error callback are pinned to `eed0a3ae61b0fd908e28a9686b7eb8a7999ae561`. Equivalent fixture-only HTTP endpoints run through all six real PEAR wrappers, insert/read bound-equivalent ordinary values, close/reopen and compare persisted state to identical assertions. Candidate endpoints additionally exercise explicit PDO open/options, commit and reopen, Unicode/quote/percent values, active-transaction close with a retained statement and exact unchanged rows, generic connection/query errors with no previous exception, and invalid controls/unsupported engine.

The candidate also loads all six wrappers sequentially in one PHP process, checks direct shared-module HTTP access returns 404, and uses a second **real MariaDB server on port 3307** with URI-delimiter-bearing credentials. This proves that the two port-aware PEAR wrappers and all PDO callers reach the selected server, while the four port-omitting wrappers retain their old default-port behavior and fail generically. Logs must have no PHP warnings/fatal errors or fixture secrets. Temporary configs have mode 0600; credentials are generated at runtime, never printed, and deleted with disposable fixtures. Containers, tmpfs data and networks are removed and their absence verified.

PHP lint of every changed PHP file, Python AST validation and `git diff --check` supplement real isolated HTTP/PHP/PEAR/PDO/MariaDB coverage. Tests do **not** exercise live free signup, payment verification, callback idempotency, FreeRADIUS authentication, PostgreSQL connections or production deployment. Those workflows remain legacy and must not be advertised as fully PDO-migrated or transaction-safe.

**Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.**
