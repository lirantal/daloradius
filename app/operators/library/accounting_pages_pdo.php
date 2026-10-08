<?php
/** R11: bounded, read-only accounting pages using the selected PDO backend. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/accounting_pages_pdo.php') !== false) {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__, 2) . '/common/includes/pdo_connection.php';
require_once __DIR__ . '/report_export.php';
require_once __DIR__ . '/report_export_accounting.php';

function dalo_accounting_scalar($input, $key, $default = '') {
    $value = $input[$key] ?? $default;
    if (!is_string($value) || strlen($value) > 4096 || strpos($value, "\0") !== false) {
        throw new InvalidArgumentException('Invalid accounting filter');
    }
    return trim($value);
}

function dalo_accounting_date($input, $key, $default = '') {
    $value = dalo_accounting_scalar($input, $key, $default);
    if ($value === '') { return $default; }
    try {
        return dalo_export_accounting_date_filter(array($key => $value), $key);
    } catch (InvalidArgumentException $exception) {
        return $default; // Preserve the form's fallback for invalid scalar dates.
    }
}

function dalo_accounting_hotspots($input) {
    $values = $input['hotspot'] ?? array();
    if (is_string($values)) { $values = array($values); }
    if (!is_array($values) || count($values) > 256) {
        throw new InvalidArgumentException('Invalid hotspot selection');
    }
    $result = array();
    foreach ($values as $value) {
        $value = dalo_accounting_scalar(array('value' => $value), 'value');
        if ($value !== '' && !in_array($value, $result, true)) { $result[] = $value; }
    }
    return $result;
}

function dalo_accounting_query($source, $filters, $config) {
    if ($source === 'acct-active') {
        // Historical lifetime totals and permissive GROUP BY are intentional.
        $ra = dalo_export_table($config, 'CONFIG_DB_TBL_RADACCT');
        $rc = dalo_export_table($config, 'CONFIG_DB_TBL_RADCHECK');
        return array("SELECT DISTINCT(ra.username) AS username, rc.attribute AS attribute,
                             rc.value AS maxtimeexpiration, SUM(ra.AcctSessionTime) AS usedtime
                        FROM $ra AS ra JOIN $rc AS rc ON ra.username=rc.username
                       WHERE rc.attribute IN ('Max-All-Session', 'Expiration')
                       GROUP BY ra.username", array());
    }
    if ($source === 'acct-hotspot-compare') {
        $ra = dalo_export_table($config, 'CONFIG_DB_TBL_RADACCT');
        $hs = dalo_export_table($config, 'CONFIG_DB_TBL_DALOHOTSPOTS');
        return array("SELECT hs.name AS hotspot, COUNT(DISTINCT(UserName)) AS uniqueusers,
                             COUNT(radacctid) AS totalhits, AVG(AcctSessionTime) AS avgsessiontime,
                             SUM(AcctSessionTime) AS totaltime, AVG(AcctInputOctets) AS avgInputOctets,
                             SUM(AcctInputOctets) AS sumInputOctets, AVG(AcctOutputOctets) AS avgOutputOctets,
                             SUM(AcctOutputOctets) AS sumOutputOctets
                        FROM $ra AS ra JOIN $hs AS hs ON ra.calledstationid=hs.mac
                       GROUP BY hotspot", array());
    }
    list($sql, $bindings) = dalo_export_accounting_query($source, 'accountingGeneric', $filters, $config);
    $sql = substr($sql, 0, -strlen(' ORDER BY ra.RadAcctId DESC'));
    // Only the display changes zero termination to Unknown; CSV keeps raw values.
    $sql = str_replace('ra.AcctTerminateCause,',
        "CASE WHEN ra.AcctTerminateCause = '0' THEN 'Unknown' ELSE ra.AcctTerminateCause END AS AcctTerminateCause,", $sql);
    return array($sql, $bindings);
}

function dalo_accounting_execute(PDO $pdo, $sql, $bindings = array()) {
    global $logDebugSQL;
    $statement = $pdo->prepare($sql);
    foreach ($bindings as $name => $value) {
        $statement->bindValue($name, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $statement->execute();
    $logDebugSQL .= $sql . ";\n"; // Templates only, never bound request values.
    return $statement;
}

function dalo_accounting_count(PDO $pdo, $sql, $bindings, $config) {
    $limit = $config['CONFIG_IFACE_TABLES_LISTING'] ?? null;
    if (!is_scalar($limit) || !ctype_digit((string)$limit) || (int)$limit < 1 || (int)$limit > 10000) {
        throw new InvalidArgumentException('Invalid accounting pagination');
    }
    return (int)dalo_accounting_execute($pdo, 'SELECT COUNT(*) FROM (' . $sql . ') AS accounting_rows', $bindings)->fetchColumn();
}

function dalo_accounting_rows(PDO $pdo, $sql, $bindings, $source, $orderBy, $orderType, $offset, $limit) {
    $columns = array('radacctid' => 'ra.RadAcctId', 'name' => 'hs.name',
        'username' => 'ra.UserName', 'framedipaddress' => 'ra.FramedIPAddress',
        'acctstarttime' => 'ra.AcctStartTime', 'acctstoptime' => 'ra.AcctStopTime',
        'acctsessiontime' => 'ra.AcctSessionTime', 'acctinputoctets' => 'ra.AcctInputOctets',
        'acctoutputoctets' => 'ra.AcctOutputOctets', 'acctterminatecause' => 'AcctTerminateCause',
        'nasipaddress' => 'ra.NASIPAddress');
    if ($source === 'acct-active') {
        $columns = array_combine(array('username','attribute','maxtimeexpiration','usedtime'),
                                 array('username','attribute','maxtimeexpiration','usedtime'));
    } elseif ($source === 'acct-hotspot-compare') {
        $keys = array('hotspot','uniqueusers','totalhits','avgsessiontime','totaltime','sumInputOctets','sumOutputOctets');
        $columns = array_combine($keys, $keys);
    }
    if (!isset($columns[$orderBy]) || !in_array($orderType, array('asc','desc'), true) ||
        !is_numeric($offset) || !is_numeric($limit) || $offset < 0 || $limit < 1 || $limit > 10000) {
        throw new InvalidArgumentException('Invalid accounting order or pagination');
    }
    $bindings[':accounting_offset'] = (int)$offset;
    $bindings[':accounting_limit'] = (int)$limit;
    return dalo_accounting_execute($pdo, $sql . ' ORDER BY ' . $columns[$orderBy] . ' ' . $orderType .
        ' LIMIT :accounting_offset, :accounting_limit', $bindings)->fetchAll(PDO::FETCH_NUM);
}

function dalo_accounting_failure(Throwable $exception) {
    global $failureMsg;
    unset($_SESSION['reportExport'], $_SESSION['reportTable'], $_SESSION['reportQuery'], $_SESSION['reportType']);
    $failureMsg = 'Unable to read accounting records';
    error_log('Accounting read failed (' . get_class($exception) . ')');
}

function dalo_accounting_exists(PDO $pdo, $value, $key) {
    global $configValues;
    try {
        $table = dalo_export_table($configValues, $key);
        $column = $key === 'CONFIG_DB_TBL_DALOHOTSPOTS' ? 'name' : 'username';
        return dalo_accounting_execute($pdo, "SELECT COUNT(*) FROM $table WHERE $column=:identity",
            array(':identity' => $value))->fetchColumn() > 0;
    } catch (Throwable $exception) {
        dalo_accounting_failure($exception);
        return false;
    }
}

function dalo_accounting_validate_request($input) {
    foreach (array('username','ipaddress','nasipaddress','startdate','enddate','orderBy','orderType','page','only-active') as $key) {
        if (array_key_exists($key, $input)) { dalo_accounting_scalar($input, $key); }
    }
    dalo_accounting_hotspots($input);
}
