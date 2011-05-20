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
 * Package:		Monitor User Traffic
 * Description:	This script will check the node table to see if any nodes have not reported
 *				for the set threshold, upon which it sends an email alert to the defined destination.
 *				
 * Authors:     Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */


/* Configuration Section ************************************************ */

$configValues['CONFIG_DB_ENGINE'] = 'mysql';
$configValues['CONFIG_DB_HOST'] = 'localhost';
$configValues['CONFIG_DB_PORT'] = 3306;
$configValues['CONFIG_DB_USER'] = 'daloradius';
$configValues['CONFIG_DB_PASS'] = 'daloradius';
$configValues['CONFIG_DB_NAME'] = 'radius';
$configValues['CONFIG_DB_TBL_DALONODE'] = 'node';

// HARD_DELAY_SEC is the delay in seconds to fire the email off 
$configValues['HARD_DELAY_SEC'] = 15;
$configValues['EMAIL_TO'] = "liran.tal@gmail.com";
$configValues['EMAIL_FROM'] = "daloradius@enginx.com";


/* ********************************************************************** */

require_once('DB.php');

function databaseConnect() {

	global $configValues;
	
    $mydbEngine = $configValues['CONFIG_DB_ENGINE'];
    $mydbUser = $configValues['CONFIG_DB_USER'];
    $mydbPass = $configValues['CONFIG_DB_PASS'];
    $mydbHost = $configValues['CONFIG_DB_HOST'];
    $mydbPort = $configValues['CONFIG_DB_PORT'];
    $mydbName = $configValues['CONFIG_DB_NAME'];

    $dbConnectString = $mydbEngine . "://".$mydbUser.":".$mydbPass."@".
               $mydbHost.":".$mydbPort."/".$mydbName;

    $dbSocket = DB::connect($dbConnectString);

    if (DB::isError ($dbSocket))
        die ("<b>Database connection error</b><br/>
            <b>Error Message</b>: " . $dbSocket->getMessage () . "<br/>" .
            "<b>Debug</b>: " . $dbSocket->getDebugInfo() . "<br/>");

	return $dbSocket;

}


function databaseDisconnect($dbSocket) {

    $dbSocket->disconnect();

}



function getHardDelayNodes($dbSocket) {


	global $configValues;
	$sql = "SELECT ".
			"mac,  memfree,  cpu,  wan_ip,  wan_gateway,  lan_mac,  firmware,  firmware_revision ".
		" FROM ".$configValues['CONFIG_DB_TBL_DALONODE'].
		" WHERE ( UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(time) ) > ".$configValues['HARD_DELAY_SEC'];
	$res = $dbSocket->query($sql);

	$nodes = array();
	while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {

		$message = getHTMLMessage($row);
		
		$subject = "daloRADIUS Node Monitor: Offline Node";
		sendEmailNotification($subject, $message);

	}


}



/**
 * getHTMLMessage()
 *
 */
function sendEmailNotification($subject, $message) {

	global $configValues;
	
	$to = $configValues['EMAIL_TO'];
	$from = $configValues['EMAIL_FROM'];

	// set appropriate (html, utf8 and to/from addresses) headers
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
	$headers .= 'To: System Administrator <'.$to.'>' . "\r\n";
	$headers .= 'From: daloRADIUS Node Monitor<'.$from.'>' . "\r\n";
	
	// mail it
	mail($to, $subject, $message, $headers);

}

/**
 * getHTMLMessage()
 *
 */
function getHTMLMessage($table) {

	$result = "";
	$result .= "<html><head><title>daloRADIUS Node Monitor";
	$result .= "</title></head>";
	$result .= "<body><table>";
	
	foreach ($table as $field => $value) {
		$result .= "<tr><td>$field</td><td>$value</td></tr>";
	}
	
	$result .= "</table></body></html>";

	return $result;
}

$dbh = databaseConnect();
getHardDelayNodes($dbh);
databaseDisconnect($dbh);

?>

