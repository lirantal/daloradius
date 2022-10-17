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

	isset($_POST['sourceProfile']) ? $sourceProfile = $_POST['sourceProfile'] : $sourceProfile = "";
	isset($_POST['targetProfile']) ? $targetProfile = $_POST['targetProfile'] : $targetProfile = "";

	$logAction = "";
	$logDebugSQL = "";

	if (isset($_POST['submit'])) {

		if ( (isset($_POST['sourceProfile'])) && (isset($_POST['targetProfile'])) && ($_POST['targetProfile']) ) {

			include 'library/opendb.php';

			// get all sets of attributes from radgroupcheck for this profile/group
			$sql = "SELECT Attribute,Op,Value FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK'].
				" WHERE GroupName='".$dbSocket->escapeSimple($sourceProfile)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";

			while($row = $res->fetchRow()) {

				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK'].
					" (GroupName,Attribute,Op,Value) VALUES (".
					"'".$dbSocket->escapeSimple($targetProfile)."',".
					"'".$row[0]."',".
					"'".$row[1]."',".
					"'".$row[2]."'".
					")";
				$dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
			
			}


			// get all sets of attributes from radgroupreply for this profile/group
			$sql = "SELECT Attribute,Op,Value FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY'].
				" WHERE GroupName='".$dbSocket->escapeSimple($sourceProfile)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";
	
			while($row = $res->fetchRow()) {

				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY'].
					" (GroupName,Attribute,Op,Value) VALUES (".
					"'".$dbSocket->escapeSimple($targetProfile)."',".
					"'".$row[0]."',".
					"'".$row[1]."',".
					"'".$row[2]."'".
					")";
				$dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

			}

			include 'library/closedb.php';

			$successMsg = "Duplicated profile: <b>$sourceProfile</b> to new profile name: <b>$targetProfile</b>";
			$logAction .= "Successfully duplicated profile [$sourceProfile] to new profile name [$targetProfile] on page: ";

		} else {

			$failureMsg = "possibly no source/target profiles were entered, please specify source and target profile names";
			$logAction .= "Failed duplicating profile [$sourceProfile] on page: ";
		}

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
	include ("menu-mng-rad-profiles.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngradprofilesduplicate.php') ?>
				:: <?php if (isset($profile)) { echo $profile; } ?><h144>&#x2754;</h144></a></h2>

				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo t('helpPage','mngradprofilesduplicate') ?>
					<br/>
				</div>
                <?php
					include_once('include/management/actionMessages.php');
                ?>
				
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

        <fieldset>

                <h302> <?php echo t('title','ProfileInfo') ?> </h302>
                <br/>

                <label for='sourceProfile' class='form'>Profile Name to Duplicate</label>
                        <?php
                                // include 'include/management/populate_selectbox.php'; // already included in menu-mng-rad-profile.php
                                populate_groups("Select Profile","sourceProfile","form");
                        ?>
                <br/>

                <label for='profile' class='form'>New Profile Name</label>
                <input name='targetProfile' type='text' id='profile' value='<?php echo $targetProfile ?>' tabindex=101 />
                <br/>

                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />

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
