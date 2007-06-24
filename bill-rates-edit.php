<?php

    include ("library/checklogin.php");
    include_once ("lang/main.php");
    $operator = $_SESSION['operator_user'];


	include 'library/config.php';
	include 'library/opendb.php';

	$type = $_GET['type'];

	// fill-in username and password in the textboxes
	$sql = "SELECT * FROM rates WHERE type='$type'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	$nt = mysql_fetch_array($res);
	$cardbank = $nt[2];
	$rate = $nt[3];


	if (isset($_POST['submit'])) {

		$type = $_POST['type'];
		$cardbank = $_POST['cardbank'];
		$rate = $_POST['rate'];

		if (trim($type) != "") {

			if (trim($cardbank) != "") {
			$sql = "UPDATE rates SET cardbank=$cardbank WHERE type='$type'";
			$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
			}
		
			if (trim($rate) != "") {
			$sql = "UPDATE rates SET rate=$rate WHERE type='$type'";
			$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
			}

		}
	}

	include 'library/closedb.php';





?>

<?php

    include ("menu-billing.php");

?>		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><?echo $l[Intro][billratesedit.php]; ?></h2>
				
				<p>
				<?echo $l[captions][detailsofnewrate]; ?>
				<br/><br/>			</p>
				<form action="bill-rates-edit.php" method="post">
						<b><?echo $l[all][Type]; ?></b>
						<input value="<?php echo $type ?>" name="type" /><br/>

						<b><? echo $l[all][CardBank];?></b>
						<input value="<?php echo $cardbank ?>" name="cardbank" /><br/>

						<b><? echo $l[all][Rate];?></b>
						<input value="<?php echo $rate ?>" name="rate" /><br/>
						
						<br/>
						<input type="submit" name="submit" value="<?echo $l[buttons][savesettings];?>"/>

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
