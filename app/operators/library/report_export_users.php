<?php
/**
 * Build the unpaginated users export query and its bound values.
 *
 * The source is the page/report descriptor basename, not a table or SQL
 * expression. Table names are resolved through the configuration whitelist.
 *
 * @return array{0:string,1:array}
 * @throws InvalidArgumentException for an unsupported descriptor or filter
 */
function dalo_export_users_query($source, $type, $filters, $configValues) {
    if (!is_string($source) || !is_string($type) || !is_array($filters) || !is_array($configValues)) {
        throw new InvalidArgumentException('Invalid users export descriptor');
    }

    $allowedFilters = array();
    switch ($source) {
        case 'mng-list-all':
            if ($type !== 'usernameListGeneric') {
                throw new InvalidArgumentException('Unsupported users export source/type pair');
            }
            break;

        case 'mng-search':
            if ($type !== 'usernameListGeneric') {
                throw new InvalidArgumentException('Unsupported users export source/type pair');
            }
            $allowedFilters = array('username');
            break;

        // This is also the direct GET report source used by the existing
        // mng-rad-profiles-list.php export link.
        case 'mng-rad-profiles-list':
        case 'usernameListByGroup':
            if ($type !== 'usernameListByGroup') {
                throw new InvalidArgumentException('Unsupported users export source/type pair');
            }
            $allowedFilters = array('groupname');
            break;

        default:
            throw new InvalidArgumentException('Unsupported users export source');
    }

    foreach (array_keys($filters) as $filter) {
        if (!in_array($filter, $allowedFilters, true)) {
            throw new InvalidArgumentException('Unsupported users export filter');
        }
    }

    $username = '';
    if ($source === 'mng-search' && array_key_exists('username', $filters)) {
        if (!is_string($filters['username'])) {
            throw new InvalidArgumentException('Invalid users export username filter');
        }
        $username = $filters['username'];
    }

    $groupname = null;
    if (in_array($source, array('mng-rad-profiles-list', 'usernameListByGroup'), true)) {
        if (!array_key_exists('groupname', $filters) || !is_string($filters['groupname']) ||
            trim($filters['groupname']) === '') {
            throw new InvalidArgumentException('Invalid users export group filter');
        }
        $groupname = trim($filters['groupname']);
    }

    $radcheck = dalo_export_table($configValues, 'CONFIG_DB_TBL_RADCHECK');
    $radreply = dalo_export_table($configValues, 'CONFIG_DB_TBL_RADREPLY');
    $userinfo = dalo_export_table($configValues, 'CONFIG_DB_TBL_DALOUSERINFO');

    $params = array();
    $where = array();

    if (in_array($source, array('mng-rad-profiles-list', 'usernameListByGroup'), true)) {
        $radusergroup = dalo_export_table($configValues, 'CONFIG_DB_TBL_RADUSERGROUP');
        $from = sprintf(
            '%s AS rug INNER JOIN %s AS rc ON rc.username = rug.username LEFT JOIN %s AS ui ON rc.username = ui.username',
            $radusergroup,
            $radcheck,
            $userinfo
        );
        $where[] = 'rug.groupname = :groupname';
        $params[':groupname'] = $groupname;
    } elseif ($source === 'mng-search') {
        $radacct = dalo_export_table($configValues, 'CONFIG_DB_TBL_RADACCT');
        $from = sprintf(
            '%s AS rc LEFT JOIN %s AS ra ON ra.username=rc.username LEFT JOIN %s AS rr ON rr.username=rc.username INNER JOIN %s AS ui ON ui.username=rc.username',
            $radcheck,
            $radacct,
            $radreply,
            $userinfo
        );
    } else {
        $from = sprintf('%s AS rc INNER JOIN %s AS ui ON rc.username = ui.username', $radcheck, $userinfo);
    }

    if (!in_array($source, array('mng-rad-profiles-list', 'usernameListByGroup'), true)) {
        $where[] = "(rc.attribute='Auth-Type' OR rc.attribute LIKE '%-Password')";
    }

    if ($source === 'mng-search' && $username !== '') {
        $searchFields = array('username', 'firstname', 'lastname', 'homephone', 'workphone', 'mobilephone');
        $searchConditions = array();
        foreach ($searchFields as $field) {
            $parameter = 'search_' . $field;
            $searchConditions[] = 'ui.' . $field . ' LIKE :' . $parameter;
            $params[':' . $parameter] = '%' . $username . '%';
        }
        $searchConditions[] = 'rr.value LIKE :search_reply_value';
        // The legacy mng-search query used a trailing-only wildcard for the
        // reply value; keep that behavior for export compatibility.
        $params[':search_reply_value'] = '%' . $username;
        $where[] = '(' . implode(' OR ', $searchConditions) . ')';
    }

    $sql = sprintf(
        "SELECT rc.username AS username,
                COALESCE(MAX(CASE WHEN rc_export.attribute LIKE '%%-Password' THEN rc_export.value END),
                         MAX(CASE WHEN rc_export.attribute='Auth-Type' THEN rc_export.value END), '') AS password,
                COALESCE(MAX(ui.email), '') AS email,
                COALESCE(MAX(ui.firstname), '') AS firstname,
                COALESCE(MAX(ui.lastname), '') AS lastname,
                COALESCE(MAX(CASE WHEN rr_export.attribute='Framed-IP-Address' THEN rr_export.value END), '') AS framedipaddress,
                COALESCE(MAX(CASE WHEN rc_export.attribute='Expiration' THEN rc_export.value END), '') AS expiration,
                COALESCE(MAX(ui.department), '') AS department,
                COALESCE(MAX(ui.company), '') AS company,
                COALESCE(MAX(ui.mobilephone), '') AS mobilephone,
                COALESCE(MAX(ui.workphone), '') AS workphone,
                COALESCE(MAX(ui.homephone), '') AS homephone,
                COALESCE(MAX(ui.address), '') AS address,
                COALESCE(MAX(ui.city), '') AS city,
                COALESCE(MAX(ui.state), '') AS state,
                COALESCE(MAX(ui.country), '') AS country,
                COALESCE(MAX(ui.zip), '') AS zip,
                COALESCE(MAX(CASE WHEN rr_export.attribute='Session-Timeout' THEN rr_export.value END), '') AS sessiontimeout,
                COALESCE(MAX(CASE WHEN rr_export.attribute='Idle-Timeout' THEN rr_export.value END), '') AS idletimeout,
                COALESCE(MAX(CASE WHEN rc_export.attribute='Max-Daily-Session' THEN rc_export.value END), '') AS maxdailysession
           FROM %s
           LEFT JOIN %s AS rc_export ON rc_export.username=rc.username
           LEFT JOIN %s AS rr_export ON rr_export.username=rc.username
          WHERE %s
          GROUP BY rc.username
          ORDER BY rc.username ASC",
        $from,
        $radcheck,
        $radreply,
        implode(' AND ', $where)
    );

    return array($sql, $params);
}
