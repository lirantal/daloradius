<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	// declaring variables
	//	isset($_GET['profile']) ? $group = $_GET['profile'] : $profile = "";

	$logDebugSQL = "";

	if (isset($_POST['submit'])) {
	
		$profile = $_POST['profile'];
		if ($profile != "") {

		include 'library/opendb.php';

			 foreach($_POST as $element=>$field) { 

                                        switch ($element) {

                                                case "submit":
                                                case "profile":
                                                        $skipLoopFlag = 1; 
							break;
                                        }
                                
                                        if ($skipLoopFlag == 1) {
                                                $skipLoopFlag = 0;             
                                                continue;
					}

                                        if (isset($field[0]))
                                                $attribute = $field[0];
                                        if (isset($field[1]))
                                                $value = $field[1];
                                        if (isset($field[2]))
                                                $op = $field[2];
                                        if (isset($field[3]))
                                                $table = $field[3];

                                        if ($table == 'check')
                                                $table = $configValues['CONFIG_DB_TBL_RADGROUPCHECK'];
                                        if ($table == 'reply')
                                                $table = $configValues['CONFIG_DB_TBL_RADGROUPREPLY'];


                                        if (!($value))
                                                continue;

                                        $sql = "INSERT INTO $table values (0, '".$dbSocket->escapeSimple($profile)."', '".$dbSocket->escapeSimple($attribute)."','".$dbSocket->escapeSimple($op)."', '".$dbSocket->escapeSimple($value)."')  ";

                                        $res = $dbSocket->query($sql);
                                        $logDebugSQL .= $sql . "\n";

				}

                                $actionStatus = "success";
                                $actionMsg = "Added to database new profile: <b> $profile </b>";
                                $logAction = "Successfully added new profile [$profile] on page: ";


		include 'library/closedb.php';

		} else { // if $profile != ""

                                $actionStatus = "failure";
                                $actionMsg = "profile name is empty";
                                $logAction = "Failed adding (possibly empty) profile name [$profile] on page: ";
		}

	} // if isset($submit)
 

/*
		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." WHERE UserName='".$dbSocket->escapeSimple($username)."'
AND GroupName='".$dbSocket->escapeSimple($group)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 0) {

			if (trim($username) != "" and trim($group) != "") {

				if (!isset($priority)) {
					$priority = 1;		// default in mysql table for usergroup
				}
				
				// insert usergroup details
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." values ('".$dbSocket->escapeSimple($username)."',
'".$dbSocket->escapeSimple($group)."', ".$dbSocket->escapeSimple($priority).")";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
				
				$actionStatus = "success";
				$actionMsg = "Added new User-Group mapping to database: User<b> $username </b> and Group: <b> $group </b> ";
				$logAction = "Successfully added user-group mapping of user [$username] with group [$group] on page: ";
			} else {
				$actionStatus = "failure";
				$actionMsg = "no username or groupname was entered, it is required that you specify both username and groupname";
				$logAction = "Failed adding (missing attributes) for user or group on page: ";
			}
		} else {
			$actionStatus = "failure";
			$actionMsg = "The user $username already exists in the user-group mapping database";
			$logAction = "Failed adding already existing user-group mapping for user [$username] with group [$group] on page: ";
		}

		include 'library/closedb.php';
	}

*/


	
        
	include_once('library/config_read.php');
    $log = "visited page: ";


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>

<script src="library/javascript/pages_common.js" type="text/javascript"></script>

<script type="text/javascript" src="library/javascript/ajax.js"></script>
<script type="text/javascript" src="library/javascript/dynamic_attributes.js"></script>

<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" href="css/auto-complete.css" media="screen" type="text/css">
</head>
 
<?php
	include ("menu-mng-rad-profiles.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradprofilesnew.php'] ?>
				<h144>+</h144></a></h2>


				<div id="helpPage" style="display:none;visibility:visible" >				
					<?php echo $l['helpPage']['mngradprofilesnew'] ?>
					<br/>
				</div>
				<br/>
				
                                <form name="newusergroup" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

        <fieldset>

                <h302> <?php echo $l['title']['ProfileInfo'] ?> </h302>
                <br/>

                <label for='profile' class='form'>Profile Name</label>
                <input name='profile' type='text' id='profile' value='' tabindex=100 />
                <br />

                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' class='button' />

        </fieldset>


        <br/>

        <fieldset>

                <h302> <?php echo $l['title']['Attributes']; ?> </h302>
                <br/>

                <label for='vendor' class='form'>Vendor:</label>
                <select id='dictVendors0' onchange="getAttributesList(this,'dictAttributesDatabase')"
                        style='width: 215px' class='form' >
                        <option value=''>Select Vendor...</option>
                        <?php
                                include 'library/opendb.php';

                                $sql = "SELECT distinct(Vendor) as Vendor FROM dictionary WHERE Vendor>'' ".
					" ORDER BY Vendor ASC";
                                $res = $dbSocket->query($sql);

                                while($row = $res->fetchRow()) {
                                        echo "<option value=$row[0]>$row[0]</option>";
                                }

                                include 'library/closedb.php';
                        ?>
                </select>
                <input type='button' name='reloadAttributes' value='Reload Vendors'
                        onclick="javascript:getVendorsList('dictVendors0');" class='button'>
                <br/>

                <label for='attribute' class='form'>
                        Attribute:</label>
                <select id='dictAttributesDatabase' style='width: 270px' class='form' >
                </select>
                <input type='button' name='addAttributes' value='Add Attribute'
                        onclick="javascript:parseAttribute(1);" class='button'>
                <br/>

                <label for='attribute' class='form'>
                        Custom Attribute:</label>
                <input type='text' id='dictAttributesCustom' style='width: 264px' />
                <br/>

<?php

        include_once('library/config_read.php');

        if ( (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE'])) &&
                (strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) == "yes") ) {

                echo "
                        <script type=\"text/javascript\" src=\"library/javascript/dhtmlSuite-common.js\"></script>
                        <script type=\"text/javascript\" src=\"library/javascript/auto-complete.js\"></script>

                        <script type=\"text/javascript\">
                                autoCom = new DHTMLSuite.autoComplete();
                                autoCom.add('dictAttributesCustom','include/management/dynamicAutocomplete.php','_large','getAjaxAutocompleteAttributes');
                        </script>
                ";
        }
?>


	<br/>
        <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' class='button' />

	</fieldset>

	<br/>

        <input type="hidden" value="0" id="divCounter" />
        <div id="divContainer"> </div> <br/>

		
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
