<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        


    // declaring variables
    $username = "";
    $password = "";
    $expiration = "";

	if (isset($_POST['submit'])) {
		$username = $_REQUEST['username'];
		$password = $_REQUEST['password'];
        $passwordtype = $_REQUEST['passwordType'];	
		$expiration = $_REQUEST['expiration'];

		include 'library/opendb.php';
        include 'include/management/attributes.php';                            // required for checking if an attribute belongs to the

		$sql = "SELECT * FROM radcheck WHERE UserName='$username'";
		$res = $dbSocket->query($sql);

		if ($res->numRows() == 0) {
			if (trim($username) != "" and trim($password) != "") {

				switch($configValues['CONFIG_DB_PASSWORD_ENCRYPTION']) {
					case "cleartext":
						$password = "'$password'";
						break;
					case "crypt":
						$password = "ENCRYPT('$password')";
						break;
					case "md5":
						$password = "MD5('$password')";
						break;
					default:
						$password = "'$password'";
				}
				
				// insert username/password
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADCHECK']." values (0, '$username', '$passwordtype', '==', $password)";
				$res = $dbSocket->query($sql);
	
				// insert expiration
				if ($expiration) {
					$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADCHECK']." values (0, '$username', 'Expiration', ':=', '$expiration')";
					$res = $dbSocket->query($sql);
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
                        $res = $dbSocket->query($sql);

						$counter++;
				} // foreach
				
				$actionStatus = "success";
				$actionMsg = "Added to database new user: <b> $username </b>";
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
<input type="radio" name="passwordType" value="CHAP-Password">CHAP-Password<br>
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
						<input <?php echo $hiddenPassword ?> value="<?php echo $password ?>" name="password" />
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





