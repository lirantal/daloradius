# R01b1 — Operator MFA configuration

Base: `a363ab69c8eda7a89dab0ba0155e292a236fa910`, branch `refactor/pdo`.
Closes RES-048 (`config-operator-2fa.php`), not all R01 or the whole migration.

## Implementation

The page uses UNIT-001 PDO and a scoped `library/operator_mfa_config.php` provider.
Reads fetch only display metadata, not stored factors. Confirm, disable and recovery
regeneration lock the exact session operator ID/name on one PDO transaction, preflight
InnoDB while holding the table metadata lock, bind all values and commit before
returning generated recovery codes. A failed operation never publishes generated
codes. Existing configured operator-table names and named database locations are used.

Preserved: login/ACL/CSRF gates, the start/cancel/confirm/disable/regenerate controls,
HTML/QR flow, eight native recovery codes, hash verification through the existing
library, NULL enrollment last-counter, regeneration without resetting a consumed
counter, and disable clearing all factors. No schema migration or vendor change.

Deliberate differences, separately tested:

- Factor-bearing SQL is replaced by parameter templates in debug output/logging.
  The unmodified baseline actually exposed its enrollment secret and recovery hashes
  in on-page SQL debugging. The test captures this only in memory, never an artifact.
- Regeneration for a disabled account is rejected; the baseline actually displayed
  new codes and success after its UPDATE matched no enabled row.
- Already-enabled enrollment cannot be replaced by another pending session. Two
  independent sessions and PHP workers publish codes only for the committed winner.
- Pending enrollment is tied to ID, username and selected database location. In-flight
  enrollment sessions created before this context was added must restart setup;
  existing enabled factors are not changed. Malformed pending values are discarded.
- Reject malformed POST arrays, stale/replaced identities, nontransactional write
  tables and insufficient factor-column capacity rather than silently truncating.

The UI still permits disable/regeneration using the existing authenticated session
and CSRF policy; this is not a new reauthentication policy. Enrollment keeps the legacy
NULL last-counter semantics rather than redesigning TOTP acceptance windows. Successive
legitimate regeneration requests still invalidate older recovery codes, as the UI says.

## Actual verification

```sh
MFA_CONFIG_BASELINE=1 PYTHONDONTWRITEBYTECODE=1 python3 tests/operator_mfa_config_http.py
PYTHONDONTWRITEBYTECODE=1 python3 tests/operator_mfa_config_http.py
```

Both commands exited 0 on isolated real HTTP/PHP 8.4.24/PDO MySQL/MariaDB 11.8.9.
The baseline page is pinned to the base commit with its still-coherent PEAR provider;
normal factor state is validated with native TOTP/password verification, not compared
as random plaintext/hash dumps. Candidate-only checks cover a forced UPDATE failure,
exact unchanged factor state inside disposable SQL, backend switching mid-enrollment,
replaced identity, MyISAM, short storage and concurrent enrollment. A candidate legacy
provider tripwire proves this page no longer opens PEAR. Existing login/OTP/operator
edit HTTP regression suites also exited 0; PHP syntax and `git diff --check` passed.

No real authenticator device, production directory, NAS or deployment was tested.
Fixtures use copied tracked source without live `daloradius.conf.php`, ephemeral
configuration/session files, tmpfs SQL and internal Docker networking. Factors are
kept only in memory/disposable SQL; PHP logs are checked for factor leakage. Fixture
containers, network, config/session files and root-owned copied cache are removed.

PEAR remains installed for other workflows, including message/portal compatibility.
No push, deployment, installed cron or real data modification is part of this slice.

**Aucune clé, aucun mot de passe, jeton, secret ou identifiant de connexion ne doit être conservé.**
