<?php
/* Prepared chart reads. The borrowed PDO handle remains owned by the caller. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/includes/chart_pdo.php') !== false) {
    http_response_code(404); exit;
}
function dalo_chart_table($name) {
    if (!is_string($name) || preg_match('/\A[A-Za-z0-9_]+\z/D',$name)!==1) {
        throw new InvalidArgumentException('Invalid chart table');
    }
    return '`'.$name.'`';
}
function dalo_chart_rows(PDO $pdo, $sql, $bindings=array()) {
    $statement=$pdo->prepare($sql);
    foreach ($bindings as $name=>$value) {
        if (!is_string($value) && !is_int($value)) { throw new InvalidArgumentException('Invalid chart binding'); }
        $statement->bindValue($name,$value,is_int($value)?PDO::PARAM_INT:PDO::PARAM_STR);
    }
    $statement->execute();
    return $statement->fetchAll(PDO::FETCH_NUM);
}
function dalo_chart_overall_pdo(PDO $pdo,$table,$username,$category,$type,$size,$title_template,$require_existing) {
    $table=dalo_chart_table($table);
    if (!is_string($username) || !in_array($category,array('login','upload','download'),true) ||
        !in_array($type,array('daily','monthly','yearly'),true) || !in_array($size,array('megabytes','gigabytes'),true)) {
        throw new InvalidArgumentException('Invalid chart selection');
    }
    $labels=array();$values=array();
    if ($username!=='') {
        $bindings=array(':chart_username'=>$username);
        $exists=!$require_existing || count(dalo_chart_rows($pdo,"SELECT DISTINCT(username) FROM $table WHERE username=:chart_username",$bindings))===1;
        if ($exists) {
            $aggregate=$category==='login'?'COUNT(AcctStartTime)':($category==='upload'?'SUM(AcctInputOctets)':'SUM(AcctOutputOctets)');
            if ($type==='yearly') {
                $period='YEAR(AcctStartTime)';$group='YEAR(AcctStartTime)';$order='YEAR(AcctStartTime) DESC';
            } elseif ($type==='monthly') {
                $period="CONCAT(LEFT(MONTHNAME(AcctStartTime),3),' (',YEAR(AcctStartTime),')')";
                $group='YEAR(AcctStartTime),MONTH(AcctStartTime)';$order='YEAR(AcctStartTime) DESC,MONTH(AcctStartTime) DESC';
            } else {
                $period='DATE(AcctStartTime)';$group=$period;$order=$period.' DESC';
            }
            foreach(dalo_chart_rows($pdo,"SELECT $period,$aggregate FROM $table WHERE username=:chart_username AND AcctStopTime>0 GROUP BY $group ORDER BY $order LIMIT 36",$bindings) as $row) {
                $labels[]=(string)$row[0];
                $values[]=$category==='login'?(int)$row[1]:round((float)$row[1]/($size==='gigabytes'?1073741824:1048576),1);
            }
        }
    }
    return array('labels'=>$labels,'values'=>$values,
        'title'=>$category==='login'?sprintf('login statistics for user %s',$username):sprintf($title_template,$category,$username),
        'ytitle'=>$category==='login'?'Login count':ucfirst($size).' '.$category.'ed');
}
