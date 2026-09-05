<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *********************************************************************************************************
 * Description: returns vendor and attribute dictionary information as JSON
 *********************************************************************************************************
 */

require_once __DIR__ . '/json_info.php';
include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', '..', '..', 'common', 'includes', 'config_read.php' ]);
include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);

$operator_perm_file = 'mng_rad_attributes_list';
$operator_perm_deny_http_status = 403;
include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);
include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    dalo_info_response([ 'error' => 'Method not allowed.' ], 405);
}

$dalo_info_database_error_message = 'Unable to load attribute information.';
$db_error_handler = 'dalo_info_database_error';
include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);
// The default PEAR callback prints HTML and would corrupt the JSON response.
$dbSocket->setErrorHandling(PEAR_ERROR_RETURN);

function dalo_attribute_query_error($result) {
    if (DB::isError($result)) {
        dalo_info_response([ 'error' => 'Unable to load attribute information.' ], 500);
    }
}

function dalo_attribute_options($helper) {
    switch ($helper) {
        case 'authtype':
            return array_combine(
                [ 'Local', 'System', 'Accept', 'Reject', 'SecurID', 'Crypt-Local', 'ActivCard', 'EAP', 'PAP',
                  'CHAP', 'MS-CHAP', 'PAM', 'Kerberos', 'CRAM', 'NS-MTA-MD5', 'SMB', 'Unix', 'None', 'ARAP' ],
                [ 'Local', 'System', 'Accept', 'Reject', 'SecurID', 'Crypt-Local', 'ActivCard', 'EAP', 'PAP',
                  'CHAP', 'MS-CHAP', 'PAM', 'Kerberos', 'CRAM', 'NS-MTA-MD5', 'SMB', 'Unix', 'None', 'ARAP' ]
            );

        case 'servicetype':
            $values = [ 'Login-User', 'Framed-User', 'Callback-Login-User', 'Callback-Framed-User', 'Outbound-User',
                        'Administrative-User', 'NAS-Prompt-User', 'Authenticate-Only', 'Callback-NAS-Prompt',
                        'Call-Check', 'Callback-Administrative', 'Sip-session', 'Annex-Authorize-Only',
                        'Annex-Framed-Tunnel', 'Authorize-Only', 'Shell-User', 'Dialback-Login-User',
                        'Dialback-Framed-User', 'Login', 'Framed', 'Callback-Login', 'Callback-Framed', 'Exec-User',
                        'Sip-Session', 'Dialout-Framed-User' ];
            return array_combine($values, $values);

        case 'framedprotocol':
            $values = [ 'PPP', 'SLIP', 'ARAP', 'Gandalf-SLML', 'Xylogics-IPX-SLIP', 'X.75-Synchronous',
                        'PPTP', 'GPRS-PDP-Context' ];
            return array_combine($values, $values);

        case 'volumebytes':
            return [ '' => 'Select...', '10485760' => '10 MB', '52428800' => '50 MB',
                     '104857600' => '100 MB', '524288000' => '500 MB', '1073741824' => '1 GB',
                     '2147483648' => '2 GB', '4294967296' => '4 GB', '8589934592' => '8 GB',
                     '12884901888' => '12 GB', '17179869184' => '16 GB' ];

        case 'bitspersecond':
            return [ '' => 'Select...', '32000' => '32 Kbps', '64000' => '64 Kbps', '128000' => '128 Kbps',
                     '256000' => '256 Kbps', '512000' => '512 Kbps', '750000' => '750 Kbps',
                     '1048576' => '1 Mbps', '1572864' => '1.5 Mbps', '2097152' => '2 Mbps',
                     '3145728' => '3 Mbps', '5242880' => '5 Mbps', '8388608' => '8 Mbps',
                     '10485760' => '10 Mbps' ];

        case 'kbitspersecond':
            return [ '' => 'Select...', '32' => '32 Kbps', '64' => '64 Kbps', '128' => '128 Kbps',
                     '256' => '256 Kbps', '512' => '512 Kbps', '750' => '750 Kbps', '1000' => '1 Mbps',
                     '1500' => '1.5 Mbps', '2500' => '2 Mbps', '3000' => '3 Mbps', '5000' => '5 Mbps',
                     '8000' => '8 Mbps', '10000' => '10 Mbps' ];

        case 'mikrotikRateLimit':
            $values = [ '128k/128k', '128k/256k', '128k/512k', '128k/1M', '256k/256k', '256k/1M',
                        '512k/512k', '512k/1M', '512k/2M', '1M/1M', '1M/2M', '2M/2M', '1M/5M' ];
            return array_combine($values, $values);
    }

    return [];
}

