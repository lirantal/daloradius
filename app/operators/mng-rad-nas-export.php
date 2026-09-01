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
    "SELECT HEX(nasname) AS nasname_hex, HEX(shortname) AS shortname_hex, HEX(type) AS type_hex,
            ports, HEX(secret) AS secret_hex, HEX(server) AS server_hex,
            HEX(community) AS community_hex, HEX(description) AS description_hex
       FROM %s ORDER BY nasname ASC",
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
$usesBinaryEncoding = false;
$encodedFields = array();
$rowNumber = 0;
while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
    $rowNumber++;
    $decoded = array();
    foreach (array('nasname', 'shortname', 'type', 'secret', 'server', 'community', 'description') as $field) {
        $decoded[$field] = nas_backup_decode_database_hex($row[$field . '_hex']);
        if ($decoded[$field] === false) {
            include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            echo json_encode(array('error' => sprintf('Unable to encode NAS row %d field %s', $rowNumber, $field)));
            exit;
        }
    }
    $entry = array(
        'nasname' => nas_backup_encode_export_value($decoded['nasname'], $usesBinaryEncoding),
        'shortname' => nas_backup_encode_export_value($decoded['shortname'], $usesBinaryEncoding),
        'type' => nas_backup_encode_export_value($decoded['type'], $usesBinaryEncoding),
        'ports' => ($row['ports'] === null) ? null : intval($row['ports']),
        'secret' => nas_backup_encode_export_value($decoded['secret'], $usesBinaryEncoding),
        'server' => nas_backup_encode_export_value($decoded['server'], $usesBinaryEncoding),
        'community' => nas_backup_encode_export_value($decoded['community'], $usesBinaryEncoding),
        'description' => nas_backup_encode_export_value($decoded['description'], $usesBinaryEncoding),
    );
    $rowEncodedFields = array();
    foreach ($entry as $field => $value) {
        if (is_array($value) && ($value['encoding'] ?? '') === 'base64') {
            $rowEncodedFields[] = array(
                'row' => $rowNumber,
                'field' => $field,
                'byte_length' => $value['byte_length'],
            );
        }
    }
    if (count($rowEncodedFields) > 0) {
        $encodedFields = array_merge($encodedFields, $rowEncodedFields);
    }
    $nas[] = $entry;
}

include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);

$document = array(
    'format' => NAS_BACKUP_FORMAT,
    'version' => $usesBinaryEncoding ? NAS_BACKUP_BINARY_VERSION : NAS_BACKUP_VERSION,
    'exported_at' => gmdate('c'),
    'includes_secrets' => true,
    'count' => count($nas),
    'nas' => $nas,
);
if ($usesBinaryEncoding) {
    $document['encoded_fields'] = $encodedFields;
}

$json = json_encode(
    $document,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);

if ($json === false) {
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    echo json_encode(array('error' => 'Unable to encode the NAS backup'));
    exit;
}

$filename = sprintf('daloradius-nas-%s.json', gmdate('Ymd-His'));
header('Content-Type: application/json; charset=UTF-8');
header(sprintf('Content-Disposition: attachment; filename="%s"', $filename));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');

echo $json;
exit;
