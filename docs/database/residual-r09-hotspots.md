# R09 — hotspot pages, information and chart reads

## Scope and baseline

- Baseline: `493027ebb4ab078d83eac303ffebf80d14199c40` on `refactor/pdo`.
- R09a: `mng-hs-new.php`, `mng-hs-edit.php`, explicit shared
  `library/hotspot_pages_pdo.php` provider.
- R09b: `mng-hs-del.php`, `mng-hs-list.php`.
- R09c: `library/ajax/hotspot_info.php`, `library/graphs/hotspot_details.php`,
  native fixture and this report. These close RES-110/120/135–138.
- Shared R01 authorization/R02 selectors are reused. The generic chart and JSON
  helpers, accounting producer pages and other still-PEAR consumers are not
  silently converted or removed.

## Behavior and safety

All page-local SQL explicitly uses the selected-location PDO connection.
Identifiers and sort columns/directions are allowlisted; values and pagination
are bound. Creation keeps the global name-or-MAC collision rule; editing keeps
its name/ID immutable and excludes itself from MAC collisions. Every contact,
company, type and geocode field is checked against its actual configured column
capacity and reread before commit, including audit fields. Creation metadata is
preserved on edits. Invalid optional email strings still become empty strings,
as in the baseline; ordinary empty optional fields retain their old defaults.

Mutations refuse borrowed transactions and non-InnoDB hotspot tables. A
schema/table-scoped advisory lock on the same PDO handle serializes cooperating
creates, edits and deletes; metadata/row locks, verification and commit belong
to that connection. This is not a new database uniqueness constraint and does
not serialize uncoordinated legacy or external writers.

Deletion parses/deduplicates the entire selection, rejects malformed, truncated,
stale, collation-mismatched or ambiguous names, locks all selected rows before
any delete, and rolls back the entire batch on a later failure. Visible order
is preserved. As before, deletion only removes hotspot rows: accounting rows
and billing `hotspot_id` references are not cascaded or reassigned. The fixture
explicitly checks that this historical orphan-reference behavior is unchanged;
this lot makes no accounting-retention or billing-cleanup policy decision.
Editing the address naturally changes which historical sessions join by
`calledstationid = hotspots.mac`; IDs remain stable for billing references.

The AJAX upload/download/hits shape and chart titles, pie data, integer
conversion, descending aggregate order and color palette are retained. Both
read configured accounting/hotspot tables on the selected backend. SQL errors
return generic JSON/500 without driver details. AJAX retains its accounting
permission; the chart now enforces the `acct_hotspot_compare` producer ACL
(403 when denied), rather than admitting any logged-in operator. Both reject
non-GET requests with 405. The shared chart helper stays compatible with its
other callers pending R14.

## Intentional differences, not claimed parity

The pinned, unmodified PEAR pages demonstrate ignored stale selections and
partial earlier deletes after a later trigger failure. The candidate rejects
or rolls back those batches. The old edit renderer assigns the stored `type`
to a different variable from the form control, displaying an empty type; the
candidate displays and resubmits the stored type correctly. The baseline also
accepts a malformed MAC suffix because its validation regex is not anchored;
the candidate checks the whole MAC/IPv4 string. Exact names, including percent,
quotes, ampersands, plus, Unicode and literal `0`, remain raw identities across
forms, links, checkbox values and AJAX. HTML/URL encoding is applied only at the
output boundary, rather than stripping percent or encoding an HTML label as an
identity. Optional literal-zero fields are likewise retained. These repairs are
separate from ordinary PEAR/PDO comparisons.

## Verified execution

`PYTHONDONTWRITEBYTECODE=1 python3 -u tests/hotspot_pages_http.py`

The fixture uses six pinned PEAR entry points and candidate entry points with
equivalent disposable schemas, actual native PHP/HTTP/MariaDB, seeded authenticated
sessions and multiple PHP workers. Observed runtime: PHP 8.4.24 and MariaDB
11.8.9-MariaDB-ubu2404. No mock SQL responses or real account login are claimed.

Passed:

- Native create/edit/delete persisted business-state parity, audit-presence
  parity, stable creation metadata and invariant accounting/billing references.
  Generated audit timestamps/identities are not public comparison artifacts.
- **55 comparisons**: 40 list sort/direction/page projections, one edit-form
  comparison excluding the characterized type-renderer defect and generated
  audit fields, five ordinary chart categories (including fallback), four
  ordinary information lookups, and five NULL-aggregate information/chart
  comparisons. NULL traffic remains `(n/a)`; chart aggregates retain native
  integer conversion. Empty datasets are separately exercised.
- Actual returned create/edit links and unchanged form resubmission, including
  the address textarea. The fixture's form parser captures textareas as well
  as input controls; the earlier input-only parser could incorrectly erase
  address in a simulated submission.
- Full-state rollback after native INSERT/UPDATE and later DELETE triggers;
  stale preview followed by POST, nested/empty/truncated selections, duplicate
  and ambiguous identities, other-hotspot invariance, invalid/capacity/charset
  readback failures, 200-character Unicode and zero-valued fields.
- Independent-session create collisions by name and MAC, edit/delete races,
  actual independent advisory-lock contention, and borrowed transaction
  rejection without committing its prior write.
- Seeded authentication/expiry, page and producer ACLs, scalar CSRF checks,
  malformed controls/sorts, real SELECT-only reads and denied writes.
- Named-location routing with an additional accounting record present only on
  that backend; configured hotspot/accounting table names, absent/invalid/late
  reads, generic errors, template-only SQL debug, and clean candidate PHP logs.
- Candidate legacy `db_open.php` and `db_close.php` tripwires, PHP lint for all
  seven scoped PHP files, Python source compilation and `git diff --check`.

Five native regression suites also passed:
`selectbox_reads_http.py`, `operator_common_reads_http.py`,
`operator_sidebars_catalogs_http.py`, `huntgroup_pages_http.py`,
`user_create_import_http.py`. The previously blocked synthetic
`readonly_info_http.py` fixture is not counted as passing or used as evidence.

All fixture SQL, session/config data, containers, networks and temporary trees
are disposable; their cleanup is checked. No credentials, tokens or login
identifiers are retained in reports. Pre-existing `tests/__pycache__/` is
preserved. No push, PR, live deployment, real FreeRADIUS operation, GIS or
browser Chart.js interaction is claimed. PEAR remains required for other lots.
