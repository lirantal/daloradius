# R26 — close retired 2Checkout receipt and remove dead signup

## Decision, scope and baseline

Baseline `7279daab04783a97aa8160dc69646d098f82f6f8` (R25b).
RES-228/232 are Portal2 `signup-2checkout/index.php` and `success.php`.
The finalization lot explicitly permits either migrating or retiring the
historical success reader while keeping signup and callbacks retired.

**Decision: retire the remaining public success reader**, following UNIT-041's
already-selected retirement of the incomplete payment flow. This is a deliberate
HTTP contract change, not a PDO receipt implementation or ordinary parity claim.
No replacement payment integration or historical receipt viewer is introduced.

The pinned index already returns 410 before its unreachable registration body.
Delete that tail without changing its existing response. Replace success with
the same existing retirement helper, before configuration or SQL. Keep a real
file at the historical URI so links fail with intentional 410 rather than 404.

Repository tracing finds the historical configured success destination and the
old unreachable signup comment; no supported active same-family producer is
restored. Independent merchant SDK examples and externally configured links may
still exist; local tracing does not prove their absence outside the repository.
The receipt was explicitly retained at UNIT-041; its report now links this
follow-up rather than presenting that old retention as the current disposition.

## Implemented behavior

- `index.php` retains its exact fixed retirement response and removes all dead
  plan reads, credential generation, three dependent inserts, registration form
  and merchant checkout markup after the unconditional return.
- `success.php` always emits HTTP 410 through the unchanged retired helper.
  It no longer loads configuration, opens PEAR/PDO, reads receipt state, polls,
  reflects request values or exposes a stored username/PIN.
- The six retired HTTP surfaces (signup, historical receipt, start, callback,
  direct provision shim and direct retirement helper) keep the same plain UTF-8
  body, no-store and nosniff headers. No redirect or session is introduced.
- Existing CLI behavior is preserved: fixed stdout and exit zero, not a newly
  invented maintenance CLI exit policy. Arguments do not enable processing.
- Historical billing/account/RADIUS data are **not deleted, disabled, activated,
  reconciled or migrated**. No refund, status update, replay or schema change.
- Retirement helper, start/callback, provision/SDK shims, all PayPal/free signup
  families and connection wrappers are unchanged. PEAR dependencies, examples
  and unused providers keep their separately scheduled R28/R29 dispositions.
- Original LF/final-newline conventions and copyright notices are preserved.

## Characterized historical receipt

The pinned reader returns 200 and exposes a username only when the stored state
is Completed; other stored states poll, regardless of a forged status query.
However, its SQL has no vendor predicate, so a Completed PayPal row in the shared
merchant table can also be shown at the 2Checkout URI. It renders a stored
username without HTML escaping. Both are reproduced on synthetic native SQL
fixtures, not inferred from candidate success or called an activation exploit.

R26 removes the public reader rather than replacing those gaps with a new
supported historical-payment workflow. Every request now receives 410; even a
previously valid historical Completed reference no longer returns a PIN. Stored
history and account authorization remain unchanged.

## Executed validation

`PYTHONDONTWRITEBYTECODE=1 python3 -u tests/chilli_2checkout_residual_http.py`
passed on real isolated PHP HTTP/CLI and disposable MariaDB fixtures:

- **Eight keyed baseline/candidate comparisons**: five stored receipt states,
  cross-vendor receipt, stored markup and exact signup-retirement response.
  Only the last is byte-level response parity. Reader changes are intentional
  retirement comparisons, not claims of preserved PDO receipt output.
- **Six native control families**: characterize the pinned reader, preserve
  complete eight-table synthetic state including existing active accounts,
  and exercise all six retired routes with GET/POST/HEAD/PUT/DELETE/OPTIONS,
  valid/absent/array/empty/SQL-shaped/long queries and malformed, Unicode,
  oversized and JSON bodies plus concurrent requests.
- Native server connection counters increase by exactly the second measuring
  CLI connection: the entire candidate HTTP matrix opens **zero DB connections**.
  Row snapshots remain unchanged. This is stronger than HTTP status alone.
- Configuration/open/close/PEAR/PDO tripwire files are unreachable. Both changed
  entry points run from three unrelated CLI working directories with normal,
  force, help and historical-reference arguments, returning only the fixed body.
- All six surfaces keep returning 410 after the fixture DB is stopped and
  configuration, merchant SDK and common connection providers are removed.
- PHP stdout/stderr contain no warnings/fatals/notices/deprecations or echoed
  request marker; no application payload log is produced. Fixture configuration,
  database, containers and internal network are verified removed.

Adjacent native regressions passed:

| Suite | Result |
| --- | --- |
| UNIT-041 current candidate | All six retired routes, valid/invalid generated signature, malformed/concurrent requests, unchanged six-table state, rejected SDK/provider and cleanup |
| UNIT-041 pinned pre-retirement baseline | Original signature primitive accepts changed unsigned order/plan/payment fields; separate native HTTP 500 at missing SDK log path precisely characterized |
| UNIT-038 connection coexistence | All six wrappers retain PEAR and explicit PDO behavior, borrowed transaction/redaction rules, real alternate-port routing and special connection data |

Two production PHP lints, AST/compile for both Python scripts, LF/final-EOL and
Git whitespace checks passed. Generated test access/signing material is confined
to disposable fixtures/in-memory environment and removed, never saved as a
report or payload/signature snapshot. Pre-existing tests/__pycache__/ is retained.
No production credentials, database, provider or service are used or changed.

## Deployment and limits

Withdraw public historical receipt links alongside retired signup/checkout links
before deployment. An external customer bookmark or merchant return reference
now receives 410, including previously Completed references. Coordinate that
breaking behavior with the responsible operator. Provider acknowledgement/retry
semantics and external bookmarks are not tested; 410 is not a successful payment
acknowledgement. Reconciliation of pending/completed records remains an operator
process against authoritative payment records, not this endpoint.

This validation is native PHP/SQL with synthetic data, not a real 2Checkout/PayPal
exchange, complete browser rendering, FreeRADIUS authentication or hotspot/NAS
hardware. No supported new provider, installation or performance claim.

R26's two residual consumers are resolved by retirement/dead-code deletion,
not reactivation. R27–R29 and final fresh inventory/dependency checks remain.
No push, PR or deployment.
