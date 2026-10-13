<?php
/* Fixed, parameterized builders for accounting and plan-usage exports. */

function dalo_export_accounting_assert_filters($filters, $allowed) {
    if (!is_array($filters)) {
        throw new InvalidArgumentException('Invalid export filters');
    }

    foreach (array_keys($filters) as $key) {
        if (!is_string($key) || !in_array($key, $allowed, true)) {
            throw new InvalidArgumentException('Unsupported export filter');
        }
    }
}

function dalo_export_accounting_string_filter($filters, $key) {
    if (!array_key_exists($key, $filters)) {
        return null;
    }
    if (!is_string($filters[$key])) {
        throw new InvalidArgumentException('Invalid export filter value');
    }
    return $filters[$key];
}

function dalo_export_accounting_date_filter($filters, $key) {
    $value = dalo_export_accounting_string_filter($filters, $key);
    if ($value === null || $value === '') {
        return $value;
    }

    if (preg_match('/\\A([0-9]{4})-([0-9]{2})-([0-9]{2})\\z/D', $value, $matches) !== 1 ||
        !checkdate((int)$matches[2], (int)$matches[3], (int)$matches[1])) {
        throw new InvalidArgumentException('Invalid export date');
    }
    return $value;
}

function dalo_export_accounting_hotspots($filters) {
    if (!array_key_exists('hotspot', $filters)) {
        return array();
    }
    if (!is_array($filters['hotspot'])) {
        throw new InvalidArgumentException('Invalid hotspot filter');
    }

    foreach ($filters['hotspot'] as $value) {
        if (!is_string($value)) {
            throw new InvalidArgumentException('Invalid hotspot filter value');
        }
    }
    return array_values($filters['hotspot']);
}

function dalo_export_accounting_add_scalar_filters(&$where, &$bindings, $filters, $prefix, $columns) {
    foreach ($columns as $key => $column) {
        $value = dalo_export_accounting_string_filter($filters, $key);
        if ($value === null || $value === '') {
            continue;
        }
        $name = ':' . $prefix . '_' . $key;
        $where[] = $column . ' = ' . $name;
        $bindings[$name] = $value;
    }
}

function dalo_export_accounting_query($source, $type, $filters, $configValues) {
    if (!is_string($source) || !is_string($type) || !is_array($configValues)) {
        throw new InvalidArgumentException('Invalid export descriptor');
    }

    $accountingSources = array(
        'acct-all',
        'acct-date',
        'acct-hotspot-accounting',
        'acct-ipaddress',
        'acct-nasipaddress',
        'acct-username',
    );
    if (in_array($source, $accountingSources, true)) {
        if ($type !== 'accountingGeneric') {
            throw new InvalidArgumentException('Unsupported accounting export type');
        }
        return dalo_export_accounting_generic_query($source, $filters, $configValues);
    }

    if ($source === 'acct-plans-usage') {
        if ($type !== 'reportsPlansUsage') {
            throw new InvalidArgumentException('Unsupported plan usage export type');
        }
        return dalo_export_accounting_plans_usage_query($filters, $configValues);
    }

    throw new InvalidArgumentException('Unsupported accounting export source');
}

