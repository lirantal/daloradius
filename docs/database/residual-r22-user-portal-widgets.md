# R22 — user-portal widgets and read providers

## Scope and baseline

Pinned baseline: `41b2b0ee4c3a930aeb298adda53cad020a86ba46` (R21d).
Eight inventory blocks: RES-207, RES-208, RES-209, RES-210, RES-212,
RES-213, RES-214 and RES-215, in five planned files:

- `app/users/include/management/userReports.php`: subscription, plan,
  last-session and online-status reads.
- `app/users/library/tables/overall_users_{login,download,upload}.php`:
  grouped native statistics and pagination.
- `app/users/library/graphs/overall_users_data.php`: actual chart JSON endpoint.

Three producing pages, `graphs-overall_{logins,download,upload}.php`, also
validate scalar period/unit controls before constructing their chart URLs.
The new `app/users/library/portal_widgets_pdo.php` reuses R21's checked,
parameterized row reader and the established location-aware PDO connector.
The operators copy and shared production PEAR chart dispatch are unchanged.

## Preserved contracts

- Identity comes from the authenticated portal session. HTTP username controls
  cannot select a different account. Provider calls reject a mismatched identity.
- Every data value is bound; tables, categories, group expressions and sort keys
  are validated against fixed allowlists. SQL debug contains templates only.
- Keep each source's different statistics: subscription login counts use
  distinct accounting session IDs, graph/table login counts use start times,
  subscription/table/chart queries require completed sessions, while plan usage
  includes unfinished sessions. Last-session selection still uses descending
  accounting ID; online status still accepts NULL or the historical zero date.
- Keep table monthly labels different from chart labels (full month names for
  login tables, abbreviated names for traffic tables/charts), numeric totals,
  NULL handling, unit conversions, footers, navigation and the 36-point graph cap.
- Preserve the legacy PHP daily/monthly/weekly boundary calculations, including
  the weekly exclusive Sunday-midnight upper bound and the existing mktime
  month rollover expression. R22 does not redefine subscription/billing policy.
- Optional borrowed PDO handles remain open, and readers never begin, commit,
  roll back or disconnect the caller's transaction. Online lookup uses the
  same handle as last-session lookup.
- Finish each widget's dependent reads before rendering its data. A failed
  widget reports `Portal statistics unavailable`; a failed graph returns
  HTTP 503 and a fixed JSON error without driver details. A successful earlier
  independent accordion is not claimed to be rolled back by a later read failure.

## Deliberate repairs, not baseline parity

- A group key is added after metric sorts, preventing tied LIMIT requests from
  duplicating/omitting periods. Compare complete membership and ordered metric
  ranks against PEAR; test candidate paginated membership independently.
- Traffic-table headings escape the session username instead of interpolating
  raw text into HTML. The differential normalizes only that characterized heading
  difference for the special-character identity.
- The graph account `0` is no longer lost by PHP `empty()` and matches its
  already-working statistics tables. Assert the pinned graph's empty dataset
  and the candidate's nonempty dataset separately.
- Array-valued period/unit/sort controls fall back safely rather than reaching
  string functions. Potentially NULL widget formatter results are converted
  to strings before HTML escaping, without changing the visible output.

## Executed validation

`python3 -u tests/portal_widgets_http.py` passed:

- **802** keyed PEAR/PDO comparisons of complete native HTML widgets/tables,
  table rows/footers/navigation and complete chart JSON envelopes/options/data.
- Five synthetic session identities on default and measurably distinct named
  databases, configured table names, all daily/monthly/yearly periods, both units,
  period/metric sort directions, bounded/out-of-range/empty pagination,
  missing plans, NULL-only aggregates, duplicate session IDs, zero stop dates,
  and records exactly before/at daily/monthly/weekly boundaries.
- Exact 36-point caps where enough groups exist, complete tied metric ranks and
  candidate pagination without duplicate/missing periods.
- Real parent-produced canvas URLs and sort/navigation links followed via HTTP;
  malformed arrays, account-selector isolation, direct/anonymous guards and
  SQL-debug binding redaction checked.
- Native missing/invalid sources and a native second LIMIT-read failure after
  the full group read on each of the three table routes. The ALTER hook exists
  only inside copied disposable fixture code, not production source.
- Borrowed silent-error-mode PDO transaction with an earlier caller write:
  both success and a later subscription SQL error retain transaction ownership,
  and caller rollback restores the prior state.
- All migrated routes work with SELECT-only grants. Read-only state invariance
  is compared in memory. Candidate PEAR open/close tripwires and PHP log gates pass.

Five adjacent regression suites passed:

| Suite | Verified result |
| --- | --- |
| `tests/portal_pages_http.py` | R21: 274 comparisons, mutation/ownership cases, real PDF/CSV and cleanup |
| `tests/operator_widgets_http.py` | R14: 353 complete JSON/HTML comparisons and native negative cases |
| `tests/shared_context_http.py` | R20: 133 comparisons, real isolated SMTP/PDF/tickets and transaction cases |
| `tests/messages_http.py` | Native message editor/provider, explicit PEAR compatibility and rollback/concurrency |
| `tests/operator_common_reads_http.py` | Seven borrowed readers, default/named, PEAR dispatch, ownership and SELECT-only |

The R21 fixture's obsolete widget PEAR exemption was removed. The R14 fixture
now explicitly pins its pre-R22 portal endpoint in both copies when testing
retained PEAR chart dispatch; otherwise freezing the old shared chart helper
alongside the new PDO endpoint would test incompatible revisions, not production.
R22's new native portal fixture tests the actual current PDO endpoint separately.

PHP lint passed for all nine changed/new production PHP files, Python parse and
compile passed for all three changed/new harnesses, the four existing PHP
PDO/password/template test scripts passed, and Git whitespace checks passed.
Original CRLF endings are preserved in the four legacy widget/table files.
Disposable SQL/container/network/config/session resources were removed;
pre-existing `tests/__pycache__/` is preserved. No live data or secrets were read.

## Limits and continuation

Validation is native HTTP/PHP 8.4.24/MariaDB 11.8.9 on disposable fixtures,
not browser painting, FreeRADIUS authentication, NAS/hotspot hardware,
PostgreSQL execution, external mail or deployment. No performance claim.

All eight R22 blocks and their three necessary producers are covered. Other
scheduled residual consumers and PEAR compatibility still require their own
migration/cleanup; no dependency is removed here. No push, PR or deployment.
