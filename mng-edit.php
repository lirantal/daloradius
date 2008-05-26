<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

	include ("library/checklogin.php");
	$operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	$logAction = "";
	$logDebugSQL = ""; 	// initialize variable

	include 'library/opendb.php';

	if (isset($_REQUEST['submit'])) {

		$currDate = date('Y-m-d H:i:s');			// current date and time to enter as creationdate field
		$currBy = $_SESSION['operator_user'];

		$username = $_REQUEST['username'];
		$password = "";						// we initialize the $password variable to contain nothing

		isset ($_REQUEST['oldgroups']) ? $oldgroups = $_REQUEST['oldgroups'] : $oldgroups = "";
		isset ($_REQUEST['groups']) ? $groups = $_REQUEST['groups'] : $groups = "";
		isset ($_REQUEST['groups_priority']) ? $groups_priority = $_REQUEST['groups_priority'] : $groups_priority = "";

		$firstname = $_REQUEST['firstname'];
		$lastname = $_REQUEST['lastname'];
		$email = $_REQUEST['email'];
		$department = $_REQUEST['department'];
		$company = $_REQUEST['company'];
		$workphone = $_REQUEST['workphone'];
		$homephone = $_REQUEST['homephone'];
		$mobilephone = $_REQUEST['mobilephone'];
		$notes = $_REQUEST['notes'];

		isset($_POST['passwordOrig']) ? $passwordOrig = $_POST['passwordOrig'] : $passwordOrig = "";

		if (trim($username) != "") {

			$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].
					" WHERE username='".$dbSocket->escapeSimple($username)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";

			// if there were no records for this user present in the userinfo table
			if ($res->numRows() == 0) {
				// we add these records to the userinfo table
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].
					" (id, username, firstname, lastname, email, department, company, workphone, homephone, mobilephone,".
					" notes, creationdate, creationby, updatedate, updateby) ".
					" VALUES (0, '".$dbSocket->escapeSimple($username)."', '".
					$dbSocket->escapeSimple($firstname)."', '".$dbSocket->escapeSimple($lastname)."', '".
					$dbSocket->escapeSimple($email)."','".$dbSocket->escapeSimple($department)."', '".
					$dbSocket->escapeSimple($company)."', '".$dbSocket->escapeSimple($workphone)."','".
					$dbSocket->escapeSimple($homephone)."', '".$dbSocket->escapeSimple($mobilephone)."', '".
					$dbSocket->escapeSimple($notes)."', '$currDate', '$currBy', NULL, NULL)";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
			} else {
				// update user information table
			   $sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOUSERINFO']." SET firstname='".
					$dbSocket->escapeSimple($firstname).
					"', lastname='".$dbSocket->escapeSimple($lastname).
					"', email='".$dbSocket->escapeSimple($email).
					"', department='".$dbSocket->escapeSimple($department).
					"', company='".$dbSocket->escapeSimple($company).
					"', workphone='".$dbSocket->escapeSimple($workphone).
					"', homephone='".$dbSocket->escapeSimple($homephone).
					"', mobilephone='".$dbSocket->escapeSimple($mobilephone).
					"', notes='".$dbSocket->escapeSimple($notes).
					"', updatedate='$currDate', updateby='$currBy' ".
					" WHERE username='".$dbSocket->escapeSimple($username)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
			}
			
			 // insert usergroup mapping
			 if ($groups) {

				$grpcnt = 0;			// group counter
				foreach ($groups as $group) {

					$oldgroup = $oldgroups[$grpcnt];			

					if (!($groups_priority[$grpcnt]))
						$group_priority = 1;
					else
						$group_priority = $groups_priority[$grpcnt];

					$sql = "UPDATE ". $configValues['CONFIG_DB_TBL_RADUSERGROUP'] ." SET UserName='".
						$dbSocket->escapeSimple($username)."', GroupName='".$dbSocket->escapeSimple($group)."', priority=".
						$dbSocket->escapeSimple($group_priority)." WHERE UserName='".$dbSocket->escapeSimple($username).
						"' AND GroupName='".$dbSocket->escapeSimple($oldgroup)."';";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";

					$grpcnt++;		// we increment group index count so we can access the group priority array
				}
			}

			foreach( $_POST as $element=>$field ) { 

				// switch case to rise the flag for several $attribute which we do not
				// wish to process (ie: do any sql related stuff in the db)
				switch ($element) {
					case "username":
					case "submit":
					case "oldgroups":
					case "groups":
					case "groups_priority":
					case "firstname":
					case "lastname":
					case "email":
					case "department":
					case "company":
					case "workphone":
					case "homephone":
					case "mobilephone":
					case "notes":
					case "passwordOrig":
						$skipLoopFlag = 1;      // if any of the cases above has been met we set a flag
												// to skip the loop (continue) without entering it as
												// we do not want to process this $attribute in the following
												// code block
						break;
				}

				if ($skipLoopFlag == 1) {
					$skipLoopFlag = 0; 		// resetting the loop flag
					continue;
				}

				if (isset($field[0]))
					$attribute = $field[0];
                if (isset($field[1]))
					$value = $field[1];
                if (isset($field[2]))
					$op = $field[2];
                if (isset($field[3]))
					$table = $field[3];

                if ($table == 'check')
					$table = $configValues['CONFIG_DB_TBL_RADCHECK'];
                if ($table == 'reply')
					$table = $configValues['CONFIG_DB_TBL_RADREPLY'];


				if ( (!($value)) || (!($attribute)) )
					continue;

				$counter = 0;

				// because the $value[0] which is the attribute value is later manually appended the '' so that
				// password policies are enforced by the php server we need to perform the secure method escapeSimple()
				// at an early point in the script.
				$value = $dbSocket->escapeSimple($value);

				// we set the $password variable to the attribute value only if that attribute is actually a password attribute indeed 
				// and this has to be done because we're looping on all attributes that were submitted with the form
				switch($attribute) {
					case "User-Password":
					case "CHAP-Password":
					case "Cleartext-Password":
					case "Crypt-Password":
					case "MD5-Password":
					case "SHA1-Password":
						$value = "'$value'";
						$passwordAttribute = 1;	// if this is a password 
						break;					// attribute then we tag it
												// as true
					default:
						$value = "'$value'";
						$passwordAttribute = 0;
				}

				// first we check that the config option is actually set and available in the config file
				if ( (isset($configValues['CONFIG_DB_PASSWORD_ENCRYPTION'])) and ($passwordAttribute == 1) ) {
					// if so we need to use different function for each encryption type and so we force it here
					$passwordOrig = "'$passwordOrig'";
					switch($configValues['CONFIG_DB_PASSWORD_ENCRYPTION']) {
						case "cleartext":
							if ( ($value != $passwordOrig) )
								$value = "$value";
							break;
						case "crypt":
							if ( ($value != $passwordOrig) )
								$value = "ENCRYPT($value)";
							break;
						case "md5":
							if ( ($value != $passwordOrig) )
								$value = "MD5($value)";
							break;
					}
				}

				/* we can't simply UPDATE because it might be that the attribute
				doesn't exist at all and we need to insert it. 
				for this reason we need to check if it exists or not, if exists we update, if not we insert 
				*/

				$sql = "SELECT Attribute FROM $table WHERE UserName='".$dbSocket->escapeSimple($username).
					"' AND Attribute='".$dbSocket->escapeSimple($attribute)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
				
				if ($res->numRows() == 0) {
					/* if the returned rows equal 0 meaning this attribute is not found and we need to add it */
					$sql = "INSERT INTO $table (id,Username,Attribute,op,Value) ".
						" VALUES (0,'".$dbSocket->escapeSimple($username)."', '".
						$dbSocket->escapeSimple($attribute)."', '".$dbSocket->escapeSimple($op).
						"', $value)";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				} else {
					/* we update the $value[0] entry which is the attribute's value */
					$sql = "UPDATE $table SET Value=$value WHERE UserName='".
						$dbSocket->escapeSimple($username)."' AND Attribute='".
						$dbSocket->escapeSimple($attribute)."'";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";

					/* then we update $value[1] which is the attribute's operator */
					$sql = "UPDATE $table SET Op='".$dbSocket->escapeSimple($op).
						"' WHERE UserName='".$dbSocket->escapeSimple($username).
						"' AND Attribute='".$dbSocket->escapeSimple($attribute)."'";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
				}

				$counter++;
				$password = "";		// we MUST reset the $password variable to nothing  so that it's not kepy in the loop and will repeat itself as the value to set

	        } //foreach $_POST

			$successMsg = "Updated attributes for: <b> $username </b>";
			$logAction .= "Successfully updates attributes for user [$username] on page: ";
			
		} else { // if username != ""
			$failureMsg = "no user was entered, please specify a username to edit";		
			$logAction .= "Failed updating attributes for user [$username] on page: ";
		}
	} // if isset post submit


	if (isset($_REQUEST['username']))
		$username = $_REQUEST['username'];
	else
		$username = "";

	if (trim($username) != "") {
		$username = $_REQUEST['username'];
	} else {
		$failureMsg = "no user was entered, please specify a username to edit";
	}

	$edit_username = $username; //feed the sidebar variables

	/* an sql query to retrieve the password for the username to use in the quick link for the user test connectivity
	*/
	$sql = "SELECT Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='".
		$dbSocket->escapeSimple($username)."' AND Attribute like '%Password'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	$row = $res->numRows();
	$user_password = $row[0];


	/* fill-in all the user info details */


	$sql = "SELECT firstname, lastname, email, department, company, workphone, homephone, mobilephone, notes, ".
		" creationdate, creationby, updatedate, updateby FROM ".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].
		" WHERE UserName='".
		$dbSocket->escapeSimple($username)."'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	$row = $res->fetchRow();

	$ui_firstname = $row[0];
	$ui_lastname = $row[1];
	$ui_email = $row[2];
	$ui_department = $row[3];
	$ui_company = $row[4];
	$ui_workphone = $row[5];
	$ui_homephone = $row[6];
	$ui_mobilephone = $row[7];
	$ui_notes = $row[8];
	$ui_creationdate = $row[9];
	$ui_creationby = $row[10];
	$ui_updatedate = $row[11];
	$ui_updateby = $row[12];

	include 'library/closedb.php';

	include_once('library/config_read.php');
	$log = "visited page: ";

