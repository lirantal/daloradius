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

	isset($_GET['proxyname']) ? $proxyname = $_GET['proxyname'] : $proxyname = "";

	$logAction = "";
	$logDebugSQL = "";

	if (isset($_POST["submit"])) {

		isset($_POST['proxyname']) ? $proxyname = $_POST['proxyname'] : $proxyname = "";
		isset($_POST['retry_delay']) ? $retry_delay = $_POST['retry_delay'] : $retry_delay = "";
		isset($_POST['retry_count']) ? $retry_count = $_POST['retry_count'] : $retry_count = "";
		isset($_POST['dead_time']) ? $dead_time = $_POST['dead_time'] : $dead_time = "";
		isset($_POST['default_fallback']) ? $default_fallback = $_POST['default_fallback'] :  $default_fallback = "";
		
		include 'library/opendb.php';

		if (isset($configValues['CONFIG_FILE_RADIUS_PROXY'])) {
			$filenameRealmsProxys = $configValues['CONFIG_FILE_RADIUS_PROXY'];
			$fileFlag = 1;
		} else {
			$filenameRealmsProxys = "";
			$fileFlag = 0;
		}

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOPROXYS'].
			" WHERE proxyname='".$dbSocket->escapeSimple($proxyname)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if (trim($proxyname) != "") {

			if (!(file_exists($filenameRealmsProxys))) {
				$logAction .= "Failed non-existed proxys configuration file [$filenameRealmsProxys] on page: ";
				$failureMsg = "the file $filenameRealmsProxys doesn't exist, I can't save proxys information to the file";
				$fileFlag = 0;
			}

			if (!(is_writable($filenameRealmsProxys))) {
				$logAction .= "Failed writing proxys configuration to file [$filenameRealmsProxys] on page: ";
				$failureMsg = "the file $filenameRealmsProxys isn't writable, I can't save proxys information to the file";
				$fileFlag = 0;
			}

			$currDate = date('Y-m-d H:i:s');
			$currBy = $_SESSION['operator_user'];

			// update proxy entry in database
			$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOPROXYS']." SET ".
				" retry_delay=".$dbSocket->escapeSimple($retry_delay).", ".
				" retry_count=".$dbSocket->escapeSimple($retry_count).", ".
				" dead_time=".$dbSocket->escapeSimple($dead_time).", ".
				" default_fallback=".$dbSocket->escapeSimple($default_fallback).", ".
                                " updatedate='$currDate', updateby='$currBy' ".
				" WHERE proxyname='$proxyname';";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";

			$successMsg = "Updated database proxy: <b>$proxyname</b>";
			$logAction .= "Successfully updated proxy [$proxyname] on page: ";

			/*******************************************************************/
			/* enumerate from database all proxy entries */
			include_once('include/management/saveRealmsProxys.php');
			/*******************************************************************/

		} else {
			$failureMsg = "you must provide atleast a proxy name";
			$logAction .= "Failed updating proxy [$proxyname] on page: ";	
		}
	
		include 'library/closedb.php';

	}


	include 'library/opendb.php';

	// fill-in proxy information in html elements
	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOPROXYS'].
			" WHERE proxyname='".$dbSocket->escapeSimple($proxyname)."'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
	$retry_count = $row['retry_count'];
	$retry_delay = $row['retry_delay'];
	$dead_time = $row['dead_time'];
	$default_fallback = $row['default_fallback'];

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
        include_once ("library/tabber/tab-layout.php");
?>
 
<?php

	include ("menu-mng-rad-realms.php");
	
?>

	<div id="contentnorightbar">

		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngradproxysedit.php') ?>
		<h144>&#x2754;</h144></a></h2>
		
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','mngradproxysedit') ?>
			<br/>
		</div>
		<?php   
			include_once('include/management/actionMessages.php');
		?>

		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="<?php echo t('title','ProxyInfo'); ?>">

	<fieldset>

		<h302> <?php echo t('title','ProxyInfo'); ?> </h302>
		<br/>

		<ul>

		<input type='hidden' name='proxyname'  id='proxyname' value='<?php if (isset($proxyname)) echo $proxyname; ?>' />

                <li class='fieldset'>
                <label for='proxyname' class='form'><?php echo t('all','ProxyName') ?></label>
                <input disabled name='proxyname' type='text' id='proxyname' 
					value='<?php if (isset($proxyname)) echo $proxyname; ?>' tabindex=100
				<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('proxyNameTooltip')" />
			
                <div id='proxyNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
					<img src='images/icons/comment.png' alt='Tip' border='0' />
					<?php echo t('Tooltip','proxyNameTooltip') ?>
                </div>
                </li>

                <li class='fieldset'>
                <label for='retry_delay' class='form'><?php echo t('all','RetryDelay') ?></label>
                <input name='retry_delay' type='text' id='retry_delay' 
						value='<?php if (isset($retry_delay)) echo $retry_delay; ?>' tabindex=102
				<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('proxyRetryDelayTooltip')" />
				
                <div id='proxyRetryDelayTooltip'  style='display:none;visibility:visible' class='ToolTip'>
					<img src='images/icons/comment.png' alt='Tip' border='0' />
					<?php echo t('Tooltip','proxyRetryDelayTooltip') ?>
                </div>
                </li>
                <li class='fieldset'>
                <label for='retry_count' class='form'><?php echo t('all','RetryCount') ?></label>
                <input name='retry_count' type='text' id='retry_count' 
					value='<?php if (isset($retry_count)) echo $retry_count; ?>' tabindex=103
				<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('proxyRetryCountTooltip')" />
				
                <div id='proxyRetryCountTooltip'  style='display:none;visibility:visible' class='ToolTip'>
					<img src='images/icons/comment.png' alt='Tip' border='0' />
					<?php echo t('Tooltip','proxyRetryCountTooltip') ?>
                </div>
                </li>

                <li class='fieldset'>
                <label for='dead_time' class='form'><?php echo t('all','DeadTime') ?></label>
                <input name='dead_time' type='text' id='dead_time' 
					value='<?php if (isset($dead_time)) echo $dead_time; ?>' tabindex=104
				<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('proxyDeadTimeTooltip')" />
				
                <div id='proxyDeadTimeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
					<img src='images/icons/comment.png' alt='Tip' border='0' />
					<?php echo t('Tooltip','proxyDeadTimeTooltip') ?>
                </div>
                </li>

                <li class='fieldset'>
                <label for='default_fallback' class='form'><?php echo t('all','DefaultFallback') ?></label>
                <input name='default_fallback' type='text' id='default_fallback' 
					value='<?php if (isset($default_fallback)) echo $default_fallback; ?>' tabindex=104
				<img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('proxyDefaultFallbackTooltip')" />
				
                <div id='proxyDefaultFallbackTooltip'  style='display:none;visibility:visible' class='ToolTip'>
					<img src='images/icons/comment.png' alt='Tip' border='0' />
					<?php echo t('Tooltip','proxyDefaultFallbackTooltip') ?>
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





