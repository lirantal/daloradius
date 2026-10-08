<?php
/* Transactional realm/proxy mutations with coordinated configuration publication. */
require_once __DIR__ . '/saveRealmsProxys.php';

class RealmProxyUncertainException extends RuntimeException {}

function realm_proxy_tables($config) {
    $tables = array();
    foreach (array('proxy' => 'CONFIG_DB_TBL_DALOPROXYS',
                   'realm' => 'CONFIG_DB_TBL_DALOREALMS') as $kind => $key) {
        $value = $config[$key] ?? null;
        if (!is_string($value) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $value)) {
            throw new InvalidArgumentException('Invalid realm/proxy table');
        }
        $tables[$kind] = '`' . $value . '`';
    }
    return $tables;
}

function realm_proxy_connect($config, $location, $write = false) {
    require_once $config['COMMON_INCLUDES'] . '/pdo_connection.php';
    $tables = realm_proxy_tables($config);
    $pdo = dalo_pdo_connect($config, $location);
    if ($write) {
        $query = $pdo->prepare('SELECT ENGINE FROM INFORMATION_SCHEMA.TABLES '
                              . 'WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
        foreach (array('CONFIG_DB_TBL_DALOPROXYS','CONFIG_DB_TBL_DALOREALMS') as $key) {
            $query->execute(array($config[$key]));
            if (strcasecmp((string)$query->fetchColumn(), 'InnoDB') !== 0) {
                throw new RuntimeException('Realm/proxy table must be InnoDB');
            }
            $query->closeCursor();
        }
    }
    return array($pdo, $tables);
}

function realm_proxy_input($post, $key, $limit, $required = false) {
    $value = $post[$key] ?? '';
    if (!is_string($value)) { throw new InvalidArgumentException('Invalid realm/proxy field'); }
    $value = trim($value);
    if (($required && ($value === '' || $value === '0')) ||
        (function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value)) > $limit ||
        !preg_match('//u', $value) || preg_match('/[\r\n{}\x00]/', $value)) {
        throw new InvalidArgumentException('Invalid realm/proxy value');
    }
    return $value;
}

function realm_proxy_number($post, $key) {
    $raw = realm_proxy_input($post, $key, 15);
    if ($raw === '' || $raw === '0') { return 0; }
    if (!ctype_digit($raw) || strlen($raw) > 10 || (float)$raw > 2147483647) {
        throw new InvalidArgumentException('Invalid realm/proxy number');
    }
    return (int)$raw;
}

function realm_proxy_fields($kind, $post) {
    if ($kind === 'proxy') {
        $fields = array('proxyname' => realm_proxy_input($post, 'proxyname', 128, true));
        foreach (array('retry_delay','retry_count','dead_time','default_fallback') as $name) {
            $fields[$name] = realm_proxy_number($post, $name);
        }
        return $fields;
    }
    $types = array('fail-over','load-balance','client-balance','client-port-balance','keyed-balance');
    $type = realm_proxy_input($post, 'type', 32);
    $fields = array('realmname' => realm_proxy_input($post, 'realmname', 128, true),
                    'type' => in_array($type, $types, true) ? $type : $types[0]);
    foreach (array('authhost' => 256, 'accthost' => 256, 'secret' => 128,
                   'ldflag' => 64) as $name => $limit) {
        $fields[$name] = realm_proxy_input($post, $name, $limit);
    }
    $nostrip = realm_proxy_input($post, 'nostrip', 3);
    $fields['nostrip'] = strtolower($nostrip) === 'yes' ? 1 : 0;
    foreach (array('hints','notrealm') as $name) { $fields[$name] = realm_proxy_number($post, $name); }
    return $fields;
}

