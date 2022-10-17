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

	isset($_POST['ratename']) ? $ratename = $_POST['ratename'] : $ratename = "";
	isset($_POST['ratetypenum']) ? $ratetypenum = $_POST['ratetypenum'] : $ratetypenum = "";
	isset($_POST['ratetypetime']) ? $ratetypetime = $_POST['ratetypetime'] : $ratetypetime = "";
	isset($_POST['ratecost']) ? $ratecost = $_POST['ratecost'] : $ratecost = "";

	$logAction = "";
	$logDebugSQL = "";

	if (isset($_POST["submit"])) {
		$ratename = $_POST['ratename'];
		$ratetypenum = $_POST['ratetypenum'];
		$ratetypetime = $_POST['ratetypetime'];
		$ratecost = $_POST['ratecost'];
		
		include 'library/opendb.php';

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES']." WHERE rateName='".$dbSocket->escapeSimple($ratename)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 0) {
			if (trim($ratename) != "" and trim($ratetypenum) != "" and trim($ratetypetime) != "" and trim($ratecost) != "") {

				$currDate = date('Y-m-d H:i:s');
				$currBy = $_SESSION['operator_user'];
				
				$ratetype = "$ratetypenum/$ratetypetime";

				// insert rate info
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES'].
					" (id, ratename, ratetype, ratecost, ".
					"  creationdate, creationby, updatedate, updateby) ".
					" VALUES (0, '".$dbSocket->escapeSimple($ratename)."', '".
					$dbSocket->escapeSimple($ratetype)."',".$dbSocket->escapeSimple($ratecost).",".
					" '$currDate', '$currBy', NULL, NULL)";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$successMsg = "Added to database new rate: <b>$ratename</b>";
				$logAction .= "Successfully added new rate [$ratename] on page: ";
			} else {
				$failureMsg = "you must provide a rate name, type and cost";	
				$logAction .= "Failed adding new rate [$ratename] on page: ";	
			}
		} else { 
			$failureMsg = "You have tried to add a rate that already exist in the database: $ratename";
			$logAction .= "Failed adding new rate already in database [$ratename] on page: ";		
		}
	
		include 'library/closedb.php';

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

	<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','billratesnew.php') ?>
	<h144>&#x2754;</h144></a></h2>
	
	<div id="helpPage" style="display:none;visibility:visible" >
		<?php echo t('helpPage','billratesnew') ?>
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
		<label for='name' class='form'><?php echo t('all','RateName') ?></label>
		<input name='ratename' type='text' id='ratename' value='' tabindex=100 />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('rateNameTooltip')" /> 
		
		<div id='rateNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','rateNameTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='ratetype' class='form'><?php echo t('all','RateType') ?></label>

		<input class='integer' name='ratetypenum' type='text' id='ratetypenum' value='1' tabindex=101 />
                <img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('ratetypenum','increment')" />
                <img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('ratetypenum','decrement')"/>

                <select class='form' tabindex=102 name='ratetypetime' id='ratetypetime' >
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
		<input class='integer' name='ratecost' type='text' id='ratecost' value='1' tabindex=103 />
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
		<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000 class='button' />
		</li>

		</ul>
	</fieldset>

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





