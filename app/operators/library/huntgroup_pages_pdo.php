<?php
/** R08: explicit PDO huntgroup pages; no implicit conversion of shared PEAR handles. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/huntgroup_pages_pdo.php') !== false) {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__, 2) . '/common/includes/pdo_connection.php';

function dalo_hunt_table($config) {
    $name = $config['CONFIG_DB_TBL_RADHG'] ?? null;
    if (!is_string($name) || !preg_match('/\A[A-Za-z_][A-Za-z0-9_]{0,63}\z/', $name)) {
        throw new InvalidArgumentException('Invalid Huntgroup table');
    }
    return '`' . $name . '`';
}

function dalo_hunt_query(PDO $pdo, $sql, $values = array()) {
    global $logDebugSQL;
    $stmt = $pdo->prepare($sql);
    foreach (array_values($values) as $i=>$value) {
        $stmt->bindValue($i+1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $logDebugSQL .= $sql . ";\n";
    return $stmt;
}

function dalo_hunt_id($item) {
    if (!is_string($item) || !preg_match('/\Ahuntgroup-([1-9][0-9]{0,9})\z/', $item, $match) ||
        (strlen($match[1])===10 && strcmp($match[1], '4294967295')>0)) {
        throw new InvalidArgumentException('Invalid Huntgroup item');
    }
    return $match[1]; // Preserve the canonical unsigned ID without 32-bit overflow.
}

function dalo_hunt_selection($input) {
    if (is_string($input)) { $input=array($input); }
    if (!is_array($input) || !$input) { throw new InvalidArgumentException('Missing Huntgroup selection'); }
    $ids=array();
    foreach ($input as $item) { $id=dalo_hunt_id($item); $ids[$id]=$id; }
    $ids=array_values($ids);
    usort($ids, function ($a,$b) { return strlen($a)<=>strlen($b) ?: strcmp($a,$b); });
    return $ids;
}

function dalo_hunt_fields($input) {
    $result=array();
    foreach (array('groupname','nasipaddress') as $name) {
        $value=$input[$name] ?? null;
        if (!is_string($value) || trim($value)==='' || strlen($value)>4096 ||
            strpos($value, "\0")!==false || !preg_match('//u', $value)) {
            throw new InvalidArgumentException('Invalid Huntgroup field');
        }
        $result[$name]=trim($value);
    }
    if (filter_var($result['nasipaddress'], FILTER_VALIDATE_IP)===false) {
        throw new InvalidArgumentException('Invalid IP address');
    }
    $port=$input['nasportid'] ?? '';
    if (!is_string($port) || (trim($port)!=='' && !preg_match('/\A[0-9]+\z/',trim($port))) || strlen($port)>4096) {
        throw new InvalidArgumentException('Invalid huntgroup port');
    }
    $port=ltrim(trim($port),'0');
    $result['nasportid']=$port==='' ? '0' : $port;
    return $result;
}

function dalo_hunt_validate_capacity(PDO $pdo, $table, $fields) {
    $rows=dalo_hunt_query($pdo, 'SELECT COLUMN_NAME, CHARACTER_MAXIMUM_LENGTH
                                  FROM INFORMATION_SCHEMA.COLUMNS
                                  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?
                                    AND COLUMN_NAME IN (\'groupname\',\'nasipaddress\',\'nasportid\')',
                           array(trim($table,'`')))->fetchAll(PDO::FETCH_NUM);
    $limits=array(); foreach ($rows as $row) { $limits[$row[0]]=(int)$row[1]; }
    foreach ($fields as $name=>$value) {
        $length=function_exists('mb_strlen') ? mb_strlen($value,'UTF-8') : strlen($value);
        if (!isset($limits[$name]) || $limits[$name]<=0 || $length>$limits[$name]) {
            throw new InvalidArgumentException('Huntgroup value exceeds configured schema capacity');
        }
    }
}

function dalo_hunt_read(PDO $pdo, $table, $id, $lock=false) {
    return dalo_hunt_query($pdo,"SELECT id, groupname, nasipaddress, nasportid FROM $table WHERE id=?" .
                            ($lock ? ' FOR UPDATE' : ''),array($id))->fetch(PDO::FETCH_ASSOC);
}

/** Own only this page's transaction; keep its advisory lock on the same handle. */
function dalo_hunt_mutate(PDO $pdo, $config, $operation) {
    if ($pdo->inTransaction()) { throw new LogicException('Huntgroup page requires its own transaction'); }
    $table=dalo_hunt_table($config);
    $schema=dalo_hunt_query($pdo,'SELECT DATABASE()')->fetchColumn();
    $name='dalo_hunt_' . substr(hash('sha256', $schema . ':' . $table),0,52);
    if ((int)dalo_hunt_query($pdo,'SELECT GET_LOCK(?, 10)',array($name))->fetchColumn()!==1) {
        throw new RuntimeException('Huntgroup is busy');
    }
    try {
        if (!$pdo->beginTransaction()) { throw new RuntimeException('Could not start Huntgroup transaction'); }
        try {
            // Acquire a metadata lock before checking the engine, without changing data.
            dalo_hunt_query($pdo,"SELECT id FROM $table WHERE id=0 FOR UPDATE")->fetchAll();
            $engine=dalo_hunt_query($pdo,'SELECT ENGINE FROM INFORMATION_SCHEMA.TABLES
                                          WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',
                                     array(trim($table,'`')))->fetchColumn();
            if (!is_string($engine) || strcasecmp($engine,'InnoDB')!==0) {
                throw new RuntimeException('Huntgroup mutation requires InnoDB');
            }
            $result=$operation($pdo,$table);
            if (!$pdo->commit()) { throw new RuntimeException('Could not commit Huntgroup transaction'); }
            return $result;
        } catch (Throwable $e) { if ($pdo->inTransaction()) { $pdo->rollBack(); } throw $e; }
    } finally {
        try { dalo_hunt_query($pdo,'SELECT RELEASE_LOCK(?)',array($name)); }
        catch (Throwable $ignored) { /* A nonpersistent connection releases it on teardown. */ }
    }
}