?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" href="css/auto-complete.css" media="screen" type="text/css">

<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->


</head>

<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>

<script type="text/javascript" src="library/javascript/ajax.js"></script>
<script type="text/javascript" src="library/javascript/dynamic_attributes.js"></script>

<?php
	include_once ("library/tabber/tab-layout.php");
?>

<?php
	include ("menu-mng-users.php");	
?>

<div id="contentnorightbar">

	<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngedit.php'] ?>
	:: <?php if (isset($username)) { echo $username; } ?><h144>+</h144></a></h2>

	<div id="helpPage" style="display:none;visibility:visible" >
		<?php echo $l['helpPage']['mngedit'] ?>
		<br/>
	</div>
	<?php
		include_once('include/management/actionMessages.php');
	?>

	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

	<input type="hidden" value="<?php echo $username ?>" name="username" />

	<div class="tabber">

		<div class="tabbertab" title="<?php echo $l['title']['RADIUSCheck']; ?>">

		<fieldset>

			<h302> <?php echo $l['title']['RADIUSCheck']; ?> </h302>
			<br/>

			<ul>
<?php

	include 'library/opendb.php';
	include 'include/management/pages_common.php';
	include 'include/management/populate_selectbox.php';

	$editCounter = 0;

	$sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute, ".
		$configValues['CONFIG_DB_TBL_RADCHECK'].".op, ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Value, ".
		$configValues['CONFIG_DB_TBL_DALODICTIONARY'].".Type, ".
		$configValues['CONFIG_DB_TBL_DALODICTIONARY'].".RecommendedTooltip ".
		" FROM ".
		$configValues['CONFIG_DB_TBL_RADCHECK']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALODICTIONARY'].
		" ON ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute=".
		$configValues['CONFIG_DB_TBL_DALODICTIONARY'].".attribute WHERE ".
		$configValues['CONFIG_DB_TBL_RADCHECK'].".UserName='".$dbSocket->escapeSimple($username)."'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	if ($numrows = $res->numRows() == 0) {
		echo "<center>";
		echo $l['messages']['noCheckAttributesForUser'];
		echo "</center>";
	}

	while($row = $res->fetchRow()) {

		echo "<label class='attributes'>";
		echo "<a class='tablenovisit' href='mng-del.php?username=$username&attribute=$row[0]&tablename=radcheck'>
				<img src='images/icons/delete.png' border=0 alt='Remove' /> </a>";
		echo "</label>";
		echo "<label for='attribute' class='attributes'>&nbsp;&nbsp;&nbsp;$row[0]</label>";

		echo "<input type='hidden' name='editValues".$editCounter."[]' value='$row[0]' />";

		if (preg_match("/.*-Password/", $row[0])) {
			if ($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] == "yes") {
				echo "<input type='password' value='$row[2]' name='editValues".$editCounter."[]'  style='width: 115px' />";
				echo "<input type='hidden' value='$row[2]' name='passwordOrig' />";
			} else {
				echo "<input type='text' value='$row[2]' name='editValues".$editCounter."[]'  style='width: 115px' />";
				echo "<input type='hidden' value='$row[2]' name='passwordOrig' />";
			}
		} else {
			echo "<input value='$row[2]' name='editValues".$editCounter."[]' style='width: 115px' />";
		}
		echo "&nbsp;";
		echo "<select name='editValues".$editCounter."[]' style='width: 45px' class='form'>";
		echo "<option value='$row[1]'>$row[1]</option>";
		drawOptions();
		echo "</select>";

		echo "<input type='hidden' name='editValues".$editCounter."[]' value='radcheck' style='width: 90px'>";

		$editCounter++;			// we increment the counter for the html elements of the edit attributes


		if (!$row[3])
			$row[3] = "unavailable";
		if (!$row[4])
			$row[4] = "unavailable";

		printq("
			<img src='images/icons/comment.png' alt='Tip' border='0' onClick=\"javascript:toggleShowDiv('$row[0]Tooltip')\" />
			<br/>
	                <div id='$row[0]Tooltip'  style='display:none;visibility:visible' class='ToolTip2'>
	                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<i><b>Type:</b> $row[3]</i><br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<i><b>Tooltip Description:</b> $row[4]</i><br/>
				<br/>
	                </div>
		");

	}

