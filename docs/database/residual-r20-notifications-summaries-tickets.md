# R20 — notifications, shared user summaries and printable tickets

## Scope and coexistence

Base: `9c2cd027308391b11c8d58b2ab35068f8e7323ef` (R19).
Covers RES-054/055/098/099/101/102/103/104/105/180/181/182, all twelve inventoried
blocks in these five files:

- `include/common/notifications.php`: selected-backend ACL and delivery context.
- `notifications/context.php`: welcome, batch and invoice documents.
- `include/management/userBilling.php`: callable invoice creation, invoice status,
  rate and merchant summaries.
- `include/management/userReports.php`: subscription periods/limits, plan usage,
  connection detail and online status.
- `include/common/printTickets.php`: printable batch plan price/validity.

`library/shared_context_pdo.php` centralizes strict fully fetched bound reads,
validated configured identifiers and the selected-location factory. R01's existing
operator ACL provider is reused. Public report/helper signatures remain compatible;
an optional final explicit PDO permits borrowed-handle testing/callers without
committing, rolling back, disconnecting or replacing their transaction. The online
lookup nested inside connection rendering uses that same handle.

All five files have no remaining PEAR query/open/close/error API calls. This does
not establish that every other application/portal/cron consumer is PDO: PEAR and
its installation dependencies remain for subsequent lots. No schema, configuration,
live data, persistent service or deployment is changed.

## Preserved display and delivery contracts

- Welcome identity, fallback recipient name, contact priority and date/filename
  policy are preserved. Missing user metadata still creates the fallback welcome
  document, but does not invent an email recipient.
- Batch counts keep distinct users/active users, first joined plan/hotspot and
  first accounting-session rendering. Existing overview/plan/business templates
  and their column order remain unchanged.
- Invoice headers, aggregated items/payments, recipient **email** (not the yes/no
  `emailinvoice` flag), item order, configurable item/header templates, mixed and
  missing currencies, per-line currency codes and decimal formatting are retained.
- Notification type/action dispatch, owning-page any-of ACL, query-over-session
  parameter precedence, preview/download filenames and dispositions are retained.
  Database handles are released before PDF rendering and SMTP. SMTP failure does
  not imply any SQL rollback or retry; no automatic notification replay is added.
- Rate summaries retain exact username, inclusive midnight endpoints, stored INT
  pricing and all six coefficients, including historical `month=187488000`.
- Merchant summaries retain join multiplicity, first grouped row display,
  substring/wildcard email matching and address/payer/payment/vendor filters,
  including the whole final day. This migration does not repair historical totals
  multiplied by joined accounting rows or collapse them into a new billing model.
  An explicit CHAR cast on the three SUM monetary expressions preserves PEAR's
  server wire-text precision: PDO's native DOUBLE-to-PHP-float conversion otherwise
  shortens a value such as `0.30000000000000004` to `0.3` before HTML rendering.
- Subscription global/month/week/day bounds and closed-session selection are
  unchanged; group Idle-Timeout fallback retains its UNION/first-row policy.
  Plan caps, remaining values, last connection ordering, zero/NULL-stop online
  policy, formatters, accordion state and no-table modes are preserved.
- Invoice status retains blank absent counts and zero balance without the old
  undefined/null-offset warnings; it does not invent invoices or a new count policy.
- Cards retain the actual batch producer's heading pair, subsequent account pairs,
  grouping by four, layout, escaped fields, information text and plan currency/time.
  This endpoint does not generate credentials or query stored account passwords.

## Intentional fail-closed repairs

- Preserve raw percent/plus/Unicode identities rather than deleting percent signs
  before notification lookup. Reject array-shaped/malformed context IDs/text,
  unsafe configured identifiers and malformed ticket pairs/text/CSRF before output.
  A literal scalar `0` is no longer silently an empty merchant filter or ticket plan.
- Ticket access now requires the real producer's `mng_batch_add` ACL. A valid
  authenticated session alone is not permission to print arbitrary posted cards.
  Missing plan/configuration/SQL data cannot produce partially priced cards.
- Complete context reads finish before any document is returned/delivered.
  A later missing invoice item/plan column refuses the whole PDF/email, not a
  plausible document with silently absent sections. Endpoint failures use generic
  400/503 responses; PDO messages, SQL and bound data are not logged or rendered.
- Summary output is locally buffered and discarded on failure; a generic complete
  failure notice replaces a half-rendered table. An online lookup failure returns
  `User status unavailable`, not an invented offline result. Stored zero/malformed
  rate divisors are refused before arithmetic.

## Callable invoice creation

`userInvoiceAdd()` has no active repository caller found in this lot's trace, but
its public callable contract is migrated rather than leaving an untracked PEAR
write behind. Defaults, numeric ID versus username dispatch, ordinary positive
item values, empty item list and true/false outcome remain.

It validates the complete request, positive IDs, calendar dates and signed fixed
DECIMAL(10,2)-compatible values; requires InnoDB invoice/item/billing-user tables;
rejects an already-owned caller transaction; resolves/locks exactly one billing
user; inserts header and every item on one PDO transaction; gets the connection's
own last inserted ID; verifies stored invoice/item values and audit/date fields;
and commits only after all rows match. Permissive physical-column truncation or a
late item error restores both header and all earlier items. Error callbacks receive
only a generic exception, not driver messages.

