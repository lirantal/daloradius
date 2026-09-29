# UNIT-036 — group attribute check/reply pages

Migrates create/edit mutations for `radgroupcheck` and `radgroupreply` to a caller-owned PDO connection in the four operator pages. Existing shared PEAR helpers and list/display reads remain outside this unit. The edit pages retain the existing PEAR display read after the PDO mutation, so this is not a whole-page or shared-helper migration.

## Safety and behavior

- POST controls are required to be scalar; identifiers use a strict `groupcheck-N` / `groupreply-N` form, and table names are configuration-allowlisted before interpolation.
- Create operations and the complete attribute list share one PDO transaction; edits lock the existing row, reject stale IDs and duplicate values, and commit on the same handle. Cleartext password policy remains enforced.
- Values are bound as data. Literal `%` is preserved. Numeric string `0` is treated as a value, not as an empty input.
- Driver exceptions are not rendered or logged; users receive generic failures and logs contain only exception classes.
- Group existence selection still uses the existing PEAR `get_groups()` helper; its three-table UNION is not part of this write migration.

## Fixture diagnosis and differential runs

`tests/group_attributes_http.py` pins PEAR baseline pages to `9cf49ec788a9cd3f85f13c17dae935d997a21db3` and runs the real operator HTTP flow against disposable PHP/MariaDB containers. Both modes import the group schemas plus `mariadb-daloradius-dictionaries.sql`, because the edit page reads the dictionary table to render its attribute choices.

The initial isolated HTTP GET failure was not caused by the group UNION: that exact UNION ran directly in MariaDB. The HTTP query that failed was the page's separate `SELECT DISTINCT(attribute) FROM dictionary ...`; the fixture had not imported the dictionary schema. Instrumented, secret-free diagnostics resolved the PEAR message to `DB Error: no such table`, MariaDB native error **1146**, and the sanitized SQL template `SELECT DISTINCT(attribute) FROM dictionary WHERE ...`. Importing the fixture's dictionary SQL fixed the baseline and candidate; no application-level PEAR workaround was added.

Run candidate (also includes candidate-only security/rollback checks):

```sh
python3 tests/group_attributes_http.py
```

Run pinned-baseline ordinary create/edit parity:

```sh
GROUP_ATTRIBUTES_BASELINE=1 python3 tests/group_attributes_http.py
```

The ordinary check value is `fixture-value` in both modes. The candidate separately exercises literal `0`, `%` group names, invalid CSRF, stale and array-typed edit inputs, duplicate rejection, late-insert rollback, and fail-closed behavior for a non-InnoDB participating table. The legacy and candidate edit success-message wording differs; persisted ordinary row values are compared explicitly, rather than treating that wording difference as a data mismatch.

These tests exercise disposable HTTP/PHP/MariaDB state only. No live records were changed; nothing was deployed or pushed.
