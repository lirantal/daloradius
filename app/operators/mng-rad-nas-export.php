<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
 *********************************************************************************************************
 * Export the complete NAS/client list as a versioned JSON backup.
 *********************************************************************************************************
 */

include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);
include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);
$operator = $_SESSION['operator_user'];

// This helper endpoint uses the same permission as the NAS list page.
$operator_perm_file = 'mng_rad_nas_list';
$operator_perm_deny_http_status = 403;
include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);
include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'nasImportExport.php' ]);
include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);

$sql = sprintf(
    "SELECT nasname, shortname, type, ports, secret, server, community, description FROM %s ORDER BY nasname ASC",
    $configValues['CONFIG_DB_TBL_RADNAS']
);
$res = $dbSocket->query($sql);

if (DB::isError($res)) {
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    echo json_encode(array('error' => 'Unable to export the NAS list'));
    exit;
}

$nas = array();
while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
    $nas[] = array(
        'nasname' => $row['nasname'],
        'shortname' => $row['shortname'],
        'type' => $row['type'],
        'ports' => ($row['ports'] === null) ? null : intval($row['ports']),
        'secret' => $row['secret'],
        'server' => $row['server'],
        'community' => $row['community'],
        'description' => $row['description'],
    );
}

include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);

$document = array(
    'format' => NAS_BACKUP_FORMAT,
    'version' => NAS_BACKUP_VERSION,
    'exported_at' => gmdate('c'),
    'includes_secrets' => true,
    'count' => count($nas),
    'nas' => $nas,
);

$filename = sprintf('daloradius-nas-%s.json', gmdate('Ymd-His'));
header('Content-Type: application/json; charset=UTF-8');
header(sprintf('Content-Disposition: attachment; filename="%s"', $filename));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');

echo json_encode(
    $document,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
);
exit;
