# R18 — Operator payment types

## Scope

Implemented without subagents on `refactor/pdo`, baseline
`f4c7aa4a941fcaa4c77d2235bece06ede89b62db` (completed R17).
RES-026/027/028/029 cover the four `bill-payment-types-{new,edit,del,list}.php`
pages. R18a contains new/edit and `library/payment_types_pdo.php`; R18b contains
del/list, native tests, the focused invoice-test correction and documentation.
No schema, R17 provider, notification, payment callback or live lab change.
No push, PR or deployment. PEAR remains for subsequent functional lots.

## Contracts and explicit policy

All four pages use the selected PDO backend and validated configurable tables.
Reads are strict numeric fetches; COUNT is explicit and pagination values are bound.
The existing allowed sort aliases/directions, form controls, readonly type identity,
notes-only edit and creation/update metadata remain. Blank edit notes deliberately
preserve existing notes, while explicit scalar `0` is retained. Unchanged edits
succeed, including when an UPDATE reports zero changed rows.

Names are scalar strings up to the stock 32-character column length; notes and
actors respect the stock 128-character limits. Raw percent/quote/plus/ampersand/
Unicode/zero identities are kept separately from escaped HTML labels. Create and
list action links encode the raw name, not an HTML-escaped version. Literal zero
preselection uses a nonempty string array so the shared selector's PHP `empty()`
check does not discard it. Stored create/edit values are re-read before commit;
permissive truncation in a narrower configured column causes rollback.

Mutations own one PDO transaction, require InnoDB and acquire metadata locks before
engine inspection. A caller's existing transaction and earlier write are preserved
on ownership rejection. A schema/table-scoped advisory lock serializes cooperating
name writes; duplicate or collation-equivalent names are rejected on create. Existing
ambiguous duplicate names cannot be edited or deleted accidentally. Selected names
are completely validated/deduplicated before writes; all corresponding type rows
are rechecked and locked in stable ID order. A stale later selection or late DELETE
failure restores every earlier row. Returned deletion counts are actual affected rows.

**Intentional reference policy:** deleting an in-use payment type is refused; a
mixed referenced/unreferenced selection is refused atomically. Payment rows are
never deleted, reassigned, zeroed or otherwise rewritten by R18. Historical orphan
payments are not repaired implicitly. This tightens the broken legacy deletion path
rather than preserving its ability to create additional missing type labels.

R17 already locks the type parent for explicit type creation/reassignment. R18 locks
the type row before checking references using a nonlocking **READ COMMITTED** read.
This sees a payment that committed while type-lock acquisition was waiting and avoids
inverting R17's payment-row-before-type order. No child-row lock is taken by R18.
The isolation choice applies to the owned next transaction only; borrowed active
transactions are rejected before changing transaction settings or acquiring locks.
The actual winning orders and existing-child edit were exercised with native workers.
Uncoordinated external/legacy writers without these type-parent locks remain a limit:
no foreign key or database-wide unique name constraint was added.

Errors expose only generic action messages and diagnostic exception classes, not
bound input or driver messages. Notices advise state verification before retrying
rather than claiming an uncertain commit could not have occurred. Read failure after
a committed edit/delete preserves both the successful mutation and separate read
failure. Advisory release failure cannot make a committed action appear rolled back;
the caller disposes its nonpersistent connection.

## Characterized baseline defects

- Text deletion names were passed through `intval()`: deleting `Type1`/`Type2` actually
  targeted the type named `0`. The unmodified PEAR SQL and persisted wrong-row outcome
  were asserted separately; the candidate deletes both intended unused types and
  preserves `0`.
- The legacy POST deletion page also passes its array-valued global name to a scalar
  sidebar, fatalling under PHP 8. Only the disposable baseline render fixture clears
  that array after the legacy action code. Its SQL is pinned and unchanged; this is
  not a claim that the unmodified baseline POST page renders successfully.
- Legacy create checks `COUNT(id) == 1`; with two existing duplicates it inserts a
  third. Native baseline behavior was demonstrated; the candidate rejects all
  nonempty duplicate sets, including case-equivalent names.
