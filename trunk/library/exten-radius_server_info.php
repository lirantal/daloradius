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

system("pgrep mysql >/dev/null 2>&1", $ret_mysql);
system("pgrep radius >/dev/null 2>&1", $ret_rad);

if ($ret_rad == 0) {
        echo "RADIUS is up <br/>";
} else {
        echo "RADIUS is down <br/>";
}

if ($ret_mysql == 0) {
        echo "MySQL is up <br/>";
} else {
        echo "MySQL is down <br/>";
}

?>
