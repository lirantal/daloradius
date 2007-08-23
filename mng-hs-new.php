<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        

	$name = "";
	$macaddress = "";
	$geocode = "";

	if (isset($_POST["submit"])) {
		$name = $_REQUEST['name'];
		$macaddress = $_REQUEST['macaddress'];
		$geocode = $_REQUEST['geocode'];
		
		include 'library/opendb.php';

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." WHERE name='$name'";
		$res = $dbSocket->query($sql);

		if ($res->numRows() == 0) {
			if (trim($name) != "" and trim($macaddress) != "") {

				// insert username/password
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." values (0, '$name', '$macaddress', '$geocode')";
				$res = $dbSocket->query($sql);

				$actionStatus = "success";
				$actionMsg = "Added to database new hotspot: <b> $name";
				$logAction = "Successfully added new hotspot [$name] on page: ";
			} else {
				$actionStatus = "failure";
				$actionMsg = "you must provide atleast a hotspot name and mac-address";	
				$logAction = "Failed adding new hotspot [$name] on page: ";	
			}
		} else { 
			$actionStatus = "failure";
			$actionMsg = "You have tried to add a hotspot that already exist in the database: $name";	
			$logAction = "Failed adding new hotspot already in database [$username] on page: ";		
		}
	
		include 'library/closedb.php';

	}



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





