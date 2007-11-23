<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

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

	$logDebugSQL = "";

	if (isset($_POST["submit"])) {
		$name = $_REQUEST['name'];
		$macaddress = $_REQUEST['macaddress'];
		$geocode = $_REQUEST['geocode'];
		
		include 'library/opendb.php';

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." WHERE name='".$dbSocket->escapeSimple($name)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 0) {
			if (trim($name) != "" and trim($macaddress) != "") {

				// insert username/password
				$sql = "insert into ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." values (0, '".$dbSocket->escapeSimple($name)."', 
'".$dbSocket->escapeSimple($macaddress)."', '".$dbSocket->escapeSimple($geocode)."','".$dbSocket->escapeSimple($owner)."',
'".$dbSocket->escapeSimple($email_owner)."','".$dbSocket->escapeSimple($manager)."','".$dbSocket->escapeSimple($email_manager)."',
'".$dbSocket->escapeSimple($address)."','".$dbSocket->escapeSimple($company)."','".$dbSocket->escapeSimple($phone1)."',
'".$dbSocket->escapeSimple($phone2)."','".$dbSocket->escapeSimple($hotspot_type)."','".$dbSocket->escapeSimple($website)."')";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$actionStatus = "success";
				$actionMsg = "Added to database new hotspot: <b> $name";
				$logAction = "Successfully added new hotspot [$name] on page: ";
			} else {
				$actionStatus = "failure";
				$actionMsg = "you must provide atleast a hotspot name and mac-address";	
				$logAction = "Failed adding new hotspot [$name] on page: ";	
			}
		} else { 
			$actionStatus = "failure";
			$actionMsg = "You have tried to add a hotspot that already exist in the database: $name";	
			$logAction = "Failed adding new hotspot already in database [$name] on page: ";		
		}
	
		include 'library/closedb.php';

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

	include ("menu-mng-main.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mnghsnew.php'] ?></a></h2>
				
                                <div id="helpPage" style="display:none;visibility:visible" >				
					<?php echo $l['helpPage']['mnghsnew'] ?>
				</div>

				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="<?php echo $l['table']['HotspotInfo']; ?>">

<table border='2' class='table1'>
                                        <thead>
                                                        <tr>
                                                        <th colspan='2'> <?php echo $l['table']['HotspotInfo']; ?> </th>
                                                        </tr>
                                        </thead>
<tr><td>
						<?php if (trim($name) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l['FormField']['mnghsdel.php']['HotspotName'] ?></b>
</td><td>
						<input value="<?php echo $name ?>" name="name" tabindex=100  /><br/>
						</font>
</td></tr>
<tr><td>
						<?php if (trim($macaddress) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l['FormField']['mnghsedit.php']['MACAddress'] ?></b>
</td><td>
						<input value="<?php echo $macaddress ?>" name="macaddress" tabindex=101 /><br/>
						</font>
</td></tr>
<tr><td>
						<b><?php echo $l['FormField']['mnghsedit.php']['Geocode'] ?></b>
</td><td>
						<input value="<?php echo $geocode ?>" name="geocode" tabindex=102 /><br/>
</td></tr>
</table>


	</div>



     <div class="tabbertab" title="<?php echo $l['table']['ContactInfo']; ?>">

<?php
        include_once('include/management/contactinfo.php');
?>

	</div>

</div>




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





