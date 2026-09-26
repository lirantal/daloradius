# UNIT-020 — Operator profile deletion

## Scope

`app/operators/mng-rad-profiles-del.php` now uses `app/operators/library/profile_delete.php` for its profile list, individual attribute selection and the two destructive POST modes. All selected rows are validated before writing, then group check/reply attributes and user-group mappings are locked and removed on one caller-owned PDO transaction. Removing only user mappings preserves both group attributes and billing plan associations.

A complete profile removal also removes its `billing_plans_profiles` references. Deleting the **last** check/reply attribute from a profile removes its user-group and plan associations; deleting one of several attributes leaves the profile and mappings intact. These plan-reference cleanups repair dangling associations that the PEAR path left behind. The selected-profile count now reports distinct affected profiles rather than counting successful DELETE statements (or reporting zero for mappings-only mode).

The attribute form's `profile__id__table` values are parsed from the right so a profile containing `__` remains addressable. Each selected ID must belong to the asserted group and one of the two allowlisted tables; a stale or conflicting later ID aborts the whole request. The GET preselection preserves literal `%` and safely handles quoted names. SQL values are bound, configured identifiers are allowlisted, and participating tables must be InnoDB. Submitted data and driver exceptions are not logged as query text or displayed in failure messages.

## Isolated verification

`tests/profile_delete_http.py` uses disposable HTTP/PHP/MariaDB instances seeded from the project schemas. It pins the PEAR page to `ab3523b3e` and compares normalized post-action group check/reply/user mappings for ordinary attribute removals, mappings-only removal and two-profile removal:

```sh
PROFILE_DELETE_BASELINE=1 PROFILE_DELETE_REFERENCE=/path/to/isolated-reference.json python3 tests/profile_delete_http.py
PROFILE_DELETE_REFERENCE=/path/to/isolated-reference.json python3 tests/profile_delete_http.py
```

Candidate-only tests cover login, ACL and CSRF, invalid/stale/ambiguous selections, wrong table or owner, late failures on a **second** profile and a **later** attribute (full rollback), preservation of mappings while attributes remain, plan-reference cleanup after the final attribute, count corrections, `%`/apostrophe/`__` and Unicode names, and refusal of a nontransactional table. These tests exercise the real page and database in an isolated lab, **not** production.

## Limits

The shared profile edit/create pages and their PEAR helpers are not part of UNIT-020. Other writers do not necessarily take these locks: without universal foreign keys and coordinated writes, concurrent group or plan-mapping inserts can still race profile deletion. A mappings-only removal intentionally does not delete plan associations even if the profile had no check/reply attributes. No schema change, push or deployment is included.