Intentional differences: malformed/non-array arguments are not silently converted
into defaults; nonexistent numeric billing IDs and ambiguous usernames no longer
create orphan/mass-associated invoices; invalid dates/precision/overflow/truncated
storage fail closed. This is not a new universal plan/status foreign-key validation
policy or request-deduplication journal. Two intentional calls may create two
invoices; no automatic mutation replay or external-writer uniqueness guarantee is
claimed. Caller-owned transactions remain untouched on refusal.

## Executed validation

`python3 -u tests/shared_context_http.py` provisions temporary internal-network
MariaDB/PHP fixtures from shipped schemas and sample configuration, with a pinned
PEAR copy of all five files and the current PDO candidate. Seventeen physical
configured tables are renamed; default and named-location schemas hold distinct
synthetic values. No live configuration/data is copied. Generated session material
and posted display-only card pairs stay in disposable fixtures or process memory;
notification bodies/PDFs, SMTP envelopes and card pairs are never written to test
reports, snapshots or stdout. The SMTP listener is bound only to this fixture's
internal Docker bridge, has no authentication credentials and is shut down/cleared.

Final observed results:

- **133 PEAR/PDO comparisons**: complete context return maps (HTML, recipient,
  subject/body, filename), ordinary and empty/missing/mixed-currency cases,
  default/named summaries and no-table modes, six rate units, complete PDF output
  (only creation/modification metadata and generated document IDs normalized),
  real SMTP recipient/message/attachment content, card output and invoice state.
- Actual notification preview/download/email endpoints pass for all three types;
  named-location PDFs agree with their corresponding PEAR fixtures and differ from
  default-schema documents where the seeded business data differs.
- Real PHPMailer talks to an isolated SMTP capture server. Recipient, subject,
  HTML body, attachment filename and normalized PDF match PEAR. SMTP refusal,
  disabled delivery and absent recipients deliver nothing and preserve SQL state.
- Authenticated denied ACL, alternate welcome-owner ACL, unauthenticated login,
  query/session precedence, malformed request fields, CSRF and missing ticket plan
  pass through native HTTP routes.
- Borrowed ERRMODE_SILENT handles preserve a real prior write and transaction on
  successful summaries/notification context and on a later subscription failure;
  caller rollback restores the original value. Caller-owned invoice mutation is
  refused without consuming that transaction.
- Callable invoice creation matches PEAR business projections by username and
  numeric ID. A recorder in an independent disposable MyISAM table observes both
  item attempts before the second item's native trigger fails: header and the first
  item nevertheless roll back completely. Malformed later items, missing users,
  invalid dates, nontransactional storage and narrower-column truncation refusal
  preserve the full fixture invoice/item state.
- Named-location creation changes only its selected schema. Two independent native
  PHP workers both create an invoice with its own correctly linked item; connection
  last-insert identities do not leak between workers.
- Actual SELECT-only grants allow notifications, all shared summaries and tickets.
  Unsafe identifiers fail closed. Late batch/invoice/ticket columns and later
  subscription/connection queries fail without partial documents/cards, invented
  offline status or external delivery.
- PEAR open/close tripwires pass for every summary, the callable writer, all three
  native notification routes and ticket output.
- Candidate PHP stdout **and stderr** have no Fatal, Warning, Notice, Deprecated,
  Uncaught or SQLSTATE output; an explicit log-channel marker is observed.
- Six changed/new PHP files lint; the three affected Python suites compile; the
  pure `notifications-render.test.php` template suite reports **ALL PASSED**;
  Git whitespace checks pass.

Nine adjacent native suites exit zero: R19 `billing_rates_http.py` (165 comparisons),
R16 `catalog_reads_http.py` (77), R15 `invoice_reads_http.py` (71),
`invoice_create_http.py`, `invoice_edit_http.py`, `invoice_delete_http.py`,
`batch_create_http.py`, R13 `operator_reports_http.py` (197) and
`acct_maintenance_http.py`. R16/R19 fixtures now explicitly pin the original shared
PEAR dependencies, remove obsolete R20 legacy exemptions, and require complete
PDO-only full-page reads. R16's former no-invoice warning exemption is removed.

Fixture containers/networks/temporary files are removed; the preexisting
`tests/__pycache__/` is preserved. This is native isolated HTTP/PHP/MariaDB plus
real PHPMailer against local captured SMTP, **not** production SMTP/deliverability,
a full-browser/print workflow, real FreeRADIUS authentication, physical NAS/hotspot
or production upgrade/deployment. Alternative database engines and SQL modes are
not established by these MariaDB fixtures.

## Local slices

- R20a: shared strict reader, notification ACL/context and delivery boundary.
- R20b: billing summaries and callable atomic invoice helper.
- R20c: user report/online readers and the earlier summary regression fixtures.
- R20d: printable tickets, dedicated native differential fixture and documentation.
