# UNIT-037 — Shared user/group mapping provider

## Scope and coexistence

Adds lazy PDO dispatch to the four inventoried helpers in `app/operators/include/management/functions.php`:

- `group_exists()`;
- `insert_multiple_plan_group_mappings()`;
- `insert_multiple_user_group_mappings()`;
- `insert_single_user_group_mapping()`.

The adjacent `update_user_group_mapping_priority()` receives PDO dispatch too: its duplicate-collapse DELETE/INSERT sequence must share the same locking and caller-owned transaction as insertion. Implementation is isolated in `groupMappingsPdo.php`. It does not create connections, start transactions, commit or roll back. A PDO mutation without an active transaction throws before any provider write; the caller must catch failures and roll back the entire operation, including its own dependent writes, and must not expose driver exception details.

This is a **shared-provider migration**, not a conversion of all remaining pages. PEAR handles still select the PEAR paths. Existing import/new-user/usergroup callers remain on their original connection; no dependent operation is split across clients. Previously migrated plan and user workflows retain their dedicated PDO implementations.

## Corrected lookup and deliberate PDO policy

The original PEAR `group_exists()` counted all groups without a name predicate. It therefore reported a nonexistent group as present whenever either attribute table contained any group. The predicate is corrected on PEAR too, using a bound name and validated table identifier. PDO likewise binds the selected group. Both paths use `radgroupcheck` and `radgroupreply`, following this helper's documented contract: a usergroup-only name is not sufficient evidence of a defined group. Equality retains the configured database collation.

PDO mutation rules:

- Validate the complete flat selection and all configured identifiers before provider writes. Trim names, skip blank form entries, deduplicate after trimming and preserve submitted insertion order. Enforce schema limits: user names/group mappings 64 characters; plan names 128; plan profile names 256. Literal `0`, quotes, Unicode and `%` are data, not empty values or SQL.
- Require InnoDB on destination, parent and group-source tables. Lock the existing user's `radcheck` rows, or one unambiguous billing-plan parent, on the caller's handle; then validate/lock groups in stable name order. A parent just created on that same transaction is visible to the provider.
- Reject missing parents, unknown groups, malformed controls and SQL failures with exceptions. PDO batches are **all-or-nothing through caller rollback**, unlike legacy partial-success batches. Empty selections return `false`; otherwise batches return actual inserted-row counts.
- Single insertion serializes cooperating requests on the existing parent row and returns `false` for an existing mapping instead of inserting another duplicate. Bulk APIs retain insertion semantics for mappings already present in the database; their deduplication applies to the submitted list, not existing rows.
- Ordinary negative priorities normalize to zero. `daloRADIUS-Disabled-Users` always uses priority `-1`. Reject nonintegral/array/out-of-range priority input before writes. Priority updates retain legacy duplicate-collapse behavior, but DELETE and replacement INSERT share the caller's transaction; an unchanged priority update succeeds after a locked existence check.
- PDO SQL debug records contain query templates, never bound names or values.

PEAR mutation loops otherwise retain their historical return values, priority handling and partial-success policy. The predicate correction intentionally stops creating mappings to nonexistent groups; it does not make PEAR batches transactional.

## Real isolated validation

```sh
GROUP_MAPPINGS_BASELINE=1 PYTHONDONTWRITEBYTECODE=1 python3 tests/group_mappings_http.py
PYTHONDONTWRITEBYTECODE=1 python3 tests/group_mappings_http.py
```

The fixture pins baseline `functions.php` to `d744a5653e382cd6025b816fd144e21d97bfbb47`, imports equivalent disposable schemas, authenticates an ephemeral operator through the real login route and calls the providers through authenticated, CSRF-protected **fixture-only HTTP endpoints**. No such endpoints are added to production. Ordinary results and complete relevant persisted projections are checked against identical expectations. The baseline also actually demonstrates the false-positive lookup and partial batches after later trigger failures; those are classified divergences, not claimed parity.

Candidate checks cover both group sources; user/plan batches; single insertion; disabled/ordinary priorities; full-selection rejection; no-transaction rejection; invalid configured identifiers; missing parent/group; late user/plan INSERT failure after an earlier mapping and caller write; caller-requested rollback; duplicate-collapse INSERT failure and restoration; idempotent priority update; all six nontransactional participating tables; quote/Unicode/percent names; debug-template redaction; concurrent independent HTTP sessions yielding one single mapping; and a priority mutation demonstrably waiting for a real second-connection parent lock.

Coexistence is exercised using the candidate's PEAR dispatch and the actual `mng-rad-usergroup-new.php` GET/POST page: a valid mapping succeeds and a crafted nonexistent group fails without changes. The UNIT-036 HTTP regression fixture is rerun. PHP lint, Python AST and `git diff --check` are required. Fixture logs must contain no PHP warnings/fatal errors or operator password; containers/network and temporary credential-bearing state are removed.

## Limits

The test HTTP endpoints exercise the provider contract, not a wholesale migration of legacy pages. No live data, real FreeRADIUS request or production service is changed. Parent locks coordinate writers that obey this protocol; independent legacy/external writers without universal foreign keys or unique constraints can still insert duplicates or remove definitions. Concurrent DDL is not coordinated. Blank/default form semantics are preserved, but stricter PDO input, parent validation, duplicate-single policy and all-or-nothing failures must be respected when later callers adopt this provider. Callers remain responsible for the transactional engines of additional tables they mutate and for rolling back propagated exceptions.

**Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.**
