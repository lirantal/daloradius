<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

        isset($_REQUEST['name']) ? $name = $_REQUEST['name'] : $name = array(1=>'[NO HOTSPOT ENTERED]');
	$logDebugSQL = "";

//	if (isset($_POST['submit'])) {
        if (isset($_REQUEST['name'])) {

                if (!is_array($name))
                        $name = array($name, NULL);

		$allHotspots = "";
	
                foreach ($name as $variable=>$value) {
			if (trim($value) != "") {

				include 'library/opendb.php';

                                $name = $value;
                                $allHotspots .= $name . ", ";

				// delete all attributes associated with a username
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." WHERE name='".$dbSocket->escapeSimple($name)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
				
				$actionStatus = "success";
				$actionMsg = "Deleted hotspot(s): <b> $allHotspots </b>";
				$logAction = "Successfully deleted hotpot(s) [$allHotspots] on page: ";
	
				include 'library/closedb.php';
			
			} else { 
				$actionStatus = "failure";
				$actionMsg = "no hotspot was entered, please specify a hotspot name to remove from database";
				$logAction = "Failed deleting hotspot(s) [$allHotspots] on page: ";
			}

		} //foreach

	} else {  //if submit
                if (isset($_REQUEST['name'])) {
			if (is_array($_REQUEST['name'])) {
	                        $singleHotspot = $_REQUEST['name'];
	                        $name = $singleHotspot[0];
			} else {
	                        $name = $_REQUEST['name'];
			}				
                } else {
                        $name = "";
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
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<?php

	include ("menu-mng-main.php");
	
?>		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mnghsdel.php'] ?></a></h2>
				
                                <div id="helpPage" style="display:none;visibility:visible" >				
					<?php echo $l['helpPage']['mnghsdel'] ?>
				</div>				

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<table border='2' class='table1'>
<tr><td>
						<?php if (trim($name) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l['FormField']['mnghsdel.php']['HotspotName'] ?></b>
</td><td>
						<input value="<?php echo $name ?>" name="name[]" tabindex=100 /><br/>
						</font>
</td></tr>
</table>
						<br/><br/>
<center>
						<input type="submit" name="submit" value="<?php echo $l['buttons']['apply'] ?>" tabindex=1000 />
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





