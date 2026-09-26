# UNIT-021 — Operator profile duplication

`app/operators/mng-rad-profiles-duplicate.php` now calls `dalo_profile_duplicate()` on the session-selected PDO connection. The source's `radgroupcheck` and `radgroupreply` attributes are copied with two parameterized `INSERT … SELECT` statements **inside one InnoDB transaction**; a failed second statement rolls the first back. Configured table names pass through the fixed identifier allowlist. The option list also uses PDO. Login, ACL, CSRF and the form remain in place; shared PEAR helpers used by other profile pages are untouched.

The helper rejects malformed/oversized names, absent or already-used targets (including user-group-only targets), and sources with no check/reply attributes. It does **not** copy user-group or billing-plan associations. Literal `%`, quotes and Unicode in names are retained rather than silently stripped. The success message reports the number of copied attributes; errors display no driver details or bound values. The legacy PEAR page treated a radusergroup-only source as successfully copied even though it created no attributes; rejecting that no-op is intentional.

## Disposable verification

`tests/profile_duplicate_http.py` starts isolated PHP HTTP and MariaDB 11.8 containers with fixture schemas and operator sessions. It pins the PEAR page to `f18aa37b3` and compares ordinary check/reply/user-group/plan rows to PDO:

```sh
PROFILE_DUPLICATE_BASELINE=1 PROFILE_DUPLICATE_REFERENCE=/path/to/isolated-reference.json PYTHONDONTWRITEBYTECODE=1 python3 tests/profile_duplicate_http.py
PROFILE_DUPLICATE_REFERENCE=/path/to/isolated-reference.json PYTHONDONTWRITEBYTECODE=1 python3 tests/profile_duplicate_http.py
```

Candidate-only coverage includes authentication, ACL, CSRF, absent/colliding names, scalar/array validation, a source with only mappings, special-character and Unicode names, a configured named location, a nontransactional-table refusal, and a trigger that fails the reply insert **after** the check insert (all rows restored). This is isolated real HTTP/PHP/MariaDB testing, not production validation.

## Limits

The schema does not uniquely constrain profile names across its three tables. A concurrent writer not coordinating these locks can race the target-existence check; a cross-table unique profile identity or universally coordinated writes would be needed to guarantee uniqueness. No schema migration, push, or production deployment is part of this unit.
