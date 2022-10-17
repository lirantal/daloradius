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
 * Authors:	Liran Tal <liran@enginx.com>, Miguel García <miguelvisgarcia@gmail.com>
 *
 *********************************************************************************************************
 */

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	isset($_REQUEST['username']) ? $username = trim($_REQUEST['username']) : $username = '';
	isset($_REQUEST['enddate']) ? $enddate = trim($_REQUEST['enddate']) : $enddate = "";

	$logAction = '';
	$logDebugSQL = '';

	if (isset($_POST['submit'])) {

		if ($username != '') {

			include 'library/opendb.php';

			$sql = 'SELECT count(*) FROM ' . $configValues['CONFIG_DB_TBL_RADACCT'] .
			       ' WHERE username = "' . $username . '" AND acctstoptime is NULL;';

			$res = $dbSocket->query($sql);

			$logDebugSQL .= $sql . "\n";

			$row = $res->fetchRow();

			if($row[0] > 0) {

				$sql = 'UPDATE ' . $configValues['CONFIG_DB_TBL_RADACCT'] .
				       ' SET acctstoptime = NOW(), acctterminatecause = "Admin-Reset"'.
				       ' WHERE username = "' . $username . '" AND acctstoptime is NULL;'; 

				$res = $dbSocket->query($sql);
				
				$logDebugSQL .= $sql . "\n";

				$successMsg = "Cleaned up stale sessions for username: <b> $username </b>";
				$logAction .= "Successfully cleaned up stale sessions for username [$username] on page: ";
			}
			else {

				$failureMsg = "There are no stale sessions for user [$username]";
				$logAction .= "Failed performing close stale sessions on user [$username] because there are no stale sessions for that user on page: ";
			}

			include 'library/closedb.php';
		}
		else if ($enddate != '') {

			include 'library/opendb.php';

			// delete all stale sessions in the database that occur until $enddate
			$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].
					" WHERE AcctStartTime<'".$dbSocket->escapeSimple($enddate)."'".
					" AND (AcctStopTime='0000-00-00 00:00:00' OR AcctStopTime IS NULL)";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";

			$successMsg = "Cleaned up stale sessions until date: <b> $enddate </b>";
			$logAction .= "Successfully cleaned up stale sessions until date [$enddate] on page: ";

			include 'library/closedb.php';

		}
		else {
			$failureMsg = "No username or ending date was entered, please specify a username or ending date for cleaning up stale sessions from the database";
			$logAction .= "Failed cleaning up stale sessions due to lack of username or ending date on page: ";
		}
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
<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->
</head>

<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>

<?php
	include ("menu-accounting-maintenance.php");
?>

<?php
	include_once ("library/tabber/tab-layout.php");
?>

	<div id="contentnorightbar">

		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','acctmaintenancecleanup.php') ?>
		<h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','acctmaintenancecleanup') ?>
			<br/>
		</div>

<?php
	include_once('include/management/actionMessages.php');
?>

		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

			<div class="tabber">

				<div class="tabbertab" title="<?php echo t('title','CleanupRecordsByUsername'); ?>">

					<fieldset>

						<h302> <?php echo t('title','CleanupRecordsByUsername'); ?> </h302>
						<br/>

						<label for='username' class='form'><?php echo t('all','Username')?></label>
						<input name="username" type="text" id="usernameEdit" autocomplete="off"
						tooltipText='<?php echo t('Tooltip','Username'); ?> <br/>'
						value="<?php if (isset($username)) echo $username; ?>" tabindex=100>

<?php
	include_once("include/management/autocomplete.php");

	if ($autoComplete) {
		echo "<script type=\"text/javascript\">
				/** Making usernameEdit interactive **/
				autoComEdit = new DHTMLSuite.autoComplete();
				autoComEdit.add('usernameEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');
				</script>";
	}
?>
						<br/><br/>
						<hr><br/>

						<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />

					</fieldset>

				</div>

				<div class="tabbertab" title="<?php echo t('title','CleanupRecordsByDate'); ?>">

					<fieldset>
						<h302> <?php echo t('title','CleanupRecordsByDate') ?> </h302>
						<br/>

						<label for='enddate' class='form'><?php echo t('all','CleanupSessions')?></label>
						<input name='enddate' type='text' id='enddate' value='<?php echo $enddate ?>' tabindex=100 />
						<img src="library/js_date/calendar.gif" onclick=
						"showChooser(this, 'enddate', 'chooserSpan', 1950, <?php echo date('Y', time());?>, 'Y-m-d H:i:s', true);" >

						<br/><br/>
						<hr><br/>
						<input type="submit" name="submit" value="<?php echo t('buttons','apply') ?>" tabindex=1000 class='button' />
					</fieldset>

					<div id="chooserSpan" class="dateChooser select-free"
						style="display: none; visibility: hidden; width: 160px;">
					</div>

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





