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
 *		this script displays the daloradius log file ofcourse
 *		proper premissions must be applied on the log file for the web
 *		server to be able to read it
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */


if (isset($configValues['CONFIG_LOG_FILE'])) {
	$logfile = $configValues['CONFIG_LOG_FILE'];

	if (!file_exists($logfile)) {

                $failureMsg = "error reading log file: <b>$logfile</b><br/>".
				"looked for log file in $logfile but couldn't find it.<br/>".
				"if you know where your daloradius log file is located, set it's location in your library/daloradius.conf file";
	} else {


		if (is_readable($logfile) == false) {

	                $failureMsg = "error reading log file: <b>$logfile</b><br/>".
				"possible cause is file premissions or file doesn't exist.<br/>";

		} else {
		    if (file_get_contents($logfile)) {
				$fileReversed = array_reverse(file($logfile));
				$counter = $daloradiusLineCount;

				// This doesn't take in any filter value
				// from the forms.
				// This takes in the log count though.
				foreach ($fileReversed as $line) {
					if ($counter == 0) {
						break;
					}
					echo $line . "<br>";
					$counter--;
				}
				// Old Code
				// $counter = $daloradiusLineCount;
				// foreach ($fileReversed as $line) {
				// 	if (preg_match("/$daloradiusFilter/i", $line)) {
				// 		if ($counter == 0)
				// 			break;
				// 		$ret = eregi_replace("\n", "<br>", $line);
				// 		echo $ret;
				// 		$counter--;
				// 	}
				// }
			}
		}
	}
}

?>
