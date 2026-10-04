<?php
/** R10: explicit GIS and heartbeat PDO operations; legacy consumers stay intact. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/geo_heartbeat_pdo.php')!==false) {
    http_response_code(404); exit;
}
require_once __DIR__ . '/hotspot_pages_pdo.php';
function dalo_geo_coordinates($value) {
    if (!is_string($value) || strlen($value)>4096 || !preg_match('/\A\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*\z/',$value,$m)) { return false; }
    $lat=(float)$m[1];$lng=(float)$m[2];
    return is_finite($lat) && is_finite($lng) && abs($lat)<=90 && abs($lng)<=180 ? array($lat,$lng) : false;
}
function dalo_geo_id($value) {
    if (!is_string($value) || !preg_match('/\A[1-9][0-9]{0,18}\z/',$value) ||
        (strlen($value)===19 && strcmp($value,'9223372036854775807')>0)) {
        throw new InvalidArgumentException('Invalid hotspot ID');
    }
    return $value;
}
function dalo_geo_markers(PDO $pdo,$config) {
    $table=dalo_hotspot_table($config);
    return dalo_hotspot_query($pdo,"SELECT id,name,mac,geocode FROM $table WHERE geocode<>'' AND geocode IS NOT NULL")->fetchAll(PDO::FETCH_NUM);
}
function dalo_geo_add(PDO $pdo,$config,$input,$operator) {
    $fields=array('name'=>dalo_hotspot_name($input['hotspotname'] ?? null),'mac'=>$input['hotspotmac'] ?? null,'geocode'=>$input['hotspotgeo'] ?? null);
    if (!is_string($fields['mac']) || !preg_match('/\A(?:[A-Fa-f0-9]{12}|[A-Fa-f0-9]{2}([-:])(?:[A-Fa-f0-9]{2}\1){4}[A-Fa-f0-9]{2})\z/',trim($fields['mac'])) || !dalo_geo_coordinates($fields['geocode'])) {
        throw new InvalidArgumentException('Invalid location');
    }
    $fields['mac']=trim($fields['mac']);$fields['geocode']=trim($fields['geocode']);
    return dalo_hotspot_mutate($pdo,$config,function ($pdo,$table) use ($fields,$operator) {
        dalo_hotspot_capacity($pdo,$table,$fields+array('creationby'=>$operator));
        // GIS historically inserts independent rows, without the CRUD name/MAC precheck.
        $now=date('Y-m-d H:i:s');
        dalo_hotspot_query($pdo,"INSERT INTO $table (name,mac,geocode,creationdate,creationby,updatedate,updateby) VALUES (?,?,?,?,?,NULL,NULL)",array($fields['name'],$fields['mac'],$fields['geocode'],$now,$operator));
        $id=$pdo->lastInsertId();
        $row=dalo_hotspot_query($pdo,"SELECT name,mac,geocode,creationdate,creationby,updatedate,updateby FROM $table WHERE id=?",array($id))->fetch(PDO::FETCH_ASSOC);
        if (!$row || $row['creationdate']!==$now || $row['creationby']!==$operator || $row['updatedate']!==null || $row['updateby']!==null) { throw new RuntimeException('Location audit mismatch'); }
        foreach ($fields as $k=>$v) { if ($row[$k]!==$v) { throw new RuntimeException('Location readback mismatch'); } }
        return $fields['name'];
    });
}
function dalo_geo_delete(PDO $pdo,$config,$id) {
    $id=dalo_geo_id($id);
    return dalo_hotspot_mutate($pdo,$config,function ($pdo,$table) use ($id) {
        $row=dalo_hotspot_query($pdo,"SELECT name FROM $table WHERE id=? FOR UPDATE",array($id))->fetch(PDO::FETCH_NUM);
        if (!$row) { throw new RuntimeException('Stale hotspot'); }
        if (dalo_hotspot_query($pdo,"DELETE FROM $table WHERE id=?",array($id))->rowCount()!==1) { throw new RuntimeException('Delete conflict'); }
        // Retain historical whole-row removal, not a geocode-only UPDATE/cascade.
        return (string)$row[0];
    });
}
function dalo_heartbeat_table($config) {
    $name=$config['CONFIG_DB_TBL_DALONODE'] ?? null;
    if (!is_string($name) || !preg_match('/\A[A-Za-z_][A-Za-z0-9_]{0,63}\z/',$name)) { throw new InvalidArgumentException('Invalid heartbeat table'); }
    return '`' . $name . '`';
}
function dalo_heartbeat_fields($input) {
    $fields=array();
    foreach (array('wan_iface','wan_ip','wan_mac','wan_gateway','wifi_iface','wifi_ip','wifi_mac','wifi_ssid','wifi_channel','lan_iface','lan_mac','lan_ip','uptime','memfree','wan_bup','wan_bdown','firmware','firmware_revision') as $key) {
        $value=$input[$key] ?? '';
        if (!is_string($value) || strlen($value)>4096 || strpos($value,"\0")!==false || !preg_match('//u',$value)) { throw new InvalidArgumentException('Invalid heartbeat field'); }
        $fields[$key]=trim($value);
    }
    // Do not persist submitted network credentials, or carry them into debugging.
    $fields['wifi_key']='';
    $fields['mac']=dalo_hotspot_name($input['nas_mac'] ?? null);
    // All bundled clients send CPU as decimal percent. Preserve the numeric value,
    // explicitly rather than depending on permissive SQL prefix coercion.
    $cpu=$input['cpu'] ?? '';
    if (is_string($cpu)) { $cpu=trim($cpu); }
    if (!is_string($cpu) || strlen($cpu)>100 || !preg_match('/\A\s*([+-]?(?:\d+(?:\.\d*)?|\.\d+)(?:[eE][+-]?\d+)?)%?\s*\z/', $cpu,$m)) {
        if ($cpu!=='') { throw new InvalidArgumentException('Invalid CPU'); }$cpu='0';
    } else { $cpu=$m[1]; }
    if (!is_finite((float)$cpu)) { throw new InvalidArgumentException('CPU overflow'); }
    $fields['cpu']=$cpu;
    return $fields;
}
/** Own only this page's transaction; keep its advisory lock on the same handle. */
function dalo_heartbeat_mutate(PDO $pdo, $config, $operation) {
    if ($pdo->inTransaction()) { throw new LogicException('Heartbeat page requires its own transaction'); }
    $table=dalo_heartbeat_table($config);
    $schema=dalo_hotspot_query($pdo,'SELECT DATABASE()')->fetchColumn();
    $name='dalo_heartbeat_' . substr(hash('sha256', $schema . ':' . $table),0,64-strlen('dalo_heartbeat_'));
    if ((int)dalo_hotspot_query($pdo,'SELECT GET_LOCK(?, 10)',array($name))->fetchColumn()!==1) {
        throw new RuntimeException('Heartbeat is busy');
    }
    try {
        if (!$pdo->beginTransaction()) { throw new RuntimeException('Could not start Heartbeat transaction'); }
        try {
            // Acquire a metadata lock before checking the engine, without changing data.
            dalo_hotspot_query($pdo,"SELECT id FROM $table WHERE id=0 FOR UPDATE")->fetchAll();
            $engine=dalo_hotspot_query($pdo,'SELECT ENGINE FROM INFORMATION_SCHEMA.TABLES
                                          WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',
                                     array(trim($table,'`')))->fetchColumn();
            if (!is_string($engine) || strcasecmp($engine,'InnoDB')!==0) {
                throw new RuntimeException('Heartbeat mutation requires InnoDB');
            }
            $result=$operation($pdo,$table);
            if (!$pdo->commit()) { throw new RuntimeException('Could not commit Heartbeat transaction'); }
            return $result;
        } catch (Throwable $e) { if ($pdo->inTransaction()) { $pdo->rollBack(); } throw $e; }
    } finally {
        try { dalo_hotspot_query($pdo,'SELECT RELEASE_LOCK(?)',array($name)); }
        catch (Throwable $ignored) { /* A nonpersistent connection releases it on teardown. */ }
    }
}