function dalo_hunt_save(PDO $pdo, $config, $fields, $id=null) {
    return dalo_hunt_mutate($pdo,$config,function ($pdo,$table) use ($fields,$id) {
        dalo_hunt_validate_capacity($pdo,$table,$fields);
        if ($id!==null && !dalo_hunt_read($pdo,$table,$id,true)) {
            throw new RuntimeException('Huntgroup item no longer exists');
        }
        $sql="SELECT id FROM $table WHERE nasipaddress=? AND nasportid=CAST(? AS DECIMAL(65,0))";
        $values=array($fields['nasipaddress'],$fields['nasportid']);
        if ($id!==null) { $sql.=' AND id<>?'; $values[]=$id; }
        if (dalo_hunt_query($pdo,$sql.' ORDER BY id FOR UPDATE',$values)->fetchColumn()!==false) {
            return false; // Preserve the global NAS/address-port numeric collision policy; exclude the edited row.
        }
        if ($id===null) {
            dalo_hunt_query($pdo,"INSERT INTO $table (groupname,nasipaddress,nasportid) VALUES (?,?,?)",
                             array($fields['groupname'],$fields['nasipaddress'],$fields['nasportid']));
            $id=$pdo->lastInsertId();
        } else {
            dalo_hunt_query($pdo,"UPDATE $table SET groupname=?,nasipaddress=?,nasportid=? WHERE id=?",
                             array($fields['groupname'],$fields['nasipaddress'],$fields['nasportid'],$id));
        }
        $row=dalo_hunt_read($pdo,$table,$id);
        if (!$row || $row['groupname']!==$fields['groupname'] || $row['nasipaddress']!==$fields['nasipaddress'] || (string)$row['nasportid']!==$fields['nasportid']) {
            throw new RuntimeException('Huntgroup stored value verification failed');
        }
        return 'huntgroup-' . $id;
    });
}

function dalo_hunt_delete(PDO $pdo, $config, $ids) {
    return dalo_hunt_mutate($pdo,$config,function ($pdo,$table) use ($ids) {
        foreach ($ids as $id) {
            if (!dalo_hunt_read($pdo,$table,$id,true)) { throw new RuntimeException('Stale Huntgroup selection'); }
        }
        $deleted=0;
        foreach ($ids as $id) {
            $count=dalo_hunt_query($pdo,"DELETE FROM $table WHERE id=?",array($id))->rowCount();
            if ($count!==1) { throw new RuntimeException('Huntgroup deletion conflict'); }
            $deleted+=$count;
        }
        return $deleted;
    });
}

/** Same catalog projection as get_huntgroups(), with page-visible failure propagation. */
function dalo_hunt_options(PDO $pdo, $config) {
    $table=dalo_hunt_table($config);
    $rows=dalo_hunt_query($pdo,"SELECT id,groupname,nasipaddress,nasportid FROM $table ORDER BY groupname ASC,nasipaddress ASC")->fetchAll(PDO::FETCH_NUM);
    $result=array();
    foreach ($rows as $row) { $result['huntgroup-' . $row[0]]=sprintf('%s:%s (%s)',$row[2],$row[3],$row[1]); }
    return $result;
}
