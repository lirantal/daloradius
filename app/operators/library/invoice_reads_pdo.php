<?php
/** R15: strict, selected-backend invoice reads. Write providers retain their own transactions. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/invoice_reads_pdo.php') !== false) {
    http_response_code(404); exit;
}
require_once __DIR__ . '/../../common/includes/pdo_connection.php';
require_once __DIR__ . '/report_export.php';
require_once __DIR__ . '/report_export_batch.php';

function dalo_invoice_read_open($config) {
    return dalo_pdo_connect($config, $_SESSION['location_name'] ?? 'default');
}

function dalo_invoice_read_table($config, $key) {
    if (!in_array($key, array('CONFIG_DB_TBL_DALOBILLINGINVOICE',
        'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS','CONFIG_DB_TBL_DALOBILLINGPLANS',
        'CONFIG_DB_TBL_DALOPAYMENTS','CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS',
        'CONFIG_DB_TBL_DALOBILLINGINVOICETYPE','CONFIG_DB_TBL_DALOUSERINFO',
        'CONFIG_DB_TBL_DALOUSERBILLINFO'), true)) {
        throw new InvalidArgumentException('Invalid invoice read table');
    }
    return dalo_export_table($config, $key);
}

/** Borrow the caller handle; do not begin, commit or roll back any transaction. */
function dalo_invoice_read_rows(PDO $pdo, $sql, $bindings = array()) {
    $statement = $pdo->prepare($sql);
    try {
        foreach ($bindings as $name => $value) {
            $statement->bindValue($name, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_NUM);
    } finally {
        $statement->closeCursor();
    }
}

function dalo_invoice_read_failure(Throwable $error) {
    global $failureMsg, $logAction;
    $failureMsg = 'Unable to read invoice data';
    $logAction .= 'Invoice read failed [' . get_class($error) . '] on page: ';
    unset($_SESSION['reportExport'], $_SESSION['reportQuery'], $_SESSION['reportTable']);
}

function dalo_invoice_read_inputs($get, $fields) {
    foreach ($fields as $field) {
        if (array_key_exists($field, $get) && !is_string($get[$field])) {
            throw new InvalidArgumentException('Invalid invoice input');
        }
    }
}

function dalo_invoice_list_query(PDO $pdo, $config, $username, $userId, $status) {
    list($sql, $bindings) = dalo_export_batch_query('bill-invoice-report', 'reportsInvoiceList', array(), $config);
    $where = array();
    if ($username !== '') {
        $table = dalo_invoice_read_table($config, 'CONFIG_DB_TBL_DALOUSERBILLINFO');
        $users = dalo_invoice_read_rows($pdo, "SELECT id FROM $table WHERE username=:username", array(':username'=>$username));
        // An absent explicit user must not turn into an unfiltered invoice list.
        $userId = isset($users[0]) ? (int)$users[0][0] : 0;
    }
    if ($username !== '' || $userId !== '') {
        $where[] = 'a.user_id=:user_id'; $bindings[':user_id']=(int)$userId;
    }
    if ($status !== '') {
        $where[] = 'a.status_id=:status_id'; $bindings[':status_id']=$status;
    }
    $sql = substr($sql, 0, strrpos($sql, 'GROUP BY a.id'));
    if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
    return array($sql . ' GROUP BY a.id', $bindings);
}
