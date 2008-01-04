<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	$username = "";
	$password = "";
	$maxallsession = "";
	$expiration = "";
	$sessiontimeout = "";
	$idletimeout = "";
	$logDebugSQL = "";

	if (isset($_POST['submit'])) {
		$username = $_REQUEST['username'];
		$password = $_REQUEST['password'];
		$group = $_REQUEST['group'];
		$maxallsession = $_REQUEST['maxallsession'];
		$expiration = $_REQUEST['expiration'];
		$sessiontimeout = $_REQUEST['sessiontimeout'];
		$idletimeout = $_REQUEST['idletimeout'];

                isset($_REQUEST['firstname']) ? $firstname = $_REQUEST['firstname'] : $firstname = "";
                isset($_REQUEST['lastname']) ? $lastname = $_REQUEST['lastname'] : $lastname = " ";
                isset($_REQUEST['email']) ? $email = $_REQUEST['email'] : $email = "";
                isset($_REQUEST['department']) ? $department = $_REQUEST['department'] : $department = "";
                isset($_REQUEST['company']) ? $company = $_REQUEST['company'] : $company = "";
                isset($_REQUEST['workphone']) ? $workphone = $_REQUEST['workphone'] : $workphone =  "";
                isset($_REQUEST['homephone']) ? $homephone = $_REQUEST['homephone'] : $homephone = "";
                isset($_REQUEST['mobilephone']) ? $mobilephone = $_REQUEST['mobilephone'] : $mobilephone = "";
                isset($_REQUEST['notes']) ? $notes = $_REQUEST['notes'] : $notes = "";



		include 'library/opendb.php';
		
		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='$username'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 0) {
		
			if (trim($username) != "" and trim($password) != "") {

				// insert username/password
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." VALUES (0, '".$dbSocket->escapeSimple($username)."', 
'User-Password', ':=', '".$dbSocket->escapeSimple($password)."')";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
	
				if ($maxallsession) {
					$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." VALUES (0, '".$dbSocket->escapeSimple($username)."',
'Max-All-Session', ':=', '".$dbSocket->escapeSimple($maxallsession)."')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}

				if ($expiration) {
					$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." VALUES (0, '".$dbSocket->escapeSimple($username)."', 
'Expiration', ':=', '".$dbSocket->escapeSimple($expiration)."')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}

				if ($sessiontimeout) {
					$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADREPLY']." VALUES (0, '".$dbSocket->escapeSimple($username)."', 
'Session-Timeout', ':=', '".$dbSocket->escapeSimple($sessiontimeout)."')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}

				if ($idletimeout) {
					$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADREPLY']." VALUES (0, '".$dbSocket->escapeSimple($username)."', 
'Idle-Timeout', ':=', '".$dbSocket->escapeSimple($idletimeout)."')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}


				if (isset($group)) {
					$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." VALUES ('".$dbSocket->escapeSimple($username)."', 
'".$dbSocket->escapeSimple($group)."', '0')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}


				// insert user information table
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOUSERINFO']." VALUES (0, '".$dbSocket->escapeSimple($username)."', 
'".$dbSocket->escapeSimple($firstname)."', '".$dbSocket->escapeSimple($lastname)."', '".$dbSocket->escapeSimple($email)."', 
'".$dbSocket->escapeSimple($department)."', '".$dbSocket->escapeSimple($company)."', '".$dbSocket->escapeSimple($workphone)."', 
'".$dbSocket->escapeSimple($homephone)."', '".$dbSocket->escapeSimple($mobilephone)."', '".$dbSocket->escapeSimple($notes)."')";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

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
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngnewquick.php'] ?>
				<h144>+</h144></a></h2>

				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngnewquick'] ?>
					<br/>
				</div>
				<br/>
				
				<form name="newuser" action="mng-new-quick.php" method="post">

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
						</font>
