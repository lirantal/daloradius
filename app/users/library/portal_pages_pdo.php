<?php
/** R21: portal-only, session-scoped pages; never accept an HTTP account selector. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/portal_pages_pdo.php') !== false) {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__, 2) . '/common/includes/pdo_connection.php';
require_once __DIR__ . '/user_report_export.php';

function dalo_portal_handle($config) {
    return dalo_pdo_connect($config, $_SESSION['location_name'] ?? 'default');
}

function dalo_portal_username($value) {
    if (!is_string($value) || $value === '' || strpos($value, "\0") !== false) {
        throw new InvalidArgumentException('Invalid portal identity');
    }
    return $value;
}

function dalo_portal_table(PDO $pdo, $config, $key) {
    $allowed = array('CONFIG_DB_TBL_RADACCT', 'CONFIG_DB_TBL_DALOHOTSPOTS',
        'CONFIG_DB_TBL_DALOUSERINFO', 'CONFIG_DB_TBL_DALOUSERBILLINFO',
        'CONFIG_DB_TBL_DALOBILLINGINVOICE', 'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS',
        'CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS', 'CONFIG_DB_TBL_DALOBILLINGINVOICETYPE',
        'CONFIG_DB_TBL_DALOBILLINGPLANS', 'CONFIG_DB_TBL_DALOPAYMENTS');
    $name = $config[$key] ?? null;
    if (!in_array($key, $allowed, true) || !is_string($name) ||
        !preg_match('/\A[A-Za-z_][A-Za-z0-9_]{0,63}\z/D', $name)) {
        throw new InvalidArgumentException('Invalid portal table');
    }
    $quote = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? '`' : '"';
    return $quote . $name . $quote;
}

function dalo_portal_rows(PDO $pdo, $sql, $params = array(), $mode = PDO::FETCH_NUM) {
    global $logDebugSQL;
    $stmt = $pdo->prepare($sql);
    if ($stmt === false) { throw new RuntimeException('Portal prepare failed'); }
    try {
        foreach ($params as $key => $value) {
            if (!is_scalar($value) && $value !== null) {
                throw new InvalidArgumentException('Invalid portal binding');
            }
            if (!$stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT :
                ($value === null ? PDO::PARAM_NULL : PDO::PARAM_STR))) {
                throw new RuntimeException('Portal bind failed');
            }
        }
        if (!$stmt->execute()) { throw new RuntimeException('Portal execute failed'); }
        $rows = $stmt->fetchAll($mode);
        if ($rows === false || $stmt->errorCode() !== '00000') {
            throw new RuntimeException('Portal fetch failed');
        }
        $logDebugSQL = ($logDebugSQL ?? '') . $sql . ";\n";
        return $rows;
    } finally { $stmt->closeCursor(); }
}

function dalo_portal_id($value) {
    if ((!is_string($value) && !is_int($value)) ||
        !preg_match('/\A[1-9][0-9]*\z/D', (string) $value) ||
        filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) === false) {
        throw new InvalidArgumentException('Invalid portal invoice identity');
    }
    return (int) $value;
}

function dalo_portal_fields() {
    return array('firstname', 'lastname', 'email', 'department', 'company', 'workphone',
        'homephone', 'mobilephone', 'address', 'city', 'state', 'country', 'zip');
}

function dalo_portal_userinfo(PDO $pdo, $config, $username) {
    $table = dalo_portal_table($pdo, $config, 'CONFIG_DB_TBL_DALOUSERINFO');
    $rows = dalo_portal_rows($pdo, 'SELECT ' . implode(',', dalo_portal_fields()) .
        " FROM $table WHERE username=:username", array(':username' => dalo_portal_username($username)));
    return $rows[0] ?? array_fill(0, count(dalo_portal_fields()), '');
}

/** Permission recheck, write and storage verification share one owned transaction. */
function dalo_portal_update_userinfo(PDO $pdo, $config, $username, $post) {
    $username = dalo_portal_username($username);
    if ($pdo->inTransaction()) { throw new LogicException('Portal update requires an owned transaction'); }
    $table = dalo_portal_table($pdo, $config, 'CONFIG_DB_TBL_DALOUSERINFO');
    $values = array();
    foreach (dalo_portal_fields() as $field) {
        $value = $post[$field] ?? '';
        if (!is_string($value) || strpos($value, "\0") !== false || preg_match('//u', $value) !== 1) {
            throw new InvalidArgumentException('Invalid portal information');
        }
        $values[$field] = $value;
    }
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') {
        throw new RuntimeException('Portal information writes require supported storage');
    }
    $engine = dalo_portal_rows($pdo, 'SELECT ENGINE FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:table', array(':table' => trim($table, '`')));
    if (strcasecmp($engine[0][0] ?? '', 'InnoDB') !== 0) {
        throw new RuntimeException('Portal information writes require InnoDB');
    }
    $metadata = dalo_portal_rows($pdo, 'SELECT COLUMN_NAME,CHARACTER_MAXIMUM_LENGTH,CHARACTER_OCTET_LENGTH
        FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:table',
        array(':table' => trim($table, '`')), PDO::FETCH_ASSOC);
    $limits = array_column($metadata, null, 'COLUMN_NAME');
    foreach ($values as $field => $value) {
        if (!isset($limits[$field]['CHARACTER_MAXIMUM_LENGTH'], $limits[$field]['CHARACTER_OCTET_LENGTH']) ||
            preg_match_all('/./us', $value) > (int) $limits[$field]['CHARACTER_MAXIMUM_LENGTH'] ||
            strlen($value) > (int) $limits[$field]['CHARACTER_OCTET_LENGTH']) {
            throw new InvalidArgumentException('Portal information exceeds storage capacity');
        }
    }
    if (!$pdo->beginTransaction()) { throw new RuntimeException('Portal transaction failed'); }
    try {
        $rows = dalo_portal_rows($pdo, "SELECT id,changeuserinfo FROM $table
            WHERE username=:username ORDER BY id FOR UPDATE", array(':username' => $username));
        if (!$rows) { throw new DomainException('You are not allowed to update your user info'); }
        foreach ($rows as $row) {
            if ((int) $row[1] !== 1) { throw new DomainException('You are not allowed to update your user info'); }
        }
        $assignments = array(); $params = array(':username' => $username);
        foreach ($values as $field => $value) {
            $assignments[] = "$field=:$field"; $params[":$field"] = $value;
        }
        $stmt = $pdo->prepare("UPDATE $table SET " . implode(',', $assignments) . ' WHERE username=:username');
        if ($stmt === false || !$stmt->execute($params)) { throw new RuntimeException('Portal update failed'); }
        $stmt->closeCursor();
        $stored = dalo_portal_rows($pdo, 'SELECT ' . implode(',', dalo_portal_fields()) .
            " FROM $table WHERE username=:username ORDER BY id", array(':username' => $username));
        if (count($stored) !== count($rows)) { throw new RuntimeException('Portal rows changed'); }
        foreach ($stored as $row) {
            if ($row !== array_values($values)) { throw new RuntimeException('Portal information was not stored exactly'); }
        }
        if (!$pdo->commit()) { throw new RuntimeException('Portal commit failed'); }
    } finally {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
    }
}

