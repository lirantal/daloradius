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

	// declaring variables
	$username = "";
	$group = "";
	$groupOld = "";
	$priority = "";

	$username = $_REQUEST['username'];
	$groupOld = $_REQUEST['group'];

	$logAction = "";
	$logDebugSQL = "";

	// fill-in nashost details in html textboxes
	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP'].
		" WHERE UserName='".$dbSocket->escapeSimple($username)."' AND GroupName='".$dbSocket->escapeSimple($groupOld)."'";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";
	$row = $res->fetchRow();		// array fetched with values from $sql query

	// assignment of values from query to local variables
	// to be later used in html to display on textboxes (input)
	$priority = $row[2];

	if (isset($_POST['submit'])) {
		$username = $_REQUEST['username'];
		$groupOld = $_REQUEST['groupOld'];;
		$group = $_REQUEST['group'];;
		$priority = $_REQUEST['priority'];;
		
		include 'library/opendb.php';

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP'].
			" WHERE UserName='".$dbSocket->escapeSimple($username)."' AND GroupName='".$dbSocket->escapeSimple($groupOld)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 1) {

			if (trim($username) != "" and trim($group) != "") {

				if (!isset($priority)) {
					$priority = 1;
				}

				// insert nas details
				$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." SET GroupName='".$dbSocket->escapeSimple($group)."',
priority='".$dbSocket->escapeSimple($priority)."' WHERE UserName='".$dbSocket->escapeSimple($username)."'
AND GroupName='".$dbSocket->escapeSimple($groupOld)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
						
				$successMsg = "Updated User-Group mapping in database: User<b> $username </b> and Group: <b> $group </b> ";
				$logAction .= "Successfully updated attributes for user-group mapping of user [$username] with group [$group] on page: ";
			} else {
				$failureMsg = "no username or groupname was entered, it is required that you specify both username and groupname";
				$logAction .= "Failed updating (missing attributes) attributes on page: ";
			}
		} else {
			$failureMsg = "The user $username already exists in the user-group mapping database
			<br/> It seems that you have duplicate entries for User-Group mapping. Check your database";
			$logAction .= "Failed updating already existing user [$username] with group [$group] on page: ";
		} 

		include 'library/closedb.php';
	}
	
	if (isset($_REQUEST['username']))
		$username = $_REQUEST['username'];
	else
		$username = "";

	if (isset($_REQUEST['group']))
		$group = $_REQUEST['group'];
	else
		$group = "";

	if (trim($username) == "" OR trim($group) == "") {
		$failureMsg = "no username or groupname was entered, please specify a username and groupname to edit ";
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
	include ("menu-mng-rad-usergroup.php");
?>

	<div id="contentnorightbar">
	
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngradusergroupedit') ?> 
		<?php echo $username ?><h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >				
			<?php echo t('helpPage','mngradusergroupedit') ?>
			<br/>
		</div>
		<?php
			include_once('include/management/actionMessages.php');
		?>
				
		<form name="newuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

			<input type="hidden" value="<?php echo $username ?>" name="username" />

        <fieldset>

                <h302> <?php echo t('title','GroupInfo') ?> </h302>
                <br/>

                <ul>

                <li class='fieldset'>
                <label for='username' class='form'><?php echo t('all','Username') ?></label>
                <input type='hidden' name='username' type='text' id='username' value='<?php echo $username ?>' tabindex=100 />
                <input disabled type='text' id='username' value='<?php echo $username ?>' tabindex=100 />
                </li>


                <li class='fieldset'>
                <label for='groupOld' class='form'><?php echo t('all','CurrentGroupname') ?></label>
                <input type='hidden' name='groupOld' id='groupOld' value='<?php echo $groupOld ?>' tabindex=101 />
                <input disabled type='text' id='groupOld' value='<?php echo $groupOld ?>' tabindex=101 />
				Old Group Name
                </li>

                <li class='fieldset'>
                <label for='group' class='form'><?php echo t('all','NewGroupname') ?></label>
                <?php   
					include 'include/management/populate_selectbox.php';
					populate_groups("Select Groups","group","form");
                ?>
                <div id='groupTooltip'  style='display:none;visibility:visible' class='ToolTip'>
					<img src='images/icons/comment.png' alt='Tip' border='0' />
					<?php echo t('Tooltip','groupTooltip') ?>
                </div>
                </li>


                <li class='fieldset'>
                <label for='priority' class='form'><?php echo t('all','Priority') ?></label>
                <input class='integer' name='priority' type='text' id='priority' value='<?php echo $priority ?>' tabindex=103 />
                <img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('priority','increment')" />
                <img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('priority','decrement')"/>
                </li>

                <li class='fieldset'>
                <br/>
                <hr><br/>
                <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />
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
