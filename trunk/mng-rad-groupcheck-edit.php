<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        

	
    include 'library/opendb.php';

    // declaring variables
    $groupname = "";
    $value = "";
    $op = "";
    $attribute = "";

	$groupname = $_REQUEST['groupname'];
	$value = $_REQUEST['value'];
	$valueOld = $_REQUEST['value'];	

	// fill-in nashost details in html textboxes
	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK']." WHERE GroupName='$groupname' AND Value='$value'";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
	$row = mysql_fetch_array($res);		// array fetched with values from $sql query
		$op = $row['op'];
		$attribute = $row['Attribute'];
	

	if (isset($_POST['submit'])) {
		$groupname = $_REQUEST['groupname'];
		$value = $_REQUEST['value'];;
		$valueOld = $_REQUEST['valueOld'];;			
		$op = $_REQUEST['op'];;
		$attribute = $_REQUEST['attribute'];;

		include 'library/opendb.php';

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK']." WHERE GroupName='$groupname' AND Value='$valueOld'";
		$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

		if (mysql_num_rows($res) == 1) {

			if (trim($groupname) != "" and trim($value) != "" and trim($op) != "" and trim($attribute) != "") {

				$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK']." SET Value='$value', op='$op', Attribute='$attribute' WHERE GroupName='$groupname' AND Value='$valueOld'";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
			
			$actionStatus = "success";
			$actionMsg = "Updated group attributes for: <b> $groupname </b>";
			$logAction = "Successfully updated attributes for group [$groupname] on page: ";

			} else { // if groupname  != ""
				$actionStatus = "failure";
				$actionMsg = "you are missing possible values for Groupname, Attribute, Operator or Value";	
				$logAction = "Failed updating (possible missing attributes) attributes for group [$groupname] on page: ";
			}

		} else {
			$actionStatus = "failure";
			$actionMsg = "The group <b> $groupname </b> already exists in the database with value <b> $value </b>
			<br/> Please check that there are no duplicate entries in the database";
			$logAction = "Failed updating already existing group [$groupname] with value [$value] on page: ";
		} 

		include 'library/closedb.php';
		
	}

	if (isset($_REQUEST['groupname']))
		$groupname = $_REQUEST['groupname'];
	else
		$groupname = "";

	if (trim($groupname) != "") {
		$groupname = $_REQUEST['groupname'];
	} else {
		$actionStatus = "failure";
		$actionMsg = "no Groupname was entered, please specify a Groupname to edit </b>";
	}	




	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');
	
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
	include ("menu-mng-rad-groupcheck.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#"><?php echo $l[Intro][mngradgroupcheckedit.php] ?> <?php echo $groupname ?></a></h2>
				
				<p>

                                <form name="newuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

                                                <input type="hidden" value="<?php echo $groupname ?>" name="groupname" /><br/>
                                                <input type="hidden" value="<?php echo $valueOld ?>" name="valueOld" /><br/>
												
<table border='2' class='table1'>
<tr><td>
                                                <?php if (trim($attribute) == "") { echo "<font color='#FF0000'>";  }?>
	                                        <b><?php echo $l[FormField][all][Attribute] ?></b>
</td><td>											
                                                <input value="<?php echo $attribute ?>" name="attribute" />
                                                </font><br/>
</td></tr>
<tr><td>
                                                <?php if (trim($op) == "") { echo "<font color='#FF0000'>";  }?>
	                                        <b><?php echo $l[FormField][all][Operator] ?></b>
</td><td>											
											<select name="op" />
													<option value="<?php echo $op ?>"><?php echo $op ?></option>
				<?php include ('include/management/op_select_options.php');
					  drawOptions();
					  ?>
												</select>                                                
												</font><br/>
</td></tr>
<tr><td>												
                                                <?php if (trim($valueOld) == "") { echo "<font color='#FF0000'>";  }?>
	                                        <b><?php echo $l[FormField][all][NewValue] ?></b>
</td><td>											
                                                <input value="<?php echo $value ?>" name="value" />
                                                </font><br/>
</td></tr>
</table>

<center>
                                                <input type="submit" name="submit" value="<?php echo $l[buttons][apply] ?>"/>
</center>

                                </form>



				</p>
				
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