function dalo_portal_report_query($source, $username, $config, $filters) {
    list($sql, $params) = dalo_user_export_query(array('source' => $source, 'filters' => $filters),
        dalo_portal_username($username), $config);
    return array($sql, $params);
}

function dalo_portal_report_count(PDO $pdo, $sql, $params, $source) {
    if ($source === 'acct-date') {
        // Preserve historical counting before the optional hotspot join.
        $sql = preg_replace('/SELECT .*? FROM /s', 'SELECT COUNT(ra.RadAcctId) FROM ', str_replace("\n", ' ', $sql), 1);
        $sql = preg_replace('/ LEFT JOIN .*? WHERE /s', ' WHERE ', $sql, 1);
    } else { $sql = "SELECT COUNT(*) FROM ($sql) AS portal_count"; }
    return (int) dalo_portal_rows($pdo, $sql, $params)[0][0];
}

function dalo_portal_report_rows(PDO $pdo, $sql, $params, $source, $order, $direction, $offset, $limit) {
    $orders = $source === 'acct-date' ? array('radacctid', 'hotspot', 'nasipaddress', 'framedipaddress',
        'acctstarttime', 'acctstoptime', 'acctsessiontime', 'acctinputoctets', 'acctoutputoctets', 'acctterminatecause') :
        array('id', 'date', 'totalbilled', 'totalpayed', 'status_id');
    if (!in_array($order, $orders, true) || !in_array($direction, array('asc', 'desc'), true) ||
        filter_var($offset, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0))) === false ||
        filter_var($limit, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) === false) {
        throw new InvalidArgumentException('Invalid portal pagination');
    }
    $params[':offset'] = (int) $offset; $params[':limit'] = (int) $limit;
    // SUM(DOUBLE) is wire text in PEAR, a PHP float in PDO: preserve displayed precision.
    if ($source !== 'acct-date') {
        $sql = str_replace(array('COALESCE(e2.totalpayed, 0)', 'COALESCE(d2.totalbilled, 0)'),
            array('CAST(COALESCE(e2.totalpayed, 0) AS CHAR)', 'CAST(COALESCE(d2.totalbilled, 0) AS CHAR)'), $sql);
    }
    $sort = $order;
    if ($source === 'bill-invoice-report') {
        if ($order === 'totalbilled') { $sort = 'COALESCE(d2.totalbilled,0)'; }
        if ($order === 'totalpayed') { $sort = 'COALESCE(e2.totalpayed,0)'; }
    }
    // A stable final identity prevents duplicate/missing rows across tied LIMIT pages.
    $identity = $source === 'acct-date' ? 'ra.RadAcctId' : 'a.id';
    return dalo_portal_rows($pdo, "$sql ORDER BY $sort $direction, $identity ASC LIMIT :offset, :limit", $params);
}

