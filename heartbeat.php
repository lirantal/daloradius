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
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */


require_once('library/opendb.php');
require_once('library/config_read.php');

$secret_key = $configValues['CONFIG_DASHBOARD_DALO_SECRETKEY'];
$debug_mode = $configValues['CONFIG_DASHBOARD_DALO_DEBUG'];

isset($_GET['secret_key']) ? $secretKey = $dbSocket->escapeSimple($_GET['secret_key']) : $secretKey = "";
if ($secretKey != $secret_key) {
	die("authorization denied\n");
}

isset($_GET['wan_iface']) ? $wan_iface = $dbSocket->escapeSimple($_GET['wan_iface']) : $wan_iface = "";
isset($_GET['wan_ip']) ? $wan_ip = $dbSocket->escapeSimple($_GET['wan_ip']) : $wan_ip = "";
isset($_GET['wan_mac']) ? $wan_mac = $dbSocket->escapeSimple($_GET['wan_mac']) : $wan_mac = "";
isset($_GET['wan_gateway']) ? $wan_gateway = $dbSocket->escapeSimple($_GET['wan_gateway']) : $wan_gateway = "";
isset($_GET['wifi_iface']) ? $wifi_iface = $dbSocket->escapeSimple($_GET['wifi_iface']) : $wifi_iface = "";
isset($_GET['wifi_ip']) ? $wifi_ip = $dbSocket->escapeSimple($_GET['wifi_ip']) : $wifi_ip = "";
isset($_GET['wifi_mac']) ? $wifi_mac = $dbSocket->escapeSimple($_GET['wifi_mac']) : $wifi_mac = "";
isset($_GET['wifi_ssid']) ? $wifi_ssid = $dbSocket->escapeSimple($_GET['wifi_ssid']) : $wifi_ssid = "";
isset($_GET['wifi_key']) ? $wifi_key = $dbSocket->escapeSimple($_GET['wifi_key']) : $wifi_key = "";
isset($_GET['wifi_channel']) ? $wifi_channel = $dbSocket->escapeSimple($_GET['wifi_channel']) : $wifi_channel = "";
isset($_GET['lan_iface']) ? $lan_iface = $dbSocket->escapeSimple($_GET['lan_iface']) : $lan_iface = "";
isset($_GET['lan_mac']) ? $lan_mac = $dbSocket->escapeSimple($_GET['lan_mac']) : $lan_mac = "";
isset($_GET['lan_ip']) ? $lan_ip = $dbSocket->escapeSimple($_GET['lan_ip']) : $lan_ip = "";
isset($_GET['uptime']) ? $uptime = $dbSocket->escapeSimple($_GET['uptime']) : $uptime = "";
isset($_GET['memfree']) ? $memfree = $dbSocket->escapeSimple($_GET['memfree']) : $memfree = "";
isset($_GET['wan_bup']) ? $wan_bup = $dbSocket->escapeSimple($_GET['wan_bup']) : $wan_bup = "";
isset($_GET['wan_bdown']) ? $wan_bdown = $dbSocket->escapeSimple($_GET['wan_bdown']) : $wan_bdown = "";
isset($_GET['nas_mac']) ? $nas_mac = $dbSocket->escapeSimple($_GET['nas_mac']) : $nas_mac = "";
isset($_GET['firmware']) ? $firmware = $dbSocket->escapeSimple($_GET['firmware']) : $firmware = "";
isset($_GET['firmware_revision']) ? $firmware_revision = $dbSocket->escapeSimple($_GET['firmware_revision']) : $firmware_revision = "";
isset($_GET['cpu']) ? $cpu = $dbSocket->escapeSimple($_GET['cpu']) : $cpu = "";
//isset($_GET['checkin_date']) ? $checkin_date = $dbSocket->escapeSimple($_GET['checkin_date']) : $checkin_date = "";

$currDate = date('Y-m-d H:i:s');

// insert hotspot info

$sql = "SELECT mac FROM ".$configValues['CONFIG_DB_TBL_DALONODE']." WHERE mac='$nas_mac'";
$res = $dbSocket->query($sql);
if ($res->numRows() >= 1) {
	// we update
	$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALONODE']." SET ".
			"wan_iface='".$wan_iface."',".
			"wan_ip='".$wan_ip."',".
			"wan_mac='".$wan_mac."',".
			"wan_gateway='".$wan_gateway."',".
			"wifi_iface='".$wifi_iface."',".
			"wifi_ip='".$wifi_ip."',".
			"wifi_mac='".$wifi_mac."',".
			"wifi_ssid='".$wifi_ssid."',".
			"wifi_key='".$wifi_key."',".
			"wifi_channel='".$wifi_channel."',".
			"lan_iface='".$lan_iface."',".
			"lan_mac='".$lan_mac."',".
			"lan_ip='".$lan_ip."',".
			"uptime='".$uptime."',".
			"memfree='".$memfree."',".
			"wan_bup='".$wan_bup."',".
			"wan_bdown='".$wan_bdown."',".
			"firmware='".$firmware."',".
			"firmware_revision='".$firmware_revision."',".
			"mac='".$nas_mac."',".
			"time='".$currDate."',".
			"cpu='".$cpu."'".
			" WHERE mac='$nas_mac'";
			;
} else {
	// we insert
	$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALONODE']." (".
			" id, wan_iface, wan_ip, wan_mac, wan_gateway, wifi_iface, wifi_ip, wifi_mac, wifi_ssid, wifi_key, wifi_channel, ".
			" lan_iface, lan_mac, lan_ip, uptime, memfree, wan_bup, wan_bdown, firmware, firmware_revision, ".
			" mac, `time`, cpu ".
			" ) ".
			" VALUES (0, ".
			"'".$wan_iface."',".
			"'".$wan_ip."',".
			"'".$wan_mac."',".
			"'".$wan_gateway."',".
			"'".$wifi_iface."',".
			"'".$wifi_ip."',".
			"'".$wifi_mac."',".
			"'".$wifi_ssid."',".
			"'".$wifi_key."',".
			"'".$wifi_channel."',".
			"'".$lan_iface."',".
			"'".$lan_mac."',".
			"'".$lan_ip."',".
			"'".$uptime."',".
			"'".$memfree."',".
			"'".$wan_bup."',".
			"'".$wan_bdown."',".
			"'".$firmware."',".
			"'".$firmware_revision."',".
			"'".$nas_mac."',".
			"'".$currDate."',".
			"'".$cpu."'".
			" ) ";
}


$res = $dbSocket->query($sql);
require_once('library/closedb.php');

if (isset($debug_mode) && $debug_mode == 1) {
	echo "Debug: \n";
	var_dump($_GET);
	echo "\n\n$sql\n\n";	
}

echo "success";

