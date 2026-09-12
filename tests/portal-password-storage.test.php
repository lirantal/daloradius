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
$layout = file_get_contents($root . '/app/common/includes/layout.php');
$functions = file_get_contents($root . '/app/operators/include/management/functions.php');
$import = file_get_contents($root . '/app/operators/mng-import-users.php');
$config = file_get_contents($root . '/app/operators/config-user.php');
$migration = file_get_contents($root . '/contrib/scripts/maintenance/hash-user-portal-passwords.php');
$schema = file_get_contents($root . '/contrib/db/mariadb-daloradius.sql');
$operator_flows = array(
    'mng-new' => file_get_contents($root . '/app/operators/mng-new.php'),
    'mng-new-quick' => file_get_contents($root . '/app/operators/mng-new-quick.php'),
    'mng-batch-add' => file_get_contents($root . '/app/operators/mng-batch-add.php'),
    'bill-pos-new' => file_get_contents($root . '/app/operators/bill-pos-new.php'),
    'mng-edit' => file_get_contents($root . '/app/operators/mng-edit.php'),
    'bill-pos-edit' => file_get_contents($root . '/app/operators/bill-pos-edit.php'),
);

check('login verifies outside SQL',
      strpos($login, 'dalo_portal_password_verify') !== false
      && strpos($login, "portalloginpassword='%s'") === false);
check('login performs conditional lazy migration',
      strpos($login, 'WHERE id=? AND portalloginpassword=?') !== false);
check('failed login cannot reuse an authenticated session',
      strpos($login, '$authenticated = false') !== false
      && strpos($login, 'if (!$authenticated)') !== false
      && strpos($login, "unset(\$_SESSION['login_user'])") !== false);
check('password zero is not rejected by login empty semantics',
      strpos($login, "\$_POST['login_pass'] !== ''") !== false
      && strpos($login, "!empty(\$_POST['login_pass'])") === false);
check('password change hashes before storage',
      strpos($change, 'dalo_portal_password_hash') !== false
      && strpos($change, "SET portalloginpassword='%s'") === false);
check('operator form uses an empty password input',
      strpos($form, "'type' => 'password'") !== false
      && strpos($form, "'value' => ''") !== false
      && strpos($form, '$ui_PortalLoginPassword') === false);
check('portal password has separate browser length constraints',
      strpos($form, "'minlength' => 1") !== false
      && strpos($form, "'maxlength' => null") !== false
      && substr_count($layout, "array_key_exists('minlength', \$input_descriptor)") >= 2
      && substr_count($layout, "array_key_exists('maxlength', \$input_descriptor)") >= 2);
check('common create and update paths hash portal passwords',
      substr_count($functions, 'dalo_portal_password_hash') >= 2);
check('common create and update paths redact password logs',
      substr_count($functions, '[portal password redacted]') >= 2);
check('all sensitive password writes disable verbose PEAR DB errors',
      strpos($login, 'dalo_portal_db_sensitive_call') !== false
      && strpos($change, 'dalo_portal_db_sensitive_call') !== false
      && substr_count($functions, 'dalo_portal_db_sensitive_call') >= 2
      && strpos($migration, 'setErrorHandling(PEAR_ERROR_RETURN)') !== false);
check('empty edit preserves the existing portal credential',
      strpos($functions, 'unset($params[\'portalloginpassword\'])') !== false);
check('CSV portal login is independent from cleartext RADIUS setting',
      strpos($import, 'if ($cleartextPasswordAllowed)') === false
      && strpos($import, 'stores a secure, separate hash') !== false);
check('RADIUS cleartext setting explains the portal-password boundary',
      strpos($config, 'only controls RADIUS password attributes in radcheck') !== false);

foreach ($operator_flows as $name => $flow) {
    check("$name rejects missing credentials before writes",
          strpos($flow, 'dalo_portal_access_is_valid') !== false
          && strpos($flow, '!$portal_access_valid') !== false
          && strpos($flow, 'dalo_portal_password_is_acceptable') !== false);
}
check('self-service validates NUL before trimming password fields',
      substr_count($change, 'dalo_portal_password_is_acceptable') >= 3);
check('fresh schema reserves 255 characters for password hashes',
      strpos($schema, '`portalloginpassword` VARCHAR(255)') !== false);

$_SERVER['PHP_SELF'] = '/cli/tests';
require_once $root . '/app/common/includes/layout.php';
$configValues['CONFIG_DB_PASSWORD_MIN_LENGTH'] = 8;
$configValues['CONFIG_DB_PASSWORD_MAX_LENGTH'] = 14;

ob_start();
print_input_field(array('name' => 'radiusPassword', 'type' => 'password'));
$default_password_html = ob_get_clean();
check('ordinary password fields retain the configured database bounds',
      strpos($default_password_html, ' minlength="8"') !== false
      && strpos($default_password_html, ' maxlength="14"') !== false);

$portal_descriptor = array(
    'name' => 'portalLoginPassword',
    'type' => 'password',
    'minlength' => 1,
    'maxlength' => null,
);
ob_start();
print_input_field($portal_descriptor);
$portal_password_html = ob_get_clean();
check('portal password renderer accepts zero and omits the RADIUS maximum',
      strpos($portal_password_html, ' minlength="1"') !== false
      && strpos($portal_password_html, ' maxlength=') === false);

ob_start();
menu_print_input_field($portal_descriptor);
$menu_portal_password_html = ob_get_clean();
check('menu password renderer applies the same portal override',
      strpos($menu_portal_password_html, ' minlength="1"') !== false
      && strpos($menu_portal_password_html, ' maxlength=') === false);

printf("\n%s\n", $failures === 0 ? 'ALL PASSED' : sprintf('%d FAILURE(S)', $failures));
exit($failures === 0 ? 0 : 1);
