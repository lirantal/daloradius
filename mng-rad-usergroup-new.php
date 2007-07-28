<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];


	// declaring variables
	$username = "";
	$group = "";
	$priority = "";

	if (isset($_POST['submit'])) {
	
		$username = $_POST['username'];
		$group = $_POST['group'];;
		$priority = $_POST['priority'];;

		include 'library/opendb.php';

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." WHERE UserName='$username' AND GroupName='$group'";
		$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

		if (mysql_num_rows($res) == 0) {

			if (trim($username) != "" and trim($group) != "") {

				if (!$priority) {
					$priority = 1;		// default in mysql table for usergroup
				}
				
				// insert usergroup details
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." values ('$username', '$group', $priority)";
				$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
				
				$actionStatus = "success";
				$actionMsg = "Added new User-Group mapping to database: User<b> $username </b> and Group: <b> $group </b> ";
				$logAction = "Successfully added user-group mapping of user [$user] with group [$group] on page: ";
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
	include ("menu-mng-rad-usergroup.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#"><?php echo $l[Intro][mngradusergroupnew.php] ?></a></h2>
				
				<p>

                                <form name="newusergroup" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
<tr><td>
                                                <?php if (trim($username) == "") { echo "<font color='#FF0000'>"; }?>
                                                <b><?php echo $l[FormField][all][Username] ?></b>
</td><td>												
                                                <input value="<?php echo $username ?>" name="username"/>
                                                </font><br/>
</td></tr>
<tr><td>
                                                <?php if (trim($group) == "") { echo "<font color='#FF0000'>";  }?>
	                                        <b><?php echo  $l[FormField][all][Groupname] ?></b>
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
                                                <input value="<?php echo $priority ?>" name="priority" /> (default: 1)
                                                </font>										
</div><br/>
</td></tr>
</table>
                                                <br/><br/>
<center>												
                                                <input type="submit" name="submit" value="<?php echo $l[buttons][apply]?>"/>
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
