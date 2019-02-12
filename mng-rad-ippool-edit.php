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

	isset($_GET['poolname']) ? $poolname = $_GET['poolname'] : $poolname = "";
	isset($_GET['ipaddress']) ? $ipaddress = $_GET['ipaddress'] : $ipaddress = "";
	isset($_GET['ipaddressold']) ? $ipaddressold = $_GET['ipaddressold'] : $ipaddressold = "";

	$logAction = "";
	$logDebugSQL = "";

	if (isset($_POST['submit'])) {
	
		$poolname = $_POST['poolname'];
		$ipaddress = $_POST['ipaddress'];
		$ipaddressold = $_POST['ipaddressold'];

		include 'library/opendb.php';

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADIPPOOL'].
			" WHERE pool_name='".$dbSocket->escapeSimple($poolname)."'".
			" AND framedipaddress='".$dbSocket->escapeSimple($ipaddressold)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 1) {

			if (trim($poolname) != "" and trim($ipaddress) != "" and trim($ipaddressold) != "") {

				// insert ippool name and ip address
				$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_RADIPPOOL'].
					" SET framedipaddress='".$dbSocket->escapeSimple($ipaddress)."'".
					" WHERE pool_name='".$dbSocket->escapeSimple($poolname)."'".
					" AND framedipaddress='".$dbSocket->escapeSimple($ipaddressold)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
			
				$successMsg = "Updated database with new IP Address: <b>$ipaddress</b> for Pool Name: <b>$poolname</b>";
				$logAction .= "Successfully updated IP Address [$ipaddress] for Pool Name [$poolname] on page: ";
			} else {
				$failureMsg = "IP Address left unchanged";
				$logAction .= "IP Address left unchanged";
				//$failureMsg = "No IP Address or Pool Name was entered, it is required that you specify both";
				//$logAction .= "Failed updating (missing ipaddress/poolname) IP Address [$ipaddress] for Pool Name [$poolname] on page: ";
			}
		} else {
			$failureMsg = "The IP Address <b>$ipaddress</b> for Pool Name <b>$poolname</b> doesn't exist in database";
			$logAction .= "Failed updating non-existing IP Address [$ipaddress] for Pool Name [$poolname] on page: ";
		}

		include 'library/closedb.php';
	}
	

	include_once('library/config_read.php');
    $log = "visited page: ";

	

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>

<script src="library/javascript/pages_common.js" type="text/javascript"></script>

<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>


<?php
	include_once ("library/tabber/tab-layout.php");
?> 
 
<?php
	include ("menu-mng-rad-ippool.php");
?>

	<div id="contentnorightbar">
	
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngradippoolnew.php') ?>
		<h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >				
			<?php echo t('helpPage','mngradippoolnew') ?>
			<br/>
		</div>
<?php
	include_once('include/management/actionMessages.php');
?>

		<form name="newippool" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<div class="tabber">
	<div class="tabbertab" title="<?php echo t('title','IPPoolInfo'); ?>">
	<fieldset>

		<h302> <?php echo t('title','IPPoolInfo') ?> </h302>
		<br/>

			<label for='poolname' class='form'><?php echo t('all','PoolName') ?></label>
			<input name='poolname' type='hidden' id='poolname' value='<?php echo $poolname ?>' tabindex=100 />
			<input disabled name='poolname' type='text' id='poolname' value='<?php echo $poolname ?>' tabindex=100 />
			<br />

			<label for='ipaddressold' class='form'><?php echo t('all','IPAddress') ?></label>
			<input name='ipaddressold' type='text' id='ipaddressold' value='<?php echo $ipaddressold ?>' tabindex=101 />
			<br />

			<label for='ipaddress' class='form'>New <?php echo t('all','IPAddress') ?></label>
			<input name='ipaddress' type='text' id='ipaddress' value='<?php echo $ipaddress ?>' tabindex=102 />
			<br />

			<br/><br/>
			<hr><br/>

			<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />

        </fieldset>
     </div>
</div>
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
