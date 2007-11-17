<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

    // declaring variables
    isset($_REQUEST['username']) ? $username = $_REQUEST['username'] : $username = "";
    isset($_REQUEST['password']) ? $password = $_REQUEST['password'] : $password = "";
    isset($_REQUEST['group']) ? $group = $_REQUEST['group'] : $group = "";
    $logDebugSQL = "";

	if (isset($_POST['submit'])) {
		$username = $_REQUEST['username'];
		$password = $_REQUEST['password'];
	        $passwordtype = $_REQUEST['passwordType'];	
		$group = $_REQUEST['group'];

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
						$dbPassword = "'$password'";
						break;
					case "crypt":
						$dbPassword = "ENCRYPT('$password')";
						break;
					case "md5":
						$dbPassword = "MD5('$password')";
						break;
					default:
						$dbPassword = "'$password'";
				}
				
				// insert username/password
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_RADCHECK']." values (0, '$username', '$passwordtype', '==', $dbPassword)";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
	
				// insert usergroup mapping
				if (isset($group)) {
					$sql = "INSERT INTO ". $configValues['CONFIG_DB_TBL_RADUSERGROUP'] ." values ('$username', '$group',0) ";
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
						case "group":
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
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngnew.php'] ?></a></h2>
				
				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngnew'] ?>
				</div>
				<form name="newuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<div class="tabber">

     <div class="tabbertab" title="<?php echo $l['table']['AccountInfo']; ?>">

<table border='2' class='table1'>
                                        <thead>
                                                        <tr>
                                                        <th colspan='2'> <?php echo $l['table']['AccountInfo']; ?> </th>
                                                        </tr>
                                        </thead>
<tr><td>
						<?php if (trim($username) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l['FormField']['all']['Username'] ?></b>
</td><td>
						<input value="<?php echo $username ?>" name="username" tabindex=100 />
<a href="javascript:randomUsername()" tabindex=101> genuser</a><br/>

<a href="javascript:toggleShowDiv('showPasswordType')" tabindex=102>advanced</a><br/>
<div id="showPasswordType" style="display:none;visibility:visible" >
<br/>
<input type="radio" name="passwordType" value="User-Password" checked tabindex=103>User-Password<br>
<input type="radio" name="passwordType" value="CHAP-Password" tabindex=104>CHAP-Password<br>
<input type="radio" name="passwordType" value="Cleartext-Password" tabindex=105>Cleartext-Password<br>
<input type="radio" name="passwordType" value="Crypt-Password" tabindex=106>Crypt-Password<br>
<input type="radio" name="passwordType" value="MD5-Password" tabindex=107>MD5-Password<br>
<input type="radio" name="passwordType" value="SHA1-Password" tabindex=108>SHA1-Password<br>
</div>


						</font>
</td></tr>
<tr><td>
						<?php if (trim($password) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l['FormField']['all']['Password'] ?></b>
</td><td>
						<input <?php if (isset($hiddenPassword)) echo $hiddenPassword ?> value="<?php echo $password ?>" name="password" tabindex=109 />
<a href="javascript:randomPassword()" tabindex=110> genpass</a><br/>
						</font>
</td></tr>


<tr><td>                                        <b><?php echo $l['FormField']['all']['Group']; ?></b>
</td><td>
                                                <input value="<?php if (isset($group)) echo $group ?>" name="group" id="group" tabindex=111 />

<select onChange="javascript:setStringText(this.id,'group')" id='usergroup' tabindex=105>
<?php

        include 'library/opendb.php';

        // Grabing the group lists from usergroup table

	$sql = "(SELECT distinct(GroupName) FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY'].") UNION (SELECT distinct(GroupName) FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK'].");";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "
                        <option value='$row[0]'> $row[0]
                        ";

        }

        include 'library/closedb.php';
?>
</select>
</td></tr>
</table>

     </div>


     <div class="tabbertab" title="<?php echo $l['table']['UserInfo']; ?>">

<?php
	include_once('include/management/userinfo.php');
?>
     </div>



     <div class="tabbertab" title="<?php echo $l['table']['Attributes']; ?>">

<?php
        include_once('include/management/attributes.php');
        drawAttributes();
?>
	<br/>
     </div>		

</div>

	<br/>
	<center>
						<input type="submit" name="submit" value="<?php echo $l['buttons']['apply'] ?>" tabindex=1000 />
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





