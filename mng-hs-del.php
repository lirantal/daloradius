<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	$name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : '[hotspot name]';
	$logDebugSQL = "";

	if (isset($_POST['submit'])) {
		$name = $_REQUEST['name'];

		if (trim($name) != "") {
			
			include 'library/opendb.php';

			// delete all attributes associated with a username
			$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." WHERE name='$name'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";
			
			$actionStatus = "success";
			$actionMsg = "Deleted hotspot: <b> $name </b>";
			$logAction = "Successfully deleted hotpot [$name] on page: ";

			include 'library/closedb.php';
			
		} else { 
			$actionStatus = "failure";
			$actionMsg = "no hotspot was entered, please specify a hotspot name to remove from database";
			$logAction = "Failed deleting hotspot [$name] on page: ";
		}
	}



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

	include ("menu-mng-main.php");
	
?>		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><?php echo $l['Intro']['mnghsdel.php'] ?></h2>
				
				<p>
				<?php echo $l['captions']['mnghsdel'] ?>
				<br/><br/>
				</p>
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<table border='2' class='table1'>
<tr><td>
						<?php if (trim($name) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l['FormField']['mnghsdel.php']['HotspotName'] ?></b>
</td><td>
						<input value="<?php echo $name ?>" name="name"/><br/>
						</font>
</td></tr>
</table>
						<br/><br/>
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