function realm_proxy_rows(PDO $pdo, $table, $column) {
    return $pdo->query("SELECT id,$column FROM $table ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
}

function realm_proxy_mutate($config, $location, $operator, $kind, $action, $payload) {
    if (!in_array($kind, array('proxy','realm'), true) ||
        !in_array($action, array('create','edit','delete'), true)) {
        throw new InvalidArgumentException('Invalid realm/proxy operation');
    }
    $fields = $action === 'delete' ? null : realm_proxy_fields($kind, $payload);
    $ids = null;
    if ($action === 'delete') {
        if (!is_array($payload) || count($payload) === 0 || count($payload) > 5000) {
            throw new InvalidArgumentException('Invalid realm/proxy selection');
        }
        $ids = array();
        foreach ($payload as $name) {
            if (!is_string($name) || $name === '') {
                throw new InvalidArgumentException('Invalid realm/proxy selection');
            }
            $ids[trim($name)] = trim($name);
        }
        if (isset($ids[''])) { throw new InvalidArgumentException('Invalid realm/proxy selection'); }
        $ids = array_values($ids);
    }
    $pdo = null;
    $lock = null;
    $path = null;
    $old = null;
    $staged = null;
    $published = false;
    $committed = false;
    $commitAttempted = false;
    try {
        list($pdo, $tables) = realm_proxy_connect($config, $location, true);
        $database = $pdo->query('SELECT DATABASE()')->fetchColumn();
        if (!is_string($database) || $database === '') { throw new RuntimeException('Database name unavailable'); }
        $lockName = 'daloradius:realm-proxy:' . substr(hash('sha256', $database), 0, 36);
        $stmt = $pdo->prepare('SELECT GET_LOCK(?,30)');
        $stmt->execute(array($lockName));
        if ((int)$stmt->fetchColumn() !== 1) { throw new RuntimeException('Realm/proxy change lock unavailable'); }
        $lock = $lockName;
        list($path, $old) = realm_proxy_file_target($config);
        if (!$pdo->beginTransaction()) { throw new RuntimeException('Transaction unavailable'); }
        $table = $tables[$kind];
        $nameColumn = $kind === 'proxy' ? 'proxyname' : 'realmname';
        $result = array();
        if ($action === 'create') {
            $check = $pdo->prepare("SELECT id FROM $table WHERE $nameColumn=? LIMIT 1 FOR UPDATE");
            $check->execute(array($fields[$nameColumn]));
            if ($check->fetchColumn() !== false) { throw new InvalidArgumentException('Name already exists'); }
            $columns = array_keys($fields);
            $columns = array_merge($columns, array('creationdate','creationby'));
            $sql = "INSERT INTO $table (" . implode(',', $columns) . ') VALUES ('
                 . implode(',', array_fill(0, count($columns) - 2, '?')) . ',NOW(),?)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge(array_values($fields), array($operator)));
            $result = array('id' => (int)$pdo->lastInsertId(), 'name' => $fields[$nameColumn]);
        } elseif ($action === 'edit') {
            $identity = $kind === 'proxy' ? ($payload['item'] ?? null) : ($payload['realmname'] ?? null);
            if ($kind === 'proxy') {
                if (!is_string($identity) || !preg_match('/^proxy-([1-9][0-9]*)$/D', $identity, $m)) {
                    throw new InvalidArgumentException('Invalid proxy identity');
                }
                $lookup = $pdo->prepare("SELECT id,$nameColumn FROM $table WHERE id=? FOR UPDATE");
                $lookup->execute(array($m[1]));
            } else {
                $lookup = $pdo->prepare("SELECT id,$nameColumn FROM $table WHERE BINARY realmname=BINARY ? LIMIT 2 FOR UPDATE");
                $lookup->execute(array($identity));
            }
            $row = $lookup->fetch(PDO::FETCH_ASSOC);
            if (!$row || ($kind === 'realm' && $lookup->fetch(PDO::FETCH_ASSOC))) {
                throw new InvalidArgumentException('Missing or ambiguous realm/proxy');
            }
            if ($kind === 'proxy' && $fields['proxyname'] !== $row['proxyname']) {
                $check = $pdo->prepare("SELECT id FROM $table WHERE proxyname=? AND id<>? LIMIT 1 FOR UPDATE");
                $check->execute(array($fields['proxyname'], $row['id']));
                if ($check->fetchColumn() !== false) { throw new InvalidArgumentException('Name already exists'); }
            }
            $updates = array();
            foreach (array_keys($fields) as $key) { $updates[] = "$key=?"; }
            $updates[] = 'updatedate=NOW()';
            $updates[] = 'updateby=?';
            $stmt = $pdo->prepare("UPDATE $table SET " . implode(',', $updates) . " WHERE id=?");
            $stmt->execute(array_merge(array_values($fields), array($operator, $row['id'])));
            $result = array('id' => (int)$row['id'], 'name' => $fields[$nameColumn]);
        } else {
            $targets = array();
            $lookup = $pdo->prepare("SELECT id,$nameColumn FROM $table WHERE BINARY $nameColumn=BINARY ? "
                                  . 'ORDER BY id LIMIT 2 FOR UPDATE');
            foreach ($ids as $name) {
                $lookup->execute(array($name));
                $rows = $lookup->fetchAll(PDO::FETCH_ASSOC);
                if (count($rows) !== 1) { throw new InvalidArgumentException('Stale or ambiguous selection'); }
                $targets[] = $rows[0];
            }
            usort($targets, static function($a, $b) { return $a['id'] <=> $b['id']; });
            $delete = $pdo->prepare("DELETE FROM $table WHERE id=? AND BINARY $nameColumn=BINARY ?");
            foreach ($targets as $row) {
                $delete->execute(array($row['id'], $row[$nameColumn]));
                if ($delete->rowCount() !== 1) { throw new RuntimeException('Delete was not exact'); }
            }
            $result = array('names' => $ids, 'count' => count($ids));
        }
        $generated = realm_proxy_render($pdo, $tables['proxy'], $tables['realm']);
        $staged = realm_proxy_stage_file($path, $generated);
        if (!rename($staged, $path)) { throw new RuntimeException('Cannot publish proxy configuration'); }
        $staged = null;
        $published = true;
        $commitAttempted = true;
        if (!$pdo->commit()) { throw new RuntimeException('Realm/proxy commit failed'); }
        $committed = true;
        return $result;
    } catch (Throwable $exception) {
        $rollbackFailed = false;
        $restoreFailed = false;
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            try { $pdo->rollBack(); }
            catch (Throwable $rollbackError) {
                $rollbackFailed = true;
                error_log('Realm/proxy rollback: ' . get_class($rollbackError));
            }
        }
        if ($published && !$committed) {
            try {
                $restore = realm_proxy_stage_file($path, $old);
                if (!rename($restore, $path)) { @unlink($restore); throw new RuntimeException('Restore rename failed'); }
            } catch (Throwable $restoreError) {
                $restoreFailed = true;
                error_log('Realm/proxy file restore failed: ' . get_class($restoreError));
            }
        }
        error_log('Realm/proxy mutation: ' . get_class($exception));
        if ($commitAttempted || $rollbackFailed || $restoreFailed) {
            throw new RealmProxyUncertainException('Realm/proxy state uncertain', 0, $exception);
        }
        throw $exception;
    } finally {
        if ($staged !== null) { @unlink($staged); }
        if ($lock !== null && $pdo instanceof PDO) {
            try {
                $release = $pdo->prepare('SELECT RELEASE_LOCK(?)');
                $release->execute(array($lock));
                if ((int)$release->fetchColumn() !== 1) { error_log('Realm/proxy lock release unconfirmed'); }
            } catch (Throwable $releaseError) { error_log('Realm/proxy lock release: ' . get_class($releaseError)); }
        }
        $pdo = null;
    }
}
