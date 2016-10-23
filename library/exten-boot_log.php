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
 *		this script displays the radius log file ofcourse
 *		proper premissions must be applied on the log file for the web
 *		server to be able to read it
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

$logfile_loc1 = '/var/log/dmesg';
$logfile_loc2 = '/usr/local/var/log/dmesg';

if (file_exists($logfile_loc1))
	$logfile = $logfile_loc1;
else if (file_exists($logfile_loc2))
	$logfile = $logfile_loc2;
else {
	$failureMsg = "error reading log file: <br/>".
		"looked for log file in $logfile_loc1 and $logfile_loc2 but couldn't find it.<br/>".
		"if you know where your dmesg (boot) log file is located, set it's location in " . $_SERVER[SCRIPT_NAME];
	exit;
}
	

if (is_readable($logfile) == false) {
	$failureMsg = "error reading log file: <u>$logfile</u> <br/>".
		"possible cause is file premissions or file doesn't exist.<br/>";
} else {
                if (file_get_contents($logfile)) {
                        $fileReversed = array_reverse(file($logfile));
                        $counter = $bootLineCount;
                        foreach ($fileReversed as $line) {
                                if (preg_match("/$bootFilter/i", $line)) {
                                        if ($counter == 0)
                                                break;
                                        $ret = preg_replace("/\n/i", "<br>", $line);
                                        echo $ret;
                                        $counter--;
                                }
                        }
                }
}

?>

