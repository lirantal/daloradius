# UNIT-023 — Shared attribute mutation engine (PDO opt-in)

`app/operators/library/attributes.php` dispatches `handleAttributes()` and `is_attribute_already_present()` to `library/attributes_pdo.php` **only when the supplied handle is PDO**. Existing PEAR callers continue through their original path; UNIT-023 deliberately does not open a second connection inside a PEAR page. Later user/group page units must pass their **own already-transactional PDO handle** and include dependent account/group writes in that same caller transaction. The provider does not begin, commit, or roll back it; a database or validation failure propagates so the caller can roll everything back.

The PDO path validates the entire four-part attribute selection and configured table/selector allowlist before the first write, requires the selected tables to be InnoDB, then locks and verifies ownership of **every** positive attribute ID before mutation. It binds SQL values, preserves literal `0`, skips a completely blank default row and exact duplicates, hashes password attributes with the existing helper, preserves an unchanged password hash when only its operator changes, and rejects disabled cleartext password attributes. A stale/foreign ID, malformed control, disallowed operator/table or later SQL error fails closed rather than silently partially applying the request. Return counts reflect changed/inserted rows; debug logs contain SQL templates only.

The legacy provider's fuzzy table routing, unvalidated IDs, silent skips and success-after-error behavior remain confined to pages that still pass PEAR. Do **not** call the new provider with a PDO handle from the middle of a page whose other dependent writes remain on PEAR; such pages must be migrated as separate functional units. Without unique constraints or cooperation from all writers, simultaneous duplicate attribute inserts can still race.

## Isolated differential verification

`tests/attribute_engine_cli.py` runs the real PHP library in disposable PHP CLI and MariaDB 11.8 containers, with canonical project schemas and synthetic fixtures. The PEAR baseline is pinned to `d73639c1d`. The ordinary user/group insert, duplicate suppression, update and existence-lookup final states are compared:

```sh
ATTRIBUTE_ENGINE_BASELINE=1 ATTRIBUTE_ENGINE_REFERENCE=/path/to/isolated-reference.json PYTHONDONTWRITEBYTECODE=1 python3 tests/attribute_engine_cli.py
ATTRIBUTE_ENGINE_REFERENCE=/path/to/isolated-reference.json PYTHONDONTWRITEBYTECODE=1 python3 tests/attribute_engine_cli.py
```

Candidate-only tests exercise the still-live PEAR dispatch, hashed and unchanged password values, forbidden cleartext, malformed selectors/fields and foreign IDs, caller rollback of its own preceding write, a later reply-table trigger after a check-table insert, non-InnoDB refusal and literal `0`. These are **isolated PHP CLI + real MariaDB** tests of the provider, not authenticated HTTP page flows or production validation. No schema change, push or deployment is included.
