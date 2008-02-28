<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	$logDebugSQL = "";

	isset($_GET['vendor']) ? $vendor = $$_GET['vendor'] : $vendor = "";
	isset($_GET['attribute']) ? $attribute = $$_GET['attribute'] : $attribute = "";

	if (isset($_POST['vendor'])) {

		if (is_array($_POST['vendor'])) {
			$vendor_array = $_POST['vendor'];
		} else {
			$vendor_array = array($_POST['vendor']."||".$_POST['attribute']);
		}

		foreach ($vendor_array as $vendor_attribute) {

	                list($vendor, $attribute) = split('\|\|', $vendor_attribute);

	                if ( (trim($vendor) != "") && (trim($attribute) != "") ) {

	                        $allVendors =  "";
	                        $allAttributes = "";
	                        include 'library/opendb.php';

				include 'library/opendb.php';

				$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALODICTIONARY']." WHERE vendor='".$dbSocket->escapeSimple($vendor).
					"' AND attribute='".$dbSocket->escapeSimple($attribute)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				if ($res->numRows() == 1) {
					if (trim($vendor) != "" and trim($attribute) != "") {
						// remove vendor/attribute pairs from database
						$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALODICTIONARY']." WHERE vendor='".
							$dbSocket->escapeSimple($vendor)."' AND attribute='".$dbSocket->escapeSimple($attribute)."'";
						$res = $dbSocket->query($sql);
						$logDebugSQL .= $sql . "\n";

						$actionStatus = "success";
						$actionMsg = "Removed from database vendor attribute: <b>$attribute</b> of vendor: <b>$vendor</b>";
						$logAction = "Successfully removed vendor [$vendor] and attribute [$attribute] from database on page: ";
					} else {
						$actionStatus = "failure";
						$actionMsg = "you must provide atleast a vendor name and attribute";	
						$logAction = "Failed removing vendor [$vendor] and attribute [$attribute] from database on page: ";
					}
				} else { 
					$actionStatus = "failure";
					$actionMsg = "You have tried to remove a vendor's attribute that either is not present in the database or there
							may be more than 1 entry for this vendor attribute in database (attribute :$attribute)";
					$logAction = "Failed removing vendor attribute already in database [$attribute] on page: ";		
				} //if ($res->numRows() == 1)
			
				include 'library/closedb.php';
		
			} // if (trim...

		} //foreach

	} //if (isset)


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

	include ("menu-mng-rad-attributes.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradattributesdel.php'] ?>
				<h144>+</h144></a></h2>
				
				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngradattributesdel'] ?>
					<br/>
				</div>
				<br/>

				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

	<fieldset>

		<h302> <?php echo $l['title']['VendorAttribute']; ?> </h302>
		<br/>

		<ul>

                <li class='fieldset'>
		<label for='vendor' class='form'><?php echo $l['all']['VendorName'] ?></label>
		<input name='vendor' type='text' id='vendor' value='<?php if (isset($vendor)) echo $vendor ?>' tabindex=100
                        onfocus="javascript:toggleShowDiv('vendorNameTooltip')"
                        onblur="javascript:toggleShowDiv('vendorNameTooltip')" />
                <div id='vendorNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/error.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['vendorNameTooltip'] ?>
                </div>
		</li>

                <li class='fieldset'>
		<label for='attribute' class='form'><?php echo $l['all']['Attribute'] ?></label>
		<input name='attribute' type='text' id='attribute' value='<?php if (isset($attribute)) echo $attribute ?>' tabindex=101
                        onfocus="javascript:toggleShowDiv('attributeTooltip')"
                        onblur="javascript:toggleShowDiv('attributeTooltip')" />
                <div id='attributeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/error.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['attributeTooltip'] ?>
                </div>
		</li>

	
                <li class='fieldset'>
                <br/>
                <hr><br/>
                <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' tabindex=10000
			class='button' />
		</li>

		</ul>
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





