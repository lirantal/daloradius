# UNIT-039 — Atomic Chilli free signup

## Scope

Migrates the full free-signup write workflows:

- `contrib/chilli/portal1/signup-free/signup.php` (historical inline configuration);
- `contrib/chilli/portal2/signup-free/index.php`;
- `contrib/chilli/portal3/signup-free/index.php`.

Shared implementation: `contrib/chilli/common/freeSignup.php`, using the explicit UNIT-038 PDO provider. No page in this family opens PEAR, including indirect credential generation or group insertion. Existing compatibility `library/opendb.php`/`closedb.php` wrappers remain for other consumers and are not silently switched. PayPal and 2Checkout remain outside this unit.

## Transaction and data contract

Validate all submitted profile fields, generation settings, table identifiers, optional group and priority before writes. Bind every value. First/last name and email use their actual 200-character schema bounds, and malformed arrays, invalid UTF-8, NULs and nonempty required-field violations are rejected. Literal `0`, percent signs, quotes and Unicode are data. Username prefix plus generated suffix is bounded to 64 characters; configured suffix length is 1–64 and password length 1–253. Generation alphabets must contain 1–256 printable ASCII bytes.

Open one nonpersistent PDO connection using the portal's configuration and honor its configured port. Serialize cooperating free-signup requests with a database-scoped advisory lock on that same handle. Check InnoDB for every write table and, when a group is configured, both group-definition sources. Begin one transaction, validate/lock the configured group's definitions from `radgroupcheck`/`radgroupreply`, check candidate identity collisions in participating account/info/mapping tables, and insert:

1. the RADIUS authentication row;
2. user information;
3. the configured group mapping, if enabled.

Commit before returning or displaying generated credentials. A later userinfo/group SQL failure rolls back the earlier account row. Never render driver exceptions or append credential-bearing SQL to debug logs. Closing never commits; a failed release after a successful commit does not misreport the committed signup as failed.

Configured identifiers support both the modern `radusergroup` table and the historical three-column `usergroup` table: inserts name columns explicitly and do not assume a mapping ID. Portal1 retains its `usergroup` target, `guest` prefix, four-character lengths and original alphabet; portals2/3 retain their local generation configuration and optional-group policy. The RADIUS attribute/operator remain **`User-Password` / `==`** with the generated value, as in baseline. This unit does not claim to modernize the authentication policy or verify FreeRADIUS behavior.

## Deliberate corrected behavior

The pinned PEAR baseline actually leaves account/info rows committed and reports success when the final mapping INSERT fails. Candidate behavior is all-or-nothing and reports a generic failure with no generated credentials.

The old pages also accepted missing session CAPTCHA state through loose comparison and had no CSRF gate. Every candidate form now emits a session-bound CSRF token; the request handler requires scalar controls, a real nonempty stored CAPTCHA and exact token/CAPTCHA matching. Consume the CAPTCHA and rotate CSRF before a valid creation attempt, including one that later fails validation/SQL. Replaying that POST cannot create another account; a fresh CAPTCHA/form is needed to retry. Existing field/captcha messages and signup forms remain, with a new generic database-failure rendering.

Use `random_int()` instead of reseeding `rand()` and indexing only the first 33 alphabet characters. Configured lengths stay unchanged, including historical weak four-character defaults; operators must choose adequate lengths. Detect collisions under the actual database collation, regenerate up to twenty times and fail without writes when the namespace is exhausted. Cooperating requests choosing the same candidate serialize; one can succeed and the other must retry or fail rather than duplicate that account.

A configured nonempty group must now have an actual check/reply definition; the old pages could add arbitrary orphan mappings. Empty group disables mapping insertion (including an explicitly empty Portal1 configuration). Signed 32-bit configured priorities remain unchanged rather than applying an unrelated group-provider normalization policy. Reject overlong or malformed config values instead of permitting database truncation/coercion. Escape submitted first name and generated credentials in the success HTML, escape form actions and explicitly render UTF-8. Portal3 source CRLF endings are preserved.

## Real isolated validation

```sh
CHILLI_SIGNUP_BASELINE=1 PYTHONDONTWRITEBYTECODE=1 python3 tests/chilli_signup_http.py
PYTHONDONTWRITEBYTECODE=1 python3 tests/chilli_signup_http.py
PYTHONDONTWRITEBYTECODE=1 python3 tests/chilli_connections_http.py
```

Baseline pages are pinned to `79aad6e0918c77699837213081ab68cd9339bcb8`. Both modes use equivalent disposable MariaDB schemas, including a historical three-column usergroup table. Fixture-only configuration substitutes mysqli for Portal1's unavailable legacy mysql extension and points all connections at the disposable database; no live portal configuration is modified.

Tests GET the **actual signup forms and CAPTCHA image scripts**, preserve cookies, then POST to the actual pages. A private endpoint installed only inside the temporary fixture reads the real CAPTCHA from that same PHP session; it does not substitute captcha/authentication functions or introduce a production bypass. Ordinary persisted account attribute/operator/value, profile fields and selected group/priority are checked against the same expected contract in both modes. Generated identities/passwords remain in memory only, are not printed or serialized into comparison snapshots, and are removed with tmpfs databases. Baseline separately proves partial-success failures rather than claiming parity with the corrected PDO behavior.

Candidate assertions cover all three pages: wrong/missing/array factors and CSRF, malformed/overlong fields, absent CAPTCHA state, later userinfo and later mapping INSERT errors with unchanged persisted projections, all five participating nontransactional tables, unknown group, invalid table identifier, failed connection, quote/percent/Unicode/zero fields, escaped success and consumed-CAPTCHA replay. Additional cases cover no group, reply-only group definitions, quote/percent/Unicode generated identity prefix and group name, negative configured priority, an exhausted existing identity, and concurrent independent sessions attempting the same identity. Temporary fixture OPcache is disabled because tests deliberately rewrite configs between requests; otherwise cached inline Portal1 settings can invalidate the intended error scenario.

The UNIT-038 connection regression suite also passes. PHP lint of every changed PHP file, Python AST and CRLF-aware `git diff --check` supplement real HTTP/PHP/PEAR/PDO/MariaDB execution. Candidate PHP logs must contain no warnings/fatal errors or generated passwords; resources and their cleanup are verified. No live data/service deployment or real FreeRADIUS exchange is part of this validation.

## Limits and packaging

Keep the repository-relative shared Chilli directory and UNIT-001 `app/common/includes/pdo_connection.php` available, as documented by UNIT-038. A formerly standalone Portal1 directory now has these dependencies too; copying only `signup-free/` is insufficient.

Advisory locking coordinates only writers using this protocol. There is no universal username unique constraint or foreign-key protection, and independent legacy/external writers or concurrent DDL can still race. A broken connection during commit can have an uncertain outcome; the generic error does not promise no write occurred in that case. Client CAPTCHA images, session-cookie settings, signup rate limiting, password-length policy and HTTPS deployment remain legacy/operator responsibilities. This is not a production security certification of the old captive-portal examples.

**Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.**
