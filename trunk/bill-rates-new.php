<?php
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];


	$type = "";
	$cardbank = "";
	$rate = "";

	if ($type == "") { $type = ""; }
	if ($cardbank == "") { $cardbank = ""; }
	if ($rate == "") { $rate = ""; }

	
	if (isset($_POST['submit'])) {

		$type = $_POST['type'];
		$cardbank = $_POST['cardbank'];
		$rate = $_POST['rate'];

		include 'library/config.php';
		include 'library/opendb.php';

		$sql = "SELECT * FROM rates WHERE type='$type'";
		$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

		if (mysql_num_rows($res) == 0) {
		
			if (trim($type) != "" and trim($cardbank) != "") {

				// insert username/password
				$sql = "INSERT INTO rates VALUES (0, '$type', $cardbank, $rate)";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
		
				echo "success<br/>";
	
			}
		} 
		
		include 'library/closedb.php';

	}



?>

<?php

    include ("menu-billing.php");

?>		
		
		<div id="contentnorightbar">

				<h2 id="Intro">New Rate entry</h2>
				
				<p>
				Fill below the details for the new rate entry
				<br/><br/>
<?php
		if (trim($type) == "") { echo "error: missing type <br/>";  }
		if (trim($cardbank) == "") { echo "error: missing cardbank <br/>";  }
		if (trim($rate) == "") { echo "error: missing rate <br/>";  }
?>
				</p>
				<form action="bill-rates-new.php" method="post">

						<b>Type</b>
						<input value="<?php echo $type ?>" name="type"/><br/>

						<b>Cardbank</b>
						<input value="<?php echo $cardbank ?>" name="cardbank" /><br/>

						<b>Rate</b>
						<input value="<?php echo $rate ?>" name="rate" /><br/>

						<br/>
						<input type="submit" name="submit" value="Apply"/>

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
