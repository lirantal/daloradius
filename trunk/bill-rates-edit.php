<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

	include 'library/opendb.php';

	$type = $_GET['type'];

	// fill-in username and password in the textboxes
	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALORATES']." WHERE type='$type'";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
	$nt = mysql_fetch_array($res);
	$cardbank = $nt[2];
	$rate = $nt[3];


	if (isset($_POST['submit'])) {

		$type = $_POST['type'];
		$cardbank = $_POST['cardbank'];
		$rate = $_POST['rate'];

		if (trim($type) != "") {

			if (trim($cardbank) != "") {
				$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALORATES']." SET cardbank=$cardbank WHERE type='$type'";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
			}
		
			if (trim($rate) != "") {
				$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALORATES']." SET rate=$rate WHERE type='$type'";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

				$actionStatus = "success";
				$actionMsg = "Updated rates of type: <b> $type</b>";

			} 

		} else {
			$actionStatus = "failure";
			$actionMsg = "you didn't specify a rate type";
		}
	}

	include 'library/closedb.php';



	if (isset($_REQUEST['type']))
		$type = $_REQUEST['type'];
	else
		$type = "";

	if (trim($type) != "") {
		$type = $_REQUEST['type'];
	} else {
		$actionStatus = "failure";
		$actionMsg = "no type was entered, please specify a rate type to edit";
	}



?>

<?php

    include ("menu-billing.php");

?>		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><?echo $l[Intro][billratesedit.php]; ?></h2>
				
				<p>
				<?echo $l[captions][detailsofnewrate]; ?>
				<br/><br/>			</p>
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
<tr><td>
						<b><?echo $l[all][Type]; ?></b>
</td><td>
						<input value="<?php echo $type ?>" name="type" /><br/>
</td></tr>
<tr><td>
						<b><? echo $l[all][CardBank];?></b>
</td><td>
						<input value="<?php echo $cardbank ?>" name="cardbank" /><br/>
</td></tr>
<tr><td>
						<b><? echo $l[all][Rate];?></b>
</td><td>
						<input value="<?php echo $rate ?>" name="rate" /><br/>
</td></tr>
</table>						
						<br/>
<center>
						<input type="submit" name="submit" value="<?echo $l[buttons][savesettings];?>"/>

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
