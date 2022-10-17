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

	isset($_REQUEST['planName']) ? $plans = $_REQUEST['planName'] : $plans = "";
	$logAction = "";
	$logDebugSQL = "";

	$showRemoveDiv = "block";

	if (isset($_REQUEST['planName'])) {

		if (!is_array($plans))
			$plans = array($plans);

		$allPlans = "";

		include 'library/opendb.php';
	
		foreach ($plans as $variable=>$value) {
			if (trim($value) != "") {

				$planName = $value;
				$allPlans .= $planName . ", ";

				// remove the plan entry from the plans table
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
						" WHERE planName='".$dbSocket->escapeSimple($planName)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
				
				// remove plan's association with profiles from the plans_profiles table
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES'].
						" WHERE plan_name='".$dbSocket->escapeSimple($planName)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
				
				$successMsg = "Deleted billing plan(s): <b> $allPlans </b>";
				$logAction .= "Successfully deleted billing plan(s) [$allPlans] on page: ";
				
			} else { 
				$failureMsg = "no billing plan name was entered, please specify a billing plan name to remove from database";
				$logAction .= "Failed deleting billing plan(s) [$allPlans] on page: ";
			}

		} //foreach

		$plans = "";
		include 'library/closedb.php';

		$showRemoveDiv = "none";
	} 


	include_once('library/config_read.php');
    $log = "visited page: ";

?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
</head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<?php
	include ("menu-bill-plans.php");
?>		

<div id="contentnorightbar">

	<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','billplansdel.php') ?>
	:: <?php if (isset($plans)) { echo $plans; } ?><h144>&#x2754;</h144></a></h2>

	<div id="helpPage" style="display:none;visibility:visible" >
		<?php echo t('helpPage','billplansdel') ?>
		<br/>
	</div>
	<?php
		include_once('include/management/actionMessages.php');
	?>

	<div id="removeDiv" style="display:<?php echo $showRemoveDiv ?>;visibility:visible" >
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

	<fieldset>

		<h302> <?php echo t('title','PlanRemoval') ?> </h302>
		<br/>

		<label for='planNname' class='form'><?php echo t('all','PlanName') ?></label>
		<input name='planName[]' type='text' id='planName' value='<?php echo $plans ?>' tabindex=100 autocomplete="off" />
		<br/>

		<br/><br/>
		<hr><br/>

		<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=1000 
			class='button' />

	</fieldset>

	</form>
	</div>


<?php
        include_once("include/management/autocomplete.php");

        if ($autoComplete) {
                echo "<script type=\"text/javascript\">
                      autoComEdit = new DHTMLSuite.autoComplete();
                      autoComEdit.add('planName','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteBillingPlans');
                      </script>";
        }

?>


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





