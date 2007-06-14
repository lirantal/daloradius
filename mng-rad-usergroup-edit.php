<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];


	include 'library/config.php';
        include 'library/opendb.php';

        // declaring variables
        $username = "";
        $group = "";
        $groupOld = "";
        $priority = "";

	$username = $_REQUEST['username'];
	$groupOld = $_REQUEST['group'];

        // fill-in nashost details in html textboxes
        $sql = "SELECT * FROM usergroup WHERE UserName='$username' AND GroupName='$groupOld'";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
        $row = mysql_fetch_array($res);		// array fetched with values from $sql query

						// assignment of values from query to local variables
						// to be later used in html to display on textboxes (input)
        $priority = $row['priority'];

        if (isset($_POST['submit'])) {
	        $username = $_POST['username'];
	        $groupOld = $_POST['groupOld'];;
	        $group = $_POST['group'];;
	        $priority = $_POST['priority'];;

                include 'library/config.php';
                include 'library/opendb.php';

                $sql = "SELECT * FROM usergroup WHERE UserName='$username' AND GroupName='$groupOld'";
                $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

                if (mysql_num_rows($res) == 1) {

                        if (trim($username) != "" and trim($group) != "") {

				if (!$priority) {
					$priority = 1;
				}

                                // insert nas details
                                $sql = "UPDATE usergroup SET GroupName='$group', priority='$priority' WHERE UserName='$username' AND GroupName='$groupOld'";
                                $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                        
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
 
<body>

<div id="wrapper">
<div id="innerwrapper">

		<div id="header">
		
				<form action="">
				<input value="Search" />
				</form>
				
				<h1><a href="index.php">daloRADIUS</a></h1>
				
				<h2>
				
						Radius Reporting, Billing and Management by <a href="http://www.enginx.com">Enginx</a>
				
				</h2>
				
				<ul id="nav">
				
						<li><a href="index.php"><em>H</em>ome</a></li>
						
						<li><a href="mng-main.php" class="active"><em>M</em>anagment</a></li>
						
						<li><a href="rep-main.php"><em>R</em>eports</a></li>
						
						<li><a href="acct-main.php"><em>A</em>ccounting</a></li>

						<li><a href="bill-main.php"><em>B</em>illing</a></li>
						<li><a href="gis-main.php"><em>GIS</em></a></li>
						<li><a href="graph-main.php"><em>G</em>raphs</a></li>

						<li><a href="help-main.php"><em>H</em>elp</a></li>
				
				</ul>
				<ul id="subnav">
						<li><a href="mng-rad-nas.php" class="active"><em>N</em>as</a></li>
						<li><a href="mng-rad-usergroup.php"><em>U</em>ser-group Mapping</a></li>
						<li><a href="mng-rad-groupreply.php"><em>G</em>roup-reply</a></li>
						<li><a href="mng-rad-groupcheck.php"><em>G</em>roup-check</a></li>
						<li><a href="mng-rad-postauth.php"><em>P</em>ostauth</a></li>
						<li><a href="mng-rad-operators.php"><em>O</em>perators</a></li>

						<li>Welcome, <?php echo $operator; ?></li>

						<li><a href="logout.php">[logout]</a></li>
				
				</ul>
		
		</div>
		
		<div id="sidebar">
		
	
                                <h2>Management</h2>

                                <h3>User-Group Management</h3>
                                <ul class="subnav">

                                                <li><a href="mng-rad-usergroup-list.php"><b>&raquo;</b>List User-Group Mappings</a></li>
                                                <li><a href="javascript:document.mngradusrgrplist.submit();""><b>&raquo;</b>List User's Group Mapping<a>
                                                        <form name="mngradusrgrplist" action="mng-rad-usergroup-list-user.php" method="get" class="sidebar">
                                                        <input name="username" type="text">
                                                        </form></li>

                                                <li><a href="mng-rad-usergroup-add-manual.php"><b>&raquo;</b>New User-Group Mapping</a></li>
                                                <li><a href="mng-rad-usergroup-add-multi.php"><b>&raquo;</b>New Multi User-Group Mapping</a></li>
                                                <li><a href="javascript:document.mngradusrgrpedit.submit();""><b>&raquo;</b>Edit User-Group Mapping<a>
                                                        <form name="mngradusrgrpedit" action="mng-rad-usergroup-edit.php" method="get" class="sidebar">
                                                        <input name="username" type="text" value="[username]">
                                                        <input name="group" type="text" value="[groupname]">
                                                        </form></li>


                                                <li><a href="mng-rad-usergroup-del.php"><b>&raquo;</b>Remove User-Group Mapping</a></li>
                                </ul>


	
		</div>
		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Edit User-Group Mapping for User: <?php echo $username ?></a></h2>
				
				<p>

                                <form name="newuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

                                                <input type="hidden" value="<?php echo $username ?>" name="username" /><br/>

                                                <?php if (trim($groupOld) == "") { echo "<font color='#FF0000'>";  }?>
	                                        <b>Current Group Name</b>
                                                <input value="<?php echo $groupOld ?>" name="groupOld" /> (Old Group Name)
                                                </font><br/>

                                                <?php if (trim($group) == "") { echo "<font color='#FF0000'>";  }?>
	                                        <b>New Group Name</b>
                                                <input value="<?php echo $group ?>" name="group" /> 
                                                </font><br/>


        <br/>
        <h4> Advnaced User-Group Attributes </h4>


                                                <?php if (trim($priority) == "") { echo "<font color='#FF0000'>";  }?>
			<input type="checkbox" onclick="javascript:toggleShowDiv('attributesPriority')">
                                                <b>Priority</b>
<div id="attributesPriority" style="display:none;visibility:visible" >
						<br/>
                                                <input value="<?php echo $priority ?>" name="priority" />
                                                </font>
</div><br/>

                                                <input type="submit" name="submit" value="Apply"/>

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
