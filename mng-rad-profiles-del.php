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
                $actionMsg = "Deleted attribute: <b> $attribute </b> for profile(s): <b> $profile </b> from database";
                $logAction = "Successfully deleted attribute [$attribute] for profile [$profile] on page: ";

                include 'library/closedb.php';

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
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradprofilesdel.php'] ?>
				<h144>+</h144></a></h2>

				<div id="helpPage" style="display:none;visibility:visible" >				
					<?php echo $l['helpPage']['mngradprofilesdel'] ?>
					<br/>
				</div>
				<br/>
				
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

        <fieldset>

                <h302> <?php echo $l['title']['ProfileInfo'] ?> </h302>
                <br/>

                <label for='profile' class='form'>Profile Name</label>
                <input name='profile[]' type='text' id='profile' value='<?php echo $profile ?>' tabindex=100 />
                <br/>

                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' class='button' />

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
