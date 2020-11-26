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
 *		This code allows for running the 'radtest' binary tool provided with freeradius
 *		for performing a dry-run check to see if a user is able to successfully login
 *		or that there may be problems connecting.
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *		Giso Kegal <kegel@barum.de>
 *
 *********************************************************************************************************
 */


// user_auth function
// sends to the radius server an authentication request packet (for the sake of testing a user)
// $radiusaddr	- the server address, this would most likely be the radius server IP Address/Hostname
// $radiusport  - the server's port number, radius server's port number (1812 or the old port for auth)
// $options	- the options passed to the radclient program
// $command	- the command string that radclient sends (auth, acct, status, coa, disconnect), by default this functions does 'auth'
function user_auth($options,$user,$pass,$radiusaddr,$radiusport,$secret,$command="auth",$additional="") {

	$user = escapeshellarg($user);
	$pass = escapeshellarg($pass);

	$args = escapeshellarg("$radiusaddr:$radiusport")." ".escapeshellarg($command).
		" ".escapeshellarg($secret);
	$query = "User-Name=$user,User-Password=$pass";

	$radclient = "radclient"; 		// or you can change this with the full path if the binary radcilent program can not be
									// found within your $PATH variable

	$radclient_options = "-c ".escapeshellarg($options['count']).
				" -n ".escapeshellarg($options['requests']).
				" -r ".escapeshellarg($options['retries']).
				" -t ".escapeshellarg($options['timeout']).
				" ".$options['debug'];

	if ($options['dictionary'])
		$radclient_options .= " -d ".escapeshellarg($options['dictionary']);

	$cmd = "echo ".escapeshellcmd($query)." | $radclient $radclient_options $args 2>&1";

	$print_cmd = "<b>Executed:</b><br/>$cmd<br/><br/><b>Results:</b><br/>";
	$res = shell_exec($cmd);

	if ($res == "") {
		echo "<b>Error:</b> Command did not return any results<br/>";
		echo "Please check that you have the radclient binary program installed and that
			it is found in your \$PATH variable<br/>
			You may also consult the file library/exten-maint-radclient.php for other problems<br/>
		";
	}

	// todo better layout
	$output_html = nl2br($res);
	return $print_cmd . $output_html;
}


// user_disconnect function
// sends to the NAS a CoA (Change of Authorization) or a CoD (Disconnect) packet
// $nasaddr	- NAS address to receive the coa or disconnect request packet
// $nasport	- NAS Port address (depends on the configuration on the NAS, this may be a different port for either CoA or Disconnect packets).
function user_disconnect($options,$user,$nasaddr,$nasport="3779",$nassecret,$command="disconnect",$additional="") {

	$user = escapeshellarg($user);

	$args = escapeshellarg("$nasaddr:$nasport")." ".escapeshellarg($command)." ".
			escapeshellarg($nassecret);
	$query = "User-Name=$user";

	if (!empty($additional)) {
		$query .= ','.$additional;
	}

	$radclient = "radclient"; 		// or you can change this with the full path if the binary radcilent program can not be
						// found within your $PATH variable

	$radclient_options = "-c ".escapeshellarg($options['count']).
				" -n ".escapeshellarg($options['requests']).
				" -r ".escapeshellarg($options['retries']).
				" -t ".escapeshellarg($options['timeout']).
				" ".$options['debug'];

	if ($options['dictionary'])
		$radclient_options .= " -d ".escapeshellarg($options['dictionary']);

	$cmd = "echo \"".escapeshellcmd($query)."\" | $radclient $radclient_options $args 2>&1";
	$print_cmd = "<b>Executed:</b><br/>$cmd<br/><br/><b>Results:</b><br/>";
	$res = shell_exec($cmd);

	if ($res == "") {
		echo "<b>Error:</b> Command did not return any results<br/>";
		echo "Please check that you have the radclient binary program installed and that
			it is found in your \$PATH variable<br/>
			You may also consult the file library/exten-maint-radclient.php for other problems<br/>
		";
	}

	// todo better layout
	$output_html = nl2br($res);
	return $print_cmd . $output_html;
}

?>
