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
 
<?php

	include ("menu-config-operators.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><?php echo $l[Intro][mngnew.php] ?></h2>
				
				<p>
				<?php echo $l[captions][mngnew] ?>
				<br/><br/>
				</p>
				<form name="newuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">


<table border='2' class='table1'>
<thead>
                <tr>
                <th colspan='10'>Account Settings</th>
                </tr>
</thead>
<tr><td>
						<?php if (trim($username) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Operator Username</b>
</td><td>
						<input value="<?php echo $username ?>" name="username"/>
						</font>
</td></tr>
<tr><td>
						<?php if (trim($password) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l[FormField][all][Password] ?></b>
</td><td>
						<input <?php echo $hiddenPassword ?> value="<?php echo $password ?>" name="password" />
						</font>
</td></tr>
</table>



<br/><br/>


<table border='2' class='table1'>
<thead>
                <tr>
                <th colspan='10'>Operator Details</th>
                </tr>
</thead>
<tr><td>
						<?php if (trim($operator_firstname) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Operator Firstname</b>
</td><td>
						<input value="<?php echo $operator_firstname ?>" name="operator_firstname"/>
						</font>
</td></tr>
<tr><td>
						<?php if (trim($operator_lastname) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Operator Lastname</b>
</td><td>
						<input value="<?php echo $operator_lastname ?>" name="operator_lastname" />
						</font>
</td></tr>
<tr><td>
						<?php if (trim($operator_title) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Operator Title</b>
</td><td>
						<input value="<?php echo $operator_title ?>" name="operator_title" />
						</font>
</td></tr>
<tr><td>
						<?php if (trim($operator_position) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Operator Position</b>
</td><td>
						<input value="<?php echo $operator_position ?>" name="operator_postition" />
						</font>
</td></tr>
<tr><td>
						<?php if (trim($operator_company) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Operator Company</b>
</td><td>
						<input value="<?php echo $operator_company ?>" name="operator_company" />
						</font>
</td></tr>
<tr><td>
						<?php if (trim($operator_phone1) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Operator Phone1</b>
</td><td>
						<input value="<?php echo $operator_phone1 ?>" name="operator_phone1" />
						</font>
</td></tr>
<tr><td>
						<?php if (trim($operator_phone2) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Operator Phone2</b>
</td><td>
						<input value="<?php echo $operator_phone2 ?>" name="operator_phone2" />
						</font>
</td></tr>
<tr><td>
						<?php if (trim($operator_email2) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Operator Email1</b>
</td><td>
						<input value="<?php echo $operator_email1 ?>" name="operator_email1" />
						</font>
</td></tr>
<tr><td>
						<?php if (trim($operator_email2) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Operator Email2</b>
</td><td>
						<input value="<?php echo $operator_email2 ?>" name="operator_email2" />
						</font>
</td></tr>
<tr><td>
						<?php if (trim($operator_messenger1) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Operator Messenger1</b>
</td><td>
						<input value="<?php echo $operator_messenger1 ?>" name="operator_messenger1" />
						</font>
</td></tr>
<tr><td>
						<?php if (trim($operator_messenger2) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Operator Messenger2</b>
</td><td>
						<input value="<?php echo $operator_messenger2 ?>" name="operator_messenger2" />
						</font>
</td></tr>
<tr><td>
						<?php if (trim($operator_notes) == "") { echo "<font color='#FF0000'>";  }?>
						<b>Operator Notes</b>
</td><td>
						<input value="<?php echo $operator_notes ?>" name="operator_notes" />
						</font>
</td></tr>


<br/><br/>



</table>






<br/><br/>








	<br/>
	<center>
						<input type="submit" name="submit" value="<?php echo $l[buttons][apply] ?>"/>
	</center>
<?php
        include_once('include/management/operator_tables.php');
        drawPagesPermissions($arrayPagesAvailable);
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





