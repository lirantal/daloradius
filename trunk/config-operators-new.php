<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	$logDebugSQL = "";

	if (isset($_POST['submit'])) {
		(isset($_REQUEST['operator_username'])) ? $operator_username = $_REQUEST['operator_username'] : $operator_username = "";
		(isset($_REQUEST['operator_password'])) ? $operator_password = $_REQUEST['operator_password'] : $operator_password = "";

//		echo "form was submitted.... $operator_username  $operator_password ";

	include 'library/opendb.php';

		if ( (trim($operator_username) != "") && (trim($operator_password) != "") ) {

//			echo "ok so user/pass were given...";

			$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username='$operator_username'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";

			if ($res->numRows() == 0) {

				$sql = "insert into ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." (id, username, password) values (0, '$operator_username', '$operator_password')";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
			
				foreach ($_POST as $field => $value ) { 
					if ( ($field == "operator_username") || ($field == "operator_password") )
						continue; // we skip these variables as we have already added the user to the database

					if ($field == "submit")
						continue; // we skip these variables as it is of no important for us
			
					$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." SET $field='$value' WHERE username='$operator_username' ";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";

				} // foreach

                                $actionStatus = "success";
                                $actionMsg = "Added to database new operator user: <b> $operator_username </b>";
                                $logAction = "Successfully added new operator user [$operator_username] on page: ";

			} else {
				// if statement returns false which means there is at least one operator
				// in the database with the same username

	                        $actionStatus = "failure";
	                        $actionMsg = "operator user already exist in database: <b> $operator_username </b>";
	                        $logAction = "Failed adding new operator user already existing in database [$operator_username] on page: ";
			}
			
		} else {
			// if statement returns false which means that the user has left an empty field for
			// either the username or password, or both

                        $actionStatus = "failure";
                        $actionMsg = "username or password are empty";
                        $logAction = "Failed adding (possible empty user/pass) new operator user [$operator_username] on page: ";
		}


	include 'library/closedb.php';

	} // if form was submitted
	

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


<?php
        include_once ("library/tabber/tab-layout.php");
?>
 
<?php

	include ("menu-config-operators.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['configoperatorsnew.php'] ?></a></h2>
				
                <div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['configoperatorsnew'] ?>		
		</div>

				<form name="newoperator" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="Operator Info">
        <br/>

<table border='2' class='table1'>
<thead>
                <tr>
                <th colspan='10'>Account Settings</th>
                </tr>
</thead>
<tr><td>
						<?php if (!isset($operator_username)) { echo "<font color='#FF0000'>";  }?>
						<b>Operator Username</b>
</td><td>
						<input value="<?php if (isset($operator_username)) echo $operator_username ?>" name="operator_username"/>
						</font>
</td></tr>
<tr><td>
						<?php if (!isset($operator_password)) { echo "<font color='#FF0000'>";  }?>
						<b>Operator Password</b>
</td><td>
						<input <?php if (isset($operator_hiddenPassword)) echo $hiddenPassword ?> value="<?php if (isset($operator_password)) echo $operator_password ?>" name="operator_password" />
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
						<?php if (!isset($operator_firstname)) { echo "<font color='#FF0000'>";  }?>
						<b>Operator Firstname</b>
</td><td>
						<input value="<?php if (isset($operator_firstname)) echo $operator_firstname ?>" name="firstname"/>
						</font>
</td></tr>
<tr><td>
						<?php if (!isset($operator_lastname)) { echo "<font color='#FF0000'>";  }?>
						<b>Operator Lastname</b>
</td><td>
						<input value="<?php if (isset($operator_lastname)) echo $operator_lastname ?>" name="lastname" />
						</font>
</td></tr>
<tr><td>
						<?php if (!isset($operator_title)) { echo "<font color='#FF0000'>";  }?>
						<b>Operator Title</b>
</td><td>
						<input value="<?php if (isset($operator_title)) echo $operator_title ?>" name="title" />
						</font>
</td></tr>
<tr><td>
						<?php if (!isset($operator_department)) { echo "<font color='#FF0000'>";  }?>
						<b>Operator Department</b>
</td><td>
						<input value="<?php if (isset($operator_department)) echo $operator_department ?>" name="department" />
						</font>
</td></tr>
<tr><td>
						<?php if (!isset($operator_company)) { echo "<font color='#FF0000'>";  }?>
						<b>Operator Company</b>
</td><td>
						<input value="<?php if (isset($operator_company)) $operator_company ?>" name="company" />
						</font>
</td></tr>
<tr><td>
						<?php if (!isset($operator_phone1)) { echo "<font color='#FF0000'>";  }?>
						<b>Operator Phone1</b>
</td><td>
						<input value="<?php if (isset($operator_phone1)) echo $operator_phone1 ?>" name="phone1" />
						</font>
</td></tr>
<tr><td>
						<?php if (!isset($operator_phone2)) { echo "<font color='#FF0000'>";  }?>
						<b>Operator Phone2</b>
</td><td>
						<input value="<?php if (isset($operator_phone2)) echo $operator_phone2 ?>" name="phone2" />
						</font>
</td></tr>
<tr><td>
						<?php if (!isset($operator_email1)) { echo "<font color='#FF0000'>";  }?>
						<b>Operator Email1</b>
</td><td>
						<input value="<?php if (isset($operator_email1)) echo $operator_email1 ?>" name="email1" />
						</font>
</td></tr>
<tr><td>
						<?php if (!isset($operator_email2)) { echo "<font color='#FF0000'>";  }?>
						<b>Operator Email2</b>
</td><td>
						<input value="<?php if (isset($operator_email2)) echo $operator_email2 ?>" name="email2" />
						</font>
</td></tr>
<tr><td>
						<?php if (!isset($operator_messenger1)) { echo "<font color='#FF0000'>";  }?>
						<b>Operator Messenger1</b>
</td><td>
						<input value="<?php if (isset($operator_messenger1)) echo $operator_messenger1 ?>" name="messenger1" />
						</font>
</td></tr>
<tr><td>
						<?php if (!isset($operator_messenger2)) { echo "<font color='#FF0000'>";  }?>
						<b>Operator Messenger2</b>
</td><td>
						<input value="<?php if (isset($operator_messenger2)) echo $operator_messenger2 ?>" name="messenger2" />
						</font>
</td></tr>
<tr><td>
						<?php if (!isset($operator_notes)) { echo "<font color='#FF0000'>";  }?>
						<b>Operator Notes</b>
</td><td>
						<input value="<?php if (isset($operator_notes)) echo $operator_notes ?>" name="notes" />
						</font>
</td></tr>


<br/><br/>



</table>



	</div>

     <div class="tabbertab" title="ACL Settings">
        <br/>


<?php
        include_once('include/management/operator_tables.php');
        drawPagesPermissions($arrayPagesAvailable);
?>

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





