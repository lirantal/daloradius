# R19 — rate catalogue, dated usage, history and merchant transactions

## Scope and coexistence

Base: `7f06b3fcc03d2ab40a9240c49ffb5edd23bc307e` (R18).
Covers RES-019/025/040/041/042/043/044: `bill-rates-{new,edit,del,list,date}.php`,
`bill-history-query.php` and `bill-merchant-transactions.php`.

The new `library/billing_rates_pdo.php` borrows the shared selected-location PDO
connection policy and strict numeric-fetch reader. Two necessary producers are
also repaired: the rates sidebar removal route and merchant sidebar filter state.

The independently connected `userBillingRatesSummary()` and
`userBillingPayPalSummary()` remain unchanged PEAR consumers scheduled for R20.
They run only after page-local reads succeed, outside any mutation transaction.
Other legacy consumers and the PEAR installation dependency remain in place.
No schema, live configuration/data, installation dependency or persistent service
is changed by this lot. Nothing is pushed, published or deployed.

## Preserved contracts

- Configured table identifiers are validated; values are bound. PDO uses the
  session-selected backend, not a silently substituted default connection.
- Rates retain their immutable exact name, creation metadata and ordinary partial
  edit policy: empty/zero cost does not replace the current positive cost; an
  omitted unit does not replace the stored type. A valid unit with an omitted or
  zero number defaults to one on edit. Unchanged edits remain successful.
- Stock `rateCost` is an **INT**, not a DECIMAL currency amount. Ordinary positive
  decimals retain PEAR's truncation: `12.75` stores `12`; duration `2.50` stores
  `2`. This is explicitly tested against PEAR. This lot does not silently change
  pricing precision or add a decimal schema migration. Malformed, negative and
  overflowing numeric inputs are rejected rather than coerced from arbitrary text.
- The dated detail query keeps DISTINCT over its five output columns and the
  exclusive `AcctStartTime > startdate` / `< enddate` midnight bounds. Its retained
  summary has its own inclusive bounds and exact-username policy.
- All six time units retain the historical coefficients, including the unusual
  `month = 187488000`; changing billing policy is outside a driver migration.
- Merchant records retain inclusive whole-end-day selection, vendor/status
  allowlists and substring email search. Existing decimal/text amounts and NULL
  fields are rendered without mutating the merchant/history/accounting tables.
  Address/payer status controls retain their legacy summary-only effect, not a
  newly invented table filter.
- History keeps its selectable-column allowlist, substring username/action
  predicates, output column order, pagination and collated search semantics.
  Duplicate selected columns are deduplicated; malformed column selections fail
  closed. Ordinary scalar invalid dates fall back to the established defaults.
- Reads are borrowed-handle operations. Counts and complete page rows are loaded
  before table emission; later SQL failures render a generic complete page rather
  than partially printing a results table. Exceptions log classes, not driver
  messages or bound data. A successful committed write remains visibly successful
  even if its subsequent form/selector read fails.

## Mutation policy and intentional repairs

Creation, edit and full-selection deletion own one transaction after metadata-lock
acquisition and InnoDB preflight. A database/table-scoped advisory lock on the same
nonpersistent handle serializes cooperating catalogue writers. Names and numeric
values are validated before writes; stored names, values and operator metadata are
verified before commit to reject permissive truncation in narrower configured
columns. A caller-owned transaction is rejected without altering it.

Every deletion name is validated and rechecked before any delete; stale, malformed
or ambiguous later selections reject the whole batch. Names are deduplicated and
sorted. A late deletion failure restores all earlier deletions. No history,
merchant, accounting or unrelated catalogue rows are cleaned up or reassigned.

Intentional, characterized differences from PEAR:

1. Creation rejects any duplicate/collation-equivalent name, not only a count of
   exactly one; edit/delete reject ambiguous identities rather than mass mutation.
2. Literal `0`, percent, plus, quotes and Unicode remain raw identities. Display
   escaping is separate from links and checkbox values. LIKE `%`, `_` and backslash
   in explicit filters are treated as literal text, not wildcard injection.
3. Rate-list checkboxes contain actual rate names (the old variable was undefined),
   list actions point to real billing-rate routes, and success links encode raw
   names rather than HTML labels.
