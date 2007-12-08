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


/* user_auth()
 * $radiusaddr	- the server address, this would most likely be the radius server IP Address/Hostname
 * $radiusport  - the server's port number, radius server's port number (1812 or the old port for auth)
 *
 *
*/ 
function user_auth($options,$user,$pass,$radiusaddr,$radiusport,$secret,$command="auth",$additional=""){

	//$tmp = " ".$user." ".$pass." ".$radius.":".$radiusport." ".$nasport." ".$secret;

	$args = "$radiusaddr:$radiusport $command $secret";
	$query = "User-Name=$user,User-Password=$pass";
	
	$radclient = "radclient"; 		// or you can change this with the full path if the binary radcilent program can not be
						// found within your $PATH variable

	$cmd = "echo \"$query\" | $radclient $options $args";
	$print_cmd = "<b>Executed:</b> $cmd <br/><b>Results:</b><br/><br/>";
	$res = shell_exec($cmd);

	## todo better layout
	$output_html = nl2br($res);
	return $print_cmd . $output_html;
}


/*
function user_login_test($user,$pass,$radius,$radiusport,$nasport,$secret){

    $tmp = " ".$user." ".$pass." ".$radius.":".$radiusport." ".$nasport." ".$secret;

	$res = shell_exec("radtest $tmp");

	## todo better layout
	$output_html = nl2br($res);
	return $output_html;
}
*/

?>