?>
			<br/><br/>
			<hr><br/>
		
<?php
	include 'include/management/buttons.php';
?>

			<br/>
			<input type='submit' name='submit' value='<?php echo $l['buttons']['apply']?>' class='button' />
			<br/>

			</ul>

		</fieldset>
	</div>

	<div class='tabbertab' title='<?php echo $l['title']['RADIUSReply']?>' >

	<fieldset>

		<h302> <?php echo $l['title']['RADIUSReply']; ?> </h302>
		<br/>

		<ul>

<?php

	$sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADREPLY'].".Attribute, ".
		$configValues['CONFIG_DB_TBL_RADREPLY'].".op, ".$configValues['CONFIG_DB_TBL_RADREPLY'].".Value, ".
		$configValues['CONFIG_DB_TBL_DALODICTIONARY'].".Type, ".
		$configValues['CONFIG_DB_TBL_DALODICTIONARY'].".RecommendedTooltip ".
		" FROM ".
		$configValues['CONFIG_DB_TBL_RADREPLY']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALODICTIONARY'].
		" ON ".$configValues['CONFIG_DB_TBL_RADREPLY'].".Attribute=".
		$configValues['CONFIG_DB_TBL_DALODICTIONARY'].".attribute WHERE ".
		$configValues['CONFIG_DB_TBL_RADREPLY'].".UserName='".$dbSocket->escapeSimple($username)."'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	if ($numrows = $res->numRows() == 0) {
		echo "<center>";
		echo $l['messages']['noReplyAttributesForUser'];
		echo "</center>";
	}
	
	while($row = $res->fetchRow()) {
		
		echo "<label class='attributes'>";
		echo "<a class='tablenovisit' href='mng-del.php?username=$username&attribute=$row[0]&tablename=radreply'>
				<img src='images/icons/delete.png' border=0 alt='Remove' /> </a>";
		echo "</label>";
                echo "<label for='attribute' class='attributes'>&nbsp;&nbsp;&nbsp;$row[0]</label>";

		echo "<input type='hidden' name='editValues".$editCounter."[]' value='$row[0]' />";

		if ( ($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] == "yes") and (preg_match("/.*-Password/", $row[0])) ) {
			echo "<input type='password' value='$row[2]' name='editValues".$editCounter."[]'  style='width: 115px' />";
			echo "&nbsp;";
			echo "<select name='editValues".$editCounter."[]' style='width: 45px' class='form'>";
			echo "<option value='$row[1]'>$row[1]</option>";
			drawOptions();
			echo "</select>";
		} else {
			echo "<input value='$row[2]' name='editValues".$editCounter."[]' style='width: 115px' />";
			echo "&nbsp;";
			echo "<select name='editValues".$editCounter."[]' style='width: 45px' class='form'>";
			echo "<option value='$row[1]'>$row[1]</option>";
			drawOptions();
			echo "</select>";
		}

		echo "<input type='hidden' name='editValues".$editCounter."[]' value='radreply' style='width: 90px'>";
		$editCounter++;			// we increment the counter for the html elements of the edit attributes

		if (!$row[3])
			$row[3] = "unavailable";
		if (!$row[4])
			$row[4] = "unavailable";

		printq("
			<img src='images/icons/comment.png' alt='Tip' border='0' onClick=\"javascript:toggleShowDiv('$row[0]Tooltip')\" />
			<br/>
	                <div id='$row[0]Tooltip'  style='display:none;visibility:visible' class='ToolTip2'>
	                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<i><b>Type:</b> $row[3]</i><br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<i><b>Tooltip Description:</b> $row[4]</i><br/>
				<br/>
	                </div>
		");

	}

?>
        <br/><br/>
        <hr><br/>
<?php
	include 'include/management/buttons.php';
?>
        <br/>
        <input type='submit' name='submit' value='<?php echo $l['buttons']['apply']?>' class='button' />
        <br/>

	</ul>

        </fieldset>  
    </div>  

<?php
    include 'library/closedb.php';
?>


     <div class="tabbertab" title="<?php echo $l['title']['UserInfo']; ?>">
        <?php
                include_once('include/management/userinfo.php');
        ?>
     </div>


     <div class="tabbertab" title="<?php echo $l['title']['Attributes']; ?>">
        <?php
                include_once('include/management/attributes.php');
        ?>
     </div>

     <div class="tabbertab" title="<?php echo $l['title']['Groups']; ?>">

	
<?php
        include 'library/opendb.php';
        include_once('include/management/groups.php');
        include 'library/closedb.php';	
?>
        <br/>

     </div>

</div>

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

