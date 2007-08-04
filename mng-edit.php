<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        

	include 'library/opendb.php';
		// required for checking if an attribute belongs to the
		// radcheck table or the radreply based upon it's name	
	include 'include/management/attributes.php';				

	if (isset($_REQUEST['submit'])) {

		$username = $_REQUEST['username'];
		if (trim($username) != "") {

			 foreach( $_POST as $attribute=>$value ) { 

				if ( ($attribute == "username") || ($attribute == "submit") )	// we skip these post variables as they are not important
					continue;	
					
					$useTable = checkTables($attribute);			// checking if the attribute's name belong to the radreply
												// or radcheck table (using include/management/attributes.php function)

			                $counter = 0;

					$sql = "UPDATE $useTable SET Value='$value' WHERE UserName='$username' AND Attribute='$attribute'";
					$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

					$counter++;

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


	/* fill-in all the user radcheck attributes */

	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='$username'";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

	$arrAttr = array();
	$arrOp = array();
	$arrValue = array();

        while($nt = mysql_fetch_array($res)) {
		array_push($arrAttr, $nt['Attribute']);
		array_push($arrOp, $nt['op']);
		array_push($arrValue, $nt['Value']);
	}	
		


	/* fill-in all the user radreply attributes */

	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADREPLY']." WHERE UserName='$username'";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

	$arrAttrReply = array();
	$arrOpReply = array();
	$arrValueReply = array();

        while($nt = mysql_fetch_array($res)) {
		array_push($arrAttrReply, $nt['Attribute']);
		array_push($arrOpReply, $nt['op']);
		array_push($arrValueReply, $nt['Value']);
	}	


	include 'library/closedb.php';



	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');


?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
<?php

	include ("menu-mng-main.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><?php echo $l[Intro][mngedit.php] ?></h2>
				
				<p>
				<?php echo $l[captions][mngedit] ?>
				</p>

<br/>
<table border='2' class='table1'>
<thead>
                <tr>
                <th colspan='10'>Tool-Box</th>
                </tr>
</thead>
<tr><td>
        <a class='novisit' href="config-maint-test-user.php?username=<?php echo $username ?>"> Test Connectivity </a>
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

<?php


		echo "<table border='2' class='table1'>";
	        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>radcheck</th>
                                </tr>
                        </thead>
                ";

                $counter = 0;
                foreach ($arrAttr as $attribute) {

			echo "<tr><td>";
			echo "<b>$arrAttr[$counter]</b";
			echo "</td><td>";

			if ( ($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] == "yes") and (preg_match("/.*-Password/", $arrAttr[$counter])) )
				echo "<input type='password' value='$arrValue[$counter]' name='$arrAttr[$counter]' /><br/>";
			else
				echo "<input value='$arrValue[$counter]' name='$arrAttr[$counter]' /><br/>";

			echo "</td></tr>";
			$counter++;

		}

		echo "</table>";

		echo "<br/><br/>";

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
			echo "<b>$arrAttrReply[$counter]</b";
                        echo "</td><td>";
			echo "<input value='$arrValueReply[$counter]' name='$arrAttrReply[$counter]' /><br/>";
                        echo "</td></tr>";
			$counter++;

		}

		echo "</table>";


?>

						<br/><br/>
<center>
						<input type="submit" name="submit" value="<?php echo $l[buttons][apply] ?>"/>
</center>

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

