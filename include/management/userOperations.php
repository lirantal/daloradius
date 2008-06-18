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
 *              returns user status (active, expired, disabled)
 *		as well as performs different user operations (disable user, enable user, etc)
 *
 * Authors:     Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */



/*
 * The following handles disabling the user
 */
if ((isset($_GET['userDisable'])) && (isset($_GET['username']))) {
	userDisable($_GET['username'], $_GET['divContainer']);
}


function userDisable($username, $divContainer) {

	include 'pages_common.php';
	include '../../library/opendb.php';

	//echo "alert('{$username}');";

	if (!is_array($username))
		$username = array($username, NULL);

	$allUsers = "";
	$allUsersSuccess = array();
	$allUsersFailure = array();

	foreach ($username as $variable=>$value) {
	
	        $user = $dbSocket->escapeSimple($value);		// clean username argument from harmful code
		$allUsers .= $user . ", ";

		$sql = "SELECT Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK'].
			" WHERE Attribute='Auth-Type' AND Value='Reject' AND Username='$user'";
		$res = $dbSocket->query($sql);
		if ($numrows = $res->numRows() <= 0) {
	
		        $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK'].
		                " VALUES (0,'$user','Auth-Type',':=','Reject')";
		        $res = $dbSocket->query($sql);
	
			array_push($allUsersSuccess, $user);
		} else {
			array_push($allUsersFailure, $user);
		}
	
	}

	if (count($allUsersSuccess) > 0) {
		$users = "";
		foreach($allUsersSuccess as $value)
			$users .= $value . ", ";

		$users = substr($users, 0, -2);
	        printqn("
	               var divContainer = document.getElementById('{$divContainer}');
	               divContainer.innerHTML += '<div class=\"success\">User(s) <b>$users</b> are now disabled.</div>';
	        ");
	}

	if (count($allUsersFailure) > 0) {
		$users = "";
		foreach($allUsersFailure as $value)
			$users .= $value . ", ";

		$users = substr($users, 0, -2);
	        printqn("
	               var divContainer = document.getElementById('{$divContainer}');
	               divContainer.innerHTML += '<div class=\"failure\">User(s) <b>$users</b> are already disabled.</div>';
	        ");
	}


        include '../../library/closedb.php';

}

function checkDisabled($username) {

	include 'library/opendb.php';

	$username = $dbSocket->escapeSimple($username);

        $sql = "SELECT Attribute,Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK'].
		" WHERE Attribute='Auth-Type' AND Value='Reject' AND Username='$username'";
	$res = $dbSocket->query($sql);
	if ($numrows = $res->numRows() >= 1) {
	
	        echo "<div class='failure'>
	              	Please note, the user <b>$username</b> is currently disabled.<br/>
			To enable the user, remove the Auth-Type entry set to Reject.<br/>
	              </div>";

	}

	include 'library/closedb.php';

}
