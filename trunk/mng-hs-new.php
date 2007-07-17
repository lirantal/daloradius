<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];


	$name = "";
	$macaddress = "";
	$geocode = "";

	if (isset($_POST["submit"])) {
		$name = $_POST['name'];
		$macaddress = $_POST['macaddress'];
		$geocode = $_POST['geocode'];

		
		include 'library/opendb.php';


		$sql = "SELECT * FROM hotspots WHERE name='$name'";
		$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

		if (mysql_num_rows($res) == 0) {
		
			if (trim($name) != "" and trim($macaddress) != "") {

				// insert username/password
				$sql = "insert into hotspots values (0, '$name', '$macaddress', '$geocode')";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

			echo "<font color='#0000FF'>success<br/></font>";
			} else {
				echo "<font color='#FF0000'>error: you must provide atleast a hotspot name and mac-address <br/></font>"; 
			}

		} else { 
			echo "<font color='#FF0000'>error: hotspot [$name] already exist <br/></font>"; 
			echo "
				<script language='JavaScript'>
				<!--
				alert('You have tried to add a hotspot that already exist in the database.\\nHotspot $name already exist'); 
				-->
				</script>
				";
		}
	
		include 'library/closedb.php';

	}

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
		
				<h2 id="Intro"><?php echo $l[Intro][mnghsnew.php] ?></h2>
				
				<p>
				<?php echo $l[captions][mnghsnew] ?>
				<br/><br/>
				</p>
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<table border='2' class='table1'>
<tr><td>
						<?php if (trim($name) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l[FormField][mnghsdel.php][HotspotName] ?></b>
</td><td>
						<input value="<?php echo $name ?>" name="name"/><br/>
						</font>
</td></tr>
<tr><td>
						<?php if (trim($macaddress) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l[FormField][mnghsedit.php][MACAddress] ?></b>
</td><td>
						<input value="<?php echo $macaddress ?>" name="macaddress" /><br/>
						</font>
</td></tr>
<tr><td>
						<b><?php echo $l[FormField][mnghsedit.php][Geocode] ?></b>
</td><td>
						<input value="<?php echo $geocode ?>" name="geocode" /><br/>
</td></tr>
</table>
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





