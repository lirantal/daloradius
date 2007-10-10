<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	$username = !empty($_REQUEST['operator_username']) ? $_REQUEST['operator_username'] : '[operator_username]';
	$logDebugSQL = "";

	if (isset($_POST['submit'])) {

		if (trim($username) != "") {
			
			include 'library/opendb.php';

			// delete all attributes associated with a username
			$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." where Username='$username'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";


			$actionStatus = "success";
			$actionMsg = "Deleted operator: <b> $username";
			$logAction = "Successfully deleted operator [$username] on page: ";

			include 'library/closedb.php';
			
		}  else { 
			$actionStatus = "failure";
			$actionMsg = "no operator username was entered, please specify an operator username to remove from database";		
			$logAction = "Failed deleting operator username [$username] on page: ";
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

</head>
 
<?php

	include ("menu-config-operators.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro">Remove Operator</h2>
				
				<p>
				To remove an operator from the database you must provide the username.
				<br/><br/>
				</p>
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
<tr><td>
						<?php if (trim($username) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Operator Username</b>
</td><td>
						<input value="<?php echo $username ?>" name="operator_username"/><br/>
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