function dalo_attribute_helper($helper, $dynamic_options) {
    if ($helper === 'datetime') {
        return [ 'name' => $helper, 'type' => 'datetime', 'options' => [], 'initialValue' => date('Y-m-d\TH:i') ];
    }
    if ($helper === 'date') {
        return [ 'name' => $helper, 'type' => 'date', 'options' => [], 'initialValue' => date('D j M Y G:i:s T') ];
    }

    $options = dalo_attribute_options($helper);
    if (!empty($options)) {
        $type = in_array($helper, [ 'volumebytes', 'bitspersecond', 'kbitspersecond' ], true)
              ? 'select' : 'datalist';
        $result = [];
        foreach ($options as $value => $label) {
            $result[] = [ 'value' => (string)$value, 'label' => (string)$label ];
        }
        return [ 'name' => $helper, 'type' => $type, 'options' => $result, 'initialValue' => null ];
    }

    if (!empty($dynamic_options)) {
        return [
            'name' => 'dictionary',
            'type' => 'datalist',
            'options' => array_map(function ($value) {
                return [ 'value' => (string)$value, 'label' => (string)$value ];
            }, $dynamic_options),
            'initialValue' => null,
        ];
    }

    return [ 'name' => $helper, 'type' => 'none', 'options' => [], 'initialValue' => null ];
}

if (isset($_GET['vendorAttributes'])) {
    if (!is_string($_GET['vendorAttributes'])) {
        dalo_info_response([ 'error' => 'Missing or invalid parameter.' ], 400);
    }
    $vendor = trim($_GET['vendorAttributes']);
    if ($vendor === '') {
        include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
        dalo_info_response([ 'vendor' => '', 'attributes' => [] ]);
    }

    $sql = sprintf("SELECT DISTINCT `attribute` FROM %s WHERE `Vendor`='%s' ORDER BY `attribute` ASC",
                   $configValues['CONFIG_DB_TBL_DALODICTIONARY'], $dbSocket->escapeSimple($vendor));
    $res = $dbSocket->query($sql);
    dalo_attribute_query_error($res);
    $attributes = [];
    while ($row = $res->fetchRow()) {
        if (DB::isError($row)) {
            dalo_info_response([ 'error' => 'Unable to load attribute information.' ], 500);
        }
        $attributes[] = trim($row[0]);
    }
    $attributes = dalo_filter_cleartext_password_attributes($attributes);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
    dalo_info_response([ 'vendor' => $vendor, 'attributes' => array_values($attributes) ]);
}

if (isset($_GET['getValuesForAttribute'])) {
    if (!is_string($_GET['getValuesForAttribute']) || trim($_GET['getValuesForAttribute']) === '') {
        dalo_info_response([ 'error' => 'Missing or invalid parameter.' ], 400);
    }
    $attribute = trim($_GET['getValuesForAttribute']);

    $sql = sprintf("SELECT `recommendedOP`, `recommendedTable`, `recommendedTooltip`, `type`, `recommendedHelper`
                      FROM %s
                     WHERE `attribute`='%s' AND (`Value` = '' OR `Value` IS NULL)
                     ORDER BY `id` ASC LIMIT 1",
                   $configValues['CONFIG_DB_TBL_DALODICTIONARY'], $dbSocket->escapeSimple($attribute));
    $res = $dbSocket->query($sql);
    dalo_attribute_query_error($res);
    $row = $res->fetchRow();
    if (DB::isError($row)) {
        dalo_info_response([ 'error' => 'Unable to load attribute information.' ], 500);
    }

    $found = is_array($row);
    $recommended_op = $found ? trim($row[0] ?? '') : '';
    $recommended_table = $found ? trim($row[1] ?? '') : '';
    $description = $found ? trim($row[2] ?? '') : '';
    $type = $found ? trim($row[3] ?? '') : '';
    $recommended_helper = $found ? trim($row[4] ?? '') : '';
    $dynamic_options = [];

    if ($found && empty(dalo_attribute_options($recommended_helper)) &&
        !in_array($recommended_helper, [ 'datetime', 'date' ], true)) {
        $sql = sprintf("SELECT DISTINCT `Value` FROM %s
                         WHERE `attribute`='%s' AND `Value` <> '' AND `Value` IS NOT NULL
                         ORDER BY `Value` ASC",
                       $configValues['CONFIG_DB_TBL_DALODICTIONARY'], $dbSocket->escapeSimple($attribute));
        $values_res = $dbSocket->query($sql);
        dalo_attribute_query_error($values_res);
        while ($value_row = $values_res->fetchRow()) {
            if (DB::isError($value_row)) {
                dalo_info_response([ 'error' => 'Unable to load attribute information.' ], 500);
            }
            $dynamic_options[] = trim($value_row[0]);
        }
    }

    $data = [
        'attribute' => $attribute,
        'found' => $found,
        'recommendedOperator' => $recommended_op,
        'recommendedTable' => $recommended_table,
        'operators' => array_values($valid_ops),
        'tables' => [ 'check', 'reply' ],
        'description' => $description,
        'type' => $type,
        'helper' => dalo_attribute_helper($recommended_helper, $dynamic_options),
    ];

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
    dalo_info_response($data);
}

$sql = sprintf("SELECT DISTINCT `Vendor` FROM %s
                 WHERE `Vendor` <> '' AND `Vendor` IS NOT NULL ORDER BY `Vendor` ASC",
               $configValues['CONFIG_DB_TBL_DALODICTIONARY']);
$res = $dbSocket->query($sql);
dalo_attribute_query_error($res);
$vendors = [];
while ($row = $res->fetchRow()) {
    if (DB::isError($row)) {
        dalo_info_response([ 'error' => 'Unable to load attribute information.' ], 500);
    }
    $vendors[] = trim($row[0]);
}

include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
dalo_info_response([ 'vendors' => $vendors ]);
