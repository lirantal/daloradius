# UNIT-022 — Operator attribute dictionary import

`app/operators/mng-rad-attributes-import.php` now parses the submitted dictionary before opening its session-selected PDO connection and delegates mutations to `library/dictionary_import.php`. The three existing strategies (`insert_or_update`, `delete_then_insert`, `only_insert_new`) use prepared statements and a **single InnoDB transaction** per request. A later failed insert rolls back earlier updates and deletes as well as inserts. The dictionary table identifier is allowlisted; successful counts are taken from committed affected rows, not attempted SQL statements. Other dictionary create/edit/search pages still use PEAR DB.

A single vendor is imported. The parser accepts a manually entered vendor or one auto-detected `VENDOR` directive, and collects `ATTRIBUTE` names and optional types (last repeated attribute wins, as in the usual legacy path). It validates the destination's 32/64/30-character vendor/attribute/type schema limits before any write. Empty attribute lists, multiple different auto-detected vendors, malformed form arrays and unknown strategies are rejected without deleting existing rows. Literal `%`, apostrophes and Unicode are preserved rather than stripped. The page logs query templates only and does not show PDO exceptions or partial success counts.

The old PEAR path could delete an entire vendor when a header-only dictionary was submitted with `delete_then_insert`, could report a successful count after a failed later statement, and counted UPDATE attempts rather than actual changed rows. Rejecting the empty import, rolling back all operations, and reporting committed affected rows are intentional corrections. `dictionary` has no uniqueness constraint on `(Vendor,Attribute)`; imports update all matching rows (including its separate Value choices) and report their changed-row count.

## Isolated verification

`tests/dictionary_import_http.py` creates disposable PHP HTTP/MariaDB 11.8 containers; its dictionary table DDL comes from `contrib/db/mariadb-daloradius-dictionaries.sql` and the rows are synthetic fixtures. It pins the PEAR page to `39f307126` for ordinary state comparison:

```sh
DICTIONARY_IMPORT_BASELINE=1 DICTIONARY_IMPORT_REFERENCE=/path/to/isolated-reference.json PYTHONDONTWRITEBYTECODE=1 python3 tests/dictionary_import_http.py
DICTIONARY_IMPORT_REFERENCE=/path/to/isolated-reference.json PYTHONDONTWRITEBYTECODE=1 python3 tests/dictionary_import_http.py
```

The test covers all three strategies, NULL types, preserving a different vendor, authentication/ACL/CSRF, malformed controls and vendor detection, duplicate Value rows, unchanged-row counts, manual and named database locations, special-character names, a non-InnoDB refusal, and failures **after** an earlier delete/insert or update. Results are from isolated HTTP/PHP/MariaDB execution, not production validation.

## Limits

The dictionary schema has no unique `(Vendor,Attribute)` key, so unrelated concurrent writers can still race the existence check unless all writers coordinate or the schema is changed. A vendor import does not change RADIUS user/group attributes already stored elsewhere. No schema migration, push or production deployment is included.
