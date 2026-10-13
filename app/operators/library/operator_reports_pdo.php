<?php
/** R13: selected-backend, read-only operator reports; no implicit PEAR switch. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/operator_reports_pdo.php') !== false) {
    http_response_code(404); exit;
}
require_once __DIR__ . '/accounting_advanced_pdo.php';
require_once __DIR__ . '/report_export_users.php';
require_once __DIR__ . '/report_export_batch.php';
require_once __DIR__ . '/report_export_reports.php';

function dalo_operator_query($source, $filters, $config) {
    $table = static function ($key) use ($config) { return dalo_export_table($config, $key); };
    $bindings = array();
    if ($source === 'mng-list-all' || $source === 'mng-search') {
        $rc=$table('CONFIG_DB_TBL_RADCHECK'); $ui=$table('CONFIG_DB_TBL_DALOUSERINFO');
        $rr=$table('CONFIG_DB_TBL_RADREPLY'); $ra=$table('CONFIG_DB_TBL_RADACCT');
        $from="$rc AS rc INNER JOIN $ui AS ui ON rc.username=ui.username";
        $where="(rc.attribute='Auth-Type' OR rc.attribute LIKE '%-Password')";
        if ($source === 'mng-search') {
            $from="$rc AS rc LEFT JOIN $ra AS ra ON ra.username=rc.username LEFT JOIN $rr AS rr ON rr.username=rc.username INNER JOIN $ui AS ui ON ui.username=rc.username";
            if (($filters['username'] ?? '') !== '') {
                $conditions=array();
                foreach (array('username','firstname','lastname','homephone','workphone','mobilephone') as $field) {
                    $conditions[]="ui.$field LIKE :search_$field";
                    $bindings[':search_'.$field]='%'.$filters['username'].'%';
                }
                $conditions[]='rr.value LIKE :search_reply';
                $bindings[':search_reply']='%'.$filters['username']; // historical suffix match
                $where.=' AND ('.implode(' OR ', $conditions).')';
            }
        }
        return array("SELECT ui.id AS id,rc.username AS username,rc.value AS auth,rc.attribute,
                  CONCAT(COALESCE(ui.firstname,''),' ',COALESCE(ui.lastname,'')) AS fullname,
                  (SELECT MAX(value) FROM $rr WHERE username=rc.username AND attribute='Framed-IP-Address') AS framedipaddress,
                  (SELECT MAX(acctstarttime) FROM $ra WHERE username=rc.username) AS lastlogin
                  FROM $from WHERE $where GROUP BY rc.username", $bindings);
    }
    if (in_array($source,array('mng-batch-list','rep-batch-list','batch-summary'),true)) {
        $bh=$table('CONFIG_DB_TBL_DALOBATCHHISTORY');$ubi=$table('CONFIG_DB_TBL_DALOUSERBILLINFO');
        $bp=$table('CONFIG_DB_TBL_DALOBILLINGPLANS');$hs=$table('CONFIG_DB_TBL_DALOHOTSPOTS');
        $id=$source==='mng-batch-list'?'bh.id AS bid':'bh.id';
        $active='';$join='';$where='';
        if ($source!=='mng-batch-list') {
            $ra=$table('CONFIG_DB_TBL_RADACCT');$active='COUNT(DISTINCT(ra.username)) AS active_users,';
            $join="LEFT JOIN $ra AS ra ON ra.username=ubi.username";
        }
        if ($source==='batch-summary') {$where='WHERE bh.batch_name=:batch_name';$bindings[':batch_name']=$filters['batch_name'];}
        return array("SELECT $id,bh.batch_name,bh.batch_description,bh.batch_status,COUNT(DISTINCT(ubi.id)) AS total_users,
                 $active ubi.planname,bp.plancost,bp.plancurrency,hs.name AS HotspotName,bh.creationdate,bh.creationby,bh.updatedate,bh.updateby
                 FROM $bh AS bh LEFT JOIN $ubi AS ubi ON bh.id=ubi.batch_id LEFT JOIN $bp AS bp ON bp.planname=ubi.planname
                 LEFT JOIN $hs AS hs ON bh.hotspot_id=hs.id $join $where GROUP BY bh.batch_name",$bindings);
    }
    if ($source==='rep-batch-details') {
        list($sql,$bindings)=dalo_export_batch_query($source,'reportsBatchActiveUsers',$filters,$config);
        $sql=str_replace('SELECT bh.batch_name AS batch_name, ubi.username AS username,','SELECT ubi.username AS username,',$sql);
        return array($sql,$bindings);
    }
    if (in_array($source,array('rep-online','rep-lastconnect','rep-topusers'),true)) {
        $types=array('rep-online'=>'reportsOnlineUsers','rep-lastconnect'=>'reportsLastConnectionAttempts','rep-topusers'=>'TopUsers');
        list($export,$bindings)=dalo_export_reports_query($source,$types[$source],$filters,$config);
        $start=strpos($export,' WHERE ');
        // Export builders use a newline before WHERE; retain only their shared predicates.
        if ($start===false) { $start=strpos($export,'WHERE ')-1; }
        $where=substr($export,$start);
        $end=strpos($where,'GROUP BY'); if ($end===false) { $end=strpos($where,'ORDER BY'); }
        $where=trim(substr($where,0,$end));
        if ($source==='rep-lastconnect') {
            $pa=$table('CONFIG_DB_TBL_RADPOSTAUTH');$ui=$table('CONFIG_DB_TBL_DALOUSERINFO');
            $user=(string)($config['FREERADIUS_VERSION']??'3')==='1'?'user':'username';
            $date=$user==='user'?'date':'authdate';
            return array("SELECT CONCAT(COALESCE(ui.firstname,''),' ',COALESCE(ui.lastname,'')) AS fullname,
                       pa.$user AS username,pa.pass,pa.reply,pa.$date FROM $pa AS pa LEFT JOIN $ui AS ui ON pa.$user=ui.username $where",$bindings);
        }
        $ra=$table('CONFIG_DB_TBL_RADACCT');$rn=$table('CONFIG_DB_TBL_RADNAS');
        if ($source==='rep-topusers') {
            return array("SELECT DISTINCT(ra.username) AS username,ra.FramedIPAddress,rn.shortname AS nasshortname,
                 ra.AcctStartTime,MAX(ra.AcctStopTime),SUM(ra.AcctSessionTime) AS Time,SUM(ra.AcctInputOctets) AS Upload,
                 SUM(ra.AcctOutputOctets) AS Download,ra.AcctTerminateCause,ra.NASIPAddress
                 FROM $ra AS ra LEFT JOIN $rn AS rn ON rn.nasname=ra.NASIPAddress $where GROUP BY username",$bindings);
        }
        $hs=$table('CONFIG_DB_TBL_DALOHOTSPOTS');$ui=$table('CONFIG_DB_TBL_DALOUSERINFO');
        return array("SELECT ra.username AS username,ra.framedipaddress AS framedipaddress,ra.callingstationid AS callingstationid,
           ra.acctstarttime AS starttime,ra.acctsessiontime AS sessiontime,ra.nasipaddress AS nasipaddress,
           ra.calledstationid AS calledstationid,ra.acctsessionid AS sessionid,ra.acctinputoctets AS upload,
           ra.acctoutputoctets AS download,hs.name AS hotspot,rn.shortname AS nasshortname,rn.id AS nasid,ui.firstname,ui.lastname
           FROM $ra AS ra LEFT JOIN $hs AS hs ON hs.mac=ra.calledstationid LEFT JOIN $rn AS rn ON rn.nasname=ra.nasipaddress
           LEFT JOIN $ui AS ui ON ra.username=ui.username $where",$bindings);
    }
    if ($source==='rep-newusers') {
        $ui=$table('CONFIG_DB_TBL_DALOUSERINFO');$where=array();
        foreach (array('startdate','enddate') as $key) {
            if (($filters[$key]??'')!=='') {
                $where[]='CreationDate '.($key==='startdate'?'>= :new_start':'< (:new_end + INTERVAL 1 DAY)');
                $bindings[$key==='startdate'?':new_start':':new_end']=$filters[$key];
            }
        }
        $where=$where?' WHERE '.implode(' AND ',$where):'';
        return array("SELECT CONCAT(MONTHNAME(CreationDate),' ',YEAR(CreationDate)) AS period,COUNT(*) AS users,
             CAST(CONCAT(YEAR(CreationDate),'-',MONTH(CreationDate),'-01') AS DATE) AS month FROM $ui $where GROUP BY month",$bindings);
    }
    if ($source==='rep-history') {
        $pieces=array();
        foreach (array('proxy'=>array('proxyname','CONFIG_DB_TBL_DALOPROXYS'),'realm'=>array('realmname','CONFIG_DB_TBL_DALOREALMS'),
            'userinfo'=>array('username','CONFIG_DB_TBL_DALOUSERINFO'),'operators'=>array('username','CONFIG_DB_TBL_DALOOPERATORS'),
            'invoice'=>array('id','CONFIG_DB_TBL_DALOBILLINGINVOICE'),'payment'=>array('id','CONFIG_DB_TBL_DALOPAYMENTS'),
            'hotspot'=>array('name','CONFIG_DB_TBL_DALOHOTSPOTS')) as $section=>$spec) {
            list($column,$key)=$spec;$target=$table($key);
            $pieces[]="SELECT '$section' AS section,$column AS item,creationdate,creationby,updatedate,updateby FROM $target";
        }
        return array(implode(' UNION ',$pieces),array());
    }
    if ($source==='rep-hb-dashboard') {
        $no=$table('CONFIG_DB_TBL_DALONODE');$hs=$table('CONFIG_DB_TBL_DALOHOTSPOTS');
        return array("SELECT hs.name AS hotspotname,no.wan_iface,no.wan_ip,no.wan_mac,no.wan_gateway,no.wifi_iface,
             no.wifi_ip,no.wifi_mac,no.wifi_ssid,no.wifi_key,no.wifi_channel,no.lan_iface,no.lan_mac,no.lan_ip,no.uptime,
             no.memfree,no.cpu,no.wan_bup,no.wan_bdown,no.firmware,no.firmware_revision,no.mac,no.time
             FROM $no AS no LEFT JOIN $hs AS hs ON hs.mac=no.mac",array());
    }
    throw new InvalidArgumentException('Unsupported operator report');
}

function dalo_operator_rows(PDO $pdo,$sql,$bindings,$source,$order,$direction,$offset,$limit,$assoc=false) {
    $keys=array(
      'mng-list-all'=>array('id','fullname','username','auth','framedipaddress','lastlogin'),
      'mng-search'=>array('id','fullname','username','auth','framedipaddress','lastlogin'),
      'mng-batch-list'=>array('bid','batch_name','creationdate','creationby'),
      'rep-batch-list'=>array('id','creationdate','creationby'),
      'rep-batch-details'=>array('username','status','acctstarttime'),
      'rep-lastconnect'=>array('username','user','fullname','pass','reply','authdate','date'),
      'rep-online'=>array('username','framedipaddress','calledstationid','nasshortname','hotspot','acctstarttime','acctsessiontime'),
      'rep-topusers'=>array('username','framedipaddress','nasshortname','acctstarttime','acctstoptime','Time','Upload','Download','acctterminatecause'),
      'rep-newusers'=>array('month','users'),'rep-history'=>array('section','item','creationdate','creationby','updatedate','updateby'),
      'rep-hb-dashboard'=>array('id'));
    if (!isset($keys[$source]) || !in_array($order,$keys[$source],true) || !in_array($direction,array('asc','desc'),true) ||
        !is_int($offset) || $offset<0 || !is_int($limit) || $limit<1 || $limit>10000) {
        throw new InvalidArgumentException('Invalid operator report pagination');
    }
    $column='`'.$order.'`';
    if ($source==='rep-online' && in_array($order,array('acctstarttime','acctsessiontime','calledstationid'),true)) {$column='ra.'.$column;}
    if ($source==='rep-topusers' && $order==='acctstoptime') {$column='ra.AcctStopTime';}
    if ($source==='rep-lastconnect' && in_array($order,array('username','user','authdate','date'),true)) {$column='pa.'.$column;}
    if ($source==='rep-hb-dashboard') {$column='hs.id';}
    $bindings[':operator_offset']=$offset;$bindings[':operator_limit']=$limit;
    return dalo_accounting_execute($pdo,$sql.' ORDER BY '.$column.' '.$direction.' LIMIT :operator_offset, :operator_limit',$bindings)->fetchAll($assoc?PDO::FETCH_ASSOC:PDO::FETCH_NUM);
}

function dalo_operator_groups(PDO $pdo,$names,$config) {
    if (!$names) {return array();}
    $table=dalo_export_table($config,'CONFIG_DB_TBL_RADUSERGROUP');$bindings=array();$tokens=array();
    foreach (array_values($names) as $i=>$name) {$tokens[]=':group_user_'.$i;$bindings[':group_user_'.$i]=$name;}
    return dalo_accounting_execute($pdo,"SELECT username,groupname FROM $table WHERE username IN (".implode(',',$tokens).')',$bindings)->fetchAll(PDO::FETCH_ASSOC);
}
