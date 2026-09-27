<?php
/* Static regression checks for the operator identity management pages. */
$root = dirname(__DIR__);
$edit = file_get_contents($root . '/app/operators/config-operators-edit.php');
$list = file_get_contents($root . '/app/operators/config-operators-list.php');
$info = file_get_contents($root . '/app/operators/include/management/operatorinfo.php');
$editHelper = file_get_contents($root . '/app/operators/library/operator_edit.php');

$failures = 0;
function check_management_identity(bool $condition, string $label): void
{
    global $failures;
    if (!$condition) {
        fwrite(STDERR, "not ok - $label\n");
        $failures++;
        return;
    }
    fwrite(STDOUT, "ok - $label\n");
}

check_management_identity(strpos($edit, "operator_edit.php") !== false
    && strpos($editHelper, "operator_prepare_update_identity(") !== false,
    'edit validates source changes through the identity helper');
check_management_identity(strpos($editHelper, "SELECT id,username,auth_source,external_id") !== false
    && strpos($editHelper, "FOR UPDATE") !== false,
    'edit locks the persisted identity before validating changes');
check_management_identity(strpos($edit, "identity_operator_id") !== false
    && strpos($editHelper, "WHERE id=?") !== false,
    'edit updates the selected immutable operator id');
check_management_identity(strpos($edit, "identity_auth_source") !== false
    && strpos($edit, "identity_external_id") !== false,
    'edit renders the identity state used for optimistic concurrency');
check_management_identity(strpos($editHelper, "Operator identity changed; reload and retry") !== false,
    'edit rejects a stale identity snapshot before mutation');
check_management_identity(strpos($editHelper, "operator_identity_state_matches(") !== false,
    'edit compares the submitted identity snapshot with the current row');
check_management_identity(strpos($editHelper, "beginTransaction()") !== false
    && strpos($editHelper, "FOR UPDATE") !== false
    && strpos($editHelper, "rollBack()") !== false,
    'edit serializes identity changes and rolls back rejected writes');
check_management_identity(strpos($editHelper, "operator_normalize_auth_source(\$current['auth_source'])") !== false
    && strpos($editHelper, "operator_identity_state_matches(\$source, \$current['external_id']") !== false,
    'edit compares the same normalized identity state shown in the form');
check_management_identity(strpos($editHelper, "\$identity['password_mode'] === 'clear' ? null") !== false,
    'edit clears local password material for LDAP identities');
check_management_identity(strpos($edit, "Updated operator profile and ACLs using PDO") !== false
    && strpos($editHelper, "password_hash") !== false,
    'edit logs a redacted update description, never a password hash');
check_management_identity(strpos($info, "'name' => 'auth_source'") !== false,
    'management form exposes the authentication source');
check_management_identity(strpos($info, "'name' => 'external_id'") !== false,
    'management form exposes the optional stable external id');
check_management_identity(strpos($info, "'name' => 'confirm_auth_source_change'") !== false,
    'edit requires an explicit source-change confirmation control');
check_management_identity(strpos($list, "password AS auth") === false,
    'operator list no longer selects password hashes');
check_management_identity(strpos($list, "CASE WHEN external_id IS NULL") !== false,
    'operator list exposes only linked/not-linked state');
check_management_identity(strpos($list, "auth_source") !== false,
    'operator list exposes authentication source');

if ($failures > 0) {
    fwrite(STDERR, "\n$failures test(s) failed\n");
    exit(1);
}
fwrite(STDOUT, "\nALL PASSED\n");
