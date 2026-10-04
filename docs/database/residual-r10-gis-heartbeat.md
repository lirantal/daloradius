# R10 — GIS pages and heartbeat

## Scope

Baseline: `95f58a27e9136353358dc1a65761cdf2ad0c4fb2`, `refactor/pdo`.

- R10a: `gis-editmap.php`, `gis-viewmap.php`, explicit
  `library/geo_heartbeat_pdo.php` provider and the shared hotspot lock budget.
- R10b: `heartbeat.php`, `tests/geo_heartbeat_http.py`, documentation/progress.
- Closes RES-050/051/052. R01 permissions, UNIT-001 selected-location PDO and
  R09 hotspot query/capacity/transaction helpers are reused. Shared PEAR
  consumers, heartbeat client scripts and production configuration are retained.

## GIS contract

Both pages read marker rows with the existing nonempty/non-NULL geocode filter.
Latitude/longitude parsing now validates the complete coordinate pair, finite
values and geographic bounds. NULL, empty or malformed coordinates are skipped;
new out-of-range markers are refused and stored out-of-range rows are omitted.
The empty-map center, first-marker center, fit-bounds policy, popups, links and
HTML-plus-inline-JavaScript escaping are otherwise retained.

Creation inserts the same small name/MAC/geocode/audit projection as the old GIS
page, preserving NULL/default contact fields. It deliberately retains the GIS
policy allowing duplicate names/MACs; this is not the CRUD collision policy.
This can create records which R09's exact-name edit deliberately refuses as
ambiguous. Deletion removes the entire row selected by ID, not just its geocode;
accounting and billing references are preserved exactly as before. No implicit
cascade, retention, duplicate cleanup or position-update workflow is introduced.

Mutations validate actual configured column capacities, reject stale IDs and
non-InnoDB tables, own one PDO transaction, verify stored fields before commit
and share the same advisory lock as R09 CRUD. Marker reads are independent and
strict: late SELECT errors produce a generic visible failure, not a misleading
empty-data success. Raw names are distinct from HTML labels, URL parameters and
JavaScript titles. Marker IDs are decimal strings in JavaScript, so submitting
a valid large signed BIGINT cannot round it through a JavaScript Number. The
native form now handles special-character names consistently on add/delete.

The shared hotspot and new heartbeat lock names fit a 64-character budget;
R09's prior prefix plus fixed hash slice exceeded that budget. Its contention
fixture was adjusted to the same name and rerun. See the official MySQL 8.4
manual, section 14.14, “Locking Functions”, for the GET_LOCK name limit. This is
not a claim of a native MySQL test: the runtime below is MariaDB. Deploy a
consistent source revision; old/new lock-name schemes do not coordinate across
mixed revisions, and uncoordinated writers still bypass the advisory protocol.

## Heartbeat contract and privacy

The endpoint retains GET and the existing authorization/method failure messages,
with constant-time comparison of a scalar configured factor. Successful committed
writes still return literal `success`. Unlike the baseline, validation errors
return generic plaintext/400 and SQL/write/readback errors generic plaintext/500;
errors cannot append a misleading success token. The optional debug response is
fixed text, never a dump of request parameters or driver exceptions.

The bundled clients identify nodes with MAC-like strings **or hostnames/NAS
identifiers**, and send CPU as decimal percent strings. Both forms are supported;
CPU percent is explicitly converted to its numeric value, rather than relying on
permissive SQL prefix coercion. Optional metrics retain their empty defaults;
literal zero is preserved. Values are checked against the actual node schema,
including its short identity/memory columns and floating CPU readback. Registration,
owner and location columns are not overwritten by telemetry updates.

The default configured backend is explicit; operator cookies cannot redirect
this machine endpoint to another location. Lookup, insert/update, verification
and commit use one PDO connection and a node-specific advisory lock. Ambiguous
identities, nontransactional engines and borrowed transactions are refused.
Existing SQL collation behavior for case-equivalent identity lookup is preserved.

