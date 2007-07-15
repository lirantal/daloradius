<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	$username = !empty($_REQUEST['username']) ? $_REQUEST['username'] : '[username]';

	if (isset($_POST['submit'])) {

		if (trim($username) != "") {
			
			include 'library/opendb.php';

			// delete all attributes associated with a username
			$sql = "delete from radcheck where Username='$username'";
			$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

			$sql = "delete from radreply where Username='$username'";
			$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

			echo "<font color='#0000FF'>success<br/></font>";
			include 'library/closedb.php';

		}  else { 
			echo "<font color='#FF0000'>error: no user was entered, please specify a username to remove from database<br/></font>"; 
			echo "
				<script language='JavaScript'>
				<!--
				alert('No user was entered, please specify a username to remove from database'); 
				-->
				</script>
				";
		}


	}

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
		
				<h2 id="Intro">Remove User</h2>
				
				<p>
				To remove a user from the database you must provide the username or the account id
				<br/><br/>
				</p>
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
<tr><td>
						<?php if (trim($username) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Username</b>
</td><td>
						<input value="<?php echo $username ?>" name="username"/><br/>
						</font>
</td></tr>
</table>
						<br/><br/>
<center>
						<input type="submit" name="submit" value="Apply"/>
</center>
				</form>
		
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





