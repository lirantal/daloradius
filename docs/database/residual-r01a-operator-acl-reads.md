# R01a — Operator permission checks and ACL form rendering

## Scope and baseline

This is the first residual migration slice from the finalization inventory pinned to
`eaecd81773c95603824825e399b83cbe833e233f`, on `refactor/pdo`.
It closes **RES-117** (`check_operator_perm.php`) and **RES-080**
(`drawOperatorACLs`), not the entire R01 family or the finalization plan.

Changed production files:

- `app/operators/library/check_operator_perm.php`
- `app/operators/include/management/operator_acls.php`
- new `app/operators/library/operator_acl_read.php`

The shared permission gate is reached by operator pages, AJAX helpers and exports.
The ACL renderer is called by `config-operators-new.php` and
`config-operators-edit.php`. These pages' already-migrated writes remain unchanged.
Neither `db_open.php`, `db_close.php`, PEAR DB dependencies nor installer files are removed.
No schema migration is needed for these reads, and MyISAM is not rejected just for reading.

## Contract

- Open a local PDO handle with `dalo_pdo_connect`, using the selected session location.
  Do not overwrite or close caller-owned `$pdo`/`$dbSocket` handles or their transactions.
- Derive the page key as before (`-` becomes `_`, `.php` is removed), retaining
  `$operator_perm_file` aliases for AJAX/export callers.
- Bind operator IDs and page keys. Validate/quote configured table names rather than
  binding/interpolating an arbitrary identifier. Supported names are unqualified ASCII
  identifiers of up to 64 characters, beginning with a letter or underscore.
- Keep the historical `intval(first access row) === 1` decision. Missing rows and
  denied rows remain denied; this change does not add grants, uniqueness constraints
  or a new duplicate-ACL policy.
- Keep the default `home-error.php` redirect and explicit
  `$operator_perm_deny_http_status` behavior. Malformed identity/page/table configuration
  cannot grant access. IDs must be positive canonical decimal integers.
- On a SQL/connection failure, stop before the caller runs. Known custom
  `$db_error_handler` callbacks receive a **redacted RuntimeException**, not a raw PDO
  exception/PEAR error. Without such a callback return HTTP 503 and a generic message.
  Logs contain only an exception class, not SQL, parameters or connection values.
- Fetch the ACL catalog before emitting the table. Keep both original join shapes:
  no-ID creation-form behavior (including grants/denials from the historical unfiltered
  join), and the edit-form filter on `opa.operator_id`. Keep category/section ordering,
  `ACL_<file>` controls, selected options, escaping and blank/NULL access as denied.
  A rendering error gives a generic failure block, never a half-rendered permissions form.
  It does not claim a previously completed business write has rolled back.

## Characterized pre-existing defect

The baseline gate interpolated the page/alias key into SQL. The differential fixture
reproduces an injected key granting a denied fixture operator access. The candidate
binds the key, returns HTTP 403, and also permits a legitimate stored key containing
an apostrophe/Unicode when its exact ACL is granted. No production exploit is attempted.

The ambiguous duplicate-row policy and the creation form's unfiltered ACL join are
explicitly preserved rather than opportunistically redesigned.

## Validation actually executed

Native differential HTTP/PHP/MariaDB test:

```sh
PYTHONDONTWRITEBYTECODE=1 python3 tests/operator_acl_reads_http.py
```

The baseline's two production files are read from the pinned Git commit; baseline and
candidate use equivalent disposable fixtures. Runtime: **PHP 8.4.24**, PDO MySQL,
**MariaDB 11.8.9**. The MariaDB instance uses tmpfs and an internal Docker network.
The application account is SELECT-only, with non-default ACL table names and a second
named database/location. A candidate `db_open.php` tripwire verifies these two paths no
longer invoke the legacy provider.

Covered and successful:

- allowed, denied and absent ACLs, redirect/403 contracts, missing authentication;
- named location, immediate permission revocation/restoration, caller-owned PDO transaction;
- creation/edit controls, empty catalog, no ACL rows, NULL grants, duplicate first-row
  behavior and escaped category/file values;
- bound aliases with apostrophe/Unicode, baseline injection, malformed arrays/IDs,
  unknown location and invalid table identifier;
- unavailable database connection and missing ACL/catalog tables, redacted SQL error handling, callback JSON contract,
  no partial form and direct-access guards;
- unchanged exact ACL/catalog state after the read scenarios; no fixture credentials
  in PHP logs; containers, network and temporary config/session files removed.

All of these existing regression scripts were also executed and exited **0** on the
candidate (native isolated fixtures, not just static checks):

```text
operator_create_http.py
operator_edit_http.py
operator_delete_http.py
operator_login_http.py
operator_otp_http.py
user_actions_http.py
report_export_http.py
attribute_engine_cli.py
profile_duplicate_http.py
```

PHP syntax checks for the changed/new production files, Python AST validation and
`git diff --check` also passed.

This is not full application validation, PostgreSQL/SQLite validation, production or
live LDAP/NAS testing. Remaining message/MFA/catalog consumers in R01 and other families
still need their own slices. PEAR DB remains installed for those consumers and coherent
historical tests.

## Data and deployment safety

The test does not copy a live `daloradius.conf.php`; generated connection values are
resolved from ephemeral environment variables and are not written into tracked files
or printed. SQL setup failures suppress driver input/details. Test-generated accounts,
sessions and data are destroyed. No installed cron, real database, SMTP destination,
NAS, upstream branch or deployment is changed.

**Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.**