</td></tr>
<tr><td>
						<?php if (trim($password) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l['FormField']['all']['Password'] ?></b>
</td><td>
						<input <?php if (isset($hiddenPassword)) echo $hiddenPassword ?> value="<?php echo $password ?>" name="password" tabindex=102 />
<a href="javascript:randomPassword()" tabindex=103> genpass</a><br/><br/>
						</font>

</td></tr>

<tr><td>                                        <b><?php echo $l['FormField']['all']['Group']; ?></b>
</td><td>
                                                <input value="<?php if (isset($group)) echo $group ?>" name="group" id="group" tabindex=104 />

<?php   
        include 'include/management/populate_groups.php';
?>

</td></tr>


</table>
<tr><td>

<br/>
<br/>

<table border='2' class='table1' width='600'>
                                        <thead>
                                                        <tr>
                                                        <th colspan='2'> <?php echo $l['table']['Attributes']; ?> </th>
                                                        </tr>
                                        </thead>
<tr><td>
		<?php if (trim($expiration) == "") { echo "<font color='#FF0000'>";  }?>
                <input type="checkbox" onclick="javascript:toggleShowDiv('attributesexpiration')" tabindex=106>
		<b><?php echo $l['FormField']['all']['Expiration'] ?></b></font><br/>
		<div id="attributesexpiration" style="display:none;visibility:visible" >

		<input value="<?php echo $maxallsession ?>" id="expiration" name="expiration" />

                <img src="library/js_date/calendar.gif" onclick="showChooser(this, 'expiration', 'chooserSpan', 1950, 2010, 'd M Y', false);">
		<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>

		<br/>
</div>
</td></tr>


<tr><td>
		<?php if (trim($sessiontimeout) == "") { echo "<font color='#FF0000'>";  }?>
                <input type="checkbox" onclick="javascript:toggleShowDiv('attributessessiontimeout')" tabindex=107>
		<b><?php echo $l['FormField']['all']['SessionTimeout'] ?></b></font><br/>
		<div id="attributessessiontimeout" style="display:none;visibility:visible" >

		<input value="<?php echo $sessiontimeout ?>" id="sessiontimeout" name="sessiontimeout" />

		<select onChange="javascript:setText(this.id,'sessiontimeout')" id="option0">
                <option value="1">calculate time</option>
                <option value="1">seconds</option>
                <option value="60">minutes</option>
                <option value="3600">hours</option>
                <option value="86400">days</option>
                <option value="604800">weeks</option>
                <option value="2592000">months (30 days)</option>
		</select>
		<br/>
</div>
</td></tr>

<tr><td>
		<?php if (trim($idletimeout) == "") { echo "<font color='#FF0000'>";  }?>
                <input type="checkbox" onclick="javascript:toggleShowDiv('attributesidletimeout')" tabindex=108>
		<b><?php echo $l['FormField']['all']['IdleTimeout'] ?></b></font><br/>
		<div id="attributesidletimeout" style="display:none;visibility:visible" >

		<input value="<?php echo $idletimeout ?>" id="idletimeout" name="idletimeout" />

		<select onChange="javascript:setText(this.id,'idletimeout')" id="option1">
                <option value="1">calculate time</option>
                <option value="1">seconds</option>
                <option value="60">minutes</option>
                <option value="3600">hours</option>
                <option value="86400">days</option>
                <option value="604800">weeks</option>
                <option value="2592000">months (30 days)</option>
		</select>
		<br/>
</div>
</td></tr>

<tr><td>
		<?php if (trim($maxallsession) == "") { echo "<font color='#FF0000'>";  }?>
                <input type="checkbox" onclick="javascript:toggleShowDiv('attributesmaxallsession')" tabindex=109>
		<b><?php echo $l['FormField']['mngnewquick.php']['MaxAllSession'] ?></b></font><br/>
		<div id="attributesmaxallsession" style="display:none;visibility:visible" >

		<input value="<?php echo $maxallsession ?>" id="maxallsession" name="maxallsession" />

		<select onChange="javascript:setText(this.id,'maxallsession')" id="option2">
                <option value="1">calculate time</option>
                <option value="1">seconds</option>
                <option value="60">minutes</option>
                <option value="3600">hours</option>
                <option value="86400">days</option>
                <option value="604800">weeks</option>
                <option value="2592000">months (30 days)</option>
		</select>
		<br/>
</div>
</td></tr>
</table>

        </div>

     <div class="tabbertab" title="<?php echo $l['table']['UserInfo']; ?>">

<?php
        include_once('include/management/userinfo.php');
?>
     </div>


</div>


<br/>
<center>
						<input type="submit" name="submit" value="<?php echo $l['buttons']['apply']?>" onclick = "javascript:small_window(document.newuser.username.value, document.newuser.password.value, document.newuser.maxallsession.value);" tabindex=10000 />

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





