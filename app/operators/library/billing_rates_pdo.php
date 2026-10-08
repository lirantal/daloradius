<?php
/** R19: rate catalogue mutations and strict page-local billing report reads. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/billing_rates_pdo.php') !== false) {
    http_response_code(404); exit;
}
require_once __DIR__ . '/catalog_reads_pdo.php';
function dalo_rate_text($value, $required = false) {
    if (!is_string($value)) { throw new InvalidArgumentException('Invalid rate input'); }
    $value = trim($value);
    if (($required && $value === '') || (function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value)) > 128) {
        throw new InvalidArgumentException('Invalid rate input length');
    }
    return $value;
}
/** Stock rateCost is INT, not currency DECIMAL. Preserve ordinary decimal truncation. */
function dalo_rate_integer($value) {
    if (!is_string($value) || !preg_match('/\A[0-9]+(?:\.[0-9]+)?\z/', $value)) {
        throw new InvalidArgumentException('Invalid rate number');
    }
    $whole = ltrim(explode('.', $value)[0], '0');
    if ($whole === '') { return 0; }
    if (strlen($whole) > 10 || (int)$whole > 2147483647) { throw new InvalidArgumentException('Rate number out of range'); }
    return (int)$whole;
}
function dalo_rate_values($post, $create) {
    $values = array();
    foreach (array('ratecost','ratetypenum','ratetypetime') as $key) {
        if (isset($post[$key]) && !is_string($post[$key])) { throw new InvalidArgumentException('Invalid rate control'); }
    }
    $cost = trim($post['ratecost'] ?? '');
    if ($cost !== '') { $cost = dalo_rate_integer($cost); if ($cost > 0) { $values['rateCost'] = $cost; } }
    $number = trim($post['ratetypenum'] ?? '');
    $number = $number === '' ? 0 : dalo_rate_integer($number);
    $unit = trim($post['ratetypetime'] ?? '');
    if ($unit !== '' && !in_array($unit, array('second','minute','hour','day','week','month'), true)) {
        throw new InvalidArgumentException('Invalid rate unit');
    }
    if ($create && (!isset($values['rateCost']) || $number < 1 || $unit === '')) {
        throw new InvalidArgumentException('Missing required rate fields');
    }
    if ($unit !== '') { $values['rateType'] = max(1, $number) . '/' . $unit; }
    return $values;
}
function dalo_rate_names($input) {
    $values = is_array($input) ? $input : array($input);
    $limit = (int)ini_get('max_input_vars');
    if (!$values || count($values) > 500 || ($limit > 0 && count($_POST, COUNT_RECURSIVE) >= $limit)) {
        throw new InvalidArgumentException('Invalid rate selection');
    }
    $names = array();
    foreach ($values as $value) { $name = dalo_rate_text($value, true); if (!in_array($name, $names, true)) { $names[] = $name; } }
    sort($names, SORT_STRING); return $names;
}
function dalo_rate_read(PDO $pdo, $config, $name) {
    $table = dalo_read_table($pdo, $config, 'CONFIG_DB_TBL_DALOBILLINGRATES');
    $rows = dalo_catalog_read_rows($pdo, "SELECT id,rateName,rateType,rateCost,creationdate,creationby,updatedate,updateby FROM $table WHERE rateName=:name", array(':name'=>$name));
    if (count($rows) !== 1 || (string)$rows[0][1] !== $name) { throw new DomainException('Missing or ambiguous rate'); }
    return $rows[0];
}
function dalo_rate_failure(Throwable $error) {
    global $failureMsg, $logAction;
    $failureMsg = 'Unable to read billing rate data';
    $logAction = ($logAction ?? '') . 'Billing rate read failed [' . get_class($error) . '] on page: ';
}
/** Cooperating writers serialize identities; uncoordinated external writers remain outside this guarantee. */
function dalo_rate_mutate(PDO $pdo, $config, $mode, $input, $post, $operator) {
    if (!in_array($mode, array('new','edit','del'), true) || $pdo->inTransaction()) {
        throw new RuntimeException('Rate transaction unavailable');
    }
    if ($mode !== 'del' && !is_string($input)) { throw new InvalidArgumentException('Scalar rate name required'); }
    $names = dalo_rate_names($input);
    if ($mode !== 'del' && count($names) !== 1) { throw new InvalidArgumentException('One rate required'); }
    $values = $mode === 'del' ? array() : dalo_rate_values($post, $mode === 'new');
    $operator = dalo_rate_text($operator, true);
    $table = dalo_read_table($pdo, $config, 'CONFIG_DB_TBL_DALOBILLINGRATES');
    $lockName = 'dalo-rate:' . substr(hash('sha256', $pdo->query('SELECT DATABASE()')->fetchColumn() . '\0' . $config['CONFIG_DB_TBL_DALOBILLINGRATES']), 0, 40);
    $locked = false; $owns = false;
    try {
        $lock = $pdo->prepare('SELECT GET_LOCK(?,10)'); $lock->execute(array($lockName));
        $locked = (int)$lock->fetchColumn() === 1; $lock->closeCursor();
        if (!$locked) { throw new RuntimeException('Rate lock unavailable'); }
        if (!$pdo->beginTransaction()) { throw new RuntimeException('Rate transaction unavailable'); }
        $owns = true;
        $pdo->query("SELECT id FROM $table WHERE 1=0")->closeCursor();
        $engine = $pdo->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
        $engine->execute(array($config['CONFIG_DB_TBL_DALOBILLINGRATES']));
        if (strtoupper((string)$engine->fetchColumn()) !== 'INNODB') { throw new RuntimeException('Transactional rate table required'); }
        $engine->closeCursor();
        $select = $pdo->prepare("SELECT id,rateName FROM $table WHERE rateName=? FOR UPDATE"); $ids = array();
        foreach ($names as $name) {
            $select->execute(array($name)); $rows = $select->fetchAll(PDO::FETCH_NUM); $select->closeCursor();
            if ($mode === 'new') { if ($rows) { throw new DomainException('Rate already exists'); } }
            else {
                if (count($rows) !== 1 || (string)$rows[0][1] !== $name) { throw new DomainException('Missing or ambiguous rate'); }
                $ids[(int)$rows[0][0]] = $name;
            }
        }
        if ($mode === 'del') {
            $stmt = $pdo->prepare("DELETE FROM $table WHERE id=?"); $result = 0;
            foreach ($ids as $id=>$name) { $stmt->execute(array($id)); $result += $stmt->rowCount(); }
        } else {
            $now = date('Y-m-d H:i:s');
            if ($mode === 'new') {
                $stmt = $pdo->prepare("INSERT INTO $table (rateName,rateType,rateCost,creationdate,creationby,updatedate,updateby) VALUES (?,?,?,?,?,NULL,NULL)");
                $stmt->execute(array($names[0],$values['rateType'],$values['rateCost'],$now,$operator));
                $result = (int)$pdo->lastInsertId();
                if ($result < 1) { throw new RuntimeException('Missing rate insert ID'); }
            } else {
                $result = array_key_first($ids); $set = array('updatedate=?','updateby=?'); $params = array($now,$operator);
                foreach ($values as $column=>$value) { $set[] = "$column=?"; $params[] = $value; }
                $params[] = $result;
                $stmt = $pdo->prepare("UPDATE $table SET " . implode(',', $set) . ' WHERE id=?'); $stmt->execute($params);
            }
            $stored = dalo_rate_read($pdo, $config, $names[0]);
            foreach (array('rateType'=>2,'rateCost'=>3) as $column=>$index) {
                if (isset($values[$column]) && (string)$stored[$index] !== (string)$values[$column]) { throw new RuntimeException('Rate storage mismatch'); }
            }
            if ((string)$stored[$mode === 'new' ? 5 : 7] !== $operator) { throw new RuntimeException('Rate metadata storage mismatch'); }
        }
        if (!$pdo->commit()) { throw new RuntimeException('Rate commit failed'); }
        return $result;
    } catch (Throwable $error) {
        if ($owns && $pdo->inTransaction()) { $pdo->rollBack(); }
        throw $error;
    } finally {
        if ($locked) {
            try { $release=$pdo->prepare('SELECT RELEASE_LOCK(?)'); $release->execute(array($lockName)); $release->closeCursor(); }
            catch (Throwable $ignored) { /* Disposed nonpersistent handle releases its lock. */ }
        }
    }
}
/** Retain the historical month multiplier; changing billing policy is not a driver migration. */
function dalo_rate_divisor($type) {
    if (!is_string($type) || !preg_match('/\A([1-9][0-9]*)\/(second|minute|hour|day|week|month)\z/', $type, $m)) {
        throw new DomainException('Invalid stored rate type');
    }
    $units=array('second'=>1,'minute'=>60,'hour'=>3600,'day'=>86400,'week'=>604800,'month'=>187488000);
    $number=dalo_rate_integer($m[1]); return $number * $units[$m[2]];
}
function dalo_billing_date($input, $key, $default) {
    $value = $input[$key] ?? $default;
    if (!is_string($value)) { throw new InvalidArgumentException('Invalid billing date input'); }
    if (!preg_match('/\A([0-9]{4})-([0-9]{2})-([0-9]{2})\z/', $value, $m) || !checkdate((int)$m[2],(int)$m[3],(int)$m[1])) { return $default; }
    return $value;
}
/** Escape LIKE metacharacters while retaining the historical substring search. */
function dalo_billing_like($value) {
    return '%' . str_replace(array('\\','%','_'), array('\\\\','\\%','\\_'), $value) . '%';
}
