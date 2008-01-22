<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include('library/check_operator_perm.php');

	$groupname = "";
	$value = "";
	$logDebugSQL = "";

	isset($_REQUEST['groupname']) ? $groupname = $_REQUEST['groupname'] : $groupname = "";
	isset($_REQUEST['attribute']) ? $attribute = $_REQUEST['attribute'] : $attribute = "";
	isset($_REQUEST['value']) ? $value = $_REQUEST['value'] : $value = "";
 	
	if (isset($_POST['submit'])) {
		if (trim($groupname) != "") {
			
			include 'library/opendb.php';

			if ( (trim($value) != "") && (trim($attribute) != "") ) {

                // delete only a specific groupname and it's attribute
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY']." 
WHERE GroupName='".$dbSocket->escapeSimple($groupname)."'AND Value='$value' AND Attribute='$attribute'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$actionStatus = "success";
				$actionMsg = "Deleted Group: <b> $groupname </b> with Attribute: <b> $attribute </b> and it's Value: <b> $value </b>";
				$logAction = "Successfully deleted group [$groupname] with attribute [$attribute] and it's value [$value] on page: ";

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
	include ("menu-mng-rad-groups.php");
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


        <fieldset>

                <h302> <?php echo $l['title']['GroupInfo'] ?> </h302>
                <br/>

                <label for='groupname' class='form'><?php echo $l['all']['Groupname'] ?></label>
                <input name='groupname' type='text' id='groupname' value='<?php echo $groupname ?>' tabindex=100 />
                <br/>

                <label for='value' class='form'><?php echo $l['all']['Value'] ?></label>
                <input name='value' type='text' id='value' value='<?php echo $value ?>' tabindex=101 />
                <br/>

                <label for='attribute' class='form'><?php echo $l['all']['Attribute'] ?></label>
                <input name='attribute' type='text' id='attribute' value='<?php echo $attribute ?>' tabindex=102 />
                <br/>

                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' class='button' />

        </fieldset>


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
