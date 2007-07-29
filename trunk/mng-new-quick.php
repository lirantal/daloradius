<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        

	$username = "";
	$password = "";
	$maxallsession = "";

	if (isset($_POST['submit'])) {
		$username = $_POST['username'];
		$password = $_POST['password'];
		$maxallsession = $_POST['maxallsession'];

		include 'library/opendb.php';
		
		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='$username'";
		$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

		if (mysql_num_rows($res) == 0) {
		
			if (trim($username) != "" and trim($password) != "") {

				// insert username/password
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADCHECK']." values (0, '$username', 'User-Password', '==', '$password')";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
	
				if ($maxallsession) {
					$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADCHECK']." values (0, '$username', 'Max-All-Session', ':=', '$maxallsession')";
					$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
				}

				if ($maxallsession) {
					$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADREPLY']." values (0, '$username', 'Session-Timeout', ':=', '$maxallsession')";
					$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
				}

				$actionStatus = "success";
				$actionMsg = "Added to database new user: <b> $username";
				$logAction = "Successfully added new user [$username] on page: ";
			} else {
				$actionStatus = "failure";
				$actionMsg = "username or password are empty";
				$logAction = "Failed adding (possible empty user/pass) new user [$username] on page: ";
			}
		} else { 
			$actionStatus = "failure";
			$actionMsg = "user already exist in database: <b> $username </b>";
			$logAction = "Failed adding new user already existing in database [$username] on page: ";
		}
		
		include 'library/closedb.php';

	}




	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

	
	if ($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] == "yes")
		$hiddenPassword = "type=\"password\"";
	

?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
</head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script src="library/javascript/productive_funcs.js" type="text/javascript"></script>

<?php

	include ("menu-mng-main.php");
	
?>

		<div id="contentnorightbar">
		
				<h2 id="Intro"><?php echo $l[Intro][mngnewquick.php] ?></h2>
				
				<p>
				<?php echo $l[captions][mngnewquick] ?>
				<br/><br/>
				</p>
				<form name="newuser" action="mng-new-quick.php" method="post">

<table border='2' class='table1'>
<tr><td>
						<?php if (trim($username) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l[FormField][all][Username] ?></b>
</td><td>
						<input value="<?php echo $username ?>" name="username"/>
<a href="javascript:randomUsername()"> genuser</a><br/>
						</font>
</td></tr>
<tr><td>
						<?php if (trim($password) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l[FormField][all][Password] ?></b>
</td><td>
						<input <?php echo $hiddenPassword ?> value="<?php echo $password ?>" name="password" />
<a href="javascript:randomPassword()"> genpass</a><br/><br/>
						</font>

</td></tr>
</table>
<br/>

	<table border='2' class='table1' width='600'>
	<tr><td>
		<?php if (trim($maxallsession) == "") { echo "<font color='#FF0000'>";  }?>
                <input type="checkbox" onclick="javascript:toggleShowDiv('attributesmaxallsession')">
		<b><?php echo $l[FormField][mngnewquick.php][MaxAllSession] ?></b><br/>
		<div id="attributesmaxallsession" style="display:none;visibility:visible" >

		<input value="<?php echo $maxallsession ?>" id="maxallsession" name="maxallsession" />

		<select onChange="javascript:setText(this.id,'maxallsession')" id="option0">
		<option value="86400">1day(s)</option>
		<option value="259200">3day(s)</option>
		<option value="604800">1week(s)</option>
		<option value="1209600">2week(s)</option>
		<option value="1814400">3week(s)</option>
		<option value="2592000">1month(s)</option>
		<option value="5184000">2month(s)</option>
		<option value="7776000">3month(s)</option>
		</select>
						<br/>
</div>
<br/>
						</font>
</td></tr></table>
<br/>
<center>
						<input type="submit" name="submit" value="<?php echo $l[buttons][apply]?>" onclick = "javascript:small_window(document.newuser.username.value, document.newuser.password.value, document.newuser.maxallsession.value);" />

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





