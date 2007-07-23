<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

	$type = "";
	if (isset($_POST['submit'])) {
		// $type = $_POST['type'];
	        $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : '';


		if (trim($type) != "") {
						include 'library/opendb.php';

			// delete all attributes associated with a username
			$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALORATES']." WHERE type='$type'";
			$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

			echo $l[messages][success]"<br/>";
			include 'library/closedb.php';

		}


	}


?>

<?php

    include ("menu-billing.php");

?>		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><?echo $l[Intro][billratesdel.php]; ?></h2>
				
				<p>
				<?echo $l[captions][providebillratetodel]; ?>
				<br/><br/>
<?php
		if (trim($type) == "") { echo $l[messages][missingratetype]." <br/>";  }

?>
				</p>
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
<tr><td>
						<b>Type</b>
</td><td>
						<input value="<?php echo $type ?>" name="type"/><br/>
</td></tr>
</table>
						<br/><br/>
<center>
						<input type="submit" name="submit" value="Apply"/>
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
