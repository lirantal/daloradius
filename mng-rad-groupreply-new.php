<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include('library/check_operator_perm.php');

	// declaring variables
	$groupname = "";
	$op = "";
	$attribute = "";
	$value = "";	
	$logDebugSQL = "";
	

    if (isset($_POST['submit'])) {
	
        
        include 'library/opendb.php';	
	
	    $groupname = $_REQUEST['groupname'];
	    $op = $_REQUEST['op'];
	    $attribute = $_REQUEST['attribute'];
		$value = $_REQUEST['value'];

		$counter = 0;
		foreach ($groupname as $group) {

			if ($group == "")
					continue;
					
			// echo "$group $attribute[$counter] $op[$counter] $value[$counter] <br/> "; 	// for debugging purposes
				
			$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY']." WHERE GroupName='$group' AND Value='$value[$counter]'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";
				
			if ($res->numRows() == 0) {
				if (trim($group) != "" and trim($value[$counter]) != "" and trim($op[$counter]) != "" and trim($attribute[$counter]) != "") {								
					// insert usergroup details
					$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY']." values (0,'$group', '$attribute[$counter]', '$op[$counter]', '$value[$counter]')";
					$res = $dbSocket->query($sql);
					$logDebugSQL .= $sql . "\n";
					$counter++;
					
					$actionStatus = "success";
					$actionMsg = "Added to database new group: <b> $group </b>";
					$logAction = "Successfully added group [$group] on page: ";
				} else {
					$actionStatus = "failure";
					$actionMsg = "you are missing possible values for Groupname, Attribute, Operator or Value";	
					$logAction = "Failed adding (missing values) group [$group] on page: ";
				}
			} else { 
				$actionStatus = "failure";
				$actionMsg = "The group <b> $groupname[$counter] </b> already exists in the database with value <b> $value[$counter] </b>";
				$logAction = "Failed adding already existing group [$group] with value [$value[$counter]] on page: ";

            } // end else if mysql
				
		}

		if (isset($_REQUEST['groupnameExtra']))
			$groupnameExtra = $_REQUEST['groupnameExtra'];
		else
			$groupnameExtra = "";
		
		if (isset($_REQUEST['opExtra']))
			$opExtra = $_REQUEST['opExtra'];
		else
			$opExtra = "";
			
		if (isset($_REQUEST['attributeExtra']))
			$attributeExtra = $_REQUEST['attributeExtra'];
		else
			$attributeExtra = "";
			
		if (isset($_REQUEST['valueExtra']))
			$valueExtra = $_REQUEST['valueExtra'];
		else
			$valueExtra = "";
			
		if ($groupnameExtra) {
		
			$counter = 0;
		
			foreach ($groupnameExtra as $groupExtra) {
			
				if ($groupExtra == "")
					continue; 

				// echo "$group $attribute[$counter] $op[$counter] $value[$counter] <br/> "; 	// for debugging purposes
					
				$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY']." WHERE GroupName='$groupExtra' AND Value='$valueExtra[$counter]'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
					
				if ($res->numRows() == 0) {
					if (trim($groupExtra) != "" and trim($valueExtra[$counter]) != "" and trim($opExtra[$counter]) != "" and trim($attributeExtra[$counter]) != "") {								
						// insert usergroup details
						$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY']." values (0,'$groupExtra', '$attributeExtra[$counter]', '$opExtra[$counter]', '$valueExtra[$counter]')";
						$res = $dbSocket->query($sql);
						$logDebugSQL .= $sql . "\n";
						$counter++;
					} // end if trim
					
					$actionStatus = "success";
					$actionMsg = "Added to database new group: <b> $groupExtra </b>";
					$logAction = "Successfully added group [$groupExtra] on page: ";
					
				} else { 
					$actionStatus = "failure";
					$actionMsg = "The group <b> $groupnameExtra[$counter] </b> already exists in the database with value <b> $valueExtra[$counter] </b>";
					$logAction = "Failed addin already existing group [$groupnameExtra[$counter]] with value [$valueExtra[$counter]] on page: ";
				}
		
			}	

        include 'library/closedb.php';
		
    }
	
	}



        isset($groupname[0]) ? $groupname = $groupname[0] : $groupname = "";
        isset($attribute[0]) ? $attribute = $attribute[0] : $attribute = "";
        isset($value[0]) ? $value = $value[0] : $value = "";
        isset($op[0]) ? $op = $op[0] : $op = "";


	include_once('library/config_read.php');
    $log = "visited page: ";


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script src="library/javascript/dynamicadd_groups.js" type="text/javascript"></script>

<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
<?php
	include ("menu-mng-rad-groupreply.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#"><?php echo $l['Intro']['mngradgroupreplynew.php'] ?></a></h2>

                                <form name="newgroupreply" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<table border='2' class='table1'>
                                        <thead>
                                                        <tr>
                                                        <th colspan='2'> <?php echo $l['table']['GroupInfo'];?> </th>
                                                        </tr>
                                        </thead>
<tr><td>								
                                                <?php if (trim($groupname) == "") { echo "<font color='#FF0000'>"; }?>
                                                <b><?php echo $l['FormField']['all']['Groupname'] ?></b>
</td><td>										
                                                <input value="<?php echo $groupname ?>" name="groupname[]"/>
                                                </font><br/>
</td></tr>
<tr><td>
                                                <?php if (trim($attribute) == "") { echo "<font color='#FF0000'>";  }?>
						<b><?php echo $l['FormField']['all']['Attribute'] ?></b>
</td><td>												
                                                <input value="<?php echo $attribute ?>" name="attribute[]" /> 
                                                </font><br/>
</td></tr>
<tr><td>
												
                                                <?php if (trim($op) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b><?php echo $l['FormField']['all']['Operator'] ?></b>
</td><td>																								
												<select name="op[]" />
				<?php include ('include/management/op_select_options.php');
					  drawOptions();
					  ?>
												</select>
                                                </font><br/>
</td></tr>
<tr><td>
                                                <?php if (trim($value) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b><?php echo $l['FormField']['all']['Value'] ?></b>
</td><td>												
                                                <input value="<?php echo $value ?>" name="value[]" />
                                                </font><br/>
</td></tr>
</table>

<br/>
<center>
<input type="button" value="Add Groups" onclick="addStuff()"/>
</center>
<br/><br/>

<div id="mydiv">
</div>

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
