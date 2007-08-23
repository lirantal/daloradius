<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];


	
	include 'library/opendb.php';

	if (isset($_REQUEST['name']))
		$name = $_REQUEST['name'];
	else
		$name = "";

	// fill-in username and password in the textboxes
	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." WHERE name='$name'";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow();
	$macaddress = $row[2];
	$geocode = $row[3];

	if (isset($_REQUEST['submit'])) {

		$name = $_REQUEST['name'];
		$macaddress = $_REQUEST['macaddress'];
		$geocode = $_REQUEST['geocode'];

		if (trim($name) != "") {

			if (trim($macaddress) != "") {
				$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." SET mac='$macaddress' WHERE name='$name'";
				$res = $dbSocket->query($sql);
			}

			if (trim($geocode) != "") {
				$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." SET geocode='$geocode' WHERE name='$name'";
				$res = $dbSocket->query($sql);
			}
			
			$actionStatus = "success";
			$actionMsg = "Updated attributes for: <b> $name </b>";
			$logAction = "Successfully updates attributes for hotspot [$name] on page: ";
			
		} else {
			$actionStatus = "failure";
			$actionMsg = "no hotspot name was entered, please specify a hotspot name to edit";
			$logAction = "Failed updating attributes for hotspot [$name] on page: ";
		}
		
	}
	

	include 'library/closedb.php';



	if (isset($_REQUEST['name']))
		$name = $_REQUEST['name'];
	else
		$name = "";

	if (trim($name) != "") {
		$name = $_REQUEST['name'];
	} else {
		$actionStatus = "failure";
		$actionMsg = "no hotspot name was entered, please specify a hotspot name to edit</b>";
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
		
				<h2 id="Intro"><?php echo $l[Intro][mnghsedit.php] ?></h2>
				
				<p>
				<?php echo $l[captions][mnghsedit] ?> 
				<br/><br/>			</p>
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
<tr><td>
						<b><?php echo $l[FormField][mnghsedit.php][MACAddress] ?></b>
</td><td>
						<input value="<?php echo $macaddress ?>" name="macaddress" /><br/>
</td></tr>
<tr><td>
						<b><?php echo $l[FormField][mnghsedit.php][Geocode] ?></b>
</td><td>
						<input value="<?php echo $geocode ?>" name="geocode" /><br/>

						<input type="hidden" value="<?php echo $name ?>" name="name" /><br/>
						
</td></tr>
</table>
						<br/><br/>
<center>
						<input type="submit" name="submit" value="<?php echo $l[buttons][savesettings] ?>"/>
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





