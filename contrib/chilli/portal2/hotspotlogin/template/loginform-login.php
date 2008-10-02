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
 *
 * Authors:     Liran Tal <liran@enginx.com>
 *
 * daloRADIUS edition - fixed up variable definition through-out the code
 * as well as parted the code for the sake of modularity and ability to 
 * to support templates and languages easier.
 * Copyright (C) Enginx and Liran Tal 2007, 2008
 * 
 *********************************************************************************************************
 */


echo "
	<form name='form1' method='post' action='$loginpath'>
		<input type='hidden' name='challenge' value='$challenge'>
		<input type='hidden' name='uamip' value='$uamip'>
		<input type='hidden' name='uamport' value='$uamport'>
		<input type='hidden' name='userurl' value='$userurl'>

		<center>
		<table border='0' cellpadding='5' cellspacing='0' style='width: 217px;'>
		<tbody>
		<tr>
			<td align='right'>$centerUsername:</td>
        		<td><input style='font-family: Arial' type='text' name='UserName' size='20' maxlength='128'></td>
		</tr>

		<tr>
		        <td align='right'>$centerPassword:</td>
		        <td><input style='font-family: Arial' type='password' name='Password' size='20' maxlength='128'></td>
      		</tr>

		<tr>
		        <td align='center' colspan='2' height='23'><input type='submit' name='button' value='Login' 
				onClick=\"javascript:popUp('$loginpath?res=popup1&uamip=$uamip&uamport=$uamport')\"></td> 
      		</tr>

		</tbody>
		</table>
		</center>
	</form>
</body>
</html>
";


?>
