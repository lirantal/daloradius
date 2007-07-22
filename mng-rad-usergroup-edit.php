<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];


	
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
        $res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
        $row = mysql_fetch_array($res);		// array fetched with values from $sql query

						// assignment of values from query to local variables
						// to be later used in html to display on textboxes (input)
        $priority = $row['priority'];

        if (isset($_POST['submit'])) {
	        $username = $_POST['username'];
	        $groupOld = $_POST['groupOld'];;
	        $group = $_POST['group'];;
	        $priority = $_POST['priority'];;

                
                include 'library/opendb.php';

                $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." WHERE UserName='$username' AND GroupName='$groupOld'";
                $res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

                if (mysql_num_rows($res) == 1) {

                        if (trim($username) != "" and trim($group) != "") {

				if (!$priority) {
					$priority = 1;
				}

                                // insert nas details
                                $sql = "UPDATE ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." SET GroupName='$group', priority='$priority' WHERE UserName='$username' AND GroupName='$groupOld'";
                                $res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");
                        
			echo "<font color='#0000FF'>success<br/></font>";

			}

                } else {
                        echo "<font color='#FF0000'>error: user $username already exist <br/></font>";
			echo "
                                <script language='JavaScript'>
                                <!--
                                alert('The user $username already exists in the database.\\nPlease check that there are no duplicate entries in the database.');
                                -->
                                </script>
                                ";
                } 

                include 'library/closedb.php';
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

<table border='2' class='table1'>
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
