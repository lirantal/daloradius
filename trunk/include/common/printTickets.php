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
 *
 * Filename: printTickets.php
 * Author: Liran Tal <liran.tal@gmail.com>
 *
 * Description:
 * This extension provides an HTML output of tickets information
 *********************************************************************/

include('../../library/checklogin.php');

$ticketInformation = "Information: To use this card, please connect <br/>".
						"your device to the nearest ssid. Open your web <br/>".
						"browser and enter each needed field.";
$ticketLogoFile = "/images/daloradius_small.png";			


if (isset($_REQUEST['type']) && $_REQUEST['type'] == "batch") {
	
	$format = $_REQUEST['format'];
	$plan = $_REQUEST['plan'];

	// accounts is a string with the format of "username1,password1||username2,password2||..."
	$accounts_temp = $_REQUEST['accounts'];
	$accounts = explode("||", $accounts_temp);
	
	include_once('../../library/opendb.php');
	include_once('../management/pages_common.php');

	$sql = "SELECT ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
			".planCost AS planCost, ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTimeBank AS planTimeBank, ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planCurrency AS planCurrency ".
			" FROM ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
				" WHERE ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planName=".
				" '$plan' ";
				
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
	
	$ticketCurrency = $row['planCurrency'];
	$ticketCost = $row['planCost'] ." " . $ticketCurrency;
	$ticketTime = time2str($row['planTimeBank']);
	
	printTicketsHTMLTable($accounts, $ticketCost, $ticketTime);

	
}


function printTicketsHTMLTable($accounts, $ticketCost, $ticketTime) {

	$output = "";
	
	global $ticketInformation;
	global $ticketLogoFile;
	
	// the $accounts array contain the username,password|| first element as it's originally
	// used to be a for CSV table header
	array_shift($accounts);
	foreach($accounts as $userpass) {
		
		list($user, $pass) = split(",", $userpass);
		
		$output .= "<table style='float:left;margin:10px 10px 10px 45px' border='2'>
						<tr>
							<td colspan='2'>
								<img src='$ticketLogoFile' alt='Logo' />
							</td>
						</tr>
						<tr>
							<td>
								<b>Login</b>: 
							</td>
							<td>
								$user
							</td>
						</tr>
						<tr>
							<td>
								<b>Password</b>:
							</td>
							<td>
								$pass
							</td>
						</tr>
						<tr>
							<td>
								<b>Validity</b>:
							</td>
							<td>
								$ticketTime
							</td>
						</tr>
						<tr>
							<td>
								<b>Price</b>:
							</td>
							<td>
								$ticketCost
							</td>
						</tr>
						<tr>
							<td colspan='2'>
								<font size='2'>
								$ticketInformation
								</font>
							</td>
						</tr>
					</table>
					
		";
		//$i++;
	}

	print "<html><body> 
				<center> 
					$output 
				</center>
			</body></html>
	";
	
}


?>
