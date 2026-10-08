<?php
/* UNIT-017: strict two-pass, transactional restore of UI-generated SQL backups. */

/** Only configured tables offered by the backup creation page are restorable. */
function dalo_backup_restorable_tables($configValues) {
    $keys = array(
        'RADCHECK', 'RADREPLY', 'RADGROUPREPLY', 'RADGROUPCHECK', 'RADUSERGROUP',
        'RADACCT', 'RADNAS', 'RADHG', 'RADPOSTAUTH', 'RADIPPOOL',
        'DALOUSERINFO', 'DALODICTIONARY', 'DALOREALMS', 'DALOPROXYS',
        'DALOBILLINGMERCHANT', 'DALOBILLINGPAYPAL', 'DALOBILLINGPLANS',
        'DALOBILLINGRATES', 'DALOBILLINGHISTORY', 'DALOBATCHHISTORY',
        'DALOBILLINGPLANSPROFILES', 'DALOUSERBILLINFO',
        'DALOBILLINGINVOICE', 'DALOBILLINGINVOICEITEMS',
        'DALOBILLINGINVOICESTATUS', 'DALOBILLINGINVOICETYPE',
        'DALOPAYMENTTYPES', 'DALOPAYMENTS', 'DALOOPERATORS',
        'DALOOPERATORS_ACL', 'DALOOPERATORS_ACL_FILES',
        'DALOHOTSPOTS', 'DALONODE',
    );
    $tables = array();
    foreach ($keys as $key) {
        $key = 'CONFIG_DB_TBL_' . $key;
        $value = $configValues[$key] ?? null;
        if (is_string($value) && preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,63}$/D', $value)) {
            $tables[$value] = true;
        }
    }
    return $tables;
}

/** Scan the exact INSERT dialect emitted by the legacy and PDO UI writers. */
class DaloBackupSqlReader {
    private $handle;
    private $chunk = '';
    private $offset = 0;
    private $hash;
    const MAX_VALUE = 16777216;

    public function __construct($handle) {
        $this->handle = $handle;
        $this->hash = hash_init('sha256');
    }

    private function peek() {
        if ($this->offset >= strlen($this->chunk)) {
            $this->chunk = fread($this->handle, 8192);
            if ($this->chunk === false) {
                throw new RuntimeException('Could not read backup');
            }
            $this->offset = 0;
            if ($this->chunk === '') {
                return null;
            }
            hash_update($this->hash, $this->chunk);
        }
        return $this->chunk[$this->offset];
    }

    private function take() {
        $character = $this->peek();
        if ($character !== null) {
            $this->offset++;
        }
        return $character;
    }

    private function whitespace() {
        while (($c = $this->peek()) !== null &&
               ($c === ' ' || $c === "\t" || $c === "\r" || $c === "\n")) {
            $this->take();
        }
    }

    private function expect($character) {
        $this->whitespace();
        if ($this->take() !== $character) {
            throw new RuntimeException('Invalid backup SQL syntax');
        }
    }

    private function keyword($word) {
        $this->whitespace();
        foreach (str_split($word) as $letter) {
            if (strtoupper((string) $this->take()) !== $letter) {
                throw new RuntimeException('Invalid backup SQL keyword');
            }
        }
        $next = $this->peek();
        if ($next !== null && (ctype_alnum($next) || $next === '_')) {
            throw new RuntimeException('Invalid backup SQL keyword');
        }
    }

    private function identifier() {
        $this->expect('`');
        $name = '';
        while (($c = $this->take()) !== '`') {
            if ($c === null || strlen($name) >= 64 ||
                !(ctype_alnum($c) || $c === '_')) {
                throw new RuntimeException('Invalid backup identifier');
            }
            $name .= $c;
        }
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,63}$/D', $name)) {
            throw new RuntimeException('Invalid backup identifier');
        }
        return $name;
    }

    private function quoted() {
        $this->expect("'");
        $result = '';
        $escapes = array('0' => "\0", 'b' => "\x08", 'n' => "\n",
                         'r' => "\r", 't' => "\t", 'Z' => "\x1a",
                         "'" => "'", '"' => '"', '\\' => '\\');
        while (true) {
            $c = $this->take();
            if ($c === null) {
                throw new RuntimeException('Unterminated backup string');
            }
            if ($c === "'") {
                if ($this->peek() !== "'") {
                    return array($result, false);
                }
                $this->take();
            } elseif ($c === '\\') {
                $escaped = $this->take();
                if ($escaped === null || !array_key_exists($escaped, $escapes)) {
                    throw new RuntimeException('Invalid backup escape');
                }
                $c = $escapes[$escaped];
            }
            $result .= $c;
            if (strlen($result) > self::MAX_VALUE) {
                throw new RuntimeException('Backup value too large');
            }
        }
    }

    private function value() {
        $this->whitespace();
        $first = $this->peek();
        if ($first === "'") {
            return $this->quoted();
        }
        if ($first === '0') {
            $this->take();
            $x = $this->take();
            if ($x !== 'x' && $x !== 'X') {
                throw new RuntimeException('Invalid backup binary value');
            }
            $hex = '';
            while (($c = $this->peek()) !== null && ctype_xdigit($c)) {
                $hex .= $this->take();
                if (strlen($hex) > 2 * self::MAX_VALUE) {
                    throw new RuntimeException('Backup value too large');
                }
            }
            if ($hex === '' || strlen($hex) % 2 !== 0) {
                throw new RuntimeException('Invalid backup binary value');
            }
            return array(hex2bin($hex), true);
        }
        $this->keyword('NULL');
        return array(null, false);
    }

    /** Callbacks receive (table, columns) and (table, values); return content hash. */
    public function parse($onTable, $onRow) {
        $found = false;
        while (true) {
            $this->whitespace();
            if ($this->peek() === null) {
                if (!$found) {
                    throw new RuntimeException('Empty backup SQL');
                }
                return hash_final($this->hash);
            }
            $found = true;
            $this->keyword('INSERT');
            $this->keyword('INTO');
            $table = $this->identifier();
            $this->expect('(');
            $columns = array();
            while (true) {
                $columns[] = $this->identifier();
                $this->whitespace();
                if ($this->peek() !== ',') {
                    break;
                }
                $this->take();
            }
            $this->expect(')');
            $this->keyword('VALUES');
            $onTable($table, $columns);
            while (true) {
                $this->expect('(');
                $values = array();
                while (true) {
                    $values[] = $this->value();
                    $this->whitespace();
                    if ($this->peek() !== ',') {
                        break;
                    }
                    $this->take();
                }
                $this->expect(')');
                if (count($values) !== count($columns)) {
                    throw new RuntimeException('Backup row has wrong column count');
                }
                $onRow($table, $values);
                $this->whitespace();
                if ($this->peek() !== ',') {
                    break;
                }
                $this->take();
            }
            $this->expect(';');
        }
    }
}

