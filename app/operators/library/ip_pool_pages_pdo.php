<?php
/** R07: explicit PDO IP-pool pages; no implicit conversion of shared PEAR handles. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/ip_pool_pages_pdo.php') !== false) {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__, 2) . '/common/includes/pdo_connection.php';

function dalo_ippool_table($config) {
    $name = $config['CONFIG_DB_TBL_RADIPPOOL'] ?? null;
    if (!is_string($name) || !preg_match('/\A[A-Za-z_][A-Za-z0-9_]{0,63}\z/', $name)) {
        throw new InvalidArgumentException('Invalid IP pool table');
    }
    return '`' . $name . '`';
}

function dalo_ippool_query(PDO $pdo, $sql, $values = array()) {
    global $logDebugSQL;
    $stmt = $pdo->prepare($sql);
    foreach (array_values($values) as $i=>$value) {
        $stmt->bindValue($i+1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $logDebugSQL .= $sql . ";\n";
    return $stmt;
}

function dalo_ippool_id($item) {
    if (!is_string($item) || !preg_match('/\Aippool-([1-9][0-9]{0,9})\z/', $item, $match) ||
        (strlen($match[1])===10 && strcmp($match[1], '4294967295')>0)) {
        throw new InvalidArgumentException('Invalid IP pool item');
    }
    return $match[1]; // Preserve the canonical unsigned ID without 32-bit overflow.
}

function dalo_ippool_selection($input) {
    if (is_string($input)) { $input=array($input); }
    if (!is_array($input) || !$input) { throw new InvalidArgumentException('Missing IP pool selection'); }
    $ids=array();
    foreach ($input as $item) { $id=dalo_ippool_id($item); $ids[$id]=$id; }
    $ids=array_values($ids);
    usort($ids, function ($a,$b) { return strlen($a)<=>strlen($b) ?: strcmp($a,$b); });
    return $ids;
}

function dalo_ippool_fields($input) {
    $result=array();
    foreach (array('pool_name','framedipaddress') as $name) {
        $value=$input[$name] ?? null;
        if (!is_string($value) || trim($value)==='' || strlen($value)>4096 ||
            strpos($value, "\0")!==false || !preg_match('//u', $value)) {
            throw new InvalidArgumentException('Invalid IP pool field');
        }
        $result[$name]=trim($value);
    }
    if (filter_var($result['framedipaddress'], FILTER_VALIDATE_IP)===false) {
        throw new InvalidArgumentException('Invalid IP address');
    }
    return $result;
}

function dalo_ippool_validate_capacity(PDO $pdo, $table, $fields) {
    $rows=dalo_ippool_query($pdo, 'SELECT COLUMN_NAME, CHARACTER_MAXIMUM_LENGTH
                                  FROM INFORMATION_SCHEMA.COLUMNS
                                  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?
                                    AND COLUMN_NAME IN (\'pool_name\',\'framedipaddress\')',
                           array(trim($table,'`')))->fetchAll(PDO::FETCH_NUM);
    $limits=array(); foreach ($rows as $row) { $limits[$row[0]]=(int)$row[1]; }
    foreach ($fields as $name=>$value) {
        $length=function_exists('mb_strlen') ? mb_strlen($value,'UTF-8') : strlen($value);
        if (!isset($limits[$name]) || $limits[$name]<=0 || $length>$limits[$name]) {
            throw new InvalidArgumentException('IP pool value exceeds configured schema capacity');
        }
    }
}

function dalo_ippool_read(PDO $pdo, $table, $id, $lock=false) {
    return dalo_ippool_query($pdo,"SELECT id, pool_name, framedipaddress FROM $table WHERE id=?" .
                            ($lock ? ' FOR UPDATE' : ''),array($id))->fetch(PDO::FETCH_ASSOC);
}

/** Own only this page's transaction; keep its advisory lock on the same handle. */
function dalo_ippool_mutate(PDO $pdo, $config, $operation) {
    if ($pdo->inTransaction()) { throw new LogicException('IP pool page requires its own transaction'); }
    $table=dalo_ippool_table($config);
    $schema=dalo_ippool_query($pdo,'SELECT DATABASE()')->fetchColumn();
    $name='dalo_ippool_' . substr(hash('sha256', $schema . ':' . $table),0,52);
    if ((int)dalo_ippool_query($pdo,'SELECT GET_LOCK(?, 10)',array($name))->fetchColumn()!==1) {
        throw new RuntimeException('IP pool is busy');
    }
    try {
        if (!$pdo->beginTransaction()) { throw new RuntimeException('Could not start IP pool transaction'); }
        try {
            // Acquire a metadata lock before checking the engine, without changing data.
            dalo_ippool_query($pdo,"SELECT id FROM $table WHERE id=0 FOR UPDATE")->fetchAll();
            $engine=dalo_ippool_query($pdo,'SELECT ENGINE FROM INFORMATION_SCHEMA.TABLES
                                          WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',
                                     array(trim($table,'`')))->fetchColumn();
            if (!is_string($engine) || strcasecmp($engine,'InnoDB')!==0) {
                throw new RuntimeException('IP pool mutation requires InnoDB');
            }
            $result=$operation($pdo,$table);
            if (!$pdo->commit()) { throw new RuntimeException('Could not commit IP pool transaction'); }
            return $result;
        } catch (Throwable $e) { if ($pdo->inTransaction()) { $pdo->rollBack(); } throw $e; }
    } finally {
        try { dalo_ippool_query($pdo,'SELECT RELEASE_LOCK(?)',array($name)); }
        catch (Throwable $ignored) { /* A nonpersistent connection releases it on teardown. */ }
    }
}

