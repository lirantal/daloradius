<?php

/**
 * Build an unpaginated PDO export query for operator report pages.
 *
 * The source/type pair is deliberately allowlisted because the descriptor is
 * carried in the session and must never be able to select arbitrary SQL.
 *
 * @return array{0: string, 1: array<string, scalar>}
 * @throws InvalidArgumentException
 */
function dalo_export_reports_query($source, $type, $filters, $configValues)
{
    $sourceTypes = array(
        'rep-online' => 'reportsOnlineUsers',
        'rep-lastconnect' => 'reportsLastConnectionAttempts',
        'rep-topusers' => 'TopUsers',
    );

    if (!is_string($source) || !is_string($type) ||
        !array_key_exists($source, $sourceTypes) || $sourceTypes[$source] !== $type) {
        throw new InvalidArgumentException('Unsupported reports export source/type pair');
    }

    if (!is_array($filters)) {
        throw new InvalidArgumentException('Reports export filters must be an array');
    }

    $validateFilters = static function ($allowedFilters) use ($filters) {
        foreach ($filters as $filter => $value) {
            if (!is_string($filter) || !in_array($filter, $allowedFilters, true) ||
                !is_scalar($value) || is_bool($value)) {
                throw new InvalidArgumentException('Unsupported reports export filter');
            }
        }
    };

    $validateDate = static function ($name, $value) {
        if (!is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value) !== 1) {
            throw new InvalidArgumentException(sprintf('Invalid reports export %s', $name));
        }

        $parts = explode('-', $value);
        if (!checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0])) {
            throw new InvalidArgumentException(sprintf('Invalid reports export %s', $name));
        }
    };

    if ($source === 'rep-online') {
        $validateFilters(array('username'));
        $radacct = dalo_export_table($configValues, 'CONFIG_DB_TBL_RADACCT');
        $params = array();
        $where = array("(ra.AcctStopTime IS NULL OR ra.AcctStopTime='0000-00-00 00:00:00')");

        if (array_key_exists('username', $filters)) {
            if (!is_string($filters['username'])) {
                throw new InvalidArgumentException('Invalid reports export username');
            }
            if ($filters['username'] !== '') {
                $where[] = 'ra.username LIKE :online_username';
                $params[':online_username'] = '%' . $filters['username'] . '%';
            }
        }

        $sql = "SELECT ra.username AS username,
                       ra.framedipaddress AS framedipaddress,
                       ra.callingstationid AS callingstationid,
                       ra.acctstarttime AS acctstarttime,
                       ra.acctsessiontime AS acctsessiontime,
                       ra.nasipaddress AS nasipaddress,
                       ra.calledstationid AS calledstationid
                  FROM " . $radacct . " AS ra
                 WHERE " . implode(' AND ', $where) . "
              ORDER BY ra.username ASC";

        return array($sql, $params);
    }

    if ($source === 'rep-lastconnect') {
        $validateFilters(array('startdate', 'enddate', 'username', 'radiusReply'));

        foreach (array('startdate', 'enddate') as $dateFilter) {
            if (array_key_exists($dateFilter, $filters)) {
                $validateDate($dateFilter, $filters[$dateFilter]);
            }
        }
        if (array_key_exists('username', $filters) && !is_string($filters['username'])) {
            throw new InvalidArgumentException('Invalid reports export username');
        }
        if (array_key_exists('radiusReply', $filters) &&
            (!is_string($filters['radiusReply']) || $filters['radiusReply'] === '')) {
            throw new InvalidArgumentException('Invalid reports export RADIUS reply');
        }

        $postauth = dalo_export_table($configValues, 'CONFIG_DB_TBL_RADPOSTAUTH');
        $userinfo = dalo_export_table($configValues, 'CONFIG_DB_TBL_DALOUSERINFO');
        $postauthUser = 'username';
        $postauthDate = 'authdate';
        if (isset($configValues['FREERADIUS_VERSION']) && (string) $configValues['FREERADIUS_VERSION'] === '1') {
            $postauthUser = 'user';
            $postauthDate = 'date';
        }

        $params = array();
        $where = array();
        if (array_key_exists('username', $filters) && $filters['username'] !== '') {
            $where[] = 'pa.' . $postauthUser . ' LIKE :lastconnect_username';
            $params[':lastconnect_username'] = '%' . $filters['username'] . '%';
        }
        if (array_key_exists('startdate', $filters)) {
            $where[] = 'pa.' . $postauthDate . ' >= :lastconnect_startdate';
            $params[':lastconnect_startdate'] = $filters['startdate'];
        }
        if (array_key_exists('enddate', $filters)) {
            $where[] = 'pa.' . $postauthDate . " < (:lastconnect_enddate + INTERVAL 1 DAY)";
            $params[':lastconnect_enddate'] = $filters['enddate'];
        }
        if (array_key_exists('radiusReply', $filters) && $filters['radiusReply'] !== 'Any') {
            $where[] = 'pa.reply = :lastconnect_radius_reply';
            $params[':lastconnect_radius_reply'] = $filters['radiusReply'];
        }

        $sql = "SELECT pa." . $postauthUser . " AS username,
                       IF(STRCMP(CONCAT(ui.firstname, ' ', ui.lastname), ' ') = 0,
                          '(n/a)', CONCAT(ui.firstname, ' ', ui.lastname)) AS fullname,
                       pa." . $postauthDate . " AS starttime,
                       pa.reply AS reply
                  FROM " . $postauth . " AS pa
             LEFT JOIN " . $userinfo . " AS ui ON pa." . $postauthUser . " = ui.username";
        if (count($where) > 0) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY pa." . $postauthDate . " DESC";

        return array($sql, $params);
    }

    $validateFilters(array('startdate', 'enddate', 'username'));
    foreach (array('startdate', 'enddate') as $dateFilter) {
        if (array_key_exists($dateFilter, $filters)) {
            $validateDate($dateFilter, $filters[$dateFilter]);
        }
    }
    if (array_key_exists('username', $filters) && !is_string($filters['username'])) {
        throw new InvalidArgumentException('Invalid reports export username');
    }

    $radacct = dalo_export_table($configValues, 'CONFIG_DB_TBL_RADACCT');
    $params = array();
    $where = array("ra.AcctStopTime > '0000-00-00 00:00:01'");
    if (array_key_exists('startdate', $filters)) {
        $where[] = 'ra.AcctStartTime >= :topusers_startdate';
        $params[':topusers_startdate'] = $filters['startdate'];
    }
    if (array_key_exists('enddate', $filters)) {
        $where[] = "ra.AcctStartTime < (:topusers_enddate + INTERVAL 1 DAY)";
        $params[':topusers_enddate'] = $filters['enddate'];
    }
    if (array_key_exists('username', $filters) && $filters['username'] !== '') {
        $where[] = 'ra.UserName LIKE :topusers_username';
        $params[':topusers_username'] = '%' . $filters['username'] . '%';
    }

    $sql = "SELECT DISTINCT ra.UserName AS username,
                           ra.FramedIPAddress AS framedipaddress,
                           ra.AcctStartTime AS acctstarttime,
                           ra.AcctStopTime AS acctstoptime,
                           SUM(ra.AcctSessionTime) AS Time,
                           SUM(ra.AcctInputOctets) AS Upload,
                           SUM(ra.AcctOutputOctets) AS Download,
                           ra.AcctTerminateCause AS acctterminatecause,
                           ra.NASIPAddress AS nasipaddress,
                           SUM(ra.AcctInputOctets + ra.AcctOutputOctets) AS Bandwidth
                      FROM " . $radacct . " AS ra
                     WHERE " . implode(' AND ', $where) . "
                  GROUP BY ra.UserName ASC
                  ORDER BY ra.UserName ASC";

    return array($sql, $params);
}
