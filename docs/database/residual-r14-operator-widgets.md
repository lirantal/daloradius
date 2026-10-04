# R14 — operator charts, statistics tables and dashboard

## Scope, base and slices

Base: `d0a8a9d20016130e56ff723350004d395a64eddd`; branch: `refactor/pdo`.
The thirteen original members and their residual IDs were reconciled against
`finalization-lots.csv` and `residual-blocks.csv`, not inferred from historical totals.
Implementation and validation were performed without subagents.

| Slice | Original files/residuals |
|---|---|
| R14a | Common `chart.php` (RES-001), operator all-time/overall JSON data (RES-119/125); new common prepared-read and operator widget providers |
| R14b | Logged/new/total-user charts (RES-121/122/126), all-time/per-user login tables (RES-127/129), their two producing login pages |
| R14c | Per-user upload/download tables (RES-128/130), their two producing traffic pages |
| R14d | Dashboard (RES-053), online-user/NAS charts (RES-123/124), differential fixture and completion documents |

Nineteen production files are changed: thirteen original members, two small
providers (`common/includes/chart_pdo.php`, `operators/library/widget_reads_pdo.php`)
and four necessary producing parent pages. The parents previously parsed inputs
before including their statistics extension and built chart URLs from HTML labels;
they must accompany their included table migrations to preserve actual identities.
No user-portal endpoint, schema, external service, live data or installation dependency
is changed by this lot.

## Coexistence and contracts

`dalo_chart_overall_user_statistics()` dispatches on a supplied PDO handle and
otherwise keeps its original PEAR implementation **exactly unchanged**. The real
remaining user-portal graph still calls that PEAR branch; it is explicitly tested.
That branch remains until its user-portal/R22 consumers migrate. Completing R14
is not removing PEAR or declaring the common file free of legacy code.

The common prepared reader borrows its caller's handle: it never opens another
connection or begins/commits/rolls back a transaction. Native numeric fetches
preserve original row shapes (FETCH_BOTH would duplicate columns when a dashboard
renders every array item). Operator pages open PDO explicitly on the session's
selected location; configured table identifiers are validated and quoted. Usernames
and date values are bound. Aggregate/period/sort choices are fixed allowlists;
pagination offsets/limits are calculated by the existing module and rendered as integers.

Retained policies include:

- Daily/monthly/yearly grouping; descending graph order, 36-group chart limits,
  titles, series styles, labels and existing decimal MB/GB conversions.
- All-time graph login uses COUNT(AcctStartTime), whereas the all-time statistics
  table uses COUNT(username). Their historical NULL distinction is deliberate.
- Per-user graph and tables retain exact user lookup and completed-session
  `AcctStopTime > 0` filtering. Monthly login tables use full month names while
  traffic tables and charts retain their respective abbreviated formats.
- Logged-users keeps its original midnight/day comparison, hour-loop rules,
  zero-traffic/session open predicate, and inclusive first day of the next month
  in its monthly iteration. These unusual rules were not silently redesigned.
- New-users retains optional valid start/end dates, whole-inclusive end day,
  ascending month groups and the old invalid-scalar-date fallback to no bound.
- Online users retains distinct counts, clamped offline counts and empty output
  when there are no radcheck users. Online NAS retains its inner NAS join.
- Total-user/dashboard counts retain required userinfo and authentication-attribute
  predicates. Other dashboard cards retain NAS/hotspot counts; recent attempts and
  online/top lists retain their limits and last-month SQL selection. Both legacy
  FreeRADIUS 1 `user`/`date` and later `username`/`authdate` columns are covered.
- Group counts, total calculations, default sort choices and table footers/paging
  remain intact. No performance or cross-query snapshot-isolation promise is made.

## Scoped behavior repairs and failures

Literal zero/percent, quotes, Unicode, ampersands and plus signs are preserved.
Table headings escape names and actual canvas/sort URLs encode raw identities;
array controls are rejected before legacy parent templates can call trim/strtolower.
NULL labels/aggregates render without candidate PHP deprecations.

