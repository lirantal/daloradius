<?php
/** R02b: borrow the caller's PDO handle; never open/close or own a transaction. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/include/management/read_helpers_pdo.php') !== false) {
    http_response_code(404);
    exit;
}

function dalo_read_identifier(PDO $pdo, $name) {
    if (!is_string($name) || !preg_match('/\A[A-Za-z0-9_]{1,64}\z/', $name)) {
        throw new InvalidArgumentException('Invalid read identifier');
    }
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if (!in_array($driver, array('mysql', 'pgsql'), true)) {
        throw new InvalidArgumentException('Unsupported read driver');
    }
    $quote = $driver === 'mysql' ? '`' : '"';
    return $quote . $name . $quote;
}

function dalo_read_table(PDO $pdo, $config, $key) {
    if (!is_string($key) || !preg_match('/\ACONFIG_DB_TBL_[A-Z0-9_]+\z/', $key) ||
        !array_key_exists($key, $config)) {
        throw new InvalidArgumentException('Invalid read table key');
    }
    return dalo_read_identifier($pdo, $config[$key]);
}

/** Only trusted application SQL, not an SQL parser or a request-query interface. */
function dalo_read_statement(PDO $pdo, $sql, $values = array()) {
    if (!is_string($sql) || !preg_match('/\A(?:SELECT|SHOW)\b/i', ltrim($sql))) {
        throw new InvalidArgumentException('Invalid read statement');
    }
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        return $stmt;
    } catch (PDOException $error) {
        error_log('Operator read failed: ' . get_class($error));
        throw new RuntimeException('Operator read failed');
    }
}