function dalo_export_accounting_generic_query($source, $filters, $configValues) {
    $allowed = array();
    switch ($source) {
        case 'acct-all':
            $allowed = array();
            break;
        case 'acct-date':
            $allowed = array('startdate', 'enddate', 'username');
            break;
        case 'acct-hotspot-accounting':
            $allowed = array('hotspot');
            break;
        case 'acct-ipaddress':
            $allowed = array('ipaddress');
            break;
        case 'acct-nasipaddress':
            $allowed = array('nasipaddress');
            break;
        case 'acct-username':
            $allowed = array('username');
            break;
        default:
            throw new InvalidArgumentException('Unsupported accounting export source');
    }
    dalo_export_accounting_assert_filters($filters, $allowed);

    $radacct = dalo_export_table($configValues, 'CONFIG_DB_TBL_RADACCT');
    $hotspots = dalo_export_table($configValues, 'CONFIG_DB_TBL_DALOHOTSPOTS');
    $where = array();
    $bindings = array();

    if (in_array('startdate', $allowed, true)) {
        $startdate = dalo_export_accounting_date_filter($filters, 'startdate');
        if ($startdate !== null && $startdate !== '') {
            $where[] = 'ra.AcctStartTime >= :acct_startdate';
            $bindings[':acct_startdate'] = $startdate;
        }
    }
    if (in_array('enddate', $allowed, true)) {
        $enddate = dalo_export_accounting_date_filter($filters, 'enddate');
        if ($enddate !== null && $enddate !== '') {
            $where[] = 'ra.AcctStartTime < (:acct_enddate + INTERVAL 1 DAY)';
            $bindings[':acct_enddate'] = $enddate;
        }
    }
    if (in_array('username', $allowed, true)) {
        dalo_export_accounting_add_scalar_filters(
            $where,
            $bindings,
            $filters,
            'acct',
            array('username' => 'ra.UserName')
        );
    }
    if ($source === 'acct-ipaddress') {
        $ipaddress = dalo_export_accounting_string_filter($filters, 'ipaddress');
        if ($ipaddress !== null && $ipaddress !== '') {
            $where[] = 'ra.FramedIPAddress LIKE :acct_ipaddress';
            $bindings[':acct_ipaddress'] = '%' . $ipaddress . '%';
        }
    }
    if ($source === 'acct-nasipaddress') {
        $nasipaddress = dalo_export_accounting_string_filter($filters, 'nasipaddress');
        if ($nasipaddress !== null && $nasipaddress !== '') {
            $where[] = 'ra.NASIPAddress LIKE :acct_nasipaddress';
            $bindings[':acct_nasipaddress'] = '%' . $nasipaddress . '%';
        }
    }
    if ($source === 'acct-hotspot-accounting') {
        $hotspotValues = dalo_export_accounting_hotspots($filters);
        if (count($hotspotValues) > 0) {
            $placeholders = array();
            foreach ($hotspotValues as $index => $hotspot) {
                $name = ':acct_hotspot_' . $index;
                $placeholders[] = $name;
                $bindings[$name] = $hotspot;
            }
            $where[] = 'hs.name IN (' . implode(', ', $placeholders) . ')';
        }
    }

    $sql = 'SELECT ra.RadAcctId, hs.name AS hotspot, ra.UserName, ra.FramedIPAddress, '
         . 'ra.AcctStartTime, ra.AcctStopTime, ra.AcctSessionTime, ra.AcctInputOctets, '
         . 'ra.AcctOutputOctets, ra.AcctTerminateCause, ra.NASIPAddress '
         . 'FROM ' . $radacct . ' AS ra '
         . 'LEFT JOIN ' . $hotspots . ' AS hs ON ra.calledstationid = hs.mac';
    if (count($where) > 0) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY ra.RadAcctId DESC';

    return array($sql, $bindings);
}

function dalo_export_accounting_plans_usage_query($filters, $configValues) {
    dalo_export_accounting_assert_filters(
        $filters,
        array('username', 'planname', 'startdate', 'enddate')
    );

    $userBillInfo = dalo_export_table($configValues, 'CONFIG_DB_TBL_DALOUSERBILLINFO');
    $radacct = dalo_export_table($configValues, 'CONFIG_DB_TBL_RADACCT');
    $billingPlans = dalo_export_table($configValues, 'CONFIG_DB_TBL_DALOBILLINGPLANS');
    $where = array('ubi.username = ra.username', 'ubi.planname = bp.planname');
    $bindings = array();

    dalo_export_accounting_add_scalar_filters(
        $where,
        $bindings,
        $filters,
        'plan',
        array(
            'username' => 'ubi.username',
            'planname' => 'bp.planname',
        )
    );

    $startdate = dalo_export_accounting_date_filter($filters, 'startdate');
    if ($startdate !== null && $startdate !== '') {
        $where[] = 'ra.AcctStartTime >= :plan_startdate';
        $bindings[':plan_startdate'] = $startdate;
    }
    $enddate = dalo_export_accounting_date_filter($filters, 'enddate');
    if ($enddate !== null && $enddate !== '') {
        $where[] = 'ra.AcctStartTime < (:plan_enddate + INTERVAL 1 DAY)';
        $bindings[':plan_enddate'] = $enddate;
    }

    $sql = 'SELECT ubi.username AS username, ubi.planname AS planname, '
         . 'SUM(ra.acctsessiontime) AS sessiontime, SUM(ra.acctinputoctets) AS upload, '
         . 'SUM(ra.acctoutputoctets) AS download, bp.plantimebank AS planTimeBank, '
         . 'bp.planTimeType AS planTimeType FROM ' . $userBillInfo . ' AS ubi '
         . 'INNER JOIN ' . $radacct . ' AS ra ON ubi.username = ra.username '
         . 'INNER JOIN ' . $billingPlans . ' AS bp ON ubi.planname = bp.planname '
         . 'WHERE ' . implode(' AND ', $where)
         . ' GROUP BY ubi.username ORDER BY username ASC';

    return array($sql, $bindings);
}
