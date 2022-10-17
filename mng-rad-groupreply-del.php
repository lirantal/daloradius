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

	$groupname = "";
	$attribute = "";
	$value = "";
	$logAction = "";
	$logDebugSQL = "";

	$showRemoveDiv = "block";

	if (isset($_POST['group'])) {
		$group_array = $_POST['group'];
	} else {
		if (isset($_GET['groupname']))
		$group_array = array($_GET['groupname']."||".$_GET['attribute']."||".$_GET['value']);
	}


	if (isset($group_array)) {

                $allGroups =  "";
                $allAttributes =  "";
                $allValues =  "";

                foreach ($group_array as $group) {

	                list($groupname, $attribute, $value) = preg_split('\|\|', $group);

                        if (trim($groupname) != "") {

                                $allGroups .= $groupname . ", ";
                                $allAttributes .= $attribute . ", ";
                                $allValues .= $value . ", ";

	                        if ( (trim($attribute) != "") && (trim($value) != "") ) {

					include 'library/opendb.php';

				        // delete only a specific groupname and it's attribute
					$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY'].
							" WHERE GroupName='".$dbSocket->escapeSimple($groupname).
							"' AND Value='$value' AND Attribute='$attribute'";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";

					$successMsg = "Deleted Group(s): <b> $allGroups </b> with Attribute(s): <b> $allAttributes </b> and it's Value: <b> $allValues </b>";
					$logAction .= "Successfully deleted group(s) [$allGroups] with attribute [$allAttributes] and it's value [$allValues] on page: ";

					include 'library/closedb.php';

				} else {

					include 'library/opendb.php';

					$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY']." WHERE GroupName='".$dbSocket->escapeSimple($groupname)."'";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";

					$successMsg = "Deleted all instances for Group(s): <b> $allGroups </b>";
					$logAction .= "Successfully deleted all instances for group(s) [$allGroups] on page: ";

					include 'library/closedb.php';

				}

			} else {

					$failureMsg = "No groupname was entered, please specify a groupname to remove from database";
					$logAction .= "Failed deleting empty group on page: ";
			}

		} // foreach

		$showRemoveDiv = "none";

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
	include ("menu-mng-rad-groups.php");
?>

	<div id="contentnorightbar">

		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngradgroupreplydel.php') ?>
		<h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','mngradgroupreplydel') ?>
			<br/>
		</div>
		<?php
			include_once('include/management/actionMessages.php');
		?>

	<div id="removeDiv" style="display:<?php echo $showRemoveDiv ?>;visibility:visible" >
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">


        <fieldset>

			<h302> <?php echo t('title','GroupInfo') ?> </h302>
			<br/>

			<label for='groupname' class='form'><?php echo t('all','Groupname') ?></label>
			<input name='groupname' type='text' id='groupname' value='<?php echo $groupname ?>' tabindex=100 />
			<br/>

			<label for='value' class='form'><?php echo t('all','Value') ?></label>
			<input name='value' type='text' id='value' value='<?php echo $value ?>' tabindex=101 />
			<br/>

			<label for='attribute' class='form'><?php echo t('all','Attribute') ?></label>
			<input name='attribute' type='text' id='attribute' value='<?php echo $attribute ?>' tabindex=102 />
			<br/>

			<br/><br/>
			<hr><br/>

			<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />

        </fieldset>


		</form>
	</div>


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
