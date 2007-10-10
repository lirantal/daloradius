<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

    // declaring variables
    $username = "";
    $password = "";
    $expiration = "";
	$logDebugSQL = "";

	if (isset($_POST['submit'])) {
		$username = $_REQUEST['username'];
		$password = $_REQUEST['password'];
	        $passwordtype = $_REQUEST['passwordType'];	
		$expiration = $_REQUEST['expiration'];

		$firstname = $_REQUEST['firstname'];
		$lastname = $_REQUEST['lastname'];
		$email = $_REQUEST['email'];
		$department = $_REQUEST['department'];
		$company = $_REQUEST['company'];
		$workphone = $_REQUEST['workphone'];
		$homephone = $_REQUEST['homephone'];
		$mobilephone = $_REQUEST['mobilephone'];
		$notes = $_REQUEST['notes'];
		

		include 'library/opendb.php';
        	include 'include/management/attributes.php';                            // required for checking if an attribute belongs to the

		$sql = "SELECT * FROM radcheck WHERE UserName='$username'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

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
				$logDebugSQL .= $sql . "\n";
	
				// insert expiration
				if ($expiration) {
					$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADCHECK']." values (0, '$username', 'Expiration', ':=', '$expiration')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}
	
				// insert user information table
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOUSERINFO']." values (0, '$username', '$firstname', '$lastname', '$email', '$department', '$company', '$workphone', '$homephone', '$mobilephone', '$notes')";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
		
				 foreach( $_POST as $attribute=>$value ) { 


					// switch case to rise the flag for several $attribute which we do not
					// wish to process (ie: do any sql related stuff in the db)
					switch ($attribute) {

						case "username":
						case "password":
						case "passwordType":
						case "expiration":
						case "submit":
						case "firstname":
						case "lastname":
						case "email":
						case "department":
						case "company":
						case "workphone":
						case "homephone":
						case "mobilephone":
						case "notes":
							$skipLoopFlag = 1;	// if any of the cases above has been met we set a flag
										// to skip the loop (continue) without entering it as
										// we do not want to process this $attribute in the following
										// code block
							break;

					}
				
					if ($skipLoopFlag == 1) {
                                                $skipLoopFlag = 0;              // resetting the loop flag
						continue;
					}

					if (!($value[0]))
						continue;
						
						$useTable = checkTables($attribute);			// checking if the attribute's name belong to the radreply
													// or radcheck table (using include/management/attributes.php function)

				        $counter = 0;

					$sql = "INSERT INTO $useTable values (0, '$username', '$attribute', '" . $value[1] ."', '$value[0]')  ";
                		        $res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";

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
        include_once ("library/tabber/tab-layout.php");
?>

<?php

	include ("menu-mng-main.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><?php echo $l['Intro']['mngnew.php'] ?></h2>
				
				<p>
				<?php echo $l['captions']['mngnew'] ?>
				</p>

				<form name="newuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<div class="tabber">

     <div class="tabbertab" title="Account Info">
        <br/>

<table border='2' class='table1'>
<tr><td>
						<?php if (trim($username) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l['FormField']['all']['Username'] ?></b>
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
						<b><?php echo $l['FormField']['all']['Password'] ?></b>
</td><td>
						<input <?php echo $hiddenPassword ?> value="<?php echo $password ?>" name="password" />
<a href="javascript:randomPassword()"> genpass</a><br/>
						</font>
</td></tr>
<tr><td>
						<?php if (trim($expiration) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l['FormField']['all']['Expiration'] ?></b>
</td><td>
<input name="expiration" type="text" id="expiration" value="<?php echo $expiration ?>">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'expiration', 'chooserSpan', 1950, 2010, 'd M Y', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
						<br/>
						</font>
</td></tr>
</table>

     </div>


     <div class="tabbertab" title="User Info">
        <br/>

<?php
	include_once('include/management/userinfo.php');
?>
     </div>



     <div class="tabbertab" title="Attributes">

<?php
        include_once('include/management/attributes.php');
        drawAttributes();
?>
	<br/>
     </div>		

</div>

	<br/>
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