Submitted Wi-Fi credentials are ignored and `wifi_key` is set to empty for the
node being inserted/updated. A previously populated value on that same checking-in
node is cleared. No background purge of other rows occurs. Neither the authorization
factor nor Wi-Fi values appear in application debug output. The configured factor
is not changed or removed. The legacy GET transport remains compatible: TLS,
web-server/proxy access-log redaction and legacy client-side logging are deployment
concerns outside this source migration; no blanket claim of secret-free external
request logs is made. No client script was run against a real device.

## Verified execution

`PYTHONDONTWRITEBYTECODE=1 python3 -u tests/geo_heartbeat_http.py`

Actual PHP 8.4.24/HTTP/MariaDB 11.8.9-MariaDB-ubu2404 with three unmodified pinned
PEAR entry points in equivalent disposable databases and seeded sessions.

Passed:

- Native GIS insert/delete and persisted business/audit-presence parity; actual
  create-response edit link, unchanged accounting/billing rows, duplicate GIS
  inserts and full-row removal.
- **10 direct comparisons**: four ordinary GIS marker projections and six CPU
  representations. Marker identity types are normalized for old Number/new
  decimal-string comparison, not falsely described as byte-identical output.
  Native heartbeat insert/update/default-state parity is also checked separately,
  excluding generated time and credential contents from public artifacts.
- Boundary/negative/zero coordinates; NULL/empty/malformed rows, empty/single
  centers, geographic range rejection, malformed controls/IDs, 200-character
  capacity checks and charset/readback failure.
- Native INSERT/DELETE GIS and INSERT/UPDATE heartbeat trigger failures, plus a
  successful UPDATE whose altered readback forces rollback; exact full pre/post
  noncredential row state and untouched unrelated records are checked.
- Actual generated JavaScript submits the real hidden GIS controls, including
  special names, negative coordinates and the largest positive signed BIGINT ID.
  JavaScript executes in Node with a **synthetic Leaflet/DOM API**. This is not
  a browser, real Leaflet, CDN, map tile or visual-UI test.
- Independent-session GIS delete race, concurrent first check-ins for one node,
  atomic complete metric tuples, and independently held native heartbeat lock.
- Seeded login/expiry, page ACL/CSRF, borrowed transaction preservation, malformed
  authorization/telemetry, SQL identifier allowlists, SELECT-only marker reads
  and denied mutation requests.
- Named GIS backend with a marker absent from the default database; default-only
  heartbeat despite a named operator session; configured hotspot/node tables,
  missing columns and invalid sources, clean candidate PHP diagnostics and
  unconditional legacy-open/close tripwires.
- Fixed debug output, no persisted incoming Wi-Fi value, same-node old-value
  clearing, zero telemetry and SQL-literal/case-equivalent node identities.

Pinned baseline defects are characterized precisely: GIS's coordinate regex
check accepts nonmatches, GIS announces a failed INSERT as added, and a triggered
heartbeat UPDATE returns HTTP 200 with the legacy database-error HTML **followed
by `success`** while row state remains unchanged. These are not parity claims and
are not relaxed to arbitrary baseline HTTP failures.

Four native regression suites passed: `hotspot_pages_http.py` (including its 55
comparisons and adjusted shared-lock contention), `selectbox_reads_http.py`,
`operator_common_reads_http.py`, `operator_acl_reads_http.py`. PHP lint for all
five affected PHP files, compilation of both Python fixtures and
`git diff --check` passed. No real heartbeat sender/hardware, browser map,
FreeRADIUS, native MySQL, installer or live deployment is claimed.

Fixture configuration/session/factor values are generated ephemerally, not saved
in reports. Node credential contents are excluded from snapshots; cleanup removes
containers, networks and temporary trees, with no remaining R10 resources.
Pre-existing `tests/__pycache__/` is retained. No push/PR/deployment. PEAR remains
needed for the other scheduled lots.
