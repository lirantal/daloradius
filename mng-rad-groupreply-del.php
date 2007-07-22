<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];


	$groupname = "";
	$value = "";

	if (isset($_REQUEST['groupname'])) {
		$groupname = $_REQUEST['groupname'];
	}

	if (isset($_REQUEST['value'])) {
		$value = $_REQUEST['value'];
 	}

	if (isset($_POST['submit'])) {
		if (trim($groupname) != "") {
			
			include 'library/opendb.php';

			if (trim($value) != "") {

				// delete all attributes associated with a username
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY']." WHERE GroupName='$groupname' AND Value='$value'";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

				echo "<font color='#0000FF'>success<br/></font>";
				include 'library/closedb.php';

			} else {

				// delete all attributes associated with a username
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY']." WHERE GroupName='$groupname'";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

				echo "<font color='#0000FF'>success<br/></font>";
				include 'library/closedb.php';
			}

		}  else {
			echo "<font color='#FF0000'>error: please specify a groupname to remove from database<br/></font>";
			echo "
				<script language='JavaScript'>
				<!--
				alert('No groupname was entered, please specify a groupname to remove from database');
				-->
				</script>
				";
		}
	}	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
 
<?php
	include ("menu-mng-rad-groupreply.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#"><?php echo $l[Intro][mngradgroupreplydel.php] ?></a></h2>
				
                                <p>
                                <?php echo $l[captions][mngradgroupreplydel] ?>
                                <br/><br/>
                                </p>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
<tr><td>
                                                <?php if (trim($groupname) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b><?php echo$l[FormField][all][Groupname] ?></b>
</td><td>												
                                                <input value="<?php echo $groupname ?>" name="groupname"/><br/>
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($value) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b><?php echo $l[FormField][all][Value] ?></b>
</td><td>												
                                                <input value="<?php echo $value ?>" name="value"/><br/>
												<?php echo $l[FormField][mngradgroupreplydel.php][ToolTip][Value] ?>
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
