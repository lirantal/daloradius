<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');



	include 'library/opendb.php';

	// declaring variables
	$username = "";
	$group = "";
	$groupOld = "";
	$priority = "";

	$username = $_REQUEST['username'];
	$groupOld = $_REQUEST['group'];

	// fill-in nashost details in html textboxes
	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." WHERE UserName='$username' AND GroupName='$groupOld'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";
	$row = $res->fetchRow();		// array fetched with values from $sql query

					// assignment of values from query to local variables
					// to be later used in html to display on textboxes (input)
					
	$priority = $row[2];

	if (isset($_POST['submit'])) {
		$username = $_REQUEST['username'];
		$groupOld = $_REQUEST['groupOld'];;
		$group = $_REQUEST['group'];;
		$priority = $_REQUEST['priority'];;

		
		include 'library/opendb.php';

	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." WHERE UserName='$username' AND GroupName='$groupOld'";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 1) {

			if (trim($username) != "" and trim($group) != "") {

				if (!$priority) {
					$priority = 1;
				}

				// insert nas details
				$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." SET GroupName='$group', priority='$priority' WHERE UserName='$username' AND GroupName='$groupOld'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
						
				$actionStatus = "success";
				$actionMsg = "Updated User-Group mapping in database: User<b> $username </b> and Group: <b> $group </b> ";
				$logAction = "Successfully updated attributes for user-group mapping of user [$username] with group [$group] on page: ";
			} else {
				$actionStatus = "failure";
				$actionMsg = "no username or groupname was entered, it is required that you specify both username and groupname";
				$logAction = "Failed updating (missing attributes) attributes on page: ";
			}
		} else {
			$actionStatus = "failure";
			$actionMsg = "The user $username already exists in the user-group mapping database
			<br/> It seems that you have duplicate entries for User-Group mapping. Check your database";
			$logAction = "Failed updating already existing user [$username] with group [$group] on page: ";
		} 

		include 'library/closedb.php';
	}
	
	if (isset($_REQUEST['username']))
		$username = $_REQUEST['username'];
	else
		$username = "";

	if (isset($_REQUEST['group']))
		$group = $_REQUEST['group'];
	else
		$group = "";
		
	if (trim($username) == "" OR trim($group) == "") {
		$actionStatus = "failure";
		$actionMsg = "no username or groupname was entered, please specify a username and groupname to edit ";
	}	



        s
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
	include ("menu-mng-rad-usergroup.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#"><?php echo $l[Intro][mngradusergroupedit] ?> <?php echo $username ?></a></h2>
				
				<p>

                                <form name="newuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

                                                <input type="hidden" value="<?php echo $username ?>" name="username" /><br/>
<table border='2' class='table1'>
<tr><td>
                                                <?php if (trim($groupOld) == "") { echo "<font color='#FF0000'>";  }?>
	                                        <b><?php echo $l[FormField][mngradusergroupedit.php][CurrentGroupname] ?></b>
</td><td>											
                                                <input value="<?php echo $groupOld ?>" name="groupOld" /> (Old Group Name)
                                                </font><br/>
</td></tr>
<tr><td>
                                                <?php if (trim($group) == "") { echo "<font color='#FF0000'>";  }?>
	                                        <b><?php echo $l[FormField][mngradusergroupedit.php][NewGroupname] ?></b>
</td><td>											
                                                <input value="<?php echo $group ?>" name="group" /> 
                                                </font><br/>
</td></tr>
</table>

        <br/>
		<center>
        <h4> Advnaced User-Group Attributes </h4>
		</center>

<table border='2' class='table1' width='600'>
<tr><td>
                                                <?php if (trim($priority) == "") { echo "<font color='#FF0000'>";  }?>
			<input type="checkbox" onclick="javascript:toggleShowDiv('attributesPriority')">
                                                <b><?php echo $l[FormField][all][Priority] ?></b>
</td><td>
<div id="attributesPriority" style="display:none;visibility:visible" >
						<br/>
                                                <input value="<?php echo $priority ?>" name="priority" />
                                                </font>
</div><br/>
</td></tr>
</table>

<center>
                                                <input type="submit" name="submit" value="<?php echo $l[buttons][apply] ?>"/>
</center>
                                </form>



				</p>

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
