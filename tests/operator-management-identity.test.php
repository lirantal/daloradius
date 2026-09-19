<?php
/* Static regression checks for the operator identity management pages. */
$root = dirname(__DIR__);
$edit = file_get_contents($root . '/app/operators/config-operators-edit.php');
$list = file_get_contents($root . '/app/operators/config-operators-list.php');
$info = file_get_contents($root . '/app/operators/include/management/operatorinfo.php');

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

check_management_identity(strpos($edit, "operator_prepare_update_identity(") !== false,
    'edit validates source changes through the identity helper');
check_management_identity(strpos($edit, "SELECT id, auth_source, external_id") !== false,
    'edit loads the persisted identity source before validating changes');
check_management_identity(strpos($edit, "WHERE id=%d") !== false,
    'edit updates the selected immutable operator id');
check_management_identity(strpos($edit, "password=NULL") !== false,
    'edit clears local password material for LDAP identities');
check_management_identity(strpos($edit, "UPDATE operator identity and profile") !== false,
    'edit logs a redacted identity update description');
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
