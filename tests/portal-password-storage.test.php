<?php
/*
 * Static regression checks for portal password storage call sites.
 *
 * Run with: php tests/portal-password-storage.test.php
 */

$root = dirname(__DIR__);
$failures = 0;
function check($label, $condition) {
    global $failures;
    if ($condition) {
        printf("ok   - %s\n", $label);
    } else {
        printf("FAIL - %s\n", $label);
        $failures++;
    }
}

$login = file_get_contents($root . '/app/users/dologin.php');
$change = file_get_contents($root . '/app/users/pref-portal-password-edit.php');
$form = file_get_contents($root . '/app/operators/include/management/userinfo.php');
$functions = file_get_contents($root . '/app/operators/include/management/functions.php');
$import = file_get_contents($root . '/app/operators/mng-import-users.php');
$config = file_get_contents($root . '/app/operators/config-user.php');
$schema = file_get_contents($root . '/contrib/db/mariadb-daloradius.sql');

check('login verifies outside SQL',
      strpos($login, 'dalo_portal_password_verify') !== false
      && strpos($login, "portalloginpassword='%s'") === false);
check('login performs conditional lazy migration',
      strpos($login, 'WHERE id=? AND portalloginpassword=?') !== false);
check('failed login cannot reuse an authenticated session',
      strpos($login, '$authenticated = false') !== false
      && strpos($login, 'if (!$authenticated)') !== false
      && strpos($login, "unset(\$_SESSION['login_user'])") !== false);
check('password change hashes before storage',
      strpos($change, 'dalo_portal_password_hash') !== false
      && strpos($change, "SET portalloginpassword='%s'") === false);
check('operator form uses an empty password input',
      strpos($form, "'type' => 'password'") !== false
      && strpos($form, "'value' => ''") !== false
      && strpos($form, '$ui_PortalLoginPassword') === false);
check('common create and update paths hash portal passwords',
      substr_count($functions, 'dalo_portal_password_hash') >= 2);
check('common create and update paths redact password logs',
      substr_count($functions, '[portal password redacted]') >= 2);
check('empty edit preserves the existing portal credential',
      strpos($functions, 'unset($params[\'portalloginpassword\'])') !== false);
check('CSV portal login is independent from cleartext RADIUS setting',
      strpos($import, 'if ($cleartextPasswordAllowed)') === false
      && strpos($import, 'stores a secure, separate hash') !== false);
check('RADIUS cleartext setting explains the portal-password boundary',
      strpos($config, 'only controls RADIUS password attributes in radcheck') !== false);
check('fresh schema reserves 255 characters for password hashes',
      strpos($schema, '`portalloginpassword` VARCHAR(255)') !== false);

printf("\n%s\n", $failures === 0 ? 'ALL PASSED' : sprintf('%d FAILURE(S)', $failures));
exit($failures === 0 ? 0 : 1);
