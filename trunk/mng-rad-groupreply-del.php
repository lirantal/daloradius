<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include('library/check_operator_perm.php');

	$groupname = "";
	$value = "";
	$logDebugSQL = "";

	isset($_REQUEST['groupname']) ? $groupname = $_REQUEST['groupname'] : $groupname = "";
	isset($_REQUEST['value']) ? $value = $_REQUEST['value'] : $value = "";
 	
	if (isset($_POST['submit'])) {
		if (trim($groupname) != "") {
			
			include 'library/opendb.php';

			if (trim($value) != "") {

                // delete only a specific groupname and it's attribute
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY']." WHERE GroupName='".$dbSocket->escapeSimple($groupname)."'AND Value='$value'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$actionStatus = "success";
				$actionMsg = "Deleted Group: <b> $groupname </b> and it's Value: <b> $value </b>";
				$logAction = "Successfully deleted group [$groupname] and it's value [$value] on page: ";

				include 'library/closedb.php';

			} else {

                // delete all attributes associated with a groupname
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY']." WHERE GroupName='".$dbSocket->escapeSimple($groupname)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$actionStatus = "success";
				$actionMsg = "Deleted all instances for Group: <b> $groupname </b>";
				$logAction = "Successfully deleted all instances for group [$groupname] on page: ";

				include 'library/closedb.php';
			}

		}  else {
			$actionStatus = "failure";
			$actionMsg = "No groupname was entered, please specify a groupname to remove from database";
			$logAction = "Failed deleting empty group on page: ";
		}
	}	
	
	if (isset($_REQUEST['groupname']))
		$groupname = $_REQUEST['groupname'];
	else
		$groupname = "";

	if (trim($groupname) != "") {
		$groupname = $_REQUEST['groupname'];
	} else {
		$actionStatus = "failure";
		$actionMsg = "no Groupname was entered, please specify a Groupname to delete </b>";
	}	




	include_once('library/config_read.php');
    $log = "visited page: ";


	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
 
<?php
	include ("menu-mng-rad-groupreply.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradgroupreplydel.php'] ?>
				<h144>+</h144></a></h2>

				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngradgroupreplydel'] ?>
					<br/>
				</div>
				<br/>
				
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
                                        <thead>
                                                        <tr>
                                                        <th colspan='2'> <?php echo $l['table']['GroupInfo']; ?> </th>
                                                        </tr>
                                        </thead>
<tr><td>
                                                <?php if (trim($groupname) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b><?php echo$l['FormField']['all']['Groupname'] ?></b>
</td><td>												
                                                <input value="<?php echo $groupname ?>" name="groupname"/><br/>
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($value) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b><?php echo $l['FormField']['all']['Value'] ?></b>
</td><td>												
                                                <input value="<?php echo $value ?>" name="value"/><br/>
												<?php echo $l['FormField']['mngradgroupreplydel.php']['ToolTip']['Value'] ?>
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
