<?php
/** R09: explicit hotspot pages and aggregate readers; PEAR coexistence unchanged. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/hotspot_pages_pdo.php') !== false) {
    http_response_code(404); exit;
}
require_once dirname(__DIR__,2) . '/common/includes/pdo_connection.php';
function dalo_hotspot_table($config,$key='CONFIG_DB_TBL_DALOHOTSPOTS') {
    if (!in_array($key,array('CONFIG_DB_TBL_DALOHOTSPOTS','CONFIG_DB_TBL_RADACCT'),true)) {
        throw new InvalidArgumentException('Invalid hotspot source');
    }
    $name=$config[$key] ?? null;
    if (!is_string($name) || !preg_match('/\A[A-Za-z_][A-Za-z0-9_]{0,63}\z/',$name)) {
        throw new InvalidArgumentException('Invalid hotspot table');
    }
    return '`' . $name . '`';
}
function dalo_hotspot_query(PDO $pdo,$sql,$values=array()) {
    global $logDebugSQL;
    $stmt=$pdo->prepare($sql);
    foreach (array_values($values) as $i=>$value) {
        $stmt->bindValue($i+1,$value,is_int($value) ? PDO::PARAM_INT : ($value===null ? PDO::PARAM_NULL : PDO::PARAM_STR));
    }
    $stmt->execute(); $logDebugSQL=($logDebugSQL ?? '') . $sql . ";\n";return $stmt;
}
function dalo_hotspot_name($value) {
    if (!is_string($value) || trim($value)==='' || strlen($value)>4096 || strpos($value,"\0")!==false || !preg_match('//u',$value)) {
        throw new InvalidArgumentException('Invalid hotspot name');
    }
    return trim($value);
}
function dalo_hotspot_map() {
    return array('name'=>'name','mac'=>'macaddress','geocode'=>'geocode','owner'=>'ownername','email_owner'=>'emailowner',
                 'manager'=>'managername','email_manager'=>'emailmanager','address'=>'address','company'=>'company',
                 'phone1'=>'phone1','phone2'=>'phone2','type'=>'hotspot_type','companywebsite'=>'companywebsite',
                 'companyemail'=>'companyemail','companycontact'=>'companycontact','companyphone'=>'companyphone');
}
function dalo_hotspot_fields($input) {
    $fields=array();
    foreach (dalo_hotspot_map() as $column=>$control) {
        $v=$input[$control] ?? '';
        if (!is_string($v) || strlen($v)>4096 || strpos($v,"\0")!==false || !preg_match('//u',$v)) {
            throw new InvalidArgumentException('Invalid hotspot field');
        }
        $v=trim($v);
        if (in_array($column,array('email_owner','email_manager','companyemail'),true) && $v!=='' && !filter_var($v,FILTER_VALIDATE_EMAIL)) { $v=''; }
        $fields[$column]=$v;
    }
    $fields['name']=dalo_hotspot_name($fields['name']);
    if (!preg_match('/\A(?:[A-Fa-f0-9]{12}|[A-Fa-f0-9]{2}([-:])(?:[A-Fa-f0-9]{2}\1){4}[A-Fa-f0-9]{2})\z/',$fields['mac']) &&
        filter_var($fields['mac'],FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)===false) {
        throw new InvalidArgumentException('Invalid hotspot MAC/IP');
    }
    return $fields;
}
function dalo_hotspot_capacity(PDO $pdo,$table,$fields) {
    $rows=dalo_hotspot_query($pdo,'SELECT COLUMN_NAME,CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',array(trim($table,'`')))->fetchAll(PDO::FETCH_NUM);
    $limits=array();foreach ($rows as $row) { $limits[$row[0]]=$row[1]; }
    foreach ($fields as $key=>$value) {
        $len=function_exists('mb_strlen') ? mb_strlen($value,'UTF-8') : strlen($value);
        if (!isset($limits[$key]) || $len>(int)$limits[$key]) { throw new InvalidArgumentException('Hotspot field exceeds schema capacity'); }
    }
}
function dalo_hotspot_read(PDO $pdo,$table,$name,$lock=false) {
    $rows=dalo_hotspot_query($pdo,"SELECT * FROM $table WHERE name=? ORDER BY id" . ($lock ? ' FOR UPDATE' : ''),array($name))->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) { return false; }
    if (count($rows)!==1 || $rows[0]['name']!==$name) { throw new RuntimeException('Ambiguous hotspot identity'); }
    return $rows[0];
}
/** Own only this page's transaction; keep its advisory lock on the same handle. */
function dalo_hotspot_mutate(PDO $pdo, $config, $operation) {
    if ($pdo->inTransaction()) { throw new LogicException('Hotspot page requires its own transaction'); }
    $table=dalo_hotspot_table($config);
    $schema=dalo_hotspot_query($pdo,'SELECT DATABASE()')->fetchColumn();
    $name='dalo_hotspot_' . substr(hash('sha256', $schema . ':' . $table),0,52);
    if ((int)dalo_hotspot_query($pdo,'SELECT GET_LOCK(?, 10)',array($name))->fetchColumn()!==1) {
        throw new RuntimeException('Hotspot is busy');
    }
    try {
        if (!$pdo->beginTransaction()) { throw new RuntimeException('Could not start Hotspot transaction'); }
        try {
            // Acquire a metadata lock before checking the engine, without changing data.
            dalo_hotspot_query($pdo,"SELECT id FROM $table WHERE id=0 FOR UPDATE")->fetchAll();
            $engine=dalo_hotspot_query($pdo,'SELECT ENGINE FROM INFORMATION_SCHEMA.TABLES
                                          WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',
                                     array(trim($table,'`')))->fetchColumn();
            if (!is_string($engine) || strcasecmp($engine,'InnoDB')!==0) {
                throw new RuntimeException('Hotspot mutation requires InnoDB');
            }
            $result=$operation($pdo,$table);
            if (!$pdo->commit()) { throw new RuntimeException('Could not commit Hotspot transaction'); }
            return $result;
        } catch (Throwable $e) { if ($pdo->inTransaction()) { $pdo->rollBack(); } throw $e; }
    } finally {
        try { dalo_hotspot_query($pdo,'SELECT RELEASE_LOCK(?)',array($name)); }
        catch (Throwable $ignored) { /* A nonpersistent connection releases it on teardown. */ }
    }
}


