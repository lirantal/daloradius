<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        


	$group = "";
	$username = "";

	if (isset($_REQUEST['username'])) {
		$username = $_REQUEST['username'];
	}

	if (isset($_REQUEST['group'])) {
		$group = $_REQUEST['group'];
 	}

	if (isset($_POST['submit'])) {

		if (trim($username) != "") {
				
			include 'library/opendb.php';

			if (trim($group) != "") {

				// // delete only a specific groupname and it's attribute
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." WHERE UserName='$username' AND GroupName='$group'";
				$res = $dbSocket->query($sql);

				$actionStatus = "success";
				$actionMsg = "Deleted Username: <b> $username </b> and it's Groupname: <b> $group </b>";
				$logAction = "Successfully deleted user [$username] and it's group [$group] on page: ";

				include 'library/closedb.php';
							
			} else {

				// delete all attributes associated with a username
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." WHERE UserName='$username'";
				$res = $dbSocket->query($sql);

				$actionStatus = "success";
				$actionMsg = "Deleted all instances for Username: <b> $username </b>";
				$logAction = "Successfully deleted all group instances for user [$username] on page: ";

				include 'library/closedb.php';
			}

		}  else {
			$actionStatus = "failure";
			$actionMsg = "No user was entered, please specify a username to remove from database";
			$logAction = "Failed deleting empty user on page: ";
		}
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
		
				<h2 id="Intro"><a href="#"><?php echo $l[Intro][mngradusergroupdel.php] ?></a></h2>
				
                                <p>
                                <?php echo $l[captions][mngradusergroupdel] ?>
                                <br/><br/>
                                </p>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
<tr><td>
                                                <input type="hidden" value="<?php echo $group ?>" name="group"/><br/>

                                                <?php if (trim($username) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b><?php echo $l[FormField][all][Username] ?></b>
</td><td>													
                                                <input value="<?php echo $username ?>" name="username"/><br/>
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($group) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b><?php echo $l[FormField][all][Groupname]?></b>
</td><td>												
                                                <input value="<?php echo $group ?>" name="group"/><br/>
												<?php echo $l[FormField][mngradusergroupdel.php][ToolTip][Groupname] ?>
                                                </font>
</td></tr>
</table>

                                                <br/><br/>
<center>												
                                                <input type="submit" name="submit" value="<?php echo $l[buttons][apply] ?>"/>
</center>
                                </form>
				
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
