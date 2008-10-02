<?php
# daloRADIUS edition - fixed up variable definition through-out the code
# as well as parted the code for the sake of modularity and ability to 
# to support templates and languages easier.
# Copyright (C) Enginx and Liran Tal 2007, 2008

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
