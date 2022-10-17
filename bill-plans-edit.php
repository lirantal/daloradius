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
 

	function addProfilesToBillingPlans($dbSocket, $planName, $groups) {

		global $logDebugSQL;
		global $configValues;

		// insert usergroup mapping
		if (isset($groups)) {

			foreach ($groups as $group) {

				if (trim($group) != "") {
					$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES']." (id,plan_name,profile_name) ".
						" VALUES (0, '".$dbSocket->escapeSimple($planName)."', '".$dbSocket->escapeSimple($group)."') ";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}
			}
		}
	}
	
	
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	include 'library/opendb.php';

	isset($_POST['planNameOld']) ? $planNameOld = $_POST['planNameOld'] : $planNameOld = "";
	isset($_REQUEST['planName']) ? $planName = $_REQUEST['planName'] : $planName = "";
	isset($_POST['planId']) ? $planId = $_POST['planId'] : $planId = "";
	isset($_POST['planType']) ? $planType = $_POST['planType'] : $planType = "";
	isset($_POST['planTimeType']) ? $planTimeType = $_POST['planTimeType'] : $planTimeType = "";
	isset($_POST['planTimeBank']) ? $planTimeBank = $_POST['planTimeBank'] : $planTimeBank = "";
	isset($_POST['planTimeRefillCost']) ? $planTimeRefillCost = $_POST['planTimeRefillCost'] : $planTimeRefillCost = "";
	isset($_POST['planBandwidthUp']) ? $planBandwidthUp = $_POST['planBandwidthUp'] : $planBandwidthUp = "";
	isset($_POST['planBandwidthDown']) ? $planBandwidthDown = $_POST['planBandwidthDown'] : $planBandwidthDown = "";
	isset($_POST['planTrafficTotal']) ? $planTrafficTotal = $_POST['planTrafficTotal'] : $planTrafficTotal = "";
	isset($_POST['planTrafficDown']) ? $planTrafficDown = $_POST['planTrafficDown'] : $planTrafficDown = "";
	isset($_POST['planTrafficUp']) ? $planTrafficUp = $_POST['planTrafficUp'] : $planTrafficUp = "";
	isset($_POST['planTrafficRefillCost']) ? $planTrafficRefillCost = $_POST['planTrafficRefillCost'] : $planTrafficRefillCost = "";
	isset($_POST['planRecurring']) ? $planRecurring = $_POST['planRecurring'] : $planRecurring = "";
	isset($_POST['planRecurringPeriod']) ? $planRecurringPeriod = $_POST['planRecurringPeriod'] : $planRecurringPeriod = "";
	isset($_POST['planRecurringBillingSchedule']) ? $planRecurringBillingSchedule = $_POST['planRecurringBillingSchedule'] : $planRecurringBillingSchedule = "Fixed";
	isset($_POST['planCost']) ? $planCost = $_POST['planCost'] : $planCost = "";
	isset($_POST['planSetupCost']) ? $planSetupCost = $_POST['planSetupCost'] : $planSetupCost = "";
	isset($_POST['planTax']) ? $planTax = $_POST['planTax'] : $planTax = "";
	isset($_POST['planCurrency']) ? $planCurrency = $_POST['planCurrency'] : $planCurrency = "";
	isset($_POST['planActive']) ? $planActive = $_POST['planActive'] : $planActive = "";
	isset($_POST['planGroup']) ? $planGroup = $_POST['planGroup'] : $planGroup = "";
	isset($_POST['groups']) ? $groups = $_POST['groups'] : $groups = "";

	$edit_planname = $planName; //feed the sidebar variables	

	$logAction = "";
	$logDebugSQL = "";

	if (isset($_POST['submit'])) {

		if (trim($planName) != "") {

			$currDate = date('Y-m-d H:i:s');
			$currBy = $_SESSION['operator_user'];

			$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']." SET ".
			" planName='".$dbSocket->escapeSimple($planName)."', ".
			" planId='".$dbSocket->escapeSimple($planId)."', ".
			" planType='".$dbSocket->escapeSimple($planType)."', ".
			" planTimeType='".$dbSocket->escapeSimple($planTimeType)."', ".
			" planTimeBank='".$dbSocket->escapeSimple($planTimeBank)."', ".
			" planTimeRefillCost='".$dbSocket->escapeSimple($planTimeRefillCost)."', ".
			" planBandwidthUp='".$dbSocket->escapeSimple($planBandwidthUp)."', ".
			" planBandwidthDown='".$dbSocket->escapeSimple($planBandwidthDown)."', ".
			" planTrafficTotal='".$dbSocket->escapeSimple($planTrafficTotal)."', ".
			" planTrafficDown='".$dbSocket->escapeSimple($planTrafficDown)."', ".
			" planTrafficUp='".$dbSocket->escapeSimple($planTrafficUp)."', ".
			" planTrafficRefillCost='".$dbSocket->escapeSimple($planTrafficRefillCost)."', ".
			" planRecurring='".$dbSocket->escapeSimple($planRecurring)."', ".
			" planRecurringPeriod='".$dbSocket->escapeSimple($planRecurringPeriod)."', ".
			" planRecurringBillingSchedule='".$dbSocket->escapeSimple($planRecurringBillingSchedule)."', ".
			" planCost='".$dbSocket->escapeSimple($planCost)."', ".
			" planSetupCost='".$dbSocket->escapeSimple($planSetupCost)."', ".
			" planTax='".$dbSocket->escapeSimple($planTax)."', ".
			" planCurrency='".$dbSocket->escapeSimple($planCurrency)."', ".
			" planActive='".$dbSocket->escapeSimple($planActive)."', ".
			" planGroup='".$dbSocket->escapeSimple($planGroup)."', ".
			" updatedate='$currDate', updateby='$currBy' ".
			" WHERE planName='".$dbSocket->escapeSimple($planNameOld)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL = "";
			$logDebugSQL .= $sql . "\n";
			

			// to change a plan's association of the profiles we removed all existing
			// association for this group and re-create it.
			
			$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES'].
			" WHERE plan_name='".$dbSocket->escapeSimple($planNameOld)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL = "";
			$logDebugSQL .= $sql . "\n";
			
			addProfilesToBillingPlans($dbSocket, $planName, $groups);

			$successMsg = "Updated billing plan settings for: <b> $planName </b>";
			$logAction .= "Successfully updated billing plan settings for hotspot [$planName] on page: ";
			
		} else {
			$failureMsg = "no billing plan name was entered, please specify a billing plan name to edit";
			$logAction .= "Failed updating billing plan settings for plan [$planName] on page: ";
		}
		


		
	}
	
	
	function getProfilesOfPlan($dbSocket, $plan_name) {
		
		global $configValues;
		
		$profiles = array();
		
		// get all profiles associated with this plan name
		$sql = "SELECT profile_name FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES'].
			" WHERE plan_name='".$dbSocket->escapeSimple($plan_name)."'";
		$res = $dbSocket->query($sql);
		while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
			$profiles[] = $row['profile_name'];
		
		return $profiles;
		
	}

	$planProfiles = getProfilesOfPlan($dbSocket, $planName);

	// fill-in username and password in the textboxes
	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']." WHERE planName='".$dbSocket->escapeSimple($planName)."'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
	$planName = $row['planName'];
	$planId = $row['planId'];
	$planType = $row['planType'];
	$planTimeType = $row['planTimeType'];
	$planTimeBank = $row['planTimeBank'];
	$planTimeRefillCost = $row['planTimeRefillCost'];
	$planBandwidthUp = $row['planBandwidthUp'];
	$planBandwidthDown = $row['planBandwidthDown'];
	$planTrafficTotal = $row['planTrafficTotal'];
	$planTrafficDown = $row['planTrafficDown'];
	$planTrafficUp = $row['planTrafficUp'];
	$planTrafficRefillCost = $row['planTrafficRefillCost'];
	$planRecurring = $row['planRecurring'];
	$planRecurringPeriod = $row['planRecurringPeriod'];
	$planRecurringBillingSchedule = $row['planRecurringBillingSchedule'];
	$planCost = $row['planCost'];
	$planSetupCost = $row['planSetupCost'];
	$planTax = $row['planTax'];
	$planCurrency = $row['planCurrency'];
	$planActive = $row['planActive'];
	$planGroup = $row['planGroup'];
	$creationdate = $row['creationdate'];
	$creationby = $row['creationby'];
	$updatedate = $row['updatedate'];
	$updateby = $row['updateby'];

	include 'library/closedb.php';

	if (trim($planName) == "") {
		$failureMsg = "no billing plan name was entered, please specify a billing plan name to edit</b>";
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
<script type="text/javascript" src="library/javascript/pages_common.js"></script>
<script type="text/javascript" src="library/javascript/ajax.js"></script>
<script type="text/javascript" src="library/javascript/dynamic_attributes.js"></script>
<script type="text/javascript" src="library/javascript/ajaxGeneric.js"></script>
<?php
	include_once ("library/tabber/tab-layout.php");
?>
 
<?php
	include ("menu-bill-plans.php");
?>		
	<div id="contentnorightbar">
		
		<h2 id="Intro" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','billplansedit.php') ?>
		:: <?php if (isset($planName)) { echo $planName; } ?><h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','billplansedit') ?>
			<br/>
		</div>
		<?php
			include_once('include/management/actionMessages.php');
		?>

		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

	<div class="tabbertab" title="<?php echo t('title','PlanInfo'); ?>">


	<fieldset>

		<h302> <?php echo t('title','PlanInfo'); ?> </h302>
		<br/>

		<ul>

                <li class='fieldset'>
                <label for='name' class='form'><?php echo t('all','PlanName') ?></label>
                <input name='planName' type='text' id='planName' value='<?php echo $planName ?>' tabindex=100 />
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planNameTooltip')" />

                <div id='planNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planNameTooltip') ?>
                </div>
                </li>

                <li class='fieldset'>
                <label for='planId' class='form'><?php echo t('all','PlanId') ?></label>
                <input name='planId' type='text' id='planId' value='<?php echo $planId ?>' tabindex=101 />
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planIdTooltip')" />

                <div id='planIdTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planIdTooltip') ?>
                </div>
                </li>

                <li class='fieldset'>
                <label for='planType' class='form'><?php echo t('all','PlanType') ?></label>
                <select class='form' tabindex=102 name='planType' >
			<option value='<?php echo $planType ?>'><?php echo $planType ?></option>
			<option value=''></option>
                        <option value='PayPal'>PayPal</option>
                        <option value='Prepaid'>Prepaid</option>
                        <option value='Postpaid'>Postpaid</option>
                </select>
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planTimeTypeTooltip')" />

                <div id='planTimeTypeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planTimeTypeTooltip') ?>
                </div>
                </li>



                <li class='fieldset'>
                <label for='planRecurring' class='form'><?php echo t('all','PlanRecurring') ?></label>
                <select class='form' name='planRecurring' id='planRecurring' tabindex=101>
			<option value='<?php echo $planRecurring ?>'><?php echo $planRecurring ?></option>
			<option value=''></option>
                        <option value='No'>No</option>
                        <option value='Yes'>Yes</option>
                </select>
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planRecurringTooltip')" />
                <div id='planRecurringTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planRecurringTooltip') ?>
                </div>
                </li>

                <li class='fieldset'>
                <label for='planRecurringPeriod' class='form'><?php echo t('all','PlanRecurringPeriod') ?></label>
                <select class='form' name='planRecurringPeriod' id='planRecurringPeriod' tabindex=101 >
						<option value='<?php echo $planRecurringPeriod ?>'><?php echo $planRecurringPeriod ?></option>
						<option value=''></option>
                        <option value='Never'>Never</option>
                        <option value='Daily'>Daily</option>
                        <option value='Weekly'>Weekly</option>
                        <option value='Monthly'>Monthly</option>
						<option value='Quarterly'>Quarterly</option>
						<option value='Semi-Yearly'>Semi-Yearly</option>
						<option value='Yearly'>Yearly</option>
                </select>
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planRecurringPeriodTooltip')" />

                <div id='planRecurringPeriodTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planRecurringPeriodTooltip') ?>
                </div>
                </li>


                <li class='fieldset'>
                <label for='planRecurringBillingSchedule' class='form'><?php echo t('all','planRecurringBillingSchedule') ?></label>
                <select class='form' name='planRecurringBillingSchedule' id='planRecurringBillingSchedule' tabindex=101 >
						<option value='<?php echo $planRecurringBillingSchedule ?>'><?php echo $planRecurringBillingSchedule ?></option>
						<option value=''></option>
						<option value='Fixed'>Fixed</option>
						<option value='Anniversary'>Anniversary</option>
                </select>
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planRecurringBillingScheduleToolTip')" />

                <div id='planRecurringBillingScheduleToolTip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planRecurringBillingScheduleTooltip') ?>
                </div>
                </li>
                
                


                <li class='fieldset'>
                <label for='planCost' class='form'><?php echo t('all','PlanCost') ?></label>
                <input name='planCost' type='text' id='planCost' value='<?php echo $planCost ?>' tabindex=101 />
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planCostTooltip')" />

                <div id='planCostTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planCostTooltip') ?>
                </div>
                </li>

                <li class='fieldset'>
                <label for='planSetupCost' class='form'><?php echo t('all','PlanSetupCost') ?></label>
                <input name='planSetupCost' type='text' id='planSetupCost' value='<?php echo $planSetupCost ?>' tabindex=101 />
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planSetupCostTooltip')" />

                <div id='planSetupCostTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planSetupCostTooltip') ?>
                </div>
                </li>

                <li class='fieldset'>
                <label for='planTax' class='form'><?php echo t('all','PlanTax') ?></label>
                <input name='planTax' type='text' id='planTax' value='<?php echo $planTax ?>' tabindex=101 />
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planTaxTooltip')" />

                <div id='planTaxTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planTaxTooltip') ?>
                </div>
                </li>

                <li class='fieldset'>
                <label for='planCurrency' class='form'><?php echo t('all','PlanCurrency') ?></label>
                <select class='form' tabindex=102 name='planCurrency' >
                        <option value='<?php echo $planCurrency ?>'><?php echo $planCurrency ?></option>
                        <option value=''></option>
                        <option value='USD'>USD</option>
                        <option value='EUR'>EUR</option>
                        <option value='GBP'>GBP</option>
                        <option value='CAD'>CAD</option>
                        <option value='JPY'>JPY</option>
                        <option value='AUD'>AUD</option>
                        <option value='NZD'>NZD</option>
                        <option value='CHF'>CHF</option>
                        <option value='HKD'>HKD</option>
                        <option value='SGD'>SGD</option>
                        <option value='SEK'>SEK</option>
                        <option value='DKK'>DKK</option>
                        <option value='PLN'>PLN</option>
                        <option value='NOK'>NOK</option>
                        <option value='HUF'>HUF</option>
                        <option value='CZK'>CZK</option>
                        <option value='ILS'>ILS</option>
                        <option value='MXN'>MXN</option>
						<option value='KSH'>KSH</option>
                </select>
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planCurrencyTooltip')" />

                <div id='planCurrencyTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planCurrencyTooltip') ?>
                </div>
                </li>



                <li class='fieldset'>
                <label for='planActive' class='form'><?php echo t('all','PlanActive') ?></label>
                <select class='form' tabindex=103 name='planActive' >
                        <option value='<?php echo $planActive ?>'><?php echo $planActive ?></option>
                        <option value='yes'>Yes</option>
                        <option value='no'>No</option>
                </select>
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planActiveTooltip')" />

                <div id='planCurrencyTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planActiveTooltip') ?>
                </div>
                </li>