- Edit reads notes into `$notes` while rendering `$paymentnotes`. The baseline textarea
  is empty despite stored notes. Candidate reload/render/unchanged submission retains
  the real stored text. This defective textarea is excluded from ordinary differential
  control equality and asserted explicitly on both sides; audit fields are verified
  separately from generated actors/timestamps.
- Percent stripping, zero treated as blank, and URL-encoded HTML labels break name
  identity or links. Candidate raw controls/preselection/create/list links are tested
  independently with zero and quote/percent/plus/ampersand/Unicode identities.

## Executed validation

`python3 -u tests/payment_types_http.py` passes with **34** PEAR/PDO comparisons
covering rows/counts, unaffected form controls, both valid sort keys, invalid-key
fallback, seven pagination requests, missing identities, named locations, ordinary
create/edit state, notes skip/unchanged behavior and numeric-name deletion.

Candidate native HTTP/PHP/MariaDB checks also pass:

- Real stored notes, audit preservation, raw special names and actual generated links.
- Scalar/array/empty/length validation, entire stale/ambiguous/malformed/oversize
  selections, duplicate selection names, CSRF, anonymous and denied POST ACL.
- Referenced/mixed deletion refusal with unchanged payments; correct actual counts.
- Later DELETE trigger rollback after an earlier deletion, native INSERT/UPDATE
  errors, full physical type snapshots including audit columns, and InnoDB refusal
  for both type and payment tables.
- Real physical truncation in a fixture-narrowed value column rejected before commit.
- Configured tables, unsafe identifier rejection, unknown selected backend, named
  mutation isolation, broken-default/valid-named backend and SELECT-only reads.
- Caller-owned transaction containing an earlier real INSERT remains owned/intact.
- Two independently blocked creators wait on the exact advisory lock; one creates
  and one rejects the duplicate. The lock name's actual bounded length is asserted.
- Three real R17 races: payment commits before type deletion (deletion refuses),
  type deletion commits before payment creation (creation refuses), and an edit
  holding an existing payment child while type deletion detects the committed
  reference without waiting on that child. Actual lock waits are observed for
  the first two; the third refuses while the editor remains deliberately paused.
- Three late SELECT failures on edit/delete/list, plus separate committed edit and
  committed delete followed by read failure. Creation has no page-local GET query;
  its actual SQL failure path is covered by an INSERT trigger.
- Throwing PEAR open/close tripwires on all four routes and successful create/edit/
  delete. Candidate PHP diagnostics from stdout **and** stderr contain no fatal,
  warning, notice, uncaught exception or SQLSTATE disclosure.

Four adjacent regression suites pass:

- `payments_http.py`: R17, **38** PEAR/PDO comparisons and concurrent parent locks.
- `invoice_reads_http.py`: R15, **71** comparisons plus real fixture PDF/CSV paths.
- `invoice_delete_http.py`: related parent deletion.
- `operator_common_reads_http.py`: selected-backend/shared selectors.

A pre-existing nondeterministic R15 test failure was traced to checkbox order within
SQL sort ties: row ranks/membership were already normalized, but checkbox controls
were not. Four test-only lines now compare the complete checkbox multiset only in
those already-normalized tie cases; ordered sort ranks and all other controls remain
checked. No invoice production source changed. The final combined run passes all
five suites, including R15 and R17, without hiding the initial failures.

PHP lint, Python compilation and `git diff --check` pass. All fixtures use disposable
internal Docker networks and tmpfs SQL; no live configuration/data is copied and no
credential or response snapshots are retained. Containers/networks/scratch fixtures
are removed. The pre-existing `tests/__pycache__/` directory is preserved and its
bytecode remains untracked.

## Limits

These are actual isolated PHP/HTTP/SQL tests with synthetic data, not full-browser,
production, remote provider payment, FreeRADIUS or NAS/hotspot hardware validation.
Reference/name guarantees require cooperating writers; existing schema-wide unique
constraints/foreign keys were not added, existing duplicate/orphan data is not cleaned,
and no automatic replay/idempotency token was introduced. Subsequent PEAR families
remain pending and must finish before dependency removal.
