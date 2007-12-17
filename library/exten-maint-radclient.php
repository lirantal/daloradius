<?php
/*******************************************************************
* Extension name: exten maint test user
*                                                      
* Description:                                                     
* This code allows for running the 'radtest' binary tool provided with freeradius
* for performing a dry-run check to see if a user is able to successfully login
* or that there may be problems connecting.
*
*
* Author: Giso Kegal <kegel@barum.de>                                                                  
* Author: Liran Tal <liran@enginx.com>                     
*                                                                  
*******************************************************************/


/* user_auth function
 * sends to the radius server an authentication request packet (for the sake of testing a user)
 * $radiusaddr	- the server address, this would most likely be the radius server IP Address/Hostname
 * $radiusport  - the server's port number, radius server's port number (1812 or the old port for auth)
 * $options	- the options passed to the radclient program
 * $command	- the command string that radclient sends (auth, acct, status, coa, disconnect), by default
		  this functions does 'auth'
*/ 
function user_auth($options,$user,$pass,$radiusaddr,$radiusport,$secret,$command="auth",$additional="") {

	$args = "$radiusaddr:$radiusport $command $secret";
	$query = "User-Name=$user,User-Password=$pass";
	
	$radclient = "radclient"; 		// or you can change this with the full path if the binary radcilent program can not be
						// found within your $PATH variable

	$cmd = "echo \"$query\" | $radclient $options $args 2>&1";
	$print_cmd = "<b>Executed:</b> $cmd <br/><b>Results:</b><br/><br/>";
	$res = shell_exec($cmd);

	if ($res == "") {
		echo "<b>Error:</b> command did not return any results<br/>";
		echo "please check that you have the radclient binary program installed and that
			it is found in your \$PATH variable<br/>
			You may also consult the library/exten-maint-radclient.php for other problems<br/>
		";
	}

	## todo better layout
	$output_html = nl2br($res);
	return $print_cmd . $output_html;
}


/* user_disconnect function
 * sends to the NAS a CoA (Change of Authorization) or a CoD (Disconnect) packet
 *
 * $nasaddr	- NAS address to receive the coa or disconnect request packet
 * $nasport	- NAS Port address (depends on the configuration on the NAS, this may be a different port for
		  either CoA or Disconnect packets). 
 *
 *
*/
function user_disconnect($options,$user,$nasaddr,$nasport="3779",$nassecret,$command="disconnect",$additional="") {

	$args = "$nasaddr:$nasport $command $nassecret";
	$query = "User-Name=$user";
	
	$radclient = "radclient"; 		// or you can change this with the full path if the binary radcilent program can not be
						// found within your $PATH variable

	$cmd = "echo \"$query\" | $radclient $options $args 2>&1";
	$print_cmd = "<b>Executed:</b> $cmd <br/><b>Results:</b><br/><br/>";
	$res = shell_exec($cmd);

	if ($res == "") {
		echo "<b>Error:</b> command did not return any results<br/>";
		echo "please check that you have the radclient binary program installed and that
			it is found in your \$PATH variable<br/>
			You may also consult the library/exten-maint-radclient.php for other problems<br/>
		";
	}

	## todo better layout
	$output_html = nl2br($res);
	return $print_cmd . $output_html;
}

?>

