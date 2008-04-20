<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include('library/check_operator_perm.php');

	// declaring variables
	$groupname = "";

	$logDebugSQL = "";
	$allValues = "";
	$allAttributes = "";

    if (isset($_POST['submit'])) {
	
        
        include 'library/opendb.php';	

	$groupname = $_REQUEST['groupname'];

	if ($groupname) {
		
		$counter = 0;
		foreach ($_POST as $element=>$field) {

                                        // switch case to rise the flag for several $attribute which we do not
                                        // wish to process (ie: do any sql related stuff in the db)
                                        switch ($element) {

                                                case "groupname":
                                                case "submit":
                                                        $skipLoopFlag = 1;      // if any of the cases above has been met we set a flag
                                                                                // to skip the loop (continue) without entering it as
                                                                                // we do not want to process this $attribute in the following
                                                                                // code block
                                                        break;

                                        }

                                        if ($skipLoopFlag == 1) {
                                                $skipLoopFlag = 0;              // resetting the loop flag
                                                continue;
                                        }



                                        if (isset($field[0]))
                                                $attribute = $field[0];
                                        if (isset($field[1]))
                                                $value = $field[1];
                                        if (isset($field[2]))
                                                $op = $field[2];

					// we explicitly set the table target to be radgroupreply
                                        $table = $configValues['CONFIG_DB_TBL_RADGROUPREPLY'];

                                        if (!($value))
                                                continue;	// we don't process empty values attributes


			$allValues .= $value . "\n";
			$allAttributes .= $attribute . "\n";

					
			$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY']." WHERE GroupName='".$dbSocket->escapeSimple($groupname)."'
AND Attribute='".$dbSocket->escapeSimple($attribute)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";
				
			if ($res->numRows() == 0) {
					// insert radgroupreply details
					// assuming there's no groupname with that attribute in the table
					$sql = "INSERT INTO $table VALUES (0,'".$dbSocket->escapeSimple($groupname)."',
'".$dbSocket->escapeSimple($attribute)."', '".$dbSocket->escapeSimple($op)."', '".$dbSocket->escapeSimple($value)."')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
					$counter++;
					
					$actionStatus = "success";
					$actionMsg = "Added to database new group: <b> $groupname </b> with attribute(s): <b> $allAttributes </b> and value(s): <b> $allValues </b>";
					$logAction = "Successfully added group [$groupname] with attribute(s): <b> $allAttributes </b> and value(s): <b> $allValues </b> on page: ";
			} else { 
				$actionStatus = "failure";
				$actionMsg = "The group <b> $groupname </b> already exist in the database with attribute(s) <b> $allAttributes </b>";
				$logAction = "Failed adding already existing group [$groupname] with attribute(s) [$allAttributes] on page: ";

		        } // end else if mysql


				
		    }

   		} else { // if groupname isset
			$actionStatus = "failure";
			$actionMsg = "No groupname was defined";
			$logAction = "Failed adding missing values for groupname on page: ";
   		}


        include 'library/closedb.php';
	
	}


        isset($groupname) ? $groupname = $groupname : $groupname = "";

	include_once('library/config_read.php');
    $log = "visited page: ";


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>

<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" href="css/auto-complete.css" media="screen" type="text/css">
<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->
</head>

<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>

<script type="text/javascript" src="library/javascript/ajax.js"></script>
<script type="text/javascript" src="library/javascript/dynamic_attributes.js"></script>

</head>
 
<?php
	include ("menu-mng-rad-groups.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradgroupreplynew.php'] ?>
				<h144>+</h144></a></h2>

				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngradgroupreplynew'] ?>
					<br/>
				</div>
				<br/>

                                <form name="newgroupreply" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">


        <fieldset>

                <h302> <?php echo $l['title']['GroupInfo'] ?> </h302>
                <br/>

                <label for='groupname' class='form'><?php echo $l['all']['Groupname'] ?></label>
                <input name='groupname' type='text' id='groupname' value='<?php echo $groupname ?>' tabindex=100 />
                <br />

                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' class='button' />

        </fieldset>

	<br/>

        <fieldset>

                <h302> <?php echo $l['title']['GroupAttributes'] ?> </h302>
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
