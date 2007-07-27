<?php
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

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

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALORATES']." WHERE type='$type'";
		$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

		if (mysql_num_rows($res) == 0) {
		
			if (trim($type) != "" and trim($cardbank) != "") {

				// insert username/password
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALORATES']." VALUES (0, '$type', $cardbank, $rate)";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
		
				$actionStatus = "success";
				$actionMsg = "Added rate type: <b> $type </b> with cardbank: <b> $cardbank </b>";	
			} else {
				$actionStatus = "failure";
				$actionMsg = "you didn't specify a rate type or a cardbank, both are required";
			}

		} else {
			$actionStatus = "failure";
			$actionMsg = "rate type <b> $type </b> already exist in database, 
			<br/> please check for duplicate entries";
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
