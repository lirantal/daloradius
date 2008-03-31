<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	isset($_REQUEST['enddate']) ? $enddate = $_REQUEST['enddate'] : $enddate = "";

	$logDebugSQL = "";

        if (isset($_POST['submit'])) {

		if (trim($enddate) != "") {
			
			include 'library/opendb.php';

			// delete all stale sessions in the database that occur until $enddate
			$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." WHERE AcctStopTime='0' AND AcctStartTime<'".$dbSocket->escapeSimple($enddate)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";

			$actionStatus = "success";
			$actionMsg = "Cleaned up stale sessions until date: <b> $enddate </b>";
			$logAction = "Successfully cleaned up stale sessions until date [$enddate] on page: ";

			include 'library/closedb.php';

		}  else { 
			$actionStatus = "failure";
			$actionMsg = "no ending date was entered, please specify an ending date for cleaning up stale sessions from the database";
			$logAction = "Failed cleaning up stale sessions until end date [$enddate] on page: ";
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
<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->
</head>

<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
 
<?php
	include ("menu-accounting-maintenance.php");	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['acctmaintenancecleanup.php'] ?>
				<h144>+</h144></a></h2>
				
				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['acctmaintenancecleanup'] ?>
					<br/>
				</div>
				
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <fieldset>

                <h302> <?php echo $l['title']['CleanupRecords'] ?> </h302>
		<br/>
                <label for='enddate' class='form'><?php echo $l['all']['CleanupSessions']?></label>
                <input name='enddate' type='text' id='enddate' value='<?php echo $enddate ?>' tabindex=100 />
			<img src="library/js_date/calendar.gif" onclick=
				"showChooser(this, 'enddate', 'chooserSpan', 1950, 2010, 'Y-m-d h:i:s', true);" >
                <br />

		<br/><br/>
		<hr><br/>
		<input type="submit" name="submit" value="<?php echo $l['buttons']['apply'] ?>" tabindex=1000 
			class='button' />

	</fieldset>

	<div id="chooserSpan" class="dateChooser select-free" 
		style="display: none; visibility: hidden; width: 160px;">
	</div>

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





