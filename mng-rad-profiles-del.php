<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include('library/check_operator_perm.php');

	isset($_REQUEST['profile']) ? $profile = $_REQUEST['profile'] : $profile = "";
        isset($_REQUEST['attribute']) ? $attribute = $_REQUEST['attribute'] : $attribute = "";
        isset($_REQUEST['tablename']) ? $tablename = $_REQUEST['tablename'] : $tablename = "";

        $logDebugSQL = "";

        if ( (isset($_REQUEST['profile'])) && (!(isset($_REQUEST['attribute']))) && (!(isset($_REQUEST['tablename']))) ) {

                $allProfiles = "";
                $isSuccessful = 0;

                if (!is_array($profile))
                        $profile = array($profile, NULL);

                foreach ($profile as $variable=>$value) {

                        if (trim($variable) != "") {

                                $profile = $value;
                                $allProfiles .= $profile . ", ";

                                include 'library/opendb.php';

                                // delete all attributes associated with a profile
                                $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK']." where 
GroupName='".$dbSocket->escapeSimple($profile)."'";
                                $res = $dbSocket->query($sql);
                                $logDebugSQL .= $sql . "\n";

                                $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY']." where 
GroupName='".$dbSocket->escapeSimple($profile)."'";
                                $res = $dbSocket->query($sql);
                                $logDebugSQL .= $sql . "\n";

                                $actionStatus = "success";
                                $actionMsg = "Deleted profile(s): <b> $allProfiles </b>";
                                $logAction = "Successfully deleted profile(s) [$allProfiles] on page: ";


                                include 'library/closedb.php';

                        }  else { 
                                $actionStatus = "failure";
                                $actionMsg = "no profile was entered, please specify a profile to remove from database";          
                                $logAction = "Failed deleting profile(s) [$allProfiles] on page: ";
                        }


                } //foreach


        } else  if ( (isset($_REQUEST['profile'])) && (isset($_REQUEST['attribute'])) && (isset($_REQUEST['tablename'])) ) {

                /* this section of the deletion process only deletes the username record with the specified attribute
                 * variable from $tablename, this is in order to support just removing a single attribute for the user
                 */

                include 'library/opendb.php';

                $sql = "DELETE FROM ".$dbSocket->escapeSimple($tablename)." WHERE GroupName='".$dbSocket->escapeSimple($profile)."'
                        AND Attribute='".$dbSocket->escapeSimple($attribute)."'";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";

                $actionStatus = "success";
                $actionMsg = "Deleted attribute: <b> $attribute <b/> for profile(s): <b> $profile </b> from database";
                $logAction = "Successfully deleted attribute [$attribute] for profile [$profile] on page: ";

                include 'library/closedb.php';

        }








/*
	$group = "";
	$username = "";

	if (isset($_REQUEST['username'])) {
		$username = $_REQUEST['username'];
	}

	if (isset($_REQUEST['group'])) {
		$group = $_REQUEST['group'];
 	}

	$logDebugSQL = "";

	if (isset($_POST['submit'])) {

		if (trim($username) != "") {
				
			include 'library/opendb.php';

			if (trim($group) != "") {

				// // delete only a specific groupname and it's attribute
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." WHERE UserName='".$dbSocket->escapeSimple($username)."'
AND GroupName='".$dbSocket->escapeSimple($group)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$actionStatus = "success";
				$actionMsg = "Deleted Username: <b> $username </b> and it's Groupname: <b> $group </b>";
				$logAction = "Successfully deleted user [$username] and it's group [$group] on page: ";

				include 'library/closedb.php';
							
			} else {

				// delete all attributes associated with a username
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." WHERE UserName='".$dbSocket->escapeSimple($username)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$actionStatus = "success";
				$actionMsg = "Deleted all instances for Username: <b> $username </b>";
				$logAction = "Successfully deleted all group instances for user [$username] on page: ";

				include 'library/closedb.php';
			}

		}  else {
			$actionStatus = "failure";
			$actionMsg = "No user was entered, please specify a username to remove from database";
			$logAction = "Failed deleting empty user on page: ";
		}
	}
*/	

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
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradprofilesdel.php'] ?>
				<h144>+</h144></a></h2>

				<div id="helpPage" style="display:none;visibility:visible" >				
					<?php echo $l['helpPage']['mngradprofilesdel'] ?>
					<br/>
				</div>
				<br/>
				
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
                                        <thead>
                                                        <tr>
                                                        <th colspan='2'> <?php echo $l['table']['GroupInfo']; ?> </th>
                                                        </tr>
                                        </thead>
<tr><td>
                                                <input type="hidden" value="<?php echo $group ?>" name="group"/><br/>

                                                <b>Profile Name</b>
</td><td>													
                                                <input value="<?php echo $profile ?>" name="profile[]"/><br/>
                                                </font>
</td></tr>
</table>

                                                <br/><br/>
<center>												
                                                <input type="submit" name="submit" value="<?php echo $l['buttons']['apply'] ?>"/>
</center>
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
