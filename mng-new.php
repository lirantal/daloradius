<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

    // declaring variables
    $username = "";
    $password = "";
    $expiration = "";

	if (isset($_POST['submit'])) {
		$username = $_POST['username'];
		$password = $_POST['password'];
        $passwordtype = $_POST['passwordType'];	
		$expiration = $_POST['expiration'];

		include 'library/opendb.php';
        include 'include/management/attributes.php';                            // required for checking if an attribute belongs to the

		$sql = "SELECT * FROM radcheck WHERE UserName='$username'";
		$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

		if (mysql_num_rows($res) == 0) {
		
			if (trim($username) != "" and trim($password) != "") {

				// insert username/password
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADCHECK']." values (0, '$username', '$passwordtype', '==', '$password')";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
	
				// insert expiration
				if ($expiration) {
					$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADCHECK']." values (0, '$username', 'Expiration', ':=', '$expiration')";
					$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
				}
			
				 foreach( $_POST as $attribute=>$value ) { 

					if ( ($attribute == "username") || ($attribute == "password") || ($attribute == "passwordType") || ($attribute == "expiration") || ($attribute == "submit") )	
						continue; // we skip these post variables as they are not important

					if (!($value[0]))
						continue;
						
						$useTable = checkTables($attribute);			// checking if the attribute's name belong to the radreply
																		// or radcheck table (using include/management/attributes.php function)

				        $counter = 0;

						$sql = "INSERT INTO $useTable values (0, '$username', '$attribute', '" . $value[1] ."', '$value[0]')  ";
                        $res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

						$counter++;

				} // foreach
				

				//echo "<font color='#0000FF'>success<br/></font>";
				$msg = "Added new user <b> $username </b> to database";
				header("location: mng-success.php?task=$msg");
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
<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->
</head>
 
<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script src="library/javascript/productive_funcs.js" type="text/javascript"></script>

<?php

	include ("menu-mng-main.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><?php echo $l[Intro][mngnew.php] ?></h2>
				
				<p>
				<?php echo $l[captions][mngnew] ?>
				<br/><br/>
				</p>
				<form name="newuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<table border='2' class='table1'>
<tr><td>
						<?php if (trim($username) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l[FormField][all][Username] ?></b>
</td><td>
						<input value="<?php echo $username ?>" name="username"/>
<a href="javascript:randomUsername()"> genuser</a><br/>

<a href="javascript:toggleShowDiv('showPasswordType')">advanced</a><br/>
<div id="showPasswordType" style="display:none;visibility:visible" >
<br/>
<input type="radio" name="passwordType" value="User-Password" checked>User-Password<br>
<input type="radio" name="passwordType" value="Chap-Password">Chap-Password<br>
<input type="radio" name="passwordType" value="Cleartext-Password">Cleartext-Password<br>
<input type="radio" name="passwordType" value="Crypt-Password">Crypt-Password<br>
<input type="radio" name="passwordType" value="MD5-Password">MD5-Password<br>
<input type="radio" name="passwordType" value="SHA1-Password">SHA1-Password<br>
</div>


						</font>
</td></tr>
<tr><td>
						<?php if (trim($password) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l[FormField][all][Password] ?></b>
</td><td>
						<input value="<?php echo $password ?>" name="password" />
<a href="javascript:randomPassword()"> genpass</a><br/>
						</font>
</td></tr>
<tr><td>
						<?php if (trim($expiration) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l[FormField][all][Expiration] ?></b>
</td><td>
<input name="expiration" type="text" id="expiration" value="<?php echo $expiration ?>">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'expiration', 'chooserSpan', 1950, 2010, 'd M Y', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
						<br/>
						</font>
</td></tr>
</table>
	<br/>
	<center>
						<input type="submit" name="submit" value="<?php echo $l[buttons][apply] ?>"/>
	</center>
<?php
        include_once('include/management/attributes.php');
        drawAttributes();
?>
		
							<br/>

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





