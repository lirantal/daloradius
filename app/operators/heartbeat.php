<?php 
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Description:    This script validates authorization and
 *                 updates/inserts system information into a database.
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    die("wrong HTTP method");
}

$secret_key = (array_key_exists('secret_key', $_GET) && !empty(trim($_GET['secret_key'])))
            ? trim($_GET['secret_key']) : "";

if (empty($secretKey)) {
    die("secret_key not provided");
}

include implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);

if ($secret_key !== $configValues['CONFIG_DASHBOARD_DALO_SECRETKEY']) {
    die("authorization denied");
}

$defaults = [
    'wan_iface' => '',
    'wan_ip' => '',
    'wan_mac' => '',
    'wan_gateway' => '',
    'wifi_iface' => '',
    'wifi_ip' => '',
    'wifi_mac' => '',
    'wifi_ssid' => '',
    'wifi_key' => '',
    'wifi_channel' => '',
    'lan_iface' => '',
    'lan_mac' => '',
    'lan_ip' => '',
    'uptime' => '',
    'memfree' => '',
    'wan_bup' => '',
    'wan_bdown' => '',
    'nas_mac' => '',
    'firmware' => '',
    'firmware_revision' => '',
    'cpu' => ''
];

foreach ($defaults as $key => $default) {
    ${$key} = array_key_exists($key, $_GET) && !empty(trim($_GET[$key])) ? trim($_GET[$key]) : $default;
}

$current_datetime = date('Y-m-d H:i:s');

include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);

// insert hotspot info

$sql0 = sprintf("SELECT `mac` FROM %s WHERE `mac`=?", $configValues['CONFIG_DB_TBL_DALONODE']);
$prepared0 = $dbSocket->prepare($sql0);
$res0 = $dbSocket->execute($prepared0, $nas_mac);

$numrows = $res0->numRows();
$data = [
               $wan_iface, $wan_ip, $wan_mac, $wan_gateway, $wifi_iface, $wifi_ip, $wifi_mac, $wifi_ssid, $wifi_key,
               $wifi_channel, $lan_iface, $lan_mac, $lan_ip, $uptime, $memfree, $wan_bup, $wan_bdown, $firmware,
               $firmware_revision, $nas_mac, $current_datetime, $cpu, $nas_mac
        ];

if ($numrows > 0) {
	// we update
    $sql1 = sprintf("UPDATE %s SET ", $configValues['CONFIG_DB_TBL_DALONODE'])
          . "`wan_iface`=?, `wan_ip`=?, `wan_mac`=?, `wan_gateway`=?, `wifi_iface`=?, `wifi_ip`=?, `wifi_mac`=?, `wifi_ssid`=?, "
          . "`wifi_key`=?, `wifi_channel`=?, `lan_iface`=?, `lan_mac`=?, `lan_ip`=?, `uptime`=?, `memfree`=?, `wan_bup`=?, "
          . "`wan_bdown`=?, `firmware`=?, `firmware_revision`=?, `mac`=?, `time`=?, `cpu`=? WHERE `mac`=?";
} else {
	// we insert
	$sql1 = sprintf("INSERT INTO %s ( ", $configValues['CONFIG_DB_TBL_DALONODE'])
          . "`id`, `wan_iface`, `wan_ip`, `wan_mac`, `wan_gateway`, `wifi_iface`, `wifi_ip`, `wifi_mac`, `wifi_ssid`, "
          . "`wifi_key`, `wifi_channel`, `lan_iface`, `lan_mac`, `lan_ip`, `uptime`, `memfree`, `wan_bup`, `wan_bdown`, "
          . "`firmware`, `firmware_revision`, `mac`, `time`, `cpu` ) "
          . "VALUES (0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,  ?, ?, ?)";
    array_pop($data);
}

$prepared1 = $dbSocket->prepare($sql1);
$res1 = $dbSocket->execute($prepared1, $data);

include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);

$debug_mode = $configValues['CONFIG_DASHBOARD_DALO_DEBUG'];

if (array_key_exists('CONFIG_DASHBOARD_DALO_DEBUG', $_GET) &&
    !empty(trim($_GET['CONFIG_DASHBOARD_DALO_DEBUG'])) &&
    intval(trim($_GET['CONFIG_DASHBOARD_DALO_DEBUG'])) > 0) {
	echo "Debug: \n";
	var_dump($_GET);
	echo "\n\n$sql\n\n";	
}

echo "success";
