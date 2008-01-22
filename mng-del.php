<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	isset($_REQUEST['username']) ? $username = $_REQUEST['username'] : $username = "";
	isset($_REQUEST['attribute']) ? $attribute = $_REQUEST['attribute'] : $attribute = "";
	isset($_REQUEST['tablename']) ? $tablename = $_REQUEST['tablename'] : $tablename = "";

	$logDebugSQL = "";

	if ( (isset($_REQUEST['username'])) && (!(isset($_REQUEST['attribute']))) && (!(isset($_REQUEST['tablename']))) ) {

		$allUsernames = "";
		$isSuccessful = 0;

		/* since the foreach loop will report an error/notice of undefined variable $value because
		   it is possible that the $username is not an array, but rather a simple GET request
		   with just some value, in this case we check if it's not an array and convert it to one with
		   a NULL 2nd element
		*/
		if (!is_array($username))
			$username = array($username, NULL);

		foreach ($username as $variable=>$value) {

			if (trim($variable) != "") {
			
				$username = $value;
				$allUsernames .= $username . ", ";
				//echo "user: $username <br/>";

				include 'library/opendb.php';


				// delete all attributes associated with a username
				$sql = "delete from ".$configValues['CONFIG_DB_TBL_RADCHECK']." where Username='".$dbSocket->escapeSimple($username)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$sql = "delete from ".$configValues['CONFIG_DB_TBL_RADREPLY']." where Username='".$dbSocket->escapeSimple($username)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$sql = "delete from ".$configValues['CONFIG_DB_TBL_DALOUSERINFO']." where Username='".$dbSocket->escapeSimple($username)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$actionStatus = "success";
				$actionMsg = "Deleted user(s): <b> $allUsernames </b>";
				$logAction = "Successfully deleted user(s) [$allUsernames] on page: ";

				include 'library/closedb.php';

			}  else { 
				$actionStatus = "failure";
				$actionMsg = "no user was entered, please specify a username to remove from database";		
				$logAction = "Failed deleting user(s) [$allUsernames] on page: ";
			}


		} //foreach


	} else 	if ( (isset($_REQUEST['username'])) && (isset($_REQUEST['attribute'])) && (isset($_REQUEST['tablename'])) ) {

		/* this section of the deletion process only deletes the username record with the specified attribute
		 * variable from $tablename, this is in order to support just removing a single attribute for the user
		 */

		include 'library/opendb.php';

		$sql = "delete from ".$dbSocket->escapeSimple($tablename)." where Username='".$dbSocket->escapeSimple($username)."'
			AND Attribute='".$dbSocket->escapeSimple($attribute)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		$actionStatus = "success";
		$actionMsg = "Deleted attribute: <b> $attribute </b> for user(s): <b> $username </b> from database";
		$logAction = "Successfully deleted attribute [$attribute] for user [$username] on page: ";

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
 
<?php

	include ("menu-mng-main.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngdel.php'] ?>
				<h144>+</h144></a></h2>
				
				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngdel'] ?>
					<br/>
				</div>
				
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <fieldset>

                <h302> <?php echo $l['title']['AccountRemoval'] ?> </h302>
		<br/>

                <label for='username' class='form'><?php echo $l['all']['Username']?></label>
                <input name='username[]' type='text' id='username' value='<?php echo $username ?>' tabindex=100 />
                <br />

		<br/><br/>
		<hr><br/>
		<input type="submit" name="submit" value="<?php echo $l['buttons']['apply'] ?>" tabindex=1000 
			class='button' />

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