/** Complete invoice data is loaded before either HTML or PDF rendering starts. */
function dalo_portal_invoice(PDO $pdo, $config, $username, $invoiceId) {
    $username = dalo_portal_username($username); $invoiceId = dalo_portal_id($invoiceId);
    $tables = array();
    foreach (array('billing' => 'DALOUSERBILLINFO', 'invoice' => 'DALOBILLINGINVOICE',
        'status' => 'DALOBILLINGINVOICESTATUS', 'type' => 'DALOBILLINGINVOICETYPE',
        'items' => 'DALOBILLINGINVOICEITEMS', 'plans' => 'DALOBILLINGPLANS', 'payments' => 'DALOPAYMENTS') as $alias => $key) {
        $tables[$alias] = dalo_portal_table($pdo, $config, 'CONFIG_DB_TBL_' . $key);
    }
    extract($tables, EXTR_SKIP);
    $customer = dalo_portal_rows($pdo, "SELECT id,contactperson,city,state,username FROM $billing
        WHERE username=:username", array(':username' => $username), PDO::FETCH_ASSOC)[0] ?? null;
    if (!$customer) { return array('customer' => null, 'header' => null, 'items' => array()); }
    $header = dalo_portal_rows($pdo, "SELECT a.id,a.date,a.status_id,a.type_id,a.user_id,a.notes,
        b.contactperson,b.username,b.city,b.state,b.address,b.email,b.emailinvoice,b.phone,
        f.value AS type,c.value AS status,CAST(COALESCE(e2.totalpayed,0) AS CHAR) AS totalpayed,
        CAST(COALESCE(d2.totalbilled,0) AS CHAR) AS totalbilled
        FROM $invoice a INNER JOIN $billing b ON a.user_id=b.id
        INNER JOIN $status c ON a.status_id=c.id INNER JOIN $type f ON a.type_id=f.id
        LEFT JOIN (SELECT SUM(amount+tax_amount) AS totalbilled,invoice_id,plan_id
            FROM $items GROUP BY invoice_id) d2 ON d2.invoice_id=a.id
        LEFT JOIN $plans bp2 ON bp2.id=d2.plan_id
        LEFT JOIN (SELECT SUM(amount) AS totalpayed,invoice_id FROM $payments GROUP BY invoice_id) e2 ON e2.invoice_id=a.id
        WHERE a.id=:id AND a.user_id=:user_id AND b.username=:username GROUP BY a.id",
        array(':id' => $invoiceId, ':user_id' => (int) $customer['id'], ':username' => $username), PDO::FETCH_ASSOC)[0] ?? null;
    $rows = $header ? dalo_portal_rows($pdo, "SELECT a.id,a.plan_id,a.amount,a.tax_amount,a.notes,b.planName
        FROM $items a LEFT JOIN $plans b ON a.plan_id=b.id WHERE a.invoice_id=:id ORDER BY a.id ASC",
        array(':id' => $invoiceId), PDO::FETCH_ASSOC) : array();
    return array('customer' => $customer, 'header' => $header, 'items' => $rows);
}

function dalo_portal_statuses(PDO $pdo, $config) {
    $table = dalo_portal_table($pdo, $config, 'CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS');
    return dalo_portal_rows($pdo, "SELECT id,value FROM $table ORDER BY value ASC");
}