4. The sidebar's “Remove rate” link targets rate deletion, not plan deletion.
   Merchant filters remain selected in the real producing sidebar, including the
   previously missing global payment-status alias.
5. Delete notices count actual affected rows rather than casting a PEAR result;
   invalid partial selections no longer silently apply their valid subset.
6. Stored zero/invalid rate divisors fail closed before division. The repeated
   pagination include in the old dated page is eliminated.

Without a schema uniqueness constraint, foreign keys or universal participation in
this advisory lock, external/uncoordinated writers remain outside the concurrency
and exact-identity guarantees. Read count/page/summary calls are independent reads,
not a promised cross-client consistent report snapshot. No automatic replay follows
an uncertain mutation result; the user is asked to check current state first.

## Executed validation

`python3 tests/billing_rates_http.py` runs disposable internal-network containers,
native PHP 8.4.24 and MariaDB 11.8.9, synthetic sessions and equivalent default/named
schemas with configured table names. Live application configuration/data are never
copied. Generated connection/session material exists only in the disposable
fixture and is removed; credential/card columns are excluded from comparison
snapshots and sort projections. Legacy field-selection permissions themselves are
unchanged; this is not a merchant credential-display policy redesign.

Observed final results:

- **165 PEAR/PDO comparisons**: complete ordinary forms/rows/footers, non-secret
  selectable columns in both sort directions, rank sequences plus row multisets
  for tied sorts, unique-key pagination, named backend routing, ordinary CRUD,
  decimal truncation, six time units, missing identities and date boundaries.
- Candidate raw identity/actual producer links, zero filters, stored-field
  invariance, malformed arrays/overflow/lengths, duplicate/ambiguous names,
  ACL/login/CSRF, unsafe identifiers, SELECT-only grants, MyISAM rejection and
  permissive physical-column truncation refusal pass.
- Native INSERT/UPDATE trigger errors preserve full catalogue state. A later
  DELETE trigger with an independent MyISAM visit recorder proves the first
  deletion ran before the second failed and both catalogue deletions rolled back.
- Caller-owned write + strict read + refused mutation + caller rollback pass.
- **Four observed native concurrency scenarios**: two creators wait on the actual
  advisory lock and exactly one creates; two removers wait and only one deletes;
  edit-before-delete serializes successfully; delete-before-edit rejects the stale
  edit. Waiting server processes are observed before releasing fixture barriers.
- **Six actual late-read routes** pass (edit, delete selector, list, dated report,
  history, merchant table), including four faults after a successful COUNT.
  Committed edit and delete followed by display-read failure retain both the
  success notice and their persisted mutation.
- PEAR open/close tripwires pass on CRUD/list/history and, after disabling only
  the independent R20 summaries in copied fixtures, on the two report routes.
  Normal unmodified summaries were exercised before those scoped tripwires.
- Candidate PHP logs contain no Fatal, Warning, Notice, Uncaught or SQLSTATE output.

The untouched PEAR dated page first reproduces HTTP 200 with incomplete HTML and
`Cannot redeclare function printLinks()`. Only its duplicate include is changed to
`include_once` in the copied baseline before comparing its unchanged SQL/formatting.
The baseline deletion sidebar is scalarized only after its unchanged action logic
so a posted array does not crash PHP 8's scalar form renderer. Both necessary
baseline sidebars are pinned to the base commit. These are explicit test-fixture
adaptations, not replacements of PEAR SQL with candidate logic.

Four adjacent native suites also exit zero:
`payment_types_http.py` (34 comparisons), `payments_http.py` (38),
`catalog_reads_http.py` (77) and `operator_common_reads_http.py`.
PHP lint, Python compilation and diff whitespace checks are required before commit.

All assertions execute in the normal test command; production source has no
synchronization or failure hooks. Fixture containers/networks/files are removed.
Validation is native isolated HTTP/PHP/MariaDB, **not** a full-browser workflow,
provider sandbox/live financial exchange, external SMTP delivery, real FreeRADIUS,
or physical NAS/hotspot test. PostgreSQL, alternate MySQL versions and production
upgrade/deployment are not established by these MariaDB fixtures.

## Local commit slices

- R19a: shared provider and rate creation/editing.
- R19b: rate deletion/listing and the actual removal sidebar producer.
- R19c: dated-rate and billing-history page-local reads.
- R19d: merchant local reads/filter producer, differential fixture and documentation.