function dalo_hotspot_save(PDO $pdo,$config,$fields,$operator,$edit=false) {
    return dalo_hotspot_mutate($pdo,$config,function ($pdo,$table) use ($fields,$operator,$edit) {
        if (!is_string($operator) || $operator==='' || strpos($operator,"\0")!==false || !preg_match('//u',$operator)) {
            throw new InvalidArgumentException('Invalid audit identity');
        }
        dalo_hotspot_capacity($pdo,$table,$fields+array(($edit ? 'updateby' : 'creationby')=>$operator));
        $old=$edit ? dalo_hotspot_read($pdo,$table,$fields['name'],true) : false;
        if ($edit && !$old) { throw new RuntimeException('Hotspot no longer exists'); }
        $sql=$edit ? "SELECT id FROM $table WHERE mac=? AND id<>? ORDER BY id FOR UPDATE" : "SELECT id FROM $table WHERE name=? OR mac=? ORDER BY id FOR UPDATE";
        $params=$edit ? array($fields['mac'],$old['id']) : array($fields['name'],$fields['mac']);
        if (dalo_hotspot_query($pdo,$sql,$params)->fetchColumn()!==false) { return false; }
        $now=date('Y-m-d H:i:s');
        if ($edit) {
            $mutable=$fields;unset($mutable['name']);
            $set=array();foreach (array_keys($mutable) as $column) { $set[]="`$column`=?"; }
            dalo_hotspot_query($pdo,"UPDATE $table SET " . implode(',',$set) . ',updatedate=?,updateby=? WHERE id=?',array_merge(array_values($mutable),array($now,$operator,$old['id'])));
        } else {
            $columns='`' . implode('`,`',array_keys($fields)) . '`';
            dalo_hotspot_query($pdo,"INSERT INTO $table ($columns,creationdate,creationby,updatedate,updateby) VALUES (" . implode(',',array_fill(0,count($fields),'?')) . ',?,?,NULL,NULL)',array_merge(array_values($fields),array($now,$operator)));
            $id=$pdo->lastInsertId();
        }
        $stored=dalo_hotspot_read($pdo,$table,$fields['name']);
        if (!$stored) { throw new RuntimeException('Hotspot readback failed'); }
        foreach ($fields as $key=>$value) { if ($stored[$key]!==$value) { throw new RuntimeException('Hotspot stored value mismatch'); } }
        if ($edit && ($stored['id']!=$old['id'] || $stored['creationdate']!==$old['creationdate'] || $stored['creationby']!==$old['creationby'])) {
            throw new RuntimeException('Hotspot creation metadata changed');
        }
        if ($stored[$edit ? 'updateby' : 'creationby']!==$operator || $stored[$edit ? 'updatedate' : 'creationdate']!==$now ||
            (!$edit && ($stored['updatedate']!==null || $stored['updateby']!==null || $stored['id']!=$id))) {
            throw new RuntimeException('Hotspot audit metadata mismatch');
        }
        return true;
    });
}
function dalo_hotspot_selection($input) {
    if (is_string($input)) { $input=array($input); }
    if (!is_array($input) || !$input) { throw new InvalidArgumentException('Empty hotspot selection'); }
    $values=array();foreach ($input as $name) { $name=dalo_hotspot_name($name);if (!in_array($name,$values,true)) { $values[]=$name; } }
    return $values;
}
function dalo_hotspot_delete(PDO $pdo,$config,$names) {
    return dalo_hotspot_mutate($pdo,$config,function ($pdo,$table) use ($names) {
        $sorted=$names;sort($sorted,SORT_STRING);$rows=array();
        foreach ($sorted as $name) {
            $row=dalo_hotspot_read($pdo,$table,$name,true);if (!$row) { throw new RuntimeException('Stale hotspot selection'); }$rows[]=$row;
        }
        foreach ($rows as $row) {
            if (dalo_hotspot_query($pdo,"DELETE FROM $table WHERE id=?",array($row['id']))->rowCount()!==1) { throw new RuntimeException('Hotspot delete conflict'); }
        }
        return $names;
    });
}
function dalo_hotspot_options(PDO $pdo,$config) {
    return dalo_hotspot_query($pdo,'SELECT DISTINCT name FROM ' . dalo_hotspot_table($config))->fetchAll(PDO::FETCH_COLUMN);
}
