<?php
/* daloRADIUS — GPL-2.0-or-later. Atomic Chilli free-signup workflows. */
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(404); exit;
}
require_once __DIR__ . '/database.php';

function dalo_chilli_signup_text($value, $limit, $required = false) {
    if (!is_string($value) || preg_match('//u', $value) !== 1 ||
        strpos($value, "\0") !== false || preg_match_all('/./us', $value) > $limit ||
        ($required && trim($value) === '')) {
        throw new InvalidArgumentException('Invalid signup field');
    }
    return $value;
}

function dalo_chilli_signup_table($config, $key, $default) {
    $value = $config[$key] ?? $default;
    if (!is_string($value) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $value)) {
        throw new InvalidArgumentException('Invalid signup table');
    }
    return '`' . $value . '`';
}

function dalo_chilli_signup_length($value, $limit) {
    if ((!is_int($value) && !is_string($value)) || !ctype_digit((string) $value) ||
        (int) $value < 1 || (int) $value > $limit) {
        throw new InvalidArgumentException('Invalid generation length');
    }
    return (int) $value;
}

function dalo_chilli_signup_random($length, $alphabet) {
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return $result;
}

/** Own the only write connection and transaction; return credentials after commit. */
function dalo_chilli_free_signup($config, $input) {
    $first = dalo_chilli_signup_text($input['firstname'] ?? '', 200, true);
    $last = dalo_chilli_signup_text($input['lastname'] ?? '', 200, true);
    $email = dalo_chilli_signup_text($input['email'] ?? '', 200);
    $prefix = dalo_chilli_signup_text($config['CONFIG_USERNAME_PREFIX'] ?? 'guest', 64);
    $length = dalo_chilli_signup_length($config['CONFIG_USERNAME_LENGTH'] ?? 4, 64);
    $passwordLength = dalo_chilli_signup_length($config['CONFIG_PASSWORD_LENGTH'] ?? 4, 253);
    dalo_chilli_signup_text($prefix . str_repeat('x', $length), 64, true);
    $alphabet = $config['CONFIG_USER_ALLOWEDRANDOMCHARS'] ?? 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
    if (!is_string($alphabet) || !preg_match('/^[\x21-\x7e]{1,256}$/D', $alphabet)) {
        throw new InvalidArgumentException('Invalid generation alphabet');
    }
    $group = dalo_chilli_signup_text($config['CONFIG_GROUP_NAME'] ?? '', 64);
    $tables = array(
        'check' => dalo_chilli_signup_table($config, 'CONFIG_DB_TBL_RADCHECK', 'radcheck'),
        'info' => dalo_chilli_signup_table($config, 'CONFIG_DB_TBL_DALOUSERINFO', 'userinfo'),
    );
    if ($group !== '') {
        $tables['mapping'] = dalo_chilli_signup_table($config, 'CONFIG_DB_TBL_RADUSERGROUP', 'radusergroup');
        $tables['groupcheck'] = dalo_chilli_signup_table($config, 'CONFIG_DB_TBL_RADGROUPCHECK', 'radgroupcheck');
        $tables['groupreply'] = dalo_chilli_signup_table($config, 'CONFIG_DB_TBL_RADGROUPREPLY', 'radgroupreply');
        $priority = $config['CONFIG_GROUP_PRIORITY'] ?? 0;
        if ((!is_int($priority) && !is_string($priority)) ||
            !preg_match('/^-?[0-9]+$/D', (string) $priority) ||
            filter_var($priority, FILTER_VALIDATE_INT) === false ||
            (int) $priority < -2147483648 || (int) $priority > 2147483647) {
            throw new InvalidArgumentException('Invalid signup priority');
        }
        $priority = (int) $priority;
    }
    if (count(array_unique($tables)) !== count($tables)) {
        throw new InvalidArgumentException('Ambiguous signup tables');
    }
    $pdo = null; $locked = false;
    try {
        $pdo = dalo_chilli_pdo_open($config);
        // Serializes cooperating free-signup requests, including case-insensitive collisions.
        $lockName = 'dalo-chilli-signup-' . substr(hash('sha256', $pdo->query('SELECT DATABASE()')->fetchColumn()), 0, 40);
        $lock = $pdo->prepare('SELECT GET_LOCK(?, 10)'); $lock->execute(array($lockName));
        if ((int) $lock->fetchColumn() !== 1) { throw new RuntimeException('Signup unavailable'); }
        $locked = true;
        $engine = $pdo->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
        foreach ($tables as $table) {
            $engine->execute(array(trim($table, '`')));
            if (strcasecmp((string) $engine->fetchColumn(), 'InnoDB') !== 0) {
                throw new RuntimeException('Signup requires transactional tables');
            }
        }
        if (!$pdo->beginTransaction()) { throw new RuntimeException('Signup transaction unavailable'); }
        if ($group !== '') {
            $exists = false;
            foreach (array('groupcheck', 'groupreply') as $key) {
                $s = $pdo->prepare('SELECT groupname FROM ' . $tables[$key] . ' WHERE groupname=? FOR UPDATE');
                $s->execute(array($group));
                if ($s->fetchColumn() !== false) { $exists = true; }
                $s->closeCursor();
            }
            if (!$exists) { throw new RuntimeException('Signup group unavailable'); }
        }
        $username = null;
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $candidate = $prefix . dalo_chilli_signup_random($length, $alphabet);
            $collision = false;
            foreach (array_intersect_key($tables, array_flip(array('check', 'info', 'mapping'))) as $table) {
                $s = $pdo->prepare('SELECT username FROM ' . $table . ' WHERE username=? LIMIT 1 FOR UPDATE');
                $s->execute(array($candidate));
                if ($s->fetchColumn() !== false) { $collision = true; }
                $s->closeCursor();
            }
            if (!$collision) { $username = $candidate; break; }
        }
        if ($username === null) { throw new RuntimeException('Signup identity unavailable'); }
        $password = dalo_chilli_signup_random($passwordLength, $alphabet);
        $s = $pdo->prepare('INSERT INTO ' . $tables['check'] . ' (username,attribute,op,value) VALUES (?,?,?,?)');
        $s->execute(array($username, 'User-Password', '==', $password));
        $s = $pdo->prepare('INSERT INTO ' . $tables['info'] . ' (username,firstname,lastname,email) VALUES (?,?,?,?)');
        $s->execute(array($username, $first, $last, $email));
        if ($group !== '') {
            $s = $pdo->prepare('INSERT INTO ' . $tables['mapping'] . ' (username,groupname,priority) VALUES (?,?,?)');
            $s->execute(array($username, $group, $priority));
        }
        if (!$pdo->commit()) { throw new RuntimeException('Signup commit unavailable'); }
        return array('username' => $username, 'password' => $password);
    } catch (Throwable $error) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            try { $pdo->rollBack(); } catch (Throwable $ignored) { /* Keep uncertain failures generic. */ }
        }
        // Do not retain a driver exception, SQL, password or generated username.
        throw new RuntimeException('Signup could not be completed');
    } finally {
        if ($pdo instanceof PDO) {
            // A post-commit release problem must not turn a committed signup into failure.
            if ($locked) {
                try { $s = $pdo->prepare('SELECT RELEASE_LOCK(?)'); $s->execute(array($lockName)); }
                catch (Throwable $ignored) { /* Nonpersistent handle releases on disposal. */ }
            }
            dalo_chilli_database_close($pdo);
        }
    }
}

