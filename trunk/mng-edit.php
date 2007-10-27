<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	$logDebugSQL = ""; 	// initialize variable

	include 'library/opendb.php';
		// required for checking if an attribute belongs to the
		// radcheck table or the radreply based upon it's name	
	include 'include/management/attributes.php';				

	if (isset($_REQUEST['submit'])) {

		$username = $_REQUEST['username'];
		$password = "";						// we initialize the $password variable to contain nothing

                $firstname = $_REQUEST['firstname'];
                $lastname = $_REQUEST['lastname'];
                $email = $_REQUEST['email'];
                $department = $_REQUEST['department'];
                $company = $_REQUEST['company'];
                $workphone = $_REQUEST['workphone'];
                $homephone = $_REQUEST['homephone'];
                $mobilephone = $_REQUEST['mobilephone'];
                $notes = $_REQUEST['notes'];

		if (trim($username) != "") {

			// update user information table
                       $sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOUSERINFO']." SET firstname='$firstname', lastname='$lastname', email='$email', department='$department', company='$company', workphone='$workphone', homephone='$homephone', mobilephone='$mobilephone', notes='$notes' WHERE username='$username'";
                       $res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";
			

			 foreach( $_POST as $attribute=>$value ) { 


                                        // switch case to rise the flag for several $attribute which we do not
                                        // wish to process (ie: do any sql related stuff in the db)
                                        switch ($attribute) {

                                                case "username":
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


echo "attribute: $attribute | value[0] = $value[0] | value[1]: $value[1] <br/>";

					if (!($value[0]))
						continue;

					
					$useTable = checkTables($attribute);			// checking if the attribute's name belong to the radreply
												// or radcheck table (using include/management/attributes.php function)

			        $counter = 0;

					// we set the $password variable to the attribute value only if that attribute is actually a password attribute indeed 
					// and this has to be done because we're looping on all attributes that were submitted with the form
					switch($attribute) {
						case "User-Password":
							$password = "'$value[0]'";
							break;
						case "CHAP-Password":
							$password = "'$value[0]'";
							break;
						case "Cleartext-Password":
							$password = "'$value[0]'";
							break;							
						case "Crypt-Password":
							$password = "'$value[0]'";
							break;	
						case "MD5-Password":
							$password = "'$value[0]'";
							break;
						case "SHA1-Password":
							$password = "'$value[0]'";
							break;
						default:
							$value[0] = "'$value[0]'";
					}
					
					// first we check that the config option is actually set and available in the config file
					if (isset($configValues['CONFIG_DB_PASSWORD_ENCRYPTION'])) {
						// if so we need to use different function for each encryption type and so we force it here
						switch($configValues['CONFIG_DB_PASSWORD_ENCRYPTION']) {
							case "cleartext":
								if ($password != "")
										$value[0] = "$password";
								break;
							case "crypt":
								if ($password != "")
										$value[0] = "ENCRYPT($password)";
								break;
							case "md5":
								if ($password != "")
										$value[0] = "MD5($password)";
								break;
						}
					} else {
						// if the config option was not set and we encountered a password attribute we set it to default which is cleartext
						if ($password != "")
							$value[0] = "$password";
					}


					/* since we have added include/management/attributes.php to the form which 
					   populates the page with all the existing attributes for us to choose from, even
					   those that are not exist, we can't simply UPDATE because it might be that the attribute
				 	   doesn't exist at all and we need to insert it. 
					   for this reason we need to check if it exists or not, if exists we update, if not we insert */

					$sql = "SELECT Attribute FROM $useTable WHERE UserName='$username' AND Attribute='$attribute'";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
					if ($res->numRows() == 0) {

						/* if the returned rows equal 0 meaning this attribute is not found and we need to add it */

						$sql = "INSERT INTO $useTable values(0,'$username', '$attribute', '$value[1]', $value[0])";
						$res = $dbSocket->query($sql);
						$logDebugSQL .= $sql . "\n";

					} else {
					
						/* we update the $value[0] entry which is the attribute's value */
						$sql = "UPDATE $useTable SET Value=$value[0] WHERE UserName='$username' AND Attribute='$attribute'";
						$res = $dbSocket->query($sql);
						$logDebugSQL .= $sql . "\n";
	

						/* then we update $value[1] which is the attribute's operator */
						$sql = "UPDATE $useTable SET Op='$value[1]' WHERE UserName='$username' AND Attribute='$attribute'";
						$res = $dbSocket->query($sql);
						$logDebugSQL .= $sql . "\n";

					}

					$counter++;
					$password = "";		// we MUST reset the $password variable to nothing  so that it's not kepy in the loop and will repeat itself as the value to set

	        } //foreach $_POST

			$actionStatus = "success";
			$actionMsg = "Updated attributes for: <b> $username </b>";
			$logAction = "Successfully updates attributes for user [$username] on page: ";
			
		} else { // if username != ""
			$actionStatus = "failure";
			$actionMsg = "no user was entered, please specify a username to edit";		
			$logAction = "Failed updating attributes for user [$username] on page: ";
		}
	} // if isset post submit


	if (isset($_REQUEST['username']))
		$username = $_REQUEST['username'];
	else
		$username = "";

	if (trim($username) != "") {
		$username = $_REQUEST['username'];
	} else {
		$actionStatus = "failure";
		$actionMsg = "no user was entered, please specify a username to edit";
	}

	
	/* an sql query to retrieve the password for the username to use in the quick link for the user test connectivity
	*/
	$sql = "SELECT Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='$username' AND Attribute like '%Password'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	$row = $res->numRows();
	$user_password = $row[0];

	/* fill-in all the user radcheck attributes */

	$sql = "SELECT Attribute, op, Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='$username'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	$arrAttr = array();
	$arrOp = array();
	$arrValue = array();

        while($row = $res->fetchRow()) {
		array_push($arrAttr, $row[0]);
		array_push($arrOp, $row[1]);
		array_push($arrValue, $row[2]);
	}	
		


	/* fill-in all the user radreply attributes */

	$sql = "SELECT Attribute, op, Value FROM ".$configValues['CONFIG_DB_TBL_RADREPLY']." WHERE UserName='$username'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	$arrAttrReply = array();
	$arrOpReply = array();
	$arrValueReply = array();

        while($row = $res->fetchRow()) {
		array_push($arrAttrReply, $row[0]);
		array_push($arrOpReply, $row[1]);
		array_push($arrValueReply, $row[2]);
	}	



	/* fill-in all the user info details */


	$sql = "SELECT firstname, lastname, email, department, company, workphone, homephone, mobilephone, notes FROM ".$configValues['CONFIG_DB_TBL_DALOUSERINFO']." WHERE UserName='$username'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

                $ui_firstname = $row['firstname'];
                $ui_lastname = $row['lastname'];
                $ui_email = $row['email'];
                $ui_department = $row['department'];
                $ui_company = $row['company'];
                $ui_workphone = $row['workphone'];
                $ui_homephone = $row['homephone'];
                $ui_mobilephone = $row['mobilephone'];
                $ui_notes = $row['notes'];







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

<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->


</head>

<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>

<?php
        include_once ("library/tabber/tab-layout.php");
?>

<?php

	include ("menu-mng-main.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><?php echo $l['Intro']['mngedit.php'] ?></h2>
				
				<p>
				<?php echo $l['captions']['mngedit'] ?>
				</p>

<table border='2' class='table1'>
<thead>
                <tr>
                <th class='info' colspan='10'>Tool-Box</th>
                </tr>
</thead>
<tr><td>
</td><td>
</td><td>
</td><td>
        <a class='novisit' href="config-maint-test-user.php?username=<?php echo $username ?>&password=<?php echo $user_password ?>"> Test Connectivity </a>
</td><td>
        <a class='novisit' href="acct-username.php?username=<?php echo $username ?>"> Accounting </a>
</td><td>
        <a class='novisit' href="graphs-overall_logins.php?type=monthly&username=<?php echo $username ?>"> Graphs - Logins </a>
</td><td>
        <a class='novisit' href="graphs-overall_download.php?type=monthly&username=<?php echo $username ?>"> Graphs - Downloads </a>
</td><td>
        <a class='novisit' href="graphs-overall_upload.php?type=monthly&username=<?php echo $username ?>"> Graphs - Uploads </a>
</td></tr>
</table>
<br/>



				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

				<input type="hidden" value="<?php echo $username ?>" name="username" />

<div class="tabber">

     <div class="tabbertab" title="radcheck table">

<?php


		echo "<table border='2' class='table1'>";
	        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>radcheck</th>
                                </tr>
                        </thead>
                ";

		include ('include/management/op_select_options.php');

                $counter = 0;
                foreach ($arrAttr as $attribute) {

			echo "<tr><td>";
			echo "<b>$arrAttr[$counter]</b>";
			echo "</td><td>";

			if ( ($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] == "yes") and (preg_match("/.*-Password/", $arrAttr[$counter])) ) {
				echo "<input type='password' value='$arrValue[$counter]' name='$arrAttr[$counter]' />";
				drawOptions();
			} else {
				echo "<input value='$arrValue[$counter]' name='$arrAttr[$counter][]' id='$arrAttr[$counter][]' />";
				echo " &nbsp; ";
				echo "<select name='$arrAttr[$counter][]'";
				echo "<option value='$arrOp[$counter]'>$arrOp[$counter]</option>";
				drawOptions();
				echo "</select>";
			}

			switch ($arrAttr[$counter]) {
				case "Expiration":
							echo "&nbsp;
							<img src=\"library/js_date/calendar.gif\" onclick=\"showChooser(this, '$arrAttr[$counter][]', 
							'chooserSpan', 1950, 2010, 'd M Y', false);\">
							<div id=\"chooserSpan\" class=\"dateChooser select-free\" style=\"display: none; visibility: 
							hidden; width: 160px;\"></div>
							";
						break;
				case "Max-All-Session":
						break;
			
			}

			echo "<br/></td></tr>";
			$counter++;

		}

		echo "</table>";
		echo "</div>";

		echo "<div class='tabbertab' title='radreply table'>";


		echo "<table border='2' class='table1'>";
	        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>radreply </th>
                                </tr>
                        </thead>
                ";

                $counter = 0;
                foreach ($arrAttrReply as $attribute) {

                        echo "<tr><td>";
			echo "<b>$arrAttrReply[$counter]</b>";
                        echo "</td><td>";
			echo "<input value='$arrValueReply[$counter]' name='$arrAttrReply[$counter][]' /><br/>";
                        echo "</td></tr>";
			$counter++;

		}

		echo "</table>";
		echo "</div>";


?>

     <div class="tabbertab" title="User Info">

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



						<br/><br/>
<center>
						<input type="submit" name="submit" value="<?php echo $l['buttons']['apply'] ?>" tabindex=14 />
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

