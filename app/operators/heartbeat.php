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
 * Authors:    Liran Tal <liran@enginx.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    die("wrong HTTP method" . "\n");
}

$secret_key = (array_key_exists('secret_key', $_GET) && !empty(trim($_GET['secret_key']))
            ? trim($_GET['secret_key']) : "";

if (empty($secretKey)) {
    die("secret_key not provided" . "\n");
}

require_once('../common/includes/config_read.php');

if ($secret_key !== $configValues['CONFIG_DASHBOARD_DALO_SECRETKEY']) {
    die("authorization denied" . "\n");
}

$wan_iface = (array_key_exists('wan_iface', $_GET) && !empty(trim($_GET['wan_iface'])) ? trim($_GET['wan_iface']) : "";
$wan_ip = (array_key_exists('wan_ip', $_GET) && !empty(trim($_GET['wan_ip'])) ? trim($_GET['wan_ip']) : "";
$wan_mac = (array_key_exists('wan_mac', $_GET) && !empty(trim($_GET['wan_mac'])) ? trim($_GET['wan_mac']) : "";
$wan_gateway = (array_key_exists('wan_gateway', $_GET) && !empty(trim($_GET['wan_gateway'])) ? trim($_GET['wan_gateway']) : "";
$wifi_iface = (array_key_exists('wifi_iface', $_GET) && !empty(trim($_GET['wifi_iface'])) ? trim($_GET['wifi_iface']) : "";
$wifi_ip = (array_key_exists('wifi_ip', $_GET) && !empty(trim($_GET['wifi_ip'])) ? trim($_GET['wifi_ip']) : "";
$wifi_mac = (array_key_exists('wifi_mac', $_GET) && !empty(trim($_GET['wifi_mac'])) ? trim($_GET['wifi_mac']) : "";
$wifi_ssid = (array_key_exists('wifi_ssid', $_GET) && !empty(trim($_GET['wifi_ssid'])) ? trim($_GET['wifi_ssid']) : "";
$wifi_key = (array_key_exists('wifi_key', $_GET) && !empty(trim($_GET['wifi_key'])) ? trim($_GET['wifi_key']) : "";
$wifi_channel = (array_key_exists('wifi_channel', $_GET) && !empty(trim($_GET['wifi_channel'])) ? trim($_GET['wifi_channel']) : "";
$lan_iface = (array_key_exists('lan_iface', $_GET) && !empty(trim($_GET['lan_iface'])) ? trim($_GET['lan_iface']) : "";
$lan_mac = (array_key_exists('lan_mac', $_GET) && !empty(trim($_GET['lan_mac'])) ? trim($_GET['lan_mac']) : "";
$lan_ip = (array_key_exists('lan_ip', $_GET) && !empty(trim($_GET['lan_ip'])) ? trim($_GET['lan_ip']) : "";
$uptime = (array_key_exists('uptime', $_GET) && !empty(trim($_GET['uptime'])) ? trim($_GET['uptime']) : "";
$memfree = (array_key_exists('memfree', $_GET) && !empty(trim($_GET['memfree'])) ? trim($_GET['memfree']) : "";
$wan_bup = (array_key_exists('wan_bup', $_GET) && !empty(trim($_GET['wan_bup'])) ? trim($_GET['wan_bup']) : "";
$wan_bdown = (array_key_exists('wan_bdown', $_GET) && !empty(trim($_GET['wan_bdown'])) ? trim($_GET['wan_bdown']) : "";
$nas_mac = (array_key_exists('nas_mac', $_GET) && !empty(trim($_GET['nas_mac'])) ? trim($_GET['nas_mac']) : "";
$firmware = (array_key_exists('firmware', $_GET) && !empty(trim($_GET['firmware'])) ? trim($_GET['firmware']) : "";
$firmware_revision = (array_key_exists('firmware_revision', $_GET) && !empty(trim($_GET['firmware_revision'])) ? trim($_GET['firmware_revision']) : "";
$cpu = (array_key_exists('cpu', $_GET) && !empty(trim($_GET['cpu'])) ? trim($_GET['cpu']) : "";
//isset($_GET['checkin_date']) ? $checkin_date = $dbSocket->escapeSimple($_GET['checkin_date']) : $checkin_date = "";

$currDate = date('Y-m-d H:i:s');

require_once('../common/includes/db_open.php');

// insert hotspot info

$sql0 = sprintf("SELECT mac FROM %s WHERE mac=?", $configValues['CONFIG_DB_TBL_DALONODE']);
$prepared0 = $dbSocket->prepare($sql0);
$res0 = $dbSocket->execute($prepared0, $nas_mac);

$numrows = $res0->numRows();
$data = array(
               $wan_iface, $wan_ip, $wan_mac, $wan_gateway, $wifi_iface, $wifi_ip, $wifi_mac, $wifi_ssid, $wifi_key,
               $wifi_channel, $lan_iface, $lan_mac, $lan_ip, $uptime, $memfree, $wan_bup, $wan_bdown, $firmware,
               $firmware_revision, $nas_mac, $currDate, $cpu, $nas_mac
             );

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

require_once('../common/includes/db_close.php');

$debug_mode = $configValues['CONFIG_DASHBOARD_DALO_DEBUG'];


if (array_key_exists('CONFIG_DASHBOARD_DALO_DEBUG', $_GET) &&
    !empty(trim($_GET['CONFIG_DASHBOARD_DALO_DEBUG'])) &&
    intval(trim($_GET['CONFIG_DASHBOARD_DALO_DEBUG'])) > 0) {
	echo "Debug: \n";
	var_dump($_GET);
	echo "\n\n$sql\n\n";	
}

echo "success" . "\n";
