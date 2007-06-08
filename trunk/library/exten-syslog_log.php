<?php
/*******************************************************************
* Extension name: radius log file                                  *
*                                                                  *
* Description:                                                     *
* this script displays the radius log file ofcourse                *
* proper premissions must be applied on the log file for the web   *
* server to be able to read it                                     *
*                                                                  *
* Author: Liran Tal <liran@enginx.com>                             *
*                                                                  *
*******************************************************************/

$logfile = '/var/log/syslog';
if (is_readable($logfile) == false) {
	echo "<br/><br/>
		error reading log file: <u>$logfile</u> <br/><br/>
		possible cause is file premissions or file doesn't exist.<br/>";
} else {
	if ($filedata = file_get_contents($logfile)) {
		$ret = eregi_replace("\n", "<br>", $filedata);
		echo $ret;
	}
}

?>

