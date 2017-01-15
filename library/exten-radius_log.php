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
 

$logfile_loc = array();
$logfile_loc[1] = '/var/log/freeradius/radius.log';
$logfile_loc[2] = '/usr/local/var/log/radius/radius.log';
$logfile_loc[3] = '/var/log/radius/radius.log';

foreach ($logfile_loc as $tmp) {
	if (file_exists($tmp)) { 
		$logfile = $tmp; 
		break;
	}
}
 

if (empty($logfile)) {
	echo "<br/><br/>
		error reading log file: <br/><br/>
		looked for log file in '".implode(", ", $logfile_loc)."' but couldn't find it.<br/>
		if you know where your freeradius log file is located, set it's location in " . $_SERVER['SCRIPT_NAME'];
	exit;
}
	

if (is_readable($logfile) == false) {
	echo "<br/><br/>
		error reading log file: <u>$logfile</u> <br/><br/>
		possible cause is file premissions or file doesn't exist.<br/>";
} else {
    if (file_get_contents($logfile)) {

     $counter = $radiusLineCount;
     $fileReversed = array_reverse(file($logfile));
     foreach ($fileReversed as $line) {
        if($counter == 0) {
          break;
        }
        echo $line . "<br>";
        $counter--;
      }
      // $counter = $radiusLineCount;
      // foreach ($fileReversed as $line) {
      //         if (preg_match("/$radiusFilter/i", $line)) {
      //                 if ($counter == 0)
      //                         break;
      //                 $ret = eregi_replace("\n", "<br>", $line);
      //                 echo $ret;
      //                 $counter--;
      //         }
      // }
    }
}

?>

