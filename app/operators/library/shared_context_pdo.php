<?php
/** R20: strict borrowed reads and selected-location handles for shared reports. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/shared_context_pdo.php') !== false) {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__, 2) . '/common/includes/pdo_connection.php';

function dalo_shared_handle($config, ?PDO $pdo = null) {
    return $pdo ?? dalo_pdo_connect($config, $_SESSION['location_name'] ?? 'default');
}

function dalo_shared_table(PDO $pdo, $config, $key) {
    $allowed = array('CONFIG_DB_TBL_RADACCT', 'CONFIG_DB_TBL_RADCHECK', 'CONFIG_DB_TBL_RADREPLY',
        'CONFIG_DB_TBL_RADGROUPREPLY', 'CONFIG_DB_TBL_RADUSERGROUP', 'CONFIG_DB_TBL_DALOUSERINFO',
        'CONFIG_DB_TBL_DALOUSERBILLINFO', 'CONFIG_DB_TBL_DALOBILLINGINVOICE',
        'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS', 'CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS',
        'CONFIG_DB_TBL_DALOBILLINGINVOICETYPE', 'CONFIG_DB_TBL_DALOPAYMENTS',
        'CONFIG_DB_TBL_DALOBILLINGRATES', 'CONFIG_DB_TBL_DALOBILLINGMERCHANT',
        'CONFIG_DB_TBL_DALOBILLINGPLANS', 'CONFIG_DB_TBL_DALOBATCHHISTORY', 'CONFIG_DB_TBL_DALOHOTSPOTS');
    $name = $config[$key] ?? null;
    if (!in_array($key, $allowed, true) || !is_string($name) ||
        !preg_match('/\A[A-Za-z_][A-Za-z0-9_]{0,63}\z/', $name)) {
        throw new InvalidArgumentException('Invalid shared context table');
    }
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if (!in_array($driver, array('mysql', 'pgsql'), true)) {
        throw new InvalidArgumentException('Unsupported shared context driver');
    }
    $quote = $driver === 'mysql' ? '`' : '"';
    return $quote . $name . $quote;
}

/** Fully fetch before rendering, including on a caller's ERRMODE_SILENT handle. */
function dalo_shared_rows(PDO $pdo, $sql, $params = array(), $mode = PDO::FETCH_NUM) {
    $stmt = $pdo->prepare($sql);
    if ($stmt === false) {
        throw new RuntimeException('Shared context prepare failed');
    }
    try {
        foreach ($params as $key => $value) {
            if (!is_scalar($value) && $value !== null) {
                throw new InvalidArgumentException('Invalid shared context value');
            }
            $type = $value === null ? PDO::PARAM_NULL : (is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            if (!$stmt->bindValue($key, $value, $type)) {
                throw new RuntimeException('Shared context bind failed');
            }
        }
        if (!$stmt->execute()) {
            throw new RuntimeException('Shared context execute failed');
        }
        $rows = $stmt->fetchAll($mode);
        if ($rows === false || $stmt->errorCode() !== '00000') {
            throw new RuntimeException('Shared context fetch failed');
        }
        return $rows;
    } finally {
        $stmt->closeCursor();
    }
}

function dalo_shared_text($value) {
    if (!is_string($value) && !is_int($value)) {
        throw new InvalidArgumentException('Invalid shared context text');
    }
    if (strpos((string) $value, "\0") !== false) {
        throw new InvalidArgumentException('Invalid shared context text');
    }
    return (string) $value;
}

function dalo_shared_id($value) {
    $value = dalo_shared_text($value);
    if (!preg_match('/\A[1-9][0-9]*\z/', $value) ||
        filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) === false) {
        throw new InvalidArgumentException('Invalid shared context identity');
    }
    return (int) $value;
}