Direct JSON producers now enforce an existing producing page's ACL, rather than
accepting any logged-in operator. All-time login versus traffic and per-user
categories map to their respective parent; total users accepts either management
producer. The established ACL reader preserves its first-row duplicate policy.
An unauthenticated request still follows the original login redirect. Dashboard
itself keeps its original logged-in access contract; no new dashboard ACL is invented.

A failed JSON read returns HTTP 500 and the fixed JSON error
`Unable to read widget data`, never exception messages, SQLSTATE or SQL contents.
HTML read failures use the existing generic action-message view. Failed dashboard
card reads show unavailable data, not fabricated zero totals; a later failed section
keeps earlier completed sections and exposes a generic error. Table extensions
catch errors from both full grouped reads and the later paginated read. Identifier
failures are caught as well. Logging records templates/exception classes only,
not bound values or connection factors. The forgiving shared count selector is
not used for the total-users endpoint's strict error contract.

## Executed validation

`python3 tests/operator_widgets_http.py` completed with exit 0 on disposable native
PHP **8.4.24** and MariaDB **11.8.9**. The fixture pins all thirteen baseline sources
plus four producing parents at the base commit in an otherwise equivalent app tree.
SQL clock freezing is confined to fixture connection copies so dashboard windows
are deterministic. Synthetic identities and runtime-generated connection material
are temporary; credential columns are excluded from comparison projections.

- **353 complete PEAR/PDO JSON/HTML comparisons**: all chart categories, three
  periods, both units, populated/absent/NULL-only users, every offered table sort,
  both directions, multiple pages, empty datasets and all dashboard tables/cards.
- Graph envelopes, options, labels, complete datasets and ordering are compared,
  not just HTTP status. Long monthly histories exercise the 36-point limit.
- Actual parent-generated canvas URLs are parsed, requested and checked for the
  exact identity. Actual relative sort links are followed with the same filters.
- Configured tables and distinct named-location records are tested. Native
  SELECT-only grants execute all graph/table/dashboard routes and a selected-location
  chart request, rather than merely probing connection setup.
- Source-ACL denial, duplicate first-deny/second-allow ACLs, unauthenticated redirects,
  malformed arrays, bound injection attempts and direct extension/provider guards
  are exercised. The complete remaining portal PEAR graph response matches baseline.
- A caller-owned PDO transaction remains active after a successful common-chart read
  and an actual failed SELECT; only the caller rolls it back.
- A fixture-only column rename causes a real later paginated SELECT failure in
  **each of the four statistics extensions**, after the full grouped dataset was
  fetched. Missing accounting/userinfo/radcheck/NAS sources cover every graph family;
  malformed identifiers test both JSON and dashboard failure paths.
- Throwing legacy db_open/db_close tripwires cover all seven JSON graph endpoints,
  the four actual statistics parents and dashboard. The common compatibility branch
  is tested separately before installing those tripwires.
- Candidate logs have no PHP warnings/deprecations/fatals or SQLSTATE output.
  Containers, networks and ephemeral config/session/data trees are verified removed;
  no live data or credentials are copied or retained.

Five regression suites passed with checked exit 0:

1. `tests/operator_reports_http.py` — 197 R13 comparisons and CSV/error contracts.
2. `tests/accounting_pages_http.py` — 221 R11 comparisons.
3. `tests/report_export_http.py` — complete export producers.
4. `tests/operator_acl_reads_http.py` — retained authorization contracts.
5. `tests/operator_common_reads_http.py` — retained PEAR/PDO readers and borrowed handles.

Native PHP lint passed for all nineteen production files; Python compilation and
`git -c core.whitespace=cr-at-eol diff --check` passed. Original CRLF files remain
CRLF. Pre-existing `tests/__pycache__/` remains untracked and is not deleted.

## Limits and delivery

This is real native HTTP/PHP/SQL execution with synthetic fixtures, not a full
browser session or proof of Chart.js canvas painting. It does not exercise a real
NAS/hotspot, FreeRADIUS protocol flow, production backend, install/upgrade path or
simultaneous accounting writers. Existing legacy date/hour policy is retained;
changing it is a separate behavior request. Shared chart PEAR compatibility and
all other active consumers remain installed.

Four local R14 slice commits only; no push, PR, persistent service or lab deployment.
Progress is recorded per scope; historical residual counts are not subtracted to
invent a current global migration remainder.