function dalo_chilli_signup_csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars($_SESSION['chilli_signup_csrf'], ENT_QUOTES, 'UTF-8') . '">';
}

/** Session-bound gate shared by the three real pages; no credentials on failure. */
function dalo_chilli_signup_request($config) {
    if (!isset($_SESSION['chilli_signup_csrf']) || !is_string($_SESSION['chilli_signup_csrf'])) {
        $_SESSION['chilli_signup_csrf'] = bin2hex(random_bytes(32));
    }
    $result = array('status' => 'firstload', 'username' => '', 'password' => '', 'firstname' => '');
    if (!isset($_POST['submit'])) { return $result; }
    $result['status'] = 'captchaFailure';
    $csrf = $_POST['csrf_token'] ?? null; $captcha = $_POST['formKey'] ?? null;
    $expected = $_SESSION['key'] ?? null;
    if (!is_string($_POST['submit']) || !is_string($csrf) || !is_string($captcha) ||
        !is_string($expected) || strlen($expected) < 5 ||
        !hash_equals($_SESSION['chilli_signup_csrf'], $csrf) ||
        !hash_equals(substr($expected, 0, 5), $captcha)) { return $result; }
    unset($_SESSION['key']);
    $_SESSION['chilli_signup_csrf'] = bin2hex(random_bytes(32));
    try {
        dalo_chilli_signup_text($_POST['firstname'] ?? '', 200, true);
        dalo_chilli_signup_text($_POST['lastname'] ?? '', 200, true);
        dalo_chilli_signup_text($_POST['email'] ?? '', 200);
    } catch (Throwable $error) { $result['status'] = 'fieldsFailure'; return $result; }
    try {
        $created = dalo_chilli_free_signup($config, $_POST);
        $result = array_merge($result, $created);
        $result['firstname'] = $_POST['firstname'];
        $result['status'] = 'success';
    } catch (Throwable $error) { $result['status'] = 'databaseFailure'; }
    return $result;
}
