# R13 — operator reports and user/batch lists

## Scope and coexistence

Base: `9ca89687a94f4fd2a8392585d23f78be2495b915`; branch: `refactor/pdo`.
All eleven original lot members are implemented, without subagents:

| Slice | Source pages | Original residuals |
|---|---|---|
| R13a | `mng-list-all.php`, `mng-search.php` | RES-140, RES-179 |
| R13b | `mng-batch-list.php`, `rep-batch-list.php`, `rep-batch-details.php` | RES-133, RES-183, RES-184 |
| R13c | `rep-lastconnect.php`, `rep-online.php`, `rep-topusers.php` | RES-187, RES-189, RES-190 |
| R13d | `rep-hb-dashboard.php`, `rep-history.php`, `rep-newusers.php` | RES-185, RES-186, RES-188 |

The page-local queries, counts, pagination and user/group status reads use
`library/operator_reports_pdo.php` on the selected location's PDO handle.
Configured identifiers are validated, data values are bound, and offered sort
keys are mapped to fixed SQL identifiers. Counts cover the complete joined or
grouped dataset, including history's UNION; no SELECT `rowCount()` is used.
Connection open/close and PEAR result/error APIs were removed from these pages.
The provider has a direct-access 404 guard and contains no production mutations.

Existing fixed CSV builders/endpoint remain unchanged. The report display keeps
its own projection and formatting while reusing applicable bound export predicates.
Seven pages produce CSV contexts; batch details has two distinct export controls.
History/new-user/heartbeat pages do not have a supported CSV family: they expose
no CSV controls and clear previous descriptors. Independent graph requests and
browser widgets are **R14**, not silently migrated by this lot. PEAR and its
compatibility branches remain installed for all other active consumers.

## Preserved contracts

- User lists retain the authentication-attribute predicate, required userinfo,
  maximum framed address/last connection and disabled-group status. Search
  retains its optional accounting/reply joins, broad user/contact LIKE matches
  and the historical reply-value **suffix** pattern. LIKE wildcards remain
  patterns rather than being reinterpreted as literal characters.
- Batch lists retain their joins, distinct billing/user aggregates and displayed
  costs. The legacy nonaggregated plan selection within a mixed-plan batch is
  not redefined. Details retains batch resolution, summary counts, username
  filtering and active users' last-start data; it is not an online-only selector.
- A valid batch summary can still export **all batch users**, even if its filtered
  active-users table is empty. This is a current, authorized batch descriptor,
  not another page's stale context. The established ActiveUsers primary type
  remains necessary for the endpoint's TotalUsers override.
- Connection/reply filters and individual date endpoints are preserved, including
  FreeRADIUS 1 `user`/`date` versus later `username`/`authdate` columns.
- Top-users retains completed-session aggregation and its original grouping;
  new-users retains month/year grouping and inclusive end-day behavior.
- History retains all seven UNION sources. Heartbeat dashboard retains the node
  left join, CPU percentage strings, NULL data and formatting.
- Arbitrary ordering of rows tied on the selected legacy key remains unspecified.
  Tests compare complete row sets and ordered **sort-key sequences**, then test
  pagination separately; they do not impose a new tie-breaker policy.

## Explicit corrections and failure behavior

Raw identities are kept separate from escaped display labels and encoded URLs,
including zero, percent, quotes, Unicode, ampersands and plus signs. Present
array-typed filters are rejected without reaching templates or SQL. ACL runs
before request validation. Query identifiers/sorts are allowlisted; ordinary
text and attempted SQL injections remain bound values.

A native late SELECT failure after a successful count produces the generic
`Unable to read accounting records`, suppresses CSV controls and clears export
state. Batch summary and active-user/group reads are loaded before either export
control is emitted, so a later read failure cannot leave an earlier CSV control.
Error messages/logs never contain driver exception messages or SQLSTATE details.
The same message view now handles both first-query and later-query failures.

The unmodified baseline online report crashes on NULL traffic with the precise
PHP `Unsupported operand types: string + string` signature. This is characterized
separately, not counted as parity; the candidate treats NULL traffic as zero and
renders the report. The dashboard's pre-existing undefined pagination fragment is
initialized. Unsupported CSV controls on history/dashboard/new-users are removed.

## Validation actually executed

`python3 tests/operator_reports_http.py` completed with exit 0 against disposable
native **PHP 8.4.24 / MariaDB 11.8.9** fixtures. No production database or persistent
service is involved. The fixture replaces only the eleven baseline pages with
pinned source in an otherwise equivalent application tree; it does not invent
PEAR/PDO responses.

- **197** display comparisons, including complete table/foot projections,
  ordered displayed sort-key sequences for exposed keys, both directions,
  hidden/visible columns, filters, reply variants, date bounds and pagination;
  three heartbeat soft/hard threshold colors and actual filtered sort-link requests.
- Complete parsed CSV headers and rows from all seven producers, including both
  batch-detail controls, not just download HTTP status.
- Non-default configured tables; a separately seeded named location with an
  identity absent from the default backend; SELECT-only account execution of
  all eleven default-location pages.
- Read-only domain projections exclude credential columns. Malformed input,
  attempted injection, ACL denial (including malformed denied requests), safe
  labels and exact raw user links are exercised.
- A fixture-only column-rename hook causes an actual **later SELECT failure on
  every one of the eleven routes**, after its native count succeeded. All show
  the generic failure, omit CSV controls and clear the descriptor. Hook source
  exists only in the disposable application copy, not production code.
- Missing accounting tables, stale cross-page exports and valid-but-empty batch
  summary export behavior are tested independently.
- Throwing legacy `db_open.php` / `db_close.php` tripwires cover all eleven HTTP
  entry points. Candidate logs contain no PHP warnings/deprecations/fatals or
  SQLSTATE details. Containers, networks and temporary config/session trees are
  verified removed; runtime-generated connection material is never saved in
  reports, source values or comparison snapshots.

Five focused regression suites passed:

- `tests/accounting_pages_http.py` — 221 R11 comparisons.
- `tests/accounting_advanced_http.py` — 52 R12 comparisons, rollback and concurrent purges.
- `tests/report_export_http.py` — full accounting/user CSV and bound group export.
- `tests/group_mappings_http.py` — both dispatches, rollback and native lock tests.
- `tests/operator_acl_reads_http.py` — ACL parity, selected locations and fail-closed reads.

The R12 concurrent-purge wait assertion timed out once during a parallel suite
run; its isolated serial rerun passed, including observing both waiting native
transactions. No R12 production or fixture source was modified to hide that
intermittent timing result. An older standalone export-differential script requires
its own `DALO_SOURCE_ROOT` setup and was not counted as a passed regression;
R13's complete CSV differential coverage is in the new fixture above.

Native PHP lint passed for all twelve production files, Python source compilation
passed, and `git -c core.whitespace=cr-at-eol diff --check` passed. Original file
line endings are preserved. Pre-existing `tests/__pycache__/` is retained untracked.

## Limits and delivery

These are real isolated HTTP/PHP/SQL tests, not a complete browser session,
FreeRADIUS protocol run, hotspot/NAS hardware test, production deployment or
fresh-install/upgrade validation. Browser-requested graph providers remain R14.
No snapshot isolation against a concurrent accounting writer is newly promised;
counts and page reads retain separate read behavior. No performance claim is made.

Local slice commits only: no push, PR or lab deployment. Dependency removal and
the final cross-domain/installer audit remain separate finalization gates.
