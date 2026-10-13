<?php
/*
 * Parameterized batch and invoice report export queries (UNIT-003).
 *
 * Producers store only the source/type and validated scalar filters in the
 * session. Table identifiers come from the parent export whitelist helper;
 * values are always returned separately as PDO named bindings.
 */

function dalo_export_batch_query($source, $type, $filters, $configValues) {
    if (!is_string($source) || !is_string($type) || !is_array($filters) || !is_array($configValues)) {
        throw new InvalidArgumentException('Invalid batch export arguments');
    }

    $supported = array(
        'rep-batch-list' => array('reportsBatchList'),
        'mng-batch-list' => array('reportsBatchList'),
        'rep-batch-details' => array('reportsBatchActiveUsers', 'reportsBatchTotalUsers'),
        'bill-invoice-report' => array('reportsInvoiceList'),
    );
    if (!isset($supported[$source]) || !in_array($type, $supported[$source], true)) {
        throw new InvalidArgumentException('Unsupported batch export source/type');
    }

    $allowedFilters = array(
        'reportsBatchList' => array(),
        'reportsBatchActiveUsers' => array('batch_id', 'username'),
        'reportsBatchTotalUsers' => array('batch_id', 'username'),
        'reportsInvoiceList' => array('startdate', 'enddate', 'username', 'invoice_status'),
    );
    $unknownFilters = array_diff(array_keys($filters), $allowedFilters[$type]);
    if (count($unknownFilters) > 0) {
        throw new InvalidArgumentException('Unsupported batch export filter');
    }

    $table = function ($key) use ($configValues) {
        return dalo_export_table($configValues, $key);
    };

    if ($type === 'reportsBatchList') {
        if (count($filters) !== 0) {
            throw new InvalidArgumentException('Batch list does not accept filters');
        }

        $batchHistory = $table('CONFIG_DB_TBL_DALOBATCHHISTORY');
        $userBilling = $table('CONFIG_DB_TBL_DALOUSERBILLINFO');
        $billingPlans = $table('CONFIG_DB_TBL_DALOBILLINGPLANS');
        $hotspots = $table('CONFIG_DB_TBL_DALOHOTSPOTS');
        $activeUsers = 'NULL AS active_users';
        $accountingJoin = '';
        // The management page's legacy export had no accounting join and
        // emitted an empty active-users field with a zero batch cost.
        if ($source === 'rep-batch-list') {
            $radAcct = $table('CONFIG_DB_TBL_RADACCT');
            $activeUsers = 'COUNT(DISTINCT(ra.username)) AS active_users';
            $accountingJoin = "  LEFT JOIN $radAcct AS ra ON ra.username=ubi.username\n";
        }

        $sql = "SELECT bh.id, bh.batch_name, bh.batch_description, bh.batch_status,\n"
             . "       COUNT(DISTINCT(ubi.id)) AS total_users,\n"
             . "       $activeUsers, ubi.planname,\n"
             . "       bp.plancost, bp.plancurrency, hs.name AS HotspotName,\n"
             . "       bh.creationdate, bh.creationby, bh.updatedate, bh.updateby\n"
             . "  FROM $batchHistory AS bh\n"
             . "  LEFT JOIN $userBilling AS ubi ON bh.id=ubi.batch_id\n"
             . "  LEFT JOIN $billingPlans AS bp ON bp.planname=ubi.planname\n"
             . $accountingJoin
             . "  LEFT JOIN $hotspots AS hs ON bh.hotspot_id=hs.id\n"
             . " GROUP BY bh.batch_name";

        return array($sql, array());
    }

    if ($type === 'reportsBatchActiveUsers' || $type === 'reportsBatchTotalUsers') {
        if (!array_key_exists('batch_id', $filters) || !is_int($filters['batch_id']) || $filters['batch_id'] <= 0) {
            throw new InvalidArgumentException('Invalid batch identifier');
        }
        if (array_key_exists('username', $filters) && !is_string($filters['username'])) {
            throw new InvalidArgumentException('Invalid batch username filter');
        }

        $batchId = $filters['batch_id'];
        $username = $filters['username'] ?? '';
        $bindings = array(':batch_id' => $batchId);
        $usernameWhere = '';
        if ($username !== '' && $type === 'reportsBatchActiveUsers') {
            $bindings[':username'] = '%' . $username . '%';
            $usernameWhere = "\n   AND ubi.username LIKE :username";
        }

        $userBilling = $table('CONFIG_DB_TBL_DALOUSERBILLINFO');
        $radAcct = $table('CONFIG_DB_TBL_RADACCT');
        $batchHistory = $table('CONFIG_DB_TBL_DALOBATCHHISTORY');

        if ($type === 'reportsBatchActiveUsers') {
            $sql = "SELECT bh.batch_name AS batch_name, ubi.username AS username,\n"
                 . "       IFNULL(ra.acctstarttime, '0') AS status,\n"
                 . "       MIN(ra.acctstarttime) AS acctstarttime\n"
                 . "  FROM $userBilling AS ubi\n"
                 . "  LEFT JOIN $radAcct AS ra ON ubi.username=ra.username\n"
                 . "  INNER JOIN $batchHistory AS bh ON ubi.batch_id=bh.id\n"
                 . " WHERE ubi.batch_id=:batch_id$usernameWhere\n"
                 . " GROUP BY ubi.username";
            return array($sql, $bindings);
        }

        $radCheck = $table('CONFIG_DB_TBL_RADCHECK');
        // The parent formatter separately looks up batch_name for its header;
        // this row query intentionally returns only username/attribute/value.
        $sql = "SELECT ubi.username AS username, rc.attribute AS attribute, rc.value AS value\n"
             . "  FROM $batchHistory AS bh\n"
             . "  INNER JOIN $userBilling AS ubi ON ubi.batch_id=bh.id\n"
             . "  INNER JOIN $radCheck AS rc ON rc.username=ubi.username\n"
             . " WHERE bh.id=:batch_id\n"
             . "   AND rc.op=':='\n"
             . "   AND (rc.attribute='Auth-Type' OR rc.attribute LIKE '%-Password')";
        return array($sql, $bindings);
    }

    foreach (array('startdate', 'enddate', 'username', 'invoice_status') as $filter) {
        if (array_key_exists($filter, $filters) && !is_string($filters[$filter])) {
            throw new InvalidArgumentException('Invalid invoice filter');
        }
    }

    $invoice = $table('CONFIG_DB_TBL_DALOBILLINGINVOICE');
    $userBilling = $table('CONFIG_DB_TBL_DALOUSERBILLINFO');
    $invoiceStatus = $table('CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS');
    $invoiceItems = $table('CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS');
    $payments = $table('CONFIG_DB_TBL_DALOPAYMENTS');

    $where = array();
    $bindings = array();
    foreach (array('startdate', 'enddate') as $dateFilter) {
        if (!empty($filters[$dateFilter])) {
            if (preg_match('/\A\d{4}-\d{2}-\d{2}\z/D', $filters[$dateFilter]) !== 1) {
                throw new InvalidArgumentException('Invalid invoice date filter');
            }
            $parts = explode('-', $filters[$dateFilter]);
            if (!checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0])) {
                throw new InvalidArgumentException('Invalid invoice date filter');
            }
            $placeholder = ':' . $dateFilter;
            $where[] = "a.date " . ($dateFilter === 'startdate' ? '>=' : '<=') . " $placeholder";
            $bindings[$placeholder] = $filters[$dateFilter];
        }
    }
    if (($filters['username'] ?? '') !== '') {
        $where[] = 'b.username LIKE :username';
        $bindings[':username'] = '%' . $filters['username'] . '%';
    }
    if (($filters['invoice_status'] ?? '') !== '') {
        $where[] = 'a.status_id = :invoice_status';
        $bindings[':invoice_status'] = $filters['invoice_status'];
    }

    $sql = "SELECT a.id, a.date, a.status_id, a.type_id, b.contactperson, b.username,\n"
         . "       c.value AS status, COALESCE(e2.totalpayed, 0) AS totalpayed,\n"
         . "       COALESCE(d2.totalbilled, 0) AS totalbilled\n"
         . "  FROM $invoice AS a\n"
         . "  INNER JOIN $userBilling AS b ON a.user_id=b.id\n"
         . "  INNER JOIN $invoiceStatus AS c ON a.status_id=c.id\n"
         . "  LEFT JOIN (SELECT SUM(d.amount + d.tax_amount) AS totalbilled, invoice_id\n"
         . "               FROM $invoiceItems AS d GROUP BY d.invoice_id) AS d2\n"
         . "    ON d2.invoice_id=a.id\n"
         . "  LEFT JOIN (SELECT SUM(e.amount) AS totalpayed, invoice_id\n"
         . "               FROM $payments AS e GROUP BY e.invoice_id) AS e2\n"
         . "    ON e2.invoice_id=a.id";
    if (count($where) > 0) {
        $sql .= "\n WHERE " . implode(" AND ", $where);
    }
    $sql .= "\n GROUP BY a.id";

    return array($sql, $bindings);
}
