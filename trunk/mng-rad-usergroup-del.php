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

	                        // delete all attributes associated with a username
	                        $sql = "DELETE FROM usergroup WHERE UserName='$username' AND GroupName='$group'";
	                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	
	                        echo "<font color='#0000FF'>success<br/></font>";
	                        include 'library/closedb.php';

			} else {

	                        // delete all attributes associated with a username
	                        $sql = "DELETE FROM usergroup WHERE UserName='$username'";
	                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	
	                        echo "<font color='#0000FF'>success<br/></font>";
	                        include 'library/closedb.php';
			}

                }  else {
                        echo "<font color='#FF0000'>error: user $username, please specify a username to remove from database<br/></font>";
                        echo "
                                <script language='JavaScript'>
                                <!--
                                alert('No user was entered, please specify a username to remove from database');
                                -->
                                </script>
                                ";
                }
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>

<SCRIPT TYPE="text/javascript">
<!--
function toggleShowDiv(pass) {

        var divs = document.getElementsByTagName('div');
        for(i=0;i<divs.length;i++) {
                if (divs[i].id.match(pass)) {
                        if (document.getElementById) {                                         
                                if (divs[i].style.display=="inline")
                                        divs[i].style.display="none";
                                else
                                        divs[i].style.display="inline";
                        } else if (document.layers) {                                           
                                if (document.layers[divs[i]].display=='visible')
                                        document.layers[divs[i]].display = 'hidden';
                                else
                                        document.layers[divs[i]].display = 'visible';
                        } else {
                                if (document.all.hideShow.divs[i].visibility=='visible')     
                                        document.all.hideShow.divs[i].visibility = 'hidden';
                                else
                                        document.all.hideShow.divs[i].visibility = 'visible';
                        }
                }
        }
}



// -->
</script>


<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
 
<?php
	include ("menu-mng-rad-usergroup.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Remove User-Group Mapping</a></h2>
				
                                <p>
                                To remove a user entry from the database you must provide the username of the account.
                                <br/><br/>
                                </p>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
<tr><td>
                                                <input type="hidden" value="<?php echo $group ?>" name="group"/><br/>

                                                <?php if (trim($username) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Username</b>
</td><td>													
                                                <input value="<?php echo $username ?>" name="username"/><br/>
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($group) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Groupname</b>
</td><td>												
                                                <input value="<?php echo $group ?>" name="group"/><br/>
						If you specify group then only the single record that matches both the username and the group which you have specified will be removed. If you omit the group then all records for that particular user will be removed!
                                                </font>
</td></tr>
</table>

                                                <br/><br/>
<center>												
                                                <input type="submit" name="submit" value="Apply"/>
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
