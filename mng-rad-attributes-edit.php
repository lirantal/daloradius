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

	$logAction = "";
	$logDebugSQL = "";

	isset($_REQUEST['vendor']) ? $vendor = $_REQUEST['vendor'] : $vendor = "";
	isset($_REQUEST['attribute']) ? $attribute = $_REQUEST['attribute'] : $attribute = "";

	if (isset($_POST["submit"])) {

		isset($_POST['vendor']) ? $vendor = $_POST['vendor'] : $vendor = "";
		isset($_POST['attributeOld']) ? $attributeOld = $_POST['attributeOld'] : $attributeOld = "";
		isset($_POST['attribute']) ? $attribute = $_POST['attribute'] : $attribute = "";
		isset($_POST['type']) ? $type = $_POST['type'] : $type = "";
		isset($_POST['RecommendedOP']) ? $RecommendedOP = $_POST['RecommendedOP'] : $RecommendedOP = "";
		isset($_POST['RecommendedTable']) ? $RecommendedTable = $_POST['RecommendedTable'] : $RecommendedTable = "";
		isset($_POST['RecommendedTooltip']) ? $RecommendedTooltip = $_POST['RecommendedTooltip'] : $RecommendedTooltip = "";
		isset($_POST['RecommendedHelper']) ? $RecommendedHelper = $_POST['RecommendedHelper'] : $RecommendedHelper = "";

		include 'library/opendb.php';

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALODICTIONARY']." WHERE vendor='".$dbSocket->escapeSimple($vendor).
			"' AND attribute='".$dbSocket->escapeSimple($attribute)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 1) {
			if (trim($vendor) != "" and trim($attribute) != "") {
				// update vendor/attribute pairs to database
				$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALODICTIONARY']." SET 
					type='".
					$dbSocket->escapeSimple($type)."', attribute='".$dbSocket->escapeSimple($attribute).
					"', RecommendedOP='".$dbSocket->escapeSimple($RecommendedOP).
					"', RecommendedTable='".$dbSocket->escapeSimple($RecommendedTable).
					"', RecommendedTooltip='".$dbSocket->escapeSimple($RecommendedTooltip).
					"', RecommendedHelper='".$dbSocket->escapeSimple($RecommendedHelper).
					"' WHERE Vendor='$vendor' AND Attribute='$attributeOld'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$successMsg = "Updated database with vendor attribute: <b>$attribute</b> of vendor: <b>$vendor</b>";
				$logAction .= "Successfully update vendor [$vendor] and attribute [$attribute] on page: ";
			} else {
				$failureMsg = "you must provide atleast a vendor name and attribute";	
				$logAction .= "Failed updating vendor [$vendor] and attribute [$attribute] on page: ";
			}
		} else { 
			$failureMsg = "You have tried to update a vendor's attribute that either is not present in the database or there
					may be more than 1 entry for this vendor attribute in database (attribute :$attribute)";
			$logAction .= "Failed updating vendor attribute already in database [$attribute] on page: ";		
		}
	
		include 'library/closedb.php';

	}



	include 'library/opendb.php';

	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALODICTIONARY']." WHERE vendor='".$dbSocket->escapeSimple($vendor).
		"' AND attribute='".$dbSocket->escapeSimple($attribute)."'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	isset($row['Attribute']) ? $attribute = $row['Attribute'] : $attribute = "";
	isset($row['Type']) ? $type = $row['Type'] : $type = "";
	isset($row['Vendor']) ? $vendor = $row['Vendor'] : $vendor = "";
	isset($row['RecommendedOP']) ? $RecommendedOP = $row['RecommendedOP'] : $RecommendedOP = "";
	isset($row['RecommendedTable']) ? $RecommendedTable = $row['RecommendedTable'] : $RecommendedTable = "";
	isset($row['RecommendedTooltip']) ? $RecommendedTooltip = $row['RecommendedTooltip'] : $RecommendedTooltip = "";
	isset($row['RecommendedHelper']) ? $RecommendedHelper = $row['RecommendedHelper'] : $RecommendedHelper = "";

	include 'library/closedb.php';

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

	include ("menu-mng-rad-attributes.php");
	
