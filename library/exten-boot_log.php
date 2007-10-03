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

$logfile_loc1 = '/var/log/dmesg';
$logfile_loc2 = '/usr/local/var/log/dmesg';

if (file_exists($logfile_loc1))
	$logfile = $logfile_loc1;
else if (file_exists($logfile_loc2))
	$logfile = $logfile_loc2;
else
	{
        echo "<br/><br/>
                error reading log file: <br/><br/>
                looked for log file in $logfile_loc1 and $logfile_loc2 but couldn't find it.<br/>
		if you know where your dmesg (boot) log file is located, set it's location in " . $_SERVER[SCRIPT_NAME];
	exit;
}
	

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

