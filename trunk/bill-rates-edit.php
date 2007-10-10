<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	include 'library/opendb.php';

	$type = $_REQUEST['type'];

	// fill-in username and password in the textboxes
	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALORATES']." WHERE type='$type'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";
	
	$row = $res->fetchRow();
	$cardbank = $row[2];
	$rate = $row[3];


	if (isset($_POST['submit'])) {

		$type = $_REQUEST['type'];
		$cardbank = $_REQUEST['cardbank'];
		$rate = $_REQUEST['rate'];

		if (trim($type) != "") {

			if (trim($cardbank) != "") {
				$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALORATES']." SET cardbank=$cardbank WHERE type='$type'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
			}
		
			if (trim($rate) != "") {
				$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALORATES']." SET rate=$rate WHERE type='$type'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
				
				$actionStatus = "success";
				$actionMsg = "Updated rates of type: <b> $type</b>";
				$logAction = "Successfully updated rates of type [$type] on page: ";

			} 

		} else {
			$actionStatus = "failure";
			$actionMsg = "you didn't specify a rate type";
			$logAction = "Failed updating empty rate type on page: ";
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





	include_once('library/config_read.php');
    $log = "visited page: ";


?>

<?php

    include ("menu-billing.php");

?>		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><?echo $l['Intro']['billratesedit.php']; ?></h2>
				
				<p>
				<?echo $l['captions']['detailsofnewrate']; ?>
				<br/><br/>			</p>
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
<tr><td>
						<b><?echo $l['all']['Type']; ?></b>
</td><td>
						<input value="<?php echo $type ?>" name="type" /><br/>
</td></tr>
<tr><td>
						<b><? echo $l['all']['CardBank'];?></b>
</td><td>
						<input value="<?php echo $cardbank ?>" name="cardbank" /><br/>
</td></tr>
<tr><td>
						<b><? echo $l['all']['Rate'];?></b>
</td><td>
						<input value="<?php echo $rate ?>" name="rate" /><br/>
</td></tr>
</table>						
						<br/>
<center>
						<input type="submit" name="submit" value="<?echo $l['buttons']['savesettings'];?>"/>

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