?>

	<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngradattributesedit.php') ?>
		:: <?php if (isset($vendor)) { echo $vendor; } ?><h144>&#x2754;</h144></a></h2>
		
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','mngradattributesedit') ?>
			<br/>
		</div>
		<?php
			include_once('include/management/actionMessages.php');
		?>

		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

	<fieldset>

		<h302> <?php echo t('title','VendorAttribute'); ?> </h302>
		<br/>

		<ul>

		<input type='hidden' name='vendor' value='<?php if (isset($vendor)) echo $vendor ?>' />

		<li class='fieldset'>
		<label for='vendor' class='form'><?php echo t('all','VendorName') ?></label>
		<input disabled name='vendor' type='text' id='vendor' value='<?php if (isset($vendor)) echo $vendor ?>' tabindex=100 />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('vendorNameTooltip')" />
		
		<div id='vendorNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','vendorNameTooltip') ?>
		</div>
		</li>

		<input type='hidden' name='attributeOld' value='<?php if (isset($attribute)) echo $attribute ?>' />

		<li class='fieldset'>
		<label for='attribute' class='form'><?php echo t('all','Attribute') ?></label>
		<input name='attribute' type='text' id='attribute' value='<?php if (isset($attribute)) echo $attribute ?>' tabindex=101 />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('attributeTooltip')" />
		
		<div id='attributeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','attributeTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='type' class='form'><?php echo t('all','Type') ?></label>
		<select name='type' type='text' id='type' class='form' tabindex=102 />
		<option value='<?php echo $type; ?>'><?php echo $type; ?></option>
		<?php
			include_once('include/management/populate_selectbox.php');
			drawTypes();
		?>
		</select>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('typeTooltip')" />
		
		<div id='typeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','typeTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='RecommendedOP' class='form'><?php echo t('all','RecommendedOP') ?></label>
		<select name='RecommendedOP' type='text' id='RecommendedOP' class='form' tabindex=103 />
		<option value='<?php echo $RecommendedOP; ?>'><?php echo $RecommendedOP; ?></option>
		<?php
			include_once('include/management/populate_selectbox.php');
			drawOptions();
		?>
		</select>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('RecommendedOPTooltip')" />
		
		<div id='RecommendedOPTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','RecommendedOPTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='RecommendedTable' class='form'><?php echo t('all','RecommendedTable') ?></label>
		<select name='RecommendedTable' type='text' id='RecommendedTable' class='form' tabindex=104 />
		<option value='<?php echo $RecommendedTable; ?>'><?php echo $RecommendedTable; ?></option>
		<?php
			include_once('include/management/populate_selectbox.php');
			drawTables();
		?>
		</select>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('RecommendedTableTooltip')" />
		
		<div id='RecommendedTableTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','RecommendedTableTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='RecommendedTooltip' class='form'><?php echo t('all','RecommendedTooltip') ?></label>
		<textarea class='form' name='RecommendedTooltip' type='text' id='RecommendedTooltip' tabindex=105 /><?php if (isset($RecommendedTooltip)) echo $RecommendedTooltip ?></textarea>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('RecommendedTooltipTooltip')" />
		
		<div id='RecommendedTooltipTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','RecommendedTooltipTooltip') ?>
		</div>
		</li>


		<li class='fieldset'>
		<label for='RecommendedHelper' class='form'><?php echo t('all','RecommendedHelper') ?></label>
		<select name='RecommendedHelper' type='text' id='RecommendedHelper' class='form' tabindex=104 />
		<option value='<?php echo $RecommendedHelper; ?>'><?php echo $RecommendedHelper; ?></option>
		<?php
			include_once('include/management/populate_selectbox.php');
			drawRecommendedHelper();
		?>
		</select>
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('RecommendedHelperTooltip')" />
		
		<div id='RecommendedHelperTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','RecommendedHelperTooltip') ?>
		</div>
		</li>
	
		<li class='fieldset'>
		<br/>
		<hr><br/>
		<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000 class='button' />
		</li>

		</ul>
	</fieldset>

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





