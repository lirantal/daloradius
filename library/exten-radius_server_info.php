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
 *		this script runs a check to see if freeradius is up and running
*		the check is done by looking for a 'radius' process listening
*		on any socket interface. clumsy, but that's what we got for now
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */
 
function check_service($sname) {
	if ($sname != '') {
		system("pgrep ".escapeshellarg($sname)." >/dev/null 2>&1", $ret_service);
		if ($ret_service == 0) {
			return "Enabled";
		} else {
			return "Disabled";
		}
	} else {
		return "no service name";
	}
}

?>

<?php
	echo "<h3>Service Status</h3>";
?>

<table class='summarySection'>
  <tr>
    <td class='summaryKey'> Radius </td>
    <td class='summaryValue'><span class='sleft'><?php echo check_service("radius"); ?></span> </td>
  </tr>
  <tr>
    <td class='summaryKey'> Mysql </td>
    <td class='summaryValue'><span class='sleft'><?php echo check_service("mysql");  ?></span> </td>
  </tr>
</table>
