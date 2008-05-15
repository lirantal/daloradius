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
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	$type = "";
	if (isset($_POST['submit'])) {

	        $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : '';


		if (trim($type) != "") {
			include 'library/opendb.php';

			// delete all attributes associated with a username
			$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALORATES']." WHERE type='$type'";
			$res = $dbSocket->query($sql);
			$logDebugSQL = "";
			$logDebugSQL .= $sql . "\n";

			$actionStatus = "success";
			$actionMsg = "Deleted rate type from database: <b> $type </b>";
			$logAction = "Successfully deleted rate type [$type] on page: ";

			include 'library/closedb.php';

		} else {
			$actionStatus = "failure";
			$actionMsg = "you didn't specify a rate type";
			$logAction = "Failed deleting empty rate type on page: ";

		}


	}



	if (isset($_REQUEST['type']))
		$type = $_REQUEST['type'];
	else
		$type = "";

	if (trim($type) != "") {
		$type = $_REQUEST['type'];
	} else {
		$actionStatus = "failure";
		$actionMsg = "no type was entered, please specify a rate type to delete";
	}


	include_once('library/config_read.php');
    $log = "visited page: ";



?>

<?php

    include ("menu-billing.php");

?>		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><?echo $l['Intro']['billratesdel.php']; ?></h2>
				
				<p>
				<?echo $l['captions']['providebillratetodel']; ?>
				<br/><br/>
<?php
		if (trim($type) == "") { echo $l['messages']['missingratetype']." <br/>";  }

?>
				</p>
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
<tr><td>
						<b>Type</b>
</td><td>
						<input value="<?php echo $type ?>" name="type"/><br/>
</td></tr>
</table>
						<br/><br/>
<center>
						<input type="submit" name="submit" value="Apply"/>
</center>
				</form>

				
<?php
	include('include/config/logging.php');
?>				
		</div>
		
		<div id="footer">
		
								<?php
        include 'page-footer.php';
?>

		
		</div>
		
</div>
</div>


</body>
</html>
