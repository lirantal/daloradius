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
$language_en = file_get_contents($root . '/app/operators/lang/en.php');
$functions = file_get_contents($root . '/app/operators/include/management/functions.php');
$import = file_get_contents($root . '/app/operators/mng-import-users.php');
$config = file_get_contents($root . '/app/operators/config-user.php');
$migration = file_get_contents($root . '/contrib/scripts/maintenance/hash-user-portal-passwords.php');
$db_open = file_get_contents($root . '/app/common/includes/db_open.php');
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
      strpos($login, 'dalo_portal_password_match_condition') !== false);
check('failed login cannot reuse an authenticated session',
      strpos($login, '$authenticated = false') !== false
      && strpos($login, 'if (!$authenticated && $authentication_attempted)') !== false
      && strpos($login, "unset(\$_SESSION['login_user'])") !== false);
check('non-authenticating requests preserve an existing session',
      strpos($login, "\$authenticated = isset(\$_SESSION['logged_in'])") !== false
      && strpos($login, '$authentication_attempted = false') !== false);
check('missing and ambiguous users run dummy password verification',
      strpos($login, 'dalo_portal_password_dummy_verify($login_pass)') !== false);
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
      strpos($functions, 'redact_sensitive_values') !== false
      && strpos($functions, "array('portalloginpassword')") !== false
      && strpos($functions, '[portal password redacted]') === false);
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
check('CSV retry preserves the portal-login yes/no selection',
      strpos($import, "(\$enableportallogin === 1) ? \"yes\" : \"no\"") !== false);
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
check('self-service distinguishes database and concurrent failures',
      strpos($change, '$lookup_error') !== false
      && strpos($change, '[concurrent update]') !== false
      && strpos($change, 'dalo_portal_password_match_condition') !== false);
check('portal password tooltip uses the English fallback dictionary',
      strpos($form, "t('Tooltip', 'portalPasswordKeepTooltip')") !== false
      && strpos($language_en, "['portalPasswordKeepTooltip']") !== false);
check('migration CLI handles connection, fetch, and bytewise-guard failures',
      strpos($migration, '$db_connect_error_handler = function') !== false
      && strpos($migration, 'DB::isError($row)') !== false
      && strpos($migration, 'dalo_portal_password_match_condition') !== false);
check('migration connection handler is not installed for query failures',
      strpos($db_open, 'isset($db_connect_error_handler)') !== false
      && strpos($db_open, "\$error_handler = (isset(\$db_error_handler)") !== false
      && strpos($migration, '$db_error_handler = function') === false);
check('fresh schema reserves 255 characters for password hashes',
      strpos($schema, '`portalloginpassword` VARCHAR(255)') !== false);

$_SERVER['PHP_SELF'] = '/cli/tests';
require_once $root . '/app/common/includes/layout.php';
include_once 'DB.php';
require_once $root . '/app/operators/include/management/functions.php';
if (!defined('PEAR_ERROR_RETURN')) {
    define('PEAR_ERROR_RETURN', 2);
}
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

$redacted_values = redact_sensitive_values(
    array('firstname', 'portalloginpassword'),
    array('Alice', 'secret-hash-value'),
    array('portalloginpassword')
);
$redacted_sql = make_update_query('userinfo', 'user1',
                                  array('firstname', 'portalloginpassword'), $redacted_values);
check('redacted SQL keeps non-sensitive fields without exposing the hash',
      strpos($redacted_sql, "`firstname`='Alice'") !== false
      && strpos($redacted_sql, "`portalloginpassword`='[redacted]'") !== false
      && strpos($redacted_sql, 'secret-hash-value') === false);

class PortalPasswordStorageResult {
    private $count;

    function __construct($count) {
        $this->count = $count;
    }

    function fetchrow() {
        return array($this->count);
    }
}

class PortalPasswordStorageDb {
    public $exists;
    public $fail_write;
    public $mode = 'application-callback';
    public $queries = array();
    private $modes = array();

    function __construct($exists, $fail_write = false) {
        $this->exists = $exists;
        $this->fail_write = $fail_write;
    }

    function escapeSimple($value) {
        return addslashes($value);
    }

    function query($sql) {
        $this->queries[] = array($sql, $this->mode);
        if (strpos($sql, 'SELECT COUNT') === 0) {
            return new PortalPasswordStorageResult($this->exists ? 1 : 0);
        }

        return $this->fail_write ? PEAR::raiseError('synthetic write failure') : DB_OK;
    }

    function pushErrorHandling($mode) {
        $this->modes[] = $this->mode;
        $this->mode = $mode;
    }

    function popErrorHandling() {
        $this->mode = array_pop($this->modes);
    }
}

$configValues['CONFIG_DB_TBL_DALOUSERINFO'] = 'userinfo';
$allowed_fields = array('firstname', 'portalloginpassword');
$logDebugSQL = '';
$sensitive_db = new PortalPasswordStorageDb(true);
$sensitive_result = update_info(
    $sensitive_db,
    'user1',
    array('firstname' => 'Alice', 'portalloginpassword' => 'secret-hash-value'),
    $allowed_fields,
    array(),
    'CONFIG_DB_TBL_DALOUSERINFO',
    array('portalloginpassword')
);
check('only the password-bearing write suppresses verbose DB errors',
      $sensitive_result === true
      && count($sensitive_db->queries) === 2
      && $sensitive_db->queries[0][1] === 'application-callback'
      && $sensitive_db->queries[1][1] === PEAR_ERROR_RETURN
      && $sensitive_db->mode === 'application-callback');
check('password-bearing writes log only redacted SQL after a real query',
      strpos($logDebugSQL, "`firstname`='Alice'") !== false
      && strpos($logDebugSQL, "`portalloginpassword`='[redacted]'") !== false
      && strpos($logDebugSQL, 'secret-hash-value') === false);

$logDebugSQL = '';
$ordinary_db = new PortalPasswordStorageDb(true);
update_info(
    $ordinary_db,
    'user1',
    array('firstname' => 'Bob'),
    $allowed_fields,
    array(),
    'CONFIG_DB_TBL_DALOUSERINFO',
    array('portalloginpassword')
);
check('ordinary userinfo writes retain the application DB error mode',
      $ordinary_db->queries[1][1] === 'application-callback');

$logDebugSQL = '';
$missing_db = new PortalPasswordStorageDb(false);
$missing_result = update_info(
    $missing_db,
    'missing-user',
    array('portalloginpassword' => 'secret-hash-value'),
    $allowed_fields,
    array(),
    'CONFIG_DB_TBL_DALOUSERINFO',
    array('portalloginpassword')
);
check('missing users do not produce a synthetic password update log',
      $missing_result === false
      && count($missing_db->queries) === 1
      && strpos($logDebugSQL, 'UPDATE ') === false
      && strpos($logDebugSQL, 'secret-hash-value') === false);

$logDebugSQL = '';
$failed_db = new PortalPasswordStorageDb(true, true);
$failed_result = update_info(
    $failed_db,
    'user1',
    array('portalloginpassword' => 'secret-hash-value'),
    $allowed_fields,
    array(),
    'CONFIG_DB_TBL_DALOUSERINFO',
    array('portalloginpassword')
);
check('a returned PEAR DB error cannot compare as a successful write',
      $failed_result === false
      && $failed_db->mode === 'application-callback'
      && strpos($logDebugSQL, 'secret-hash-value') === false);

printf("\n%s\n", $failures === 0 ? 'ALL PASSED' : sprintf('%d FAILURE(S)', $failures));
exit($failures === 0 ? 0 : 1);
