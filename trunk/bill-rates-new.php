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

				include 'library/opendb.php';

		$sql = "SELECT * FROM rates WHERE type='$type'";
		$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

		if (mysql_num_rows($res) == 0) {
		
			if (trim($type) != "" and trim($cardbank) != "") {

				// insert username/password
				$sql = "INSERT INTO rates VALUES (0, '$type', $cardbank, $rate)";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
		
				echo $l[messages][success]."<br/>";
	
			}
		} 
		
		include 'library/closedb.php';

	}



?>

<?php

    include ("menu-billing.php");

?>		
		
		<div id="contentnorightbar">

		<h2 id="Intro"><? echo $l[Intro][billratesnew.php]; ?></h2>
				
				<p>
				<? echo $l[captions][filldetailsofnewrate]; ?>
				<br/><br/>
<?php
		if (trim($type) == "") { echo $l[messages][missingtype]."<br/>";  }
		if (trim($cardbank) == "") { echo $l[messages][missingcardbank]."<br/>";  }
		if (trim($rate) == "") { echo $l[messages][missingrate]."<br/>";  }
?>
				</p>
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
<tr><td>
						<b><?echo $l[all][Type]; ?></b>
</td><td>
						<input value="<?php echo $type ?>" name="type"/><br/>
</td></tr>
<tr><td>
						<b><?echo $l[all][CardBank]; ?></b>
</td><td>
						<input value="<?php echo $cardbank ?>" name="cardbank" /><br/>
</td></tr>
<tr><td>
						<b><?echo $l[all][Rate]; ?></b>
</td><td>
						<input value="<?php echo $rate ?>" name="rate" /><br/>
</td></tr>
</table>
						<br/>
<center>
						<input type="submit" name="submit" value="<?echo $l[buttons][apply]; ?>"/>
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
