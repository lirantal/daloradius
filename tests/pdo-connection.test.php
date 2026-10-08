<?php
/* Standalone UNIT-001 tests: php tests/pdo-connection.test.php */
$_SERVER['PHP_SELF'] = '/cli/tests';
require dirname(__DIR__) . '/app/common/includes/pdo_connection.php';
require dirname(__DIR__) . '/app/common/includes/portal_password.php';

$failures = 0;
function check_pdo_unit($label, $condition) {
    global $failures;
    if (!$condition) {
        fwrite(STDERR, "FAIL: $label\n");
        $failures++;
    } else {
        echo "ok: $label\n";
    }
}
function rejects_pdo_unit($callback, $message) {
    try {
        $callback();
    } catch (Throwable $exception) {
        return $exception->getMessage() === $message;
    }
    return false;
}

$config = array(
    'CONFIG_DB_ENGINE' => 'mysqli', 'CONFIG_DB_HOST' => 'localhost',
    'CONFIG_DB_PORT' => '3306', 'CONFIG_DB_NAME' => 'raddb',
    'CONFIG_DB_USER' => 'test', 'CONFIG_DB_PASS' => 'secret;@:#',
    'CONFIG_LOCATIONS' => array('office' => array(
        'Engine' => 'mysql', 'Hostname' => '192.0.2.1', 'Port' => '3307',
        'Database' => 'other', 'Username' => 'office-user',
        'Password' => 'office-secret;@:#',
    )),
);
$default = dalo_pdo_settings($config);
$office = dalo_pdo_settings($config, 'office');
check_pdo_unit('default and named location select separate credentials',
    $default['username'] === 'test' && $office['username'] === 'office-user'
    && $office['password'] === 'office-secret;@:#');
check_pdo_unit('mysqli maps to PDO MySQL with explicit charset and port',
    dalo_pdo_dsn($default) === 'mysql:host=localhost;port=3306;dbname=raddb;charset=utf8mb4');
check_pdo_unit('location DSN contains no credentials',
    dalo_pdo_dsn($office) === 'mysql:host=192.0.2.1;port=3307;dbname=other;charset=utf8mb4'
    && strpos(dalo_pdo_dsn($office), 'office-secret') === false);
check_pdo_unit('unknown location is rejected rather than falling back',
    rejects_pdo_unit(function() use ($config) { dalo_pdo_settings($config, 'missing'); },
                     'Unknown database location'));
$incomplete = $config;
unset($incomplete['CONFIG_LOCATIONS']['office']['Password']);
check_pdo_unit('incomplete location does not mix credentials',
    rejects_pdo_unit(function() use ($incomplete) { dalo_pdo_settings($incomplete, 'office'); },
                     'Incomplete database location'));
foreach (array('mysql', 'pgsql') as $engine) {
    $settings = $default;
    $settings['engine'] = $engine;
    check_pdo_unit("$engine driver uses an explicit DSN", strpos(dalo_pdo_dsn($settings),
        "$engine:host=localhost;port=3306;dbname=raddb") === 0);
}
foreach (array('odbc', 'mssql', 'unknown') as $engine) {
    $settings = $default;
    $settings['engine'] = $engine;
    check_pdo_unit("unsupported $engine fails closed", rejects_pdo_unit(
        function() use ($settings) { dalo_pdo_dsn($settings); },
        'Unsupported PDO database engine'));
}
foreach (array('host' => "localhost;dbname=other", 'database' => "db\nother") as $field => $value) {
    $settings = $default;
    $settings[$field] = $value;
    check_pdo_unit("$field cannot inject DSN options", rejects_pdo_unit(
        function() use ($settings) { dalo_pdo_dsn($settings); },
        'Invalid database connection setting'));
}
foreach (array('0', '65536', '3306;password=x') as $port) {
    $settings = $default;
    $settings['port'] = $port;
    check_pdo_unit("invalid port $port is rejected", rejects_pdo_unit(
        function() use ($settings) { dalo_pdo_dsn($settings); }, 'Invalid database port'));
}
$settings = $default;
$settings['engine'] = 'pgsql';
check_pdo_unit('uninstalled driver produces generic error without credentials',
    in_array('pgsql', PDO::getAvailableDrivers(), true) || rejects_pdo_unit(
        function() use ($config) {
            $pg = $config; $pg['CONFIG_DB_ENGINE'] = 'pgsql'; dalo_pdo_connect($pg);
        }, 'Required PDO database driver is unavailable'));

// No database driver is needed to exercise the PDO type boundary.
class UnitTestPdo extends PDO { public function __construct() {} }
$pdo = new UnitTestPdo();
$called = false;
$result = dalo_portal_db_sensitive_call($pdo, function() use (&$called) {
    $called = true;
    return 42;
});
check_pdo_unit('sensitive call permits PDO without PEAR error mode', $called && $result === 42);
$propagates = false;
try {
    dalo_portal_db_sensitive_call($pdo, function() { throw new RuntimeException('sentinel'); });
} catch (RuntimeException $exception) {
    $propagates = $exception->getMessage() === 'sentinel';
}
check_pdo_unit('sensitive call propagates non-database callback failure', $propagates);
$masked = false;
try {
    dalo_portal_db_sensitive_call($pdo, function() {
        throw new PDOException('private-password SQL statement');
    });
} catch (RuntimeException $exception) {
    $masked = $exception->getMessage() === 'Database operation failed'
        && $exception->getPrevious() === null;
}
check_pdo_unit('sensitive PDO failures cannot disclose SQL or credentials', $masked);

if (getenv('DALORADIUS_PDO_TEST_LIVE_CONFIG')) {
    $configValues = array();
    require getenv('DALORADIUS_PDO_TEST_LIVE_CONFIG');
    $live = dalo_pdo_connect($configValues);
    check_pdo_unit('live configured database accepts a read-only query',
        $live->query('SELECT 1')->fetchColumn() === 1);
    check_pdo_unit('live PDO uses exception, associative, native-prepare and no persistence',
        $live->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION
        && $live->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE) === PDO::FETCH_ASSOC
        && $live->getAttribute(PDO::ATTR_EMULATE_PREPARES) === false
        && $live->getAttribute(PDO::ATTR_PERSISTENT) === false);
    check_pdo_unit('live MySQL session preserves non-strict legacy SQL mode',
        $live->query('SELECT @@SESSION.sql_mode')->fetchColumn() === '');
    $bad = $configValues;
    $bad['CONFIG_DB_PASS'] = 'definitely-not-the-configured-password';
    check_pdo_unit('invalid credentials return a generic error', rejects_pdo_unit(
        function() use ($bad) { dalo_pdo_connect($bad); },
        'Database connection or initialization failed'));
}

echo $failures === 0 ? "ALL PASSED\n" : "$failures FAILURE(S)\n";
exit($failures === 0 ? 0 : 1);
