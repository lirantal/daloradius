<?php
/*
 * Shared PDO connection provider for application and maintenance callers.
 */

if (strpos($_SERVER['PHP_SELF'] ?? '', '/common/includes/pdo_connection.php') !== false) {
    http_response_code(404);
    exit;
}

/** Resolve configured default/location connection settings. */
function dalo_pdo_settings($configValues, $locationName = 'default') {
    $settings = array(
        'engine' => $configValues['CONFIG_DB_ENGINE'],
        'host' => $configValues['CONFIG_DB_HOST'],
        'port' => $configValues['CONFIG_DB_PORT'],
        'database' => $configValues['CONFIG_DB_NAME'],
        'username' => $configValues['CONFIG_DB_USER'],
        'password' => $configValues['CONFIG_DB_PASS'],
    );

    if ($locationName !== null && $locationName !== '' && $locationName !== 'default') {
        if (!isset($configValues['CONFIG_LOCATIONS']) ||
            !is_array($configValues['CONFIG_LOCATIONS']) ||
            !array_key_exists($locationName, $configValues['CONFIG_LOCATIONS']) ||
            !is_array($configValues['CONFIG_LOCATIONS'][$locationName])) {
            throw new InvalidArgumentException('Unknown database location');
        }

        $location = $configValues['CONFIG_LOCATIONS'][$locationName];
        foreach (array('Engine' => 'engine', 'Hostname' => 'host', 'Port' => 'port',
                       'Database' => 'database', 'Username' => 'username',
                       'Password' => 'password') as $key => $destination) {
            if (!array_key_exists($key, $location)) {
                throw new InvalidArgumentException('Incomplete database location');
            }
            $settings[$destination] = $location[$key];
        }
    }

    return $settings;
}

/** Build a driver-specific DSN without including credentials. */
function dalo_pdo_dsn($settings) {
    $engine = strtolower((string) $settings['engine']);
    $drivers = array('mysql' => 'mysql', 'mysqli' => 'mysql', 'pgsql' => 'pgsql');
    if (!isset($drivers[$engine])) {
        throw new InvalidArgumentException('Unsupported PDO database engine');
    }

    $host = $settings['host'];
    $database = $settings['database'];
    $port = $settings['port'];
    foreach (array($host, $database) as $value) {
        if (!is_string($value) || $value === '' || strpbrk($value, ";\r\n\0") !== false) {
            throw new InvalidArgumentException('Invalid database connection setting');
        }
    }
    if (!ctype_digit((string) $port) || (int) $port < 1 || (int) $port > 65535) {
        throw new InvalidArgumentException('Invalid database port');
    }

    $driver = $drivers[$engine];
    $dsn = $driver . ':host=' . $host . ';port=' . (int) $port . ';dbname=' . $database;
    if ($driver === 'mysql') {
        $dsn .= ';charset=utf8mb4';
    }

    return $dsn;
}

/**
 * Opt-in PDO connection. The caller owns the returned handle and must use that
 * same handle for every statement in a transaction. No persistent connection.
 * Only MySQL/MariaDB and PostgreSQL DSNs are implemented at this stage; an
 * uninstalled PDO driver or unsupported legacy engine fails explicitly.
 */
function dalo_pdo_connect($configValues, $locationName = 'default') {
    $settings = dalo_pdo_settings($configValues, $locationName);
    $dsn = dalo_pdo_dsn($settings);
    $driver = strstr($dsn, ':', true);
    if (!class_exists('PDO') || !in_array($driver, PDO::getAvailableDrivers(), true)) {
        throw new RuntimeException('Required PDO database driver is unavailable');
    }

    try {
        $pdo = new PDO($dsn, $settings['username'], $settings['password'], array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false,
        ));
        // Preserve the legacy session setting for MySQL/MariaDB only.
        if ($driver === 'mysql') {
            $pdo->exec("SET SESSION sql_mode = ''");
        }
        return $pdo;
    } catch (PDOException $exception) {
        // PDO errors may include host/database details: never expose the cause
        // (including a previous exception) to a web page or CLI output.
        throw new RuntimeException('Database connection or initialization failed');
    }
}
