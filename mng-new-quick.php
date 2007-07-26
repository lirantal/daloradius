<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

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

	
				echo "<font color='#0000FF'>success<br/></font>";
			}
		} else { 
			echo "<font color='#FF0000'>error: user [$username] already exist <br/></font>"; 
			echo "
				<script language='JavaScript'>
				<!--
				alert('You have tried to add a user that already exist in the database.\\nThe user $username already exist'); 
				-->
				</script>
				";
		}
		
		include 'library/closedb.php';

	}

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


<SCRIPT TYPE="text/javascript">
<!--

function small_window(user,pass,time) {
  var newWindow;
  var currentTime = new Date();
  var props = 'scrollBars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=500,height=200';
  newWindow = window.open("", "Client Receipt", props);
  newWindow.document.write("<html><title>Customer Receipt</title><body><br/>");
  newWindow.document.write("Thank you. <br/>");
  newWindow.document.write("Your username is: ");
  newWindow.document.write(user);
  newWindow.document.write("<br/>");
  newWindow.document.write("Your password is: ");
  newWindow.document.write(pass);
  newWindow.document.write("<br/>");
  newWindow.document.write("Your timecredit is: ");
  newWindow.document.write(time);
  newWindow.document.write("<br/>");
  newWindow.document.write("<br/>");
  newWindow.document.write("Receipt produced on: ");
  newWindow.document.write(currentTime);
  newWindow.document.write("<br/>");
  newWindow.document.write("Enginx HotSpot System ");
  newWindow.document.write("<br/>");
  newWindow.document.write(" </body></html>");
}

// -->
</script>
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
						<input value="<?php echo $password ?>" name="password" />
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

		<select onChange="javascript:setText(this.id,'maxallsession')" id="maxallsession">
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





