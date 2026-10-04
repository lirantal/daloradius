<?php
/** R22 portal read providers. Borrowed handles and transactions stay caller-owned. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/library/portal_widgets_pdo.php') !== false) {
    http_response_code(404); exit;
}
require_once __DIR__ . '/portal_pages_pdo.php';

function dalo_portal_widget_identity($username) {
    $username = dalo_portal_username($username);
    if ($username !== ($_SESSION['login_user'] ?? null)) {
        throw new InvalidArgumentException('Portal identity does not match session');
    }
    return $username;
}

function dalo_portal_widget_table(PDO $pdo, $config, $key) {
    if (in_array($key, array('CONFIG_DB_TBL_RADCHECK', 'CONFIG_DB_TBL_RADREPLY',
        'CONFIG_DB_TBL_RADGROUPREPLY', 'CONFIG_DB_TBL_RADUSERGROUP'), true)) {
        $name = $config[$key] ?? null;
        if (!is_string($name) || preg_match('/\A[A-Za-z_][A-Za-z0-9_]{0,63}\z/D', $name) !== 1) {
            throw new InvalidArgumentException('Invalid portal widget table');
        }
        return ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') ? '`' . $name . '`' : '"' . $name . '"';
    }
    return dalo_portal_table($pdo, $config, $key);
}

function dalo_portal_widget_choice($source, $key, $choices, $default, $lower = true) {
    $value = $source[$key] ?? null;
    if (!is_string($value)) { return $default; }
    $value = $lower ? strtolower($value) : $value;
    return in_array($value, $choices, true) ? $value : $default;
}

function dalo_portal_widget_failure() {
    $failureMsg = 'Portal statistics unavailable';
    include dirname(__DIR__) . '/include/management/actionMessages.php';
}

/** Same grouped expressions, completed-session policy and labels as each legacy table. */
function dalo_portal_widget_statistics(PDO $pdo, $config, $username, $category, $type, $orderBy, $orderType, $offset = null, $limit = null) {
    $username = dalo_portal_widget_identity($username);
    $metric = array('login' => 'logins', 'upload' => 'uploads', 'download' => 'downloads')[$category] ?? null;
    $period = array('daily' => 'day', 'monthly' => 'month', 'yearly' => 'year')[$type] ?? null;
    if ($metric === null || $period === null || !in_array($orderBy, array($metric, $period), true) ||
        !in_array($orderType, array('asc', 'desc'), true)) {
        throw new InvalidArgumentException('Invalid portal statistics selection');
    }
    $aggregate = $category === 'login' ? 'COUNT(AcctStartTime)' :
        ($category === 'upload' ? 'SUM(AcctInputOctets)' : 'SUM(AcctOutputOctets)');
    if ($type === 'monthly') {
        $monthName = $category === 'login' ? 'MONTHNAME(AcctStartTime)' : 'LEFT(MONTHNAME(AcctStartTime),3)';
        $columns = "CONCAT($monthName, ' (', YEAR(AcctStartTime), ')'), $aggregate AS $metric,
            CAST(CONCAT(YEAR(AcctStartTime), '-', MONTH(AcctStartTime), '-01') AS DATE) AS month";
    } else {
        $expression = $type === 'yearly' ? 'YEAR(AcctStartTime)' : 'DATE(AcctStartTime)';
        $columns = "$expression AS $period, $aggregate AS $metric";
    }
    $table = dalo_portal_widget_table($pdo, $config, 'CONFIG_DB_TBL_RADACCT');
    $sql = "SELECT $columns FROM $table WHERE username=:username AND AcctStopTime>0 GROUP BY $period ORDER BY $orderBy $orderType";
    // The group key breaks metric ties consistently across LIMIT requests.
    if ($orderBy !== $period) { $sql .= ", $period asc"; }
    $bindings = array(':username' => $username);
    if ($offset !== null || $limit !== null) {
        if (!is_int($offset) || $offset < 0 || !is_int($limit) || $limit < 1) {
            throw new InvalidArgumentException('Invalid portal statistics page');
        }
        $sql .= ' LIMIT :offset, :limit'; $bindings[':offset'] = $offset; $bindings[':limit'] = $limit;
    }
    return dalo_portal_rows($pdo, $sql, $bindings);
}
