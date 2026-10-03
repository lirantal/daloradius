# R02a — Independent operator selectors

Base: `d59b5c6c5019e2d8fa7e5bbffad872b1792d6d0d`, branch `refactor/pdo`.

## Scope and ownership

This is the **first commit slice of R02**, not completion of the whole family.
The seven inventoried selector consumers RES-081–RES-087 are migrated:
`get_active_plans`, `get_groups`, `get_huntgroups`, `get_invoice_status_id`,
`get_ippools`, `get_proxies`, and `list_from_db`.

The former `list_from_db` call sites are also explicitly migrated: payment types,
realms, online users, users, NAS names, plans, rate names, groups with users,
users with groups, dictionary attributes/vendors, hotspots and batch names.
That is nineteen public getters and the retained string-SQL helper, **not twenty
independent residual blocks**. `get_operators` was already migrated by R01c and
its body remains unchanged. Other function bodies, including the old rendering
helpers without established active callers, remain byte-equivalent to baseline.

`selectbox_read.php` lazily opens a private PDO handle for an independent read,
resolves the selected session location through the shared factory, validates and
quotes configured table identifiers, fetches numeric rows and releases only its
own handle. It does not overwrite, close, commit or roll back a page's PDO or
PEAR business handle. No schema or business-write changes are introduced, and
these reads do not promise a snapshot shared with a page's other connections.
Every dependent write must continue to validate on its own transaction connection.

Preserved: public getter signatures, list/associative shapes, integer ID keys,
`proxy-`/`ippool-`/`huntgroup-` prefixes, labels, DISTINCT projections, active-plan
filter, ascending order where originally specified, group UNION/deduplication and
the online-user NULL/zero-date predicate. `get_active_plans` keeps distinct
(name,id) pairs rather than incorrectly collapsing two IDs with the same name.
The unordered group UNION has no new cross-driver ordering guarantee.

Intentional hardening: invalid configured identifiers or malformed username-table
selectors yield an empty result and a class-only log. Username table keys are
restricted to the four families observed in current application callers:
RADCHECK, RADACCT, DALOUSERINFO and DALOUSERBILLINFO. Missing tables/SQL errors also
return empty options instead of allowing a PEAR error object's fetch to crash.
The simple table-name contract excludes qualified, quoted or overlong identifiers.

The retained `list_from_db($sql)` API accepts **trusted application SELECT SQL**;
its SELECT-prefix check is not a general SQL security parser. Current getters
instead construct their queries within guarded callbacks, using validated tables.
There is no request-supplied SQL, generic write adapter or connection-policy switch.
The remaining unused renderers still use PEAR and require R28 caller reconciliation.

## Executed validation

```sh
PYTHONDONTWRITEBYTECODE=1 python3 -u tests/selectbox_reads_http.py
```

Exit 0 on real PHP 8.4.24 and MariaDB 11.8.9/PDO MySQL. The fixture pins the entire
baseline selector file at the above SHA; it uses equivalent disposable source
copies and schemas, custom table names, an internal network, tmpfs SQL and generated
SELECT-only connection material that is removed at teardown.

Checked all nineteen getters and retained string-SQL helper against baseline,
including output values/keys/order; four username-source tables; duplicate active
plan names and distinct usernames; UNION across all three group sources; literal
zero keys; quoted, percent, plus and Unicode fields; NULL dictionary values;
NULL and zero-date online sessions excluding stopped sessions; populated/default
and empty/named locations; an existing caller-owned PDO transaction remaining
usable; exact table checksum invariance before deliberate fixture changes; missing
and invalid tables; array/unknown selector keys; non-SELECT helper refusal;
authentication and ACL denial. The NULL dictionary case explicitly widens only the
disposable table columns and is not a schema change proposal.

Candidate `db_open.php` is an unconditional throwing tripwire. All getter/helper
requests pass without legacy opens. PHP stdout **and stderr** are captured, with a
positive `error_log` marker verifying the log channel. No PHP fatal/warning or
generated connection material occurs in those logs. Sessions come from a
fixture-only native PHP session writer; this is not a new primary-login test.

Existing native regression suites, executed on the combined candidate, exit 0:

- `tests/group_attributes_http.py`
- `tests/group_mappings_http.py`
- `tests/profile_duplicate_http.py`
- `tests/batch_create_http.py`
- `tests/plan_create_http.py`
- `tests/plan_edit_http.py`
- `tests/pos_provision_http.py`
- `tests/pos_update_http.py`
- `tests/invoice_create_http.py`
- `tests/invoice_edit_http.py`
- `tests/realm_proxy_http.py`
- `tests/operator_catalog_reads_http.py`

These exercise actual producing forms and their existing write/rollback/security
contracts; they do not establish that those entire pages are free of PEAR.
PHP lint on both changed source files, Python AST parsing and `git diff --check`
pass. A source-body comparison confirms all other selector-file functions,
including `get_operators`, are unchanged.

Initial fixture attempts exposed missing dictionary-schema import and required
column defaults under strict CLI SQL mode; the fixture now imports all three
schemas and applies the same permissive session setting as the application.
The final uninstrumented comparison passes. No production diagnostic hooks remain.

This is isolated native HTTP/database validation, not PostgreSQL/SQLite,
FreeRADIUS protocol testing, an external directory, installation or deployment.
No performance gain, cross-connection consistency or full migration completion is
claimed. No existing dependency, cron, live configuration, NAS volume or service
is changed. The preexisting `tests/__pycache__/` remains outside the commit.

R02b (common counters/existence/columns) and R02c (sidebars and realm/proxy lists)
remain pending. PEAR DB cannot be removed while these and other consumers remain.
No push or PR is performed by this slice.

**Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.**
