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
 * Description:
 * 		performs the logging-in authorization. First creates a random session_id to be assigned to this session and then
 *		validates the operators credentials in the database
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

// first we create a random session key
$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];							// get client ip address
srand((double)microtime()*1000000 );							// initialize random seed
$rand = rand(1,9);												// generate a random number between 1 to 9
$session_id = $rand.substr(md5($REMOTE_ADDR), 0, 11+$rand);		/* append the random number to the beginning
of the session_id string followed by a substring of the md5 ip address hash with a dynamic length of anything between 11 to 16 digits (the max length of
the md5 hash) */
$session_id .= substr(md5(rand(1,1000000)), rand(1,32-$rand), 21-$rand);	// further add a dynamic length digits to 
																		// to the session_id string composed of the
																		// md5 hash for random number
session_id($session_id);							// apply the session_id that we created
//session_set_cookie_params(3600);						// deprecated, unsupported in older IE browsers, set's the session timeout 
										// to 3600 seconds (1 hour)
ini_set('session.gc_maxlifetime', 60*60);					// replaces the session_set_cookie_params directive

session_start();								// initiate the session

$errorMessage = '';

$location_name = $_POST['location'];						// we need to set location name session variable before opening the database
$_SESSION['location_name'] = $location_name;					// since the whole point is to authenticate to a spefific pre-defined database server


include 'library/opendb.php';

$operator_user = $dbSocket->escapeSimple($_POST['operator_user']);
$operator_pass = $dbSocket->escapeSimple($_POST['operator_pass']);

// check if the user id and password combination exist in database
$sql = "SELECT id, username FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATORS']." WHERE username = '".
		$operator_user."' AND password = '".$operator_pass."'";
$res = $dbSocket->query($sql);

if ($res->numRows() == 1) {
	// the user id and password match,
	// set the session

	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
	$operator_id = $row['id'];
	
	$_SESSION['daloradius_logged_in'] = true;
	$_SESSION['operator_user'] = $operator_user;
	$_SESSION['operator_id'] = $operator_id;

	// lets update the lastlogin time for this operator
	$date = date("Y-m-d H:i:s");
	$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOOPERATORS']." SET lastlogin='$date' WHERE username='$operator_user'";
	$res = $dbSocket->query($sql);

	// after login we move to the main page
	header('Location: index.php');
	exit;
} else {
	header('Location: login.php?error=an error occured');
	exit;
}

include 'library/closedb.php';
	
?>
