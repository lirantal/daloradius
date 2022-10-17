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


	include 'library/opendb.php';

        isset($_REQUEST['ratename']) ? $ratename = $_REQUEST['ratename'] : $ratename = "";
        isset($_REQUEST['ratecost']) ? $ratecost = $_REQUEST['ratecost'] : $ratecost = "";
        isset($_REQUEST['ratetypenum']) ? $ratetypenum = $_REQUEST['ratetypenum'] : $ratetypenum = "";
        isset($_REQUEST['ratetypetime']) ? $ratetypetime = $_REQUEST['ratetypetime'] : $ratetypetime = "";

	$edit_ratename = $ratename; //feed the sidebar variables

	$logAction = "";
	$logDebugSQL = "";

	if (isset($_POST['submit'])) {

                $ratename = $_POST['ratename'];
                $ratetypenum = $_POST['ratetypenum'];
                $ratetypetime = $_POST['ratetypetime'];
                $ratecost = $_POST['ratecost'];

		if (trim($ratename) != "") {

			$currDate = date('Y-m-d H:i:s');
			$currBy = $_SESSION['operator_user'];

			$ratetype = "$ratetypenum/$ratetypetime";

			$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES']." SET ".
			" rateName='".$dbSocket->escapeSimple($ratename)."', ".
			" rateType='".$dbSocket->escapeSimple($ratetype).	"', ".
			" rateCost='".$dbSocket->escapeSimple($ratecost)."', ".
			" updatedate='$currDate', updateby='$currBy' ".
			" WHERE rateName='".$dbSocket->escapeSimple($ratename)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL = "";
			$logDebugSQL .= $sql . "\n";

			$successMsg = "Updated rate: <b> $ratename </b>";
			$logAction .= "Successfully updated rate [$ratename] on page: ";

		} else {
			$failureMsg = "no rate name was entered, please specify a rate name to edit.";
			$logAction .= "Failed updating rate [$ratename] on page: ";
		}

	}


	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES']." WHERE rateName='".$dbSocket->escapeSimple($ratename)."'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	$row = $res->fetchRow();
	$ratename = $row[1];
	list($ratetypenum, $ratetypetime) = explode("/",$row[2]);
	$ratecost = $row[3];
	$creationdate = $row[4];
	$creationby = $row[5];
	$updatedate = $row[6];
	$updateby = $row[7];

	include 'library/closedb.php';


	if (trim($ratename) == "") {
		$failureMsg = "no rate name was entered or found in database, please specify a rate name to edit";
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
	include_once ("library/tabber/tab-layout.php");
?>

<?php
	include ("menu-bill-rates.php");
?>
	<div id="contentnorightbar">

		<h2 id="Intro" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','billratesedit.php') ?>
		:: <?php if (isset($ratename)) { echo $ratename; } ?><h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','billratesedit') ?>
			<br/>
		</div>
		<?php
			include_once('include/management/actionMessages.php');
		?>

		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

	<div class="tabbertab" title="<?php echo t('title','RateInfo'); ?>">


	<fieldset>

		<h302> <?php echo t('title','RateInfo'); ?> </h302>
		<br/>

		<ul>

			<li class='fieldset'>
			<label for='ratename' class='form'><?php echo t('all','RateName') ?></label>
			<input disabled name='ratename' type='text' id='ratename' value='<?php echo $ratename ?>' tabindex=100 />
			</li>

			<li class='fieldset'>
			<label for='ratetype' class='form'><?php echo t('all','RateType') ?></label>

	                <input class='integer' name='ratetypenum' type='text' id='ratetypenum' value='<?php echo $ratetypenum ?>' tabindex=101 />
	                <img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('ratetypenum','increment')" />
	                <img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('ratetypenum','decrement')"/>

	                <select class='form' tabindex=102 name='ratetypetime' id='ratetypetime' >
				<option value='<?php echo $ratetypetime ?>'><?php echo $ratetypetime ?></option>
				<option value=''></option>
	                        <option value='second'>second</option>
 	                        <option value='minute'>minute</option>
				<option value='hour'>hour</option>
	                        <option value='day'>day</option>
	                        <option value='week'>week</option>
	                        <option value='month'>month</option>
	                </select>
			<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('rateTypeTooltip')" />

			<div id='rateTypeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
				<img src='images/icons/comment.png' alt='Tip' border='0' />
				<?php echo t('Tooltip','rateTypeTooltip') ?>
			</div>
			</li>

			<li class='fieldset'>
			<label for='ratecost' class='form'><?php echo t('all','RateCost') ?></label>
			<input class='integer' name='ratecost' type='text' id='ratecost' value='<?php echo $ratecost ?>' tabindex=103 />
        	        <img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('ratecost','increment')" />
	                <img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('ratecost','decrement')"/>
			<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('rateCostTooltip')" />

			<div id='rateCostTooltip'  style='display:none;visibility:visible' class='ToolTip'>
				<img src='images/icons/comment.png' alt='Tip' border='0' />
				<?php echo t('Tooltip','rateCostTooltip') ?>
			</div>
			</li>

			<li class='fieldset'>
			<br/>
			<hr><br/>
			<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000
				class='button' />
			</li>

		</ul>

	</fieldset>

	<input type=hidden value="<?php echo $ratename ?>" name="ratename"/>

</div>

<div class="tabbertab" title="<?php echo t('title','Optional'); ?>">

<fieldset>

        <h302> Optional </h302>
        <br/>

        <br/>
        <h301> Other </h301>
        <br/>

        <br/>
        <label for='creationdate' class='form'><?php echo t('all','CreationDate') ?></label>
        <input disabled value='<?php if (isset($creationdate)) echo $creationdate ?>' tabindex=313 />
        <br/>

        <label for='creationby' class='form'><?php echo t('all','CreationBy') ?></label>
        <input disabled value='<?php if (isset($creationby)) echo $creationby ?>' tabindex=314 />
        <br/>

        <label for='updatedate' class='form'><?php echo t('all','UpdateDate') ?></label>
        <input disabled value='<?php if (isset($updatedate)) echo $updatedate ?>' tabindex=315 />
        <br/>

        <label for='updateby' class='form'><?php echo t('all','UpdateBy') ?></label>
        <input disabled value='<?php if (isset($updateby)) echo $updateby ?>' tabindex=316 />
        <br/>


        <br/><br/>
        <hr><br/>

        <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000
                class='button' />

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
