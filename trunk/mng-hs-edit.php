<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	include 'library/opendb.php';

        isset($_REQUEST['name']) ? $name = $_REQUEST['name'] : $name = "";
        isset($_REQUEST['macaddress']) ? $macaddress = $_REQUEST['macaddress'] : $macaddress = "";
        isset($_REQUEST['geocode']) ? $geocode = $_REQUEST['geocode'] : $geocode = "";
        isset($_REQUEST['owner']) ? $owner = $_REQUEST['owner'] : $owner = "";
        isset($_REQUEST['email_owner']) ? $email_owner = $_REQUEST['email_owner'] : $email_owner = "";
        isset($_REQUEST['manager']) ? $manager = $_REQUEST['manager'] : $manager = "";
        isset($_REQUEST['email_manager']) ? $email_manager = $_REQUEST['email_manager'] : $email_manager = "";
        isset($_REQUEST['address']) ? $address = $_REQUEST['address'] : $address = "";
        isset($_REQUEST['company']) ? $company = $_REQUEST['company'] : $company = "";
        isset($_REQUEST['phone1']) ? $phone1 = $_REQUEST['phone1'] : $phone1 = "";
        isset($_REQUEST['phone2']) ? $phone2 = $_REQUEST['phone2'] : $phone2 = "";
        isset($_REQUEST['hotspot_type']) ? $hotspot_type = $_REQUEST['hotspot_type'] : $hotspot_type = "";
        isset($_REQUEST['website']) ? $website = $_REQUEST['website'] : $website = "";

	$edit_hotspotname = $name; //feed the sidebar variables	

	$logDebugSQL = "";

	if (isset($_REQUEST['submit'])) {

		$name = $_REQUEST['name'];
		$macaddress = $_REQUEST['macaddress'];
		$geocode = $_REQUEST['geocode'];

		if (trim($name) != "") {

			$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." SET mac='".$dbSocket->escapeSimple($macaddress)."', 
geocode='".$dbSocket->escapeSimple($geocode)."', owner='".$dbSocket->escapeSimple($owner)."', 
email_owner='".$dbSocket->escapeSimple($email_owner)."', manager='".$dbSocket->escapeSimple($manager)."', 
email_manager='".$dbSocket->escapeSimple($email_manager)."', address='".$dbSocket->escapeSimple($address)."', 
company='".$dbSocket->escapeSimple($company)."', phone1='".$dbSocket->escapeSimple($phone1)."', phone2='".$dbSocket->escapeSimple($phone2)."', 
type='".$dbSocket->escapeSimple($hotspot_type)."', website='".$dbSocket->escapeSimple($website)."' 
WHERE name='".$dbSocket->escapeSimple($name)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL = "";
			$logDebugSQL .= $sql . "\n";
			
			$actionStatus = "success";
			$actionMsg = "Updated attributes for: <b> $name </b>";
			$logAction = "Successfully updates attributes for hotspot [$name] on page: ";
			
		} else {
			$actionStatus = "failure";
			$actionMsg = "no hotspot name was entered, please specify a hotspot name to edit";
			$logAction = "Failed updating attributes for hotspot [$name] on page: ";
		}
		
	}
	

	// fill-in username and password in the textboxes
	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." WHERE name='".$dbSocket->escapeSimple($name)."'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	$row = $res->fetchRow();
	$macaddress = $row[2];
	$geocode = $row[3];
	$owner = $row[4];
	$email_owner = $row[5];
	$manager = $row[6];
	$email_manager = $row[7];
	$address = $row[8];
	$company = $row[9];
	$phone1 = $row[10];
	$phone2 = $row[11];
	$hotspot_type = $row[12];
	$website = $row[13];

	include 'library/closedb.php';


	if (trim($name) == "") {
		$actionStatus = "failure";
		$actionMsg = "no hotspot name was entered, please specify a hotspot name to edit</b>";
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
        include_once ("library/tabber/tab-layout.php");
?>
 
<?php

	include ("menu-mng-hs.php");
	
?>		
		<div id="contentnorightbar">
		
				<h2 id="Intro" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mnghsedit.php'] ?>
				<h144>+</h144></a></h2>
				
				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mnghsedit'] ?>
					<br/>
				</div>
				<br/>

				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="<?php echo $l['title']['HotspotInfo']; ?>">


        <fieldset>

                <h302> <?php echo $l['title']['HotspotInfo']; ?> </h302>
		<br/>

                <label for='name' class='form'><?php echo $l['all']['HotSpotName'] ?></label>
                <input disabled name='name' type='text' id='name' value='<?php echo $name ?>' tabindex=100 />
                <br/>

                <label for='macaddress' class='form'><?php echo $l['all']['MACAddress'] ?></label>
                <input name='macaddress' type='text' id='macaddress' value='<?php echo $macaddress ?>' tabindex=101 />
                <br/>

                <label for='geocode' class='form'><?php echo $l['all']['Geocode'] ?></label>
                <input name='geocode' type='text' id='geocode' value='<?php echo $geocode ?>' tabindex=102 />
                <br/>

                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' tabindex=10000
                        class='button' />

        </fieldset>

					<input type=hidden value="<?php echo $name ?>" name="name"/>

</div>

<div class="tabbertab" title="<?php echo $l['title']['ContactInfo']; ?>">

<?php
        include_once('include/management/contactinfo.php');
?>

        </div>

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