<?php
/*
                <li class='fieldset'>
                <label for='profile' class='form'><?php echo t('all','Profile')?></label>
                <?php
                        include_once 'include/management/populate_selectbox.php';
                        populate_groups($planGroup,"planGroup");
                ?>
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planGroupTooltip')" />

                <div id='planGroupTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planGroupTooltip') ?>
                </div>
                </li>
*/
?>

			<li class='fieldset'>
			<br/>
			<hr><br/>
			<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000
				class='button' />
			</li>

		</ul>

	</fieldset>
	</div>

        <div class="tabbertab" title="<?php echo t('title','TimeSettings'); ?>">
        <fieldset>

                <h302> <?php echo t('title','PlanInfo'); ?> </h302>
                <br/>

                <ul>

                <li class='fieldset'>
                <label for='planTimeType' class='form'><?php echo t('all','PlanTimeType') ?></label>
                <select class='form' tabindex=102 name='planTimeType' >
			<option value='<?php echo $planTimeType ?>'><?php echo $planTimeType ?></option>
			<option value=''></option>
                        <option value='Accumulative'>Accumulative</option>
                        <option value='Time-To-Finish'>Time-To-Finish</option>
                </select>
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planTimeTypeTooltip')" />

                <div id='planTimeTypeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planTimeTypeTooltip') ?>
                </div>
                </li>


                <li class='fieldset'>
                <label for='planTimeBank' class='form'><?php echo t('all','PlanTimeBank') ?></label>
                <input name='planTimeBank' type='text' id='planTimeBank' value='<?php echo $planTimeBank ?>' tabindex=101 />
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planTimeBankTooltip')" />

                <div id='planTimeBankTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planTimeBankTooltip') ?>
                </div>
                </li>


                <li class='fieldset'>
                <label for='planTimeRefillCost' class='form'><?php echo t('all','PlanTimeRefillCost') ?></label>
                <input name='planTimeRefillCost' type='text' id='planTimeRefillCost' value='<?php echo $planTimeRefillCost ?>' tabindex=101 />
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planTimeRefillCostTooltip')" />

                <div id='planTimeRefillCostTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planTimeRefillCostTooltip') ?>
                </div>
                </li>

                <li class='fieldset'>
                <br/>
                <hr><br/>
                <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000 class='button' />
                </li>

                </ul>

        </fieldset>
        </div>


        <div class="tabbertab" title="<?php echo t('title','BandwidthSettings'); ?>">
        <fieldset>

                <h302> <?php echo t('title','PlanInfo'); ?> </h302>
                <br/>

                <ul>

                <li class='fieldset'>
                <label for='planBandwidthUp' class='form'><?php echo t('all','PlanBandwidthUp') ?></label>
                <input name='planBandwidthUp' type='text' id='planBandwidthUp' value='<?php echo $planBandwidthUp ?>' tabindex=101 />
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planBandwidthUpTooltip')" />

                <div id='planBandwidthUpTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planBandwidthUpTooltip') ?>
                </div>
                </li>

                <li class='fieldset'>
                <label for='planBandwidthDown' class='form'><?php echo t('all','PlanBandwidthDown') ?></label>
                <input name='planBandwidthDown' type='text' id='planBandwidthDown' value='<?php echo $planBandwidthDown ?>' tabindex=101 />
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planBandwidthDownTooltip')" />

                <div id='planBandwidthDownTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planBandwidthDownTooltip') ?>
                </div>
                </li>



                <li class='fieldset'>
                <label for='planTrafficTotal' class='form'><?php echo t('all','PlanTrafficTotal') ?></label>
                <input name='planTrafficTotal' type='text' id='planTrafficTotal' value='<?php echo $planTrafficTotal ?>' tabindex=101 />
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planTrafficTotalTooltip')" />

                <div id='planTrafficTotalTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planTrafficTotalTooltip') ?>
                </div>
                </li>

                <li class='fieldset'>
                <label for='planTrafficDown' class='form'><?php echo t('all','PlanTrafficDown') ?></label>
                <input name='planTrafficDown' type='text' id='planTrafficDown' value='<?php echo $planTrafficDown ?>' tabindex=101 />
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planTrafficDownTooltip')" />

                <div id='planTrafficDownTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planTrafficDownTooltip') ?>
                </div>
                </li>



                <li class='fieldset'>
                <label for='planTrafficUp' class='form'><?php echo t('all','PlanTrafficUp') ?></label>
                <input name='planTrafficUp' type='text' id='planTrafficUp' value='<?php echo $planTrafficUp ?>' tabindex=101 />
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planTrafficUpTooltip')" />

                <div id='planTrafficUpTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planTrafficUpTooltip') ?>
                </div>
                </li>



                <li class='fieldset'>
                <label for='planTrafficRefillCost' class='form'><?php echo t('all','PlanTrafficRefillCost') ?></label>
                <input name='planTrafficRefillCost' type='text' id='planTrafficRefillCost' value='<?php echo $planTrafficRefillCost ?>' tabindex=101 />
                <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planTrafficRefillCostTooltip')" />

                <div id='planTrafficRefillCostTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?php echo t('Tooltip','planTrafficRefillCostTooltip') ?>
                </div>
                </li>

                <li class='fieldset'>
                <br/>
                <hr><br/>
                <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000 class='button' />
                </li>

                </ul>

        </fieldset>
        </div>





        <div class="tabbertab" title="<?php echo t('title','Profiles'); ?>">
        <fieldset>

		<h302> <?php echo t('title','Profiles'); ?> </h302>
		
		<h301> Associated Profiles </h301>
		<br/>
		
			<ul>
			
			<?php
				include_once('include/management/populate_selectbox.php');
			?>
			
			<?php foreach($planProfiles as $profile) {
			
				echo "
					<li class='fieldset'>
					<label for='group' class='form'>".t('all','Profile')."</label>
				";
				populate_groups($profile,"groups[]", "form", "", $profile);
			
			}
			?>
			
			</ul>
		
		
		<h301> <?php echo t('title','Profiles'); ?> </h301>
		<br/>
	
			<ul>
			
					<li class='fieldset'>
					<label for='profile' class='form'><?php echo t('all','Profile')?></label>
					<?php  
						include_once('include/management/populate_selectbox.php');
						populate_groups("Select Profiles","groups[]");
					?>
			
					<a class='tablenovisit' href='#'
						onClick="javascript:ajaxGeneric('include/management/dynamic_groups.php','getGroups','divContainerProfiles',genericCounter('divCounter')+'&elemName=groups[]');">Add</a>
			
					<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('group')" />
			
					<div id='divContainerProfiles'>
					</div>
			
			
					<div id='groupTooltip'  style='display:none;visibility:visible' class='ToolTip'>
						<img src='images/icons/comment.png' alt='Tip' border='0' /> 
						<?php echo t('Tooltip','groupTooltip') ?>
					</div>
					</li>


				<li class='fieldset'>
					<br/>
					<hr><br/>
					<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000 class='button' />
				</li>

			</ul>

	</fieldset>
	</div>







	<input type=hidden value="<?php echo $planName ?>" name="planNameOld"/>

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