/** Preflight the WHOLE file/schema; change all named tables or none. */
function dalo_restore_backup(PDO $pdo, $file, $configValues) {
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql' ||
        !is_string($file) || !preg_match('/^backup-[0-9]{8}-[0-9]{6}(?:-[a-f0-9]{12})?\.sql$/D', basename($file)) ||
        is_link($file) || !is_file($file)) {
        throw new InvalidArgumentException('Invalid backup file');
    }
    $allowed = dalo_backup_restorable_tables($configValues);
    $fh = fopen($file, 'rb');
    if ($fh === false || !flock($fh, LOCK_SH)) {
        if (is_resource($fh)) fclose($fh);
        throw new RuntimeException('Could not lock backup file');
    }
    try {
        $plan = array();
        $first = new DaloBackupSqlReader($fh);
        $digest = $first->parse(function($table, $columns) use ($pdo, $allowed, &$plan) {
            if (!isset($allowed[$table]) || isset($plan[$table])) {
                throw new RuntimeException('Unknown or duplicate backup table');
            }
            $engine = $pdo->prepare('SELECT ENGINE FROM information_schema.TABLES '
                                    . 'WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:table');
            $engine->execute(array(':table' => $table));
            $type = $engine->fetchColumn();
            $engine->closeCursor();
            if (strtoupper((string) $type) !== 'INNODB') {
                throw new RuntimeException('Backup restore requires InnoDB tables');
            }
            $schema = $pdo->query('SELECT * FROM `' . $table . '` LIMIT 0');
            $current = array();
            for ($i = 0; $i < $schema->columnCount(); $i++) {
                $meta = $schema->getColumnMeta($i);
                $current[] = $meta['name'] ?? null;
            }
            $schema->closeCursor();
            if (!$current || $columns !== $current) {
                throw new RuntimeException('Backup schema differs from database');
            }
            $plan[$table] = array('columns' => $columns, 'rows' => 0);
        }, function($table, $values) use (&$plan) {
            $plan[$table]['rows']++;
        });
        $secondPlan = array();
        $prepared = null;
        if (!rewind($fh)) {
            throw new RuntimeException('Could not rewind backup');
        }
        if (!$pdo->beginTransaction()) {
            throw new RuntimeException('Could not start backup restore transaction');
        }
        $second = new DaloBackupSqlReader($fh);
        $secondDigest = $second->parse(function($table, $columns) use ($pdo, $plan, &$secondPlan, &$prepared) {
            if (!isset($plan[$table]) || isset($secondPlan[$table]) ||
                $columns !== $plan[$table]['columns']) {
                throw new RuntimeException('Backup changed before restore');
            }
            $secondPlan[$table] = array('columns' => $columns, 'rows' => 0);
            $quoted = '`' . $table . '`';
            $pdo->exec('DELETE FROM ' . $quoted);
            $names = array_map(function($column) { return '`' . $column . '`'; }, $columns);
            $params = array_fill(0, count($columns), '?');
            $prepared = $pdo->prepare('INSERT INTO ' . $quoted . ' (' . implode(',', $names)
                                       . ') VALUES (' . implode(',', $params) . ')');
        }, function($table, $values) use (&$prepared, &$secondPlan) {
            foreach ($values as $index => $value) {
                if ($value[0] === null) {
                    $prepared->bindValue($index + 1, null, PDO::PARAM_NULL);
                } else {
                    $prepared->bindValue($index + 1, $value[0],
                                         $value[1] ? PDO::PARAM_LOB : PDO::PARAM_STR);
                }
            }
            $prepared->execute();
            $secondPlan[$table]['rows']++;
        });
        if ($secondDigest !== $digest || $secondPlan !== $plan) {
            throw new RuntimeException('Backup changed during restore');
        }
        if (!$pdo->commit()) {
            throw new RuntimeException('Could not commit backup restore');
        }
        return array_keys($plan);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    } finally {
        flock($fh, LOCK_UN);
        fclose($fh);
    }
}
