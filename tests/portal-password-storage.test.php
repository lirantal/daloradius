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
$create_provider = file_get_contents($root . '/app/operators/library/user_create.php');
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
check('sensitive login rehash uses PDO without verbose PEAR errors',
      strpos($login, 'dalo_portal_login_rehash') !== false
      && strpos($login, 'dalo_portal_password_match_condition') !== false
      && strpos($login, '$dbSocket') === false
      && strpos($change, 'dalo_portal_password_update') !== false
      && strpos($change, '$dbSocket') === false
      && substr_count($functions, 'dalo_create_info') >= 2
      && strpos($create_provider, '$stmt->execute($values)') !== false
      && strpos($create_provider, '$logDebugSQL .= "$sql;') !== false
      && strpos($migration, 'dalo_pdo_connect') !== false
      && strpos($migration, 'catch (Throwable $error)') !== false
      && strpos($migration, '$dbSocket') === false);
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
      && strpos($change, 'dalo_portal_password_update') !== false
      && strpos($change, 'dalo_portal_password_match_condition') === false);
check('portal password tooltip uses the English fallback dictionary',
      strpos($form, "t('Tooltip', 'portalPasswordKeepTooltip')") !== false
      && strpos($language_en, "['portalPasswordKeepTooltip']") !== false);
check('migration CLI handles connection, batch fetch, and bytewise-guard failures',
      strpos($migration, 'dalo_pdo_connect') !== false
      && strpos($migration, 'fetchAll(PDO::FETCH_ASSOC)') !== false
      && strpos($migration, 'catch (Throwable $error)') !== false
      && strpos($migration, 'dalo_portal_password_match_condition($driver)') !== false);
check('legacy bootstrap removed while migration uses PDO autocommit directly',
      !file_exists($root . '/app/common/includes/db_open.php')
      && !file_exists($root . '/app/common/includes/db_close.php')
      && !file_exists($root . '/app/common/includes/db_error_handler.php')
      && strpos($migration, 'db_open.php') === false
      && strpos($migration, 'db_close.php') === false
      && strpos($migration, 'beginTransaction') === false);
check('migration binds keyset limits and requires adequate password capacity',
      strpos($migration, '$last_id, PDO::PARAM_INT') !== false
      && strpos($migration, '$batch_size, PDO::PARAM_INT') !== false
      && strpos($migration, '(int) $column[\'capacity\'] < 255') !== false
      && strpos($migration, '$update->rowCount()') !== false);
check('fresh schema reserves 255 characters for password hashes',
      strpos($schema, '`portalloginpassword` VARCHAR(255)') !== false);

$_SERVER['PHP_SELF'] = '/cli/tests';
require_once $root . '/app/common/includes/layout.php';
require_once $root . '/app/operators/include/management/functions.php';
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

// Synthetic PDO contracts complement the native SQL/rollback integration suite.
class PortalPasswordStoragePdo extends PDO {
    public $exists; public $fail_write; public $templates = array();
    public function __construct($exists, $fail_write = false) { $this->exists = $exists; $this->fail_write = $fail_write; }
    public function inTransaction(): bool { return true; }
    public function prepare(string $query, array $options = array()): PDOStatement|false {
        return new PortalPasswordStorageStatement($this, $query);
    }
}
class PortalPasswordStorageStatement extends PDOStatement {
    private $owner; private $sql;
    public function __construct($owner, $sql) { $this->owner = $owner; $this->sql = $sql; }
    public function execute(?array $params = null): bool {
        $this->owner->templates[] = $this->sql;
        if ($this->owner->fail_write && strpos($this->sql, 'UPDATE ') === 0) { throw new PDOException('fixture write failure'); }
        return true;
    }
    public function fetchColumn(int $column = 0): mixed { return $this->owner->exists ? 1 : false; }
    public function closeCursor(): bool { return true; }
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array { return array('firstname'=>255,'portalloginpassword'=>255); }
}
$configValues['CONFIG_DB_TBL_DALOUSERINFO'] = 'userinfo';
$allowed_fields = array('firstname', 'portalloginpassword');
foreach (array(true, false) as $sensitive) {
    $db = new PortalPasswordStoragePdo(true); $logDebugSQL = '';
    $params = $sensitive ? array('firstname'=>'Alice','portalloginpassword'=>'fixture-bound-value') : array('firstname'=>'Bob');
    $result = update_info($db, 'user1', $params, $allowed_fields, array(), 'CONFIG_DB_TBL_DALOUSERINFO', array('portalloginpassword'));
    check($sensitive ? 'password writes bind values and retain caller transaction' : 'ordinary writes bind values and retain caller transaction',
          $result === true && $db->inTransaction() && count($db->templates) === 3
          && strpos($logDebugSQL, 'UPDATE ') !== false && strpos($logDebugSQL, '?') !== false
          && strpos($logDebugSQL, 'fixture-bound-value') === false && strpos($logDebugSQL, 'Alice') === false && strpos($logDebugSQL, 'Bob') === false);
}
$db = new PortalPasswordStoragePdo(false); $logDebugSQL = '';
check('missing user does not produce a write or sensitive log',
      update_info($db, 'missing-user', array('portalloginpassword'=>'fixture-bound-value'), $allowed_fields, array(), 'CONFIG_DB_TBL_DALOUSERINFO') === false
      && count($db->templates) === 1 && $logDebugSQL === '');
$db = new PortalPasswordStoragePdo(true, true); $logDebugSQL = ''; $failed = false;
try { update_info($db, 'user1', array('portalloginpassword'=>'fixture-bound-value'), $allowed_fields, array(), 'CONFIG_DB_TBL_DALOUSERINFO'); }
catch (PDOException $error) { $failed = true; }
check('write failures propagate for caller rollback without bound-value logs', $failed && $db->inTransaction() && $logDebugSQL === '');
$rejected = false;
try { update_info(new stdClass(), 'user1', array(), $allowed_fields, array(), 'CONFIG_DB_TBL_DALOUSERINFO'); }
catch (TypeError $error) { $rejected = true; }
check('legacy information handles are rejected', $rejected);

printf("\n%s\n", $failures === 0 ? 'ALL PASSED' : sprintf('%d FAILURE(S)', $failures));
exit($failures === 0 ? 0 : 1);