function dalo_ippool_save(PDO $pdo, $config, $fields, $id=null) {
    return dalo_ippool_mutate($pdo,$config,function ($pdo,$table) use ($fields,$id) {
        dalo_ippool_validate_capacity($pdo,$table,$fields);
        if ($id!==null && !dalo_ippool_read($pdo,$table,$id,true)) {
            throw new RuntimeException('IP pool item no longer exists');
        }
        $sql="SELECT id FROM $table WHERE framedipaddress=?";
        $values=array($fields['framedipaddress']);
        if ($id!==null) { $sql.=' AND id<>?'; $values[]=$id; }
        if (dalo_ippool_query($pdo,$sql.' ORDER BY id FOR UPDATE',$values)->fetchColumn()!==false) {
            return false; // Keep the existing global-address collision policy.
        }
        if ($id===null) {
            dalo_ippool_query($pdo,"INSERT INTO $table (pool_name,framedipaddress) VALUES (?,?)",
                             array($fields['pool_name'],$fields['framedipaddress']));
            $id=$pdo->lastInsertId();
        } else {
            dalo_ippool_query($pdo,"UPDATE $table SET pool_name=?,framedipaddress=? WHERE id=?",
                             array($fields['pool_name'],$fields['framedipaddress'],$id));
        }
        $row=dalo_ippool_read($pdo,$table,$id);
        if (!$row || $row['pool_name']!==$fields['pool_name'] || $row['framedipaddress']!==$fields['framedipaddress']) {
            throw new RuntimeException('IP pool stored value verification failed');
        }
        return 'ippool-' . $id;
    });
}

function dalo_ippool_delete(PDO $pdo, $config, $ids) {
    return dalo_ippool_mutate($pdo,$config,function ($pdo,$table) use ($ids) {
        foreach ($ids as $id) {
            if (!dalo_ippool_read($pdo,$table,$id,true)) { throw new RuntimeException('Stale IP pool selection'); }
        }
        $deleted=0;
        foreach ($ids as $id) {
            $count=dalo_ippool_query($pdo,"DELETE FROM $table WHERE id=?",array($id))->rowCount();
            if ($count!==1) { throw new RuntimeException('IP pool deletion conflict'); }
            $deleted+=$count;
        }
        return $deleted;
    });
}

/** Same catalog projection as get_ippools(), with page-visible failure propagation. */
function dalo_ippool_options(PDO $pdo, $config) {
    $table=dalo_ippool_table($config);
    $rows=dalo_ippool_query($pdo,"SELECT id,pool_name,framedipaddress FROM $table ORDER BY pool_name ASC,framedipaddress ASC")->fetchAll(PDO::FETCH_NUM);
    $result=array();
    foreach ($rows as $row) { $result['ippool-' . $row[0]]=$row[1] . ' - ' . $row[2]; }
    return $result;
}
