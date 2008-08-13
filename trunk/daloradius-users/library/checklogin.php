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
 * 		verifies a user session, valid or invalid based on the random session_id generated on dologin.php
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

// internal function to verify the custom session_id which we create upon logging in
function session_verify() {
	session_start();

	$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
	if (substr(md5($REMOTE_ADDR), 0, 10+substr(session_id(), 0, 1)) == 
		substr(session_id(), 1, 10+substr(session_id(), 0, 1))) {
		$session_valid="yes";
	} else {
		$session_valid="no";
	}

	return $session_valid;
}


if (session_verify() == "yes") {

	if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != true) {
		header('Location: login.php');
		exit;
	}
} else {
	// maybe the session is verified but the user is not logged in
	header('Location: login.php');
	exit;
}


?>
