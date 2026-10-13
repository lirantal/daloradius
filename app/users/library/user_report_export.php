<?php
/* Fixed, user-scoped PDO queries for portal CSV exports (UNIT-004). */

function dalo_user_export_table($configValues, $key) {
    $allowed = array('CONFIG_DB_TBL_RADACCT', 'CONFIG_DB_TBL_DALOHOTSPOTS',
                     'CONFIG_DB_TBL_DALOBILLINGINVOICE', 'CONFIG_DB_TBL_DALOUSERBILLINFO',
                     'CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS',
                     'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS', 'CONFIG_DB_TBL_DALOPAYMENTS');
    if (!in_array($key, $allowed, true) || !isset($configValues[$key]) ||
        !is_string($configValues[$key]) ||
        preg_match('/\A[A-Za-z_][A-Za-z0-9_]*\z/D', $configValues[$key]) !== 1) {
        throw new InvalidArgumentException('Invalid export table configuration');
    }
    return '`' . $configValues[$key] . '`';
}

function dalo_user_export_date($filters, $key) {
    if (!array_key_exists($key, $filters)) {
        return '';
    }
    $value = $filters[$key];
    if (!is_string($value)) {
        throw new InvalidArgumentException('Invalid export date');
    }
    if ($value !== '') {
        if (preg_match('/\A([0-9]{4})-([0-9]{2})-([0-9]{2})\z/D', $value, $matches) !== 1 ||
            !checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1])) {
            throw new InvalidArgumentException('Invalid export date');
        }
    }
    return $value;
}

/** @return array{0:string,1:array,2:array,3:array} SQL, bindings, header, numeric column positions. */
function dalo_user_export_query($descriptor, $username, $configValues) {
    if (!is_array($descriptor) || !isset($descriptor['source'], $descriptor['filters']) ||
        !is_string($descriptor['source']) || !is_array($descriptor['filters']) ||
        !is_string($username) || $username === '' || !is_array($configValues)) {
        throw new InvalidArgumentException('Invalid user export descriptor');
    }
    $source = $descriptor['source'];
    $filters = $descriptor['filters'];
    $allowed = array(
        'acct-date' => array('startdate', 'enddate'),
        'bill-invoice-report' => array('startdate', 'enddate', 'invoice_status'),
    );
    if (!isset($allowed[$source])) {
        throw new InvalidArgumentException('Unsupported user export source');
    }
    foreach (array_keys($filters) as $key) {
        if (!is_string($key) || !in_array($key, $allowed[$source], true)) {
            throw new InvalidArgumentException('Unsupported user export filter');
        }
    }
    $startdate = dalo_user_export_date($filters, 'startdate');
    $enddate = dalo_user_export_date($filters, 'enddate');
    $params = array(':username' => $username);

    if ($source === 'acct-date') {
        $acct = dalo_user_export_table($configValues, 'CONFIG_DB_TBL_RADACCT');
        $hotspots = dalo_user_export_table($configValues, 'CONFIG_DB_TBL_DALOHOTSPOTS');
        $sql = "SELECT ra.RadAcctId, dhs.name AS hotspot, ra.NASIPAddress, ra.FramedIPAddress,\n"
             . "       ra.AcctStartTime, ra.AcctStopTime, ra.AcctSessionTime, ra.AcctInputOctets,\n"
             . "       ra.AcctOutputOctets, ra.AcctTerminateCause\n"
             . "  FROM $acct AS ra LEFT JOIN $hotspots AS dhs ON ra.calledstationid=dhs.mac\n"
             . " WHERE ra.username=:username";
        // Preserve the user portal's exclusive date boundaries (unlike the operator report).
        if ($startdate !== '') {
            $sql .= ' AND ra.AcctStartTime > :startdate';
            $params[':startdate'] = $startdate;
        }
        if ($enddate !== '') {
            $sql .= ' AND ra.AcctStartTime < :enddate';
            $params[':enddate'] = $enddate;
        }
        return array($sql, $params,
            array('radacctid', 'hotspot', 'nasipaddress', 'framedipaddress', 'acctstarttime',
                  'acctstoptime', 'acctsessiontime', 'acctinputoctets', 'acctoutputoctets',
                  'acctterminatecause'), array(0, 6, 7, 8));
    }

    $status = $filters['invoice_status'] ?? '';
    if (!is_string($status)) {
        throw new InvalidArgumentException('Invalid invoice status filter');
    }
    $invoice = dalo_user_export_table($configValues, 'CONFIG_DB_TBL_DALOBILLINGINVOICE');
    $billing = dalo_user_export_table($configValues, 'CONFIG_DB_TBL_DALOUSERBILLINFO');
    $statuses = dalo_user_export_table($configValues, 'CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS');
    $items = dalo_user_export_table($configValues, 'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS');
    $payments = dalo_user_export_table($configValues, 'CONFIG_DB_TBL_DALOPAYMENTS');
    $sql = "SELECT a.id, a.date, c.value AS status, COALESCE(e2.totalpayed, 0) AS totalpayed,\n"
         . "       COALESCE(d2.totalbilled, 0) AS totalbilled\n"
         . "  FROM $invoice AS a INNER JOIN $billing AS b ON a.user_id=b.id\n"
         . "  INNER JOIN $statuses AS c ON a.status_id=c.id\n"
         . "  LEFT JOIN (SELECT SUM(d.amount + d.tax_amount) AS totalbilled, invoice_id\n"
         . "               FROM $items AS d GROUP BY d.invoice_id) AS d2 ON d2.invoice_id=a.id\n"
         . "  LEFT JOIN (SELECT SUM(e.amount) AS totalpayed, invoice_id\n"
         . "               FROM $payments AS e GROUP BY e.invoice_id) AS e2 ON e2.invoice_id=a.id\n"
         . " WHERE b.username=:username";
    if ($startdate !== '') {
        $sql .= ' AND a.date >= :startdate';
        $params[':startdate'] = $startdate;
    }
    if ($enddate !== '') {
        $sql .= ' AND a.date <= :enddate';
        $params[':enddate'] = $enddate;
    }
    if ($status !== '') {
        $sql .= ' AND a.status_id = :status';
        $params[':status'] = $status;
    }
    $sql .= ' GROUP BY a.id';
    return array($sql, $params,
        array('id', 'date', 'status', 'totalpayed', 'totalbilled'), array(0, 3, 4));
}

/** Preserve numeric values; stop spreadsheet formulas in text fields. */
function dalo_user_export_csv_row($row, $numericColumns) {
    $fields = array();
    foreach ($row as $index => $value) {
        $field = ($value === null) ? '' : (string) $value;
        $prefix = ltrim($field, " \t\r\n");
        if ($prefix !== '' && strpbrk($prefix[0], '=+-@') !== false &&
            !(in_array($index, $numericColumns, true) &&
              preg_match('/\A[+-]?(?:[0-9]+(?:\.[0-9]*)?|\.[0-9]+)\z/D', $field) === 1)) {
            $field = "'" . $field;
        }
        $fields[] = $field;
    }
    return $fields;
}