function dalo_heartbeat_save(PDO $pdo,$config,$fields) {
    return dalo_heartbeat_mutate($pdo,$config,function ($pdo,$table) use ($fields) {
        $strings=$fields;unset($strings['cpu']);dalo_hotspot_capacity($pdo,$table,$strings);
        $ids=dalo_hotspot_query($pdo,"SELECT id FROM $table WHERE mac=? ORDER BY id FOR UPDATE",array($fields['mac']))->fetchAll(PDO::FETCH_COLUMN);
        if (count($ids)>1) { throw new RuntimeException('Ambiguous heartbeat identity'); }
        $fields['time']=date('Y-m-d H:i:s');
        if ($ids) {
            $set=array();foreach (array_keys($fields) as $column) { $set[]="`$column`=?"; }
            dalo_hotspot_query($pdo,"UPDATE $table SET " . implode(',',$set) . ' WHERE id=?',array_merge(array_values($fields),array($ids[0])));$id=$ids[0];
        } else {
            $columns='`' . implode('`,`',array_keys($fields)) . '`';
            dalo_hotspot_query($pdo,"INSERT INTO $table ($columns) VALUES (" . implode(',',array_fill(0,count($fields),'?')) . ')',array_values($fields));$id=$pdo->lastInsertId();
        }
        $row=dalo_hotspot_query($pdo,"SELECT `" . implode('`,`',array_keys($fields)) . "` FROM $table WHERE id=?",array($id))->fetch(PDO::FETCH_ASSOC);
        if (!$row) { throw new RuntimeException('Heartbeat readback failed'); }
        foreach ($fields as $key=>$value) {
            $equal=$key==='cpu' ? (is_numeric($row[$key]) && abs((float)$row[$key]-(float)$value)<=max(1,abs((float)$value))*0.000001) : $row[$key]===$value;
            if (!$equal) { throw new RuntimeException('Heartbeat stored value mismatch'); }
        }
        return true;
    });
}
