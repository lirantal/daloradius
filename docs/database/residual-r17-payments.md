# R17 — Local operator payments

## Scope and baseline

Implemented without subagents in `refactor/pdo`, based on
`2c7dc3e603bfbced669ddaa32b3ec161f3fd5ff9` (completed R16).
The original inventory scope is RES-030/031/032/033:

- R17a: `bill-payments-new.php` and the shared `library/payments_pdo.php` provider.
- R17b: `bill-payments-edit.php`.
- R17c: `bill-payments-del.php`, `bill-payments-list.php`, native tests and this report.

No schema, payment-notification, remote payment-service, user-account or installation
changes. No push, PR, deployment or modification of the running lab.
PEAR remains installed for R18 and the other residual consumers.

## Read and mutation contracts

All four pages select the session location explicitly through the PDO connection
factory. Unknown locations fail closed; a broken default backend does not prevent a
valid named backend from working. Configured identifiers are validated and quoted;
data values are bound, including list filters and integer LIMIT/OFFSET parameters.
The type selector retains its `paymentType-ID` control values and optional blank type.
The edit form retains audit fields, descriptors and unchanged/partial-edit behavior.

Amounts are canonical decimal strings for the existing signed DECIMAL(10,2) column:
no float conversion, exponent notation, commas, implicit rounding or out-of-range
coercion. Leading zeros and one fractional digit are normalized exactly. Creation
preserves negative/refund amounts and rejects zero. Editing preserves the historical
skip policy for absent, zero or negative amounts and for blank notes/date/type;
it updates audit columns even on an unchanged submission. Explicit scalar notes `0`
are retained. Dates are exact valid calendar dates; creation defaults a missing/blank
date to the current date, while malformed explicit dates are rejected rather than
silently replaced. IDs must be positive signed-INT-compatible scalar decimal IDs.
Notes and audit actor lengths respect the existing 128-character columns.

Creation, edit and complete-selection deletion each own one PDO transaction. Before
any write, participating tables have metadata locks and must be InnoDB. Caller-owned
transactions are rejected without commit/rollback of the caller's earlier changes.
The provider also validates operation shape, normalized values and identifiers,
independently of the HTTP parsing layer. Deletes validate, deduplicate, bound and
sort the complete selection; a missing, malformed or stale member rejects the whole
operation. No earlier deletion survives a later SQL failure. Historical orphan
payments remain readable and deletable, but new/relinked payments require live invoice
parents. Explicit selected types are checked and locked in the write transaction.

Invoice locks precede payment locks, in sorted parent-ID order, matching the existing
invoice/POS/batch deletion providers. Edit/reparent/delete discover old parent IDs,
lock the parent set, then lock/recheck every child; concurrent disappearance or parent
change fails closed. New payments cannot commit after a cooperating parent deletion
has already committed. Snapshot conflicts are rolled back and reported as failure;
there is no automatic replay of an uncertain mutation. Failure notices tell the
operator to verify current state before retrying, rather than asserting no write
when a commit acknowledgement could have been lost.

Read failures return a redacted message and complete HTML, with only exception classes
in diagnostic actions. A successfully committed edit followed by a failed display
read keeps the successful mutation visible alongside the separate read failure.
No SQL values or driver exception messages are added to payment debug output.

## Characterized legacy defects and intentional differences

- The original deletion page fatals in PHP 8 at `layout.php:1449`: its array-valued
  global payment ID reaches the scalar sidebar `trim()`. The candidate separates
  the selected IDs from the scalar sidebar value. For differential comparisons only,
  the pinned baseline fixture scalarizes that sidebar global **after** the original
  action logic. Its PEAR SQL is otherwise unchanged. This is not an assertion that
  the unmodified baseline deletion page renders successfully.
- Legacy `IN ('31, 32')` is one quoted string, not two bound IDs, and deletes only the
  first numerically coerced member. The candidate deletes the complete selected set
  and reports the actual affected count. Both outcomes were asserted separately.
- The list used an undefined `$item_id` for deletion checkboxes. Each candidate
  checkbox now carries its actual payment ID.
- Unknown usernames and identity `0` could silently unfilter the old list. The
  candidate keeps the intended zero/unknown scope and treats special usernames
  literally, including percent, quote, plus, ampersand and Unicode characters.
- Filter identity is preserved by sort links and both pagination controls.
- Arrays, malformed IDs/types/dates/decimals, oversize notes/selections and
  nontransactional write tables now reject before any business mutation instead
  of relying on permissive casts, driver coercion or partial writes.

## Executed validation

`python3 -u tests/payments_http.py` passed on isolated PHP/HTTP/MariaDB fixtures:

- **38** pinned PEAR/PDO comparisons: complete form controls and rows/totals,
  allowed sort keys and unsupported-key fallback, pagination, valid filters,
  missing edit IDs, missing type labels via LEFT JOIN, named locations, create
  business rows, partial/unchanged edits, audit preservation and single deletion.
- Real malformed scalar/array controls, exact decimal bounds and signed refund,
  leap date, default date, quotes/Unicode, explicit zero notes, CSRF and GET/POST ACL.
- Renamed tables, malicious configured identifiers, unknown selected location,
  a valid named backend with a deliberately broken default, and SELECT-only reads.
- Missing invoice/type, whole-selection stale rejection, duplicate IDs, orphan
  deletion, oversized selection, InnoDB checks for payments/invoices/types.
- Real trigger failures on INSERT, UPDATE and the **second** DELETE; full physical
  payment-state comparison including audit columns proves rollback.
- Caller-owned transaction with an earlier actual INSERT is preserved until the
  caller rolls it back.
- **Four observed native lock interleavings**, using separate PHP workers and
  `INNODB_LOCK_WAITS`, not arbitrary sleeps: parent deletion wins over creation;
  creation wins before the existing invoice-delete provider removes its child;
  edit versus deletion with fixture snapshot isolation ON and OFF. With ON,
  MariaDB returned error 1020 to the stale delete; the edited row was preserved
  and a fresh delete succeeded. With OFF, deletion serialized successfully.
- Four late SQL read failures after selector/preflight/count success, and an
  edit whose committed amount survives a later display failure.
- Throwing PEAR open **and close** tripwires on all four pages and successful
  create/edit/delete actions. Candidate PHP logs from both stdout and stderr
  contain no warning, notice, fatal, uncaught exception or SQLSTATE disclosure.

Seven adjacent native regression suites passed:

- `invoice_create_http.py`, `invoice_edit_http.py`, `invoice_delete_http.py`
  (UNIT-005/006/007).
- `invoice_reads_http.py` (R15).
- `pos_delete_http.py`, `batch_delete_http.py` (related parent deletion families).
- `operator_common_reads_http.py` (shared selectors and PDO routing).

PHP lint passed for the four pages and shared provider; Python compilation and
`git diff --check` passed. Fixture source/config/session material is disposable:
no live configuration is copied, no credentials or response snapshots are retained,
and containers, network and scratch directories are removed after execution.
The pre-existing `tests/__pycache__/` directory is preserved; generated bytecode
remains untracked.

## Limits and remaining work

This is real isolated HTTP/PHP/database validation using synthetic data. It is not
a full-browser test, a live payment-provider transaction, a production migration,
FreeRADIUS authentication or a NAS/hotspot hardware test. R18 type CRUD, R20
notifications and other active PEAR families remain outside this lot. Writers that
do not cooperate with invoice-first locking are not made safe by R17 alone; the
existing schema has no newly added foreign key. No automatic retry/idempotency token
has been added to manual payment creation, which retains its existing repeated-POST
creation semantics.
