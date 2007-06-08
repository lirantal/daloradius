<?php
/*******************************************************************
* Extension name: exten radius  server information                 *
*                                                                  *
* Description:                                                     *
* this script runs a check to see if freeradius is up and running  *
* the check is done by looking for a 'radius' process listening    *
* on any socket interface. clumsy, but that's what we got for now. *
*                                                                  *
* Author: Liran Tal <liran@enginx.com>                             *
*                                                                  *
*******************************************************************/

system("netstat -tanlpu | grep -i mysql", $ret_mysql);
system("netstat -tanlpu | grep -i radius", $ret_rad);

if ($ret_rad) {
	echo "RADIUS is up <br/>";
} else {
	echo "RADIUS is down <br/>";
}

if ($ret_mysql) {
	echo "MySQL is up <br/>";
} else {
	echo "MySQL is down <br/>";
}

?>

