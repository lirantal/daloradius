<?php 

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include 'library/config.php';
	include 'library/opendb.php';
	include 'include/management/attributes.php';				// required for checking if an attribute belongs to the
										// radcheck table or the radreply based upon it's name

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
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

				$counter++;

        	  } //foreach $_POST

		} // if username != ""

	} // if isset post submit


	$username = "";
	$username = $_REQUEST['username'];


	/* fill-in all the user radcheck attributes */

	$sql = "SELECT * FROM radcheck WHERE UserName='$username'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$arrAttr = array();
	$arrOp = array();
	$arrValue = array();

        while($nt = mysql_fetch_array($res)) {
		array_push($arrAttr, $nt['Attribute']);
		array_push($arrOp, $nt['op']);
		array_push($arrValue, $nt['Value']);
	}	
		


	/* fill-in all the user radreply attributes */

	$sql = "SELECT * FROM radreply WHERE UserName='$username'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$arrAttrReply = array();
	$arrOpreply = array();
	$arrValueReply = array();

        while($nt = mysql_fetch_array($res)) {
		array_push($arrAttrReply, $nt['Attribute']);
		array_push($arrOpReply, $nt['op']);
		array_push($arrValueReply, $nt['Value']);
	}	


	include 'library/closedb.php';

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
		
				<h2 id="Intro">Edit User Details</h2>
				
				<p>
				You may fill below details for new user addition to database
				</p>
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
						<input type="submit" name="submit" value="Save Settings"/>
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

