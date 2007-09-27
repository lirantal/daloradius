<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	include 'library/opendb.php';
		// required for checking if an attribute belongs to the
		// radcheck table or the radreply based upon it's name	


	if (isset($_REQUEST['submit'])) {

		$operator_username = $_REQUEST['operator_username'];
		if (trim($operator_username) != "") {

			 foreach( $_POST as $field=>$value ) { 

				if ( ($field == "operator_username") || ($field == "submit") )	// we skip these post variables as they are not important
					continue;	

				if ( ($field == "lastlogin") )
					continue;	
					
					$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." SET $field='$value' WHERE username='$operator_username'";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";

	        } //foreach $_POST

			$actionStatus = "success";
			$actionMsg = "Updated settings for: <b> $operator_username </b>";
			$logAction = "Successfully updated settings for operator user [$operator_username] on page: ";
			
		} else { // if username != ""
			$actionStatus = "failure";
			$actionMsg = "no operator user was entered, please specify an operator username to edit";
			$logAction = "Failed updating settings for operator user [$operator_username] on page: ";
		}
	} // if isset post submit


	if (isset($_REQUEST['operator_username']))
		$operator_username = $_REQUEST['operator_username'];
	else
		$operator_username = "";

	if (trim($operator_username) != "") {
		$operator_username = $_REQUEST['operator_username'];
	} else {
		$actionStatus = "failure";
		$actionMsg = "no operator user was entered, please specify an operator username to edit";
	}

	

	/* fill-in all the operator settings */

	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE UserName='$operator_username'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
	$operator_password = $row['password'];
	$operator_firstname = $row['firstname'];
	$operator_lastname = $row['lastname'];
	$operator_title = $row['title'];
	$operator_department = $row['department'];
	$operator_company = $row['company'];
	$operator_phone1 = $row['phone1'];
	$operator_phone2 = $row['phone2'];
	$operator_email1 = $row['email1'];
	$operator_email2 = $row['email2'];
	$operator_messenger1 = $row['messenger1'];
	$operator_messenger2 = $row['messenger2'];
	$operator_notes = $row['notes'];
	$operator_lastlogin = $row['lastlogin'];






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
</head>


<?php
        include_once ("library/tabber/tab-layout.php");
?>
 
<?php

	include ("menu-config-operators.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro">Edit Operator Settings</h2>
				
				<p>
				Edit the operator user details below
				</p>

<br/>
<br/>

				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
				<input type="hidden" value="<?php echo $operator_username ?>" name="operator_username" />

<div class="tabber">

     <div class="tabbertab" title="Operator Info">
        <br/>

<table border='2' class='table1'>
<thead>
                <tr>
                <th colspan='10'>Operator Settings</th>
                </tr>
</thead>
<tr><td>
                                                <?php if (trim($operator_password) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Operator Password</b>
</td><td>


		<?php
			if ($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] == "yes")
				echo "<input type='password' value='$operator_password' name='password' />";
			else 
				echo "<input value='$operator_password' name='password' />";
		?>

                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($operator_lastlogin) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Last Login Time</b>
</td><td>
                                                <input disabled value="<?php echo $operator_lastlogin ?>" name="lastlogin"/>
                                                </font>
</td></tr>
</table>

	</div>
     <div class="tabbertab" title="Contact Info">
        <br/>

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
                                                <input value="<?php echo $operator_firstname ?>" name="firstname"/>
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($operator_lastname) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Operator Lastname</b>
</td><td>
                                                <input value="<?php echo $operator_lastname ?>" name="lastname" />
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($operator_title) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Operator Title</b>
</td><td>
                                                <input value="<?php echo $operator_title ?>" name="title" />
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($operator_department) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Operator Department</b>
</td><td>
                                                <input value="<?php echo $operator_department ?>" name="department" />
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($operator_company) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Operator Company</b>
</td><td>
                                                <input value="<?php echo $operator_company ?>" name="company" />
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($operator_phone1) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Operator Phone1</b>
</td><td>
                                                <input value="<?php echo $operator_phone1 ?>" name="phone1" />
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($operator_phone2) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Operator Phone2</b>
</td><td>
                                                <input value="<?php echo $operator_phone2 ?>" name="phone2" />
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($operator_email2) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Operator Email1</b>
</td><td>
                                                <input value="<?php echo $operator_email1 ?>" name="email1" />
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($operator_email2) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Operator Email2</b>
</td><td>
                                                <input value="<?php echo $operator_email2 ?>" name="email2" />
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($operator_messenger1) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Operator Messenger1</b>
</td><td>
                                                <input value="<?php echo $operator_messenger1 ?>" name="messenger1" />
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($operator_messenger2) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Operator Messenger2</b>
</td><td>
                                                <input value="<?php echo $operator_messenger2 ?>" name="messenger2" />
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($operator_notes) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Operator Notes</b>
</td><td>
                                                <input value="<?php echo $operator_notes ?>" name="notes" />
                                                </font>
</td></tr>


<br/><br/>
</table>

	</div>
     <div class="tabbertab" title="ACL Settings">
        <br/>

<?php
        include_once('include/management/operator_tables.php');
        drawPagesPermissions($arrayPagesAvailable, $operator_username);
?>

	</div>
</div>


						<br/><br/>
<center>
						<input type="submit" name="submit" value="<?php echo $l[buttons][apply] ?>"/>
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

