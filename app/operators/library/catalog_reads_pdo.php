<?php
/** R16: selected-backend billing catalogue and batch-selector reads. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/catalog_reads_pdo.php') !== false) {
    http_response_code(404); exit;
}
require_once __DIR__ . '/../../common/includes/pdo_connection.php';
require_once __DIR__ . '/../include/management/read_helpers_pdo.php';

function dalo_catalog_read_open($config) {
    return dalo_pdo_connect($config, $_SESSION['location_name'] ?? 'default');
}

/** Borrow the handle, never change the caller's transaction. */
function dalo_catalog_read_rows(PDO $pdo, $sql, $values = array()) {
    $statement = $pdo->prepare($sql);
    try {
        foreach ($values as $name => $value) {
            $statement->bindValue($name, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_NUM);
    } finally {
        $statement->closeCursor();
    }
}

function dalo_catalog_read_failure(Throwable $error) {
    global $failureMsg, $logAction;
    $failureMsg = 'Unable to read billing catalogue data';
    $logAction .= 'Billing catalogue read failed [' . get_class($error) . '] on page: ';
}

function dalo_catalog_read_inputs($input, $fields) {
    foreach ($fields as $field) {
        if (array_key_exists($field, $input) && !is_string($input[$field])) {
            throw new InvalidArgumentException('Invalid catalogue input');
        }
    }
}

function dalo_catalog_read_options(PDO $pdo, $config, $key, $column, $ordered = false) {
    $allowed = array('CONFIG_DB_TBL_DALOBILLINGPLANS'=>'planName',
                     'CONFIG_DB_TBL_RADCHECK'=>'username',
                     'CONFIG_DB_TBL_DALOUSERBILLINFO'=>'username',
                     'CONFIG_DB_TBL_DALOBATCHHISTORY'=>'batch_name');
    if (!isset($allowed[$key]) || $allowed[$key] !== $column) {
        throw new InvalidArgumentException('Invalid catalogue selector');
    }
    $table = dalo_read_table($pdo, $config, $key);
    return array_map(function ($row) { return (string)$row[0]; },
        dalo_catalog_read_rows($pdo, "SELECT DISTINCT($column) FROM $table" . ($ordered ? " ORDER BY $column ASC" : "")));
}

function dalo_catalog_pos_query(PDO $pdo, $config, $planname) {
    $info = dalo_read_table($pdo, $config, 'CONFIG_DB_TBL_DALOUSERINFO');
    $check = dalo_read_table($pdo, $config, 'CONFIG_DB_TBL_RADCHECK');
    $bill = dalo_read_table($pdo, $config, 'CONFIG_DB_TBL_DALOUSERBILLINFO');
    $group = dalo_read_table($pdo, $config, 'CONFIG_DB_TBL_RADUSERGROUP');
    $sql = "SELECT DISTINCT(rc.username) AS username, rc.id, rc.value, rc.attribute, ubi.contactperson,
                   ubi.billstatus, ubi.planname, ubi.company, ui.firstname, IFNULL(rug.username, 0) AS disabled
              FROM $info AS ui, $check AS rc
              LEFT JOIN $bill AS ubi ON rc.username=ubi.username
              LEFT JOIN $group AS rug ON rug.username=rc.username AND rug.groupname='daloRADIUS-Disabled-Users'
             WHERE rc.username=ui.username AND (rc.attribute LIKE '%-Password' OR rc.attribute='Auth-Type')";
    $values = array();
    if ($planname !== '') { $sql .= ' AND ubi.planname LIKE :planname'; $values[':planname']='%' . str_replace(array('\\', '%'), array('\\\\', '\\%'), $planname) . '%'; }
    return array($sql . ' GROUP BY username', $values);
}
