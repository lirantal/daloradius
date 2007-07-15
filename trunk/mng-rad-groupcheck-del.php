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
	                        $sql = "DELETE FROM radgroupcheck WHERE GroupName='$groupname' AND Value='$value'";
	                        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	
	                        echo "<font color='#0000FF'>success<br/></font>";
	                        include 'library/closedb.php';

			} else {

	                        // delete all attributes associated with a username
	                        $sql = "DELETE FROM radgroupcheck WHERE GroupName='$groupname'";
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
	include ("menu-mng-rad-groupcheck.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Remove Group Check Mapping</a></h2>
				
                                <p>
                                To remove a group entry from the database you must provide the groupname of the account.
                                <br/><br/>
                                </p>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
<tr><td>
                                                <?php if (trim($groupname) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Groupname</b>
</td><td>												
                                                <input value="<?php echo $groupname ?>" name="groupname"/><br/>
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($value) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Value</b>
</td><td>												
                                                <input value="<?php echo $value ?>" name="value"/><br/>
						If you specify value then only the single record that matches both the groupname and the specific value which you have specified will be removed. If you omit the value then all records for that particular groupname will be removed!
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
