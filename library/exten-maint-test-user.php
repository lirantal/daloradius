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

 
function user_login_test($user,$pass,$radius,$radiusport,$nasport,$secret){

    $tmp = " ".$user." ".$pass." ".$radius.":".$radiusport." ".$nasport." ".$secret;

	system("radtest -d $tmp", $re);

	## todo better layout
	$re2 = nl2br($re);
	return $re2;
}


?>

