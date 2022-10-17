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

	if (isset($_POST["submit"])) {

		isset($_POST['realmname']) ? $realmname = $_POST['realmname'] : $realmname = "";
		isset($_POST['type']) ? $type = $_POST['type'] : $type = "";
		isset($_POST['authhost']) ?$authhost = $_POST['authhost'] : $authhost = "";
		isset($_POST['accthost']) ? $accthost = $_POST['accthost'] : $accthost = "";
		isset($_POST['secret']) ? $secret = $_POST['secret'] : $secert = "";
		isset($_POST['ldflag']) ? $ldflag = $_POST['ldflag'] : $ldflag = "";
		isset($_POST['nostrip']) ? $nostrip = $_POST['nostrip'] : $nostrip = "";
		isset($_POST['hints']) ? $hints = $_POST['hints'] : $hints = "";
		isset($_POST['notrealm']) ? $notrealm = $_POST['notrealm'] :  $notrealm = "";
		
		include 'library/opendb.php';

		if (isset($configValues['CONFIG_FILE_RADIUS_PROXY'])) {
			$filenameRealmsProxys = $configValues['CONFIG_FILE_RADIUS_PROXY'];
			$fileFlag = 1;
		} else {
			$filenameRealmsProxys = "";
			$fileFlag = 0;
		}

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOREALMS'].
				" WHERE realmname='".$dbSocket->escapeSimple($realmname)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 0) {

			if (!(file_exists($filenameRealmsProxys))) {
				$logAction .= "Failed non-existed realms configuration file [$filenameRealmsProxys] on page: ";
				$failureMsg = "the file $filenameRealmsProxys doesn't exist, I can't save realms information to the file";
				$fileFlag = 0;
			}
	
			if (!(is_writable($filenameRealmsProxys))) {
				$logAction .= "Failed writing realms configuration to file [$filenameRealmsProxys] on page: ";	
				$failureMsg = "the file $filenameRealmsProxys isn't writable, I can't save realms information to the file";
				$fileFlag = 0;
			}	

			if (trim($realmname) != "") {

				$currDate = date('Y-m-d H:i:s');
				$currBy = $_SESSION['operator_user'];

				// insert realm to database
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOREALMS'].
					" (id,realmname,type,authhost,accthost,secret,ldflag,nostrip,hints,notrealm,creationdate,creationby,".
					"	updatedate,updateby) ".
					" VALUES (0, '".
					$dbSocket->escapeSimple($realmname)."', '".$dbSocket->escapeSimple($type)."', '".
					$dbSocket->escapeSimple($authhost)."','".$dbSocket->escapeSimple($accthost)."','".
					$dbSocket->escapeSimple($secret)."','".$dbSocket->escapeSimple($ldflag)."','".
					$dbSocket->escapeSimple($nostrip)."','".$dbSocket->escapeSimple($hints)."','".
					$dbSocket->escapeSimple($notrealm)."' ".
					", '$currDate', '$currBy', NULL, NULL)";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$successMsg = "Added to database new realm: <b>$realmname</b>";
				$logAction .= "Successfully added new realm [$realmname] on page: ";

				/*******************************************************************/
				/* enumerate from database all realm entries */
				include_once('include/management/saveRealmsProxys.php');
				/*******************************************************************/

			} else {
				$failureMsg = "you must provide at least a realm name";
				$logAction .= "Failed adding new realm [$realmname] on page: ";	
			}

		} else { 
			$failureMsg = "You have tried to add a realm that already exist in the database: $realmname";
			$logAction .= "Failed adding new realm already in database [$realmname] on page: ";
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

	include ("menu-mng-rad-realms.php");
	
?>

	<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngradrealmsnew.php') ?>
		<h144>&#x2754;</h144></a></h2>
		
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','mngradrealmsnew') ?>
			<br/>
		</div>
		<?php 
			include_once('include/management/actionMessages.php');
		?>
		
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

	<div class="tabbertab" title="<?php echo t('title','RealmInfo'); ?>">

	<fieldset>

		<h302> <?php echo t('title','RealmInfo'); ?> </h302>
		<br/>

		<ul>

		<li class='fieldset'>
		<label for='realmname' class='form'><?php echo t('all','RealmName') ?></label>
		<input name='realmname' type='text' id='realmname' value='' tabindex=100 />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realmNameTooltip')" />
		
		<div id='realmNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','realmNameTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='type' class='form'><?php echo t('all','Type') ?></label>
		<input name='type' type='text' id='type' value='' tabindex=101 />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realmTypeTooltip')" />
		
		<div id='realmTypeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','realmTypeTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='authhost' class='form'><?php echo t('all','AuthHost') ?></label>
		<input name='authhost' type='text' id='authhost' value='' tabindex=102 />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realmAuthhostTooltip')" />
		
		<div id='realmAuthhostTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','realmAuthhostTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='accthost' class='form'><?php echo t('all','AcctHost') ?></label>
		<input name='accthost' type='text' id='accthost' value='' tabindex=103 />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realmAccthostTooltip')" />
		
		<div id='realmAccthostTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','realmAccthostTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='secret' class='form'><?php echo t('all','RealmSecret') ?></label>
		<input name='secret' type='text' id='secret' value='' tabindex=104 />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realmSecretTooltip')" />
		
		<div id='realmSecretTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','realmSecretTooltip') ?>
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


	<div class="tabbertab" title="<?php echo t('title','Advanced'); ?>">

	<fieldset>

		<h302> <?php echo t('title','RealmInfo'); ?> </h302>
		<br/>
		<ul>

		<li class='fieldset'>
		<label for='ldflag' class='form'><?php echo t('all','Ldflag') ?></label>
		<input name='ldflag' type='text' id='ldflag' value='' tabindex=105 />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realmLdflagTooltip')" />
		
		<div id='realmLdflagTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','realmLdflagTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='nostrip' class='form'><?php echo t('all','Nostrip') ?></label>
		<input name='nostrip' type='text' id='nostrip' value='' tabindex=106 />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realmNostripTooltip')" />
		
		<div id='realmNostripTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','realmNostripTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='hints' class='form'><?php echo t('all','Hints') ?></label>
		<input name='hints' type='text' id='hints' value='' tabindex=107 />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realmHintsTooltip')" />
		
		<div id='realmHintsTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','realmHintsTooltip') ?>
		</div>
		</li>

		<li class='fieldset'>
		<label for='notrealm' class='form'><?php echo t('all','Notrealm') ?></label>
		<input name='notrealm' type='text' id='notrealm' value='' tabindex=108 />
		<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realrealmNotrealmTooltipmHintsTooltip')" />
		
		<div id='realmNotrealmTooltip'  style='display:none;visibility:visible' class='ToolTip'>
			<img src='images/icons/comment.png' alt='Tip' border='0' />
			<?php echo t('Tooltip','realmNotrealmTooltip') ?>
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

