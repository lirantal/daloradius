<?php
/** R02a: independent selector reads, never a replacement for a business handle. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/include/management/selectbox_read.php') !== false) {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../../../common/includes/pdo_connection.php';

function dalo_selectbox_table(PDO $pdo, $config, $key) {
    $keys = array('CONFIG_DB_TBL_DALOBILLINGPLANS', 'CONFIG_DB_TBL_RADGROUPCHECK',
        'CONFIG_DB_TBL_RADGROUPREPLY', 'CONFIG_DB_TBL_RADUSERGROUP', 'CONFIG_DB_TBL_RADHG',
        'CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS', 'CONFIG_DB_TBL_RADIPPOOL',
        'CONFIG_DB_TBL_DALOPROXYS', 'CONFIG_DB_TBL_DALOPAYMENTTYPES',
        'CONFIG_DB_TBL_DALOREALMS', 'CONFIG_DB_TBL_RADACCT', 'CONFIG_DB_TBL_RADCHECK',
        'CONFIG_DB_TBL_DALOUSERINFO', 'CONFIG_DB_TBL_DALOUSERBILLINFO',
        'CONFIG_DB_TBL_RADNAS', 'CONFIG_DB_TBL_DALOBILLINGRATES',
        'CONFIG_DB_TBL_DALODICTIONARY', 'CONFIG_DB_TBL_DALOHOTSPOTS',
        'CONFIG_DB_TBL_DALOBATCHHISTORY');
    $name = is_string($key) && in_array($key, $keys, true) ? ($config[$key] ?? null) : null;
    if (!is_string($name) || !preg_match('/\A[A-Za-z_][A-Za-z0-9_]{0,63}\z/', $name)) {
        throw new InvalidArgumentException('Invalid selector table');
    }
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if (!in_array($driver, array('mysql', 'pgsql'), true)) {
        throw new InvalidArgumentException('Unsupported selector driver');
    }
    $quote = $driver === 'mysql' ? '`' : '"';
    return $quote . $name . $quote;
}

/** Builders and SQL strings are internal trusted code, never request-provided SQL. */
function dalo_selectbox_rows($builder) {
    global $configValues;
    $selector_pdo = null;
    try {
        $selector_pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        $sql = $builder instanceof Closure ? $builder($selector_pdo, $configValues) : $builder;
        if (!is_string($sql) || !preg_match('/\ASELECT\b/i', ltrim($sql))) {
            throw new InvalidArgumentException('Invalid selector read');
        }
        $stmt = $selector_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_NUM);
    } catch (Throwable $error) {
        error_log('Selector read failed: ' . get_class($error));
        return array();
    } finally {
        $stmt = null;
        $selector_pdo = null;
    }
}
