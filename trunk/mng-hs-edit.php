<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	
	include 'library/opendb.php';

	$name = $_GET['name'];

	// fill-in username and password in the textboxes
	$sql = "SELECT * FROM hotspots WHERE name='$name'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	$nt = mysql_fetch_array($res);
	$macaddress = $nt[2];
	$geocode = $nt[3];

	if (isset($_REQUEST['submit'])) {

		$name = $_POST['name'];
		$macaddress = $_POST['macaddress'];
		$geocode = $_POST['geocode'];

		if (trim($name) != "") {

			if (trim($macaddress) != "") {
			$sql = "UPDATE hotspots SET mac='$macaddress' WHERE name='$name'";
			$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
			}

			if (trim($geocode) != "") {
			$sql = "UPDATE hotspots SET geocode='$geocode' WHERE name='$name'";
			$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
			}

		
		}
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
		
				<h2 id="Intro">Edit Hotspots Details</h2>
				
				<p>
				You may edit below details for hotspot 
				<br/><br/>			</p>
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
<tr><td>
						<b>MAC Address</b>
</td><td>
						<input value="<?php echo $macaddress ?>" name="macaddress" /><br/>
</td></tr>
<tr><td>
						<b>Geocode</b>
</td><td>
						<input value="<?php echo $geocode ?>" name="geocode" /><br/>

						<input type="hidden" value="<?php echo $name ?>" name="name" /><br/>
						
</td></tr>
</table>
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





