<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	// declaring variables
	$nashost = "";
	$nassecret = "";
	$nasname = "";
	$nasports = "";
	$nastype = "";
	$nasdescription = "";
	$nascommunity = "";

        if (isset($_POST['submit'])) {
	        $nashost = $_POST['nashost'];
	        $nassecret = $_POST['nassecret'];;
	        $nasname = $_POST['nasname'];;
	        $nasports = $_POST['nasports'];;
	        $nastype = $_POST['nastype'];;
	        $nasdescription = $_POST['nasdescription'];;
	        $nascommunity = $_POST['nascommunity'];;

                include 'library/config.php';
                include 'library/opendb.php';

                $sql = "SELECT * FROM nas WHERE nasname='$nashost'";
                $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

                if (mysql_num_rows($res) == 0) {

                        if (trim($nashost) != "" and trim($nassecret) != "") {

			if (!$nasports) {
				$nasports = 0;
			}
                                // insert nas details
                                $sql = "INSERT INTO nas values (0, '$nashost', '$nasname', '$nastype', $nasports, '$nassecret', '$nascommunity', '$nasdescription')";
                                $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
			}
                        
			echo "<font color='#0000FF'>success<br/></font>";

                } else {
                        echo "<font color='#FF0000'>error: nas ip/host $nashost already exist <br/></font>";
			echo "
                                <script language='JavaScript'>
                                <!--
                                alert('The NAS IP/Host $nashost already exists in the database');
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
				
				<h3>NAS Management</h3>
				<ul class="subnav">
				
						<li><a href="mng-rad-nas-list.php"><b>&raquo;</b>List NAS</a></li>
						<li><a href="mng-rad-nas-new.php"><b>&raquo;</b>New NAS</a></li>
						<li><a href="javascript:document.mngradnasedit.submit();""><b>&raquo;</b>Edit NAS<a>
							<form name="mngradnasedit" action="mng-rad-nas-edit.php" method="get" class="sidebar">
							<input name="nashost" type="text">
							</form></li>


						<li><a href="mng-rad-nas-del.php"><b>&raquo;</b>Remove NAS</a></li>	
				</ul>
		
	
		</div>
		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">New NAS Record</a></h2>
				
				<p>

                                <form name="newnas" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

                                                <?php if (trim($nashost) == "") { echo "<font color='#FF0000'>"; }?>
                                                <b>NAS IP/Hostname</b>
                                                <input value="<?php echo $nashost ?>" name="nashost"/>
                                                </font><br/>

                                                <?php if (trim($nassecret) == "") { echo "<font color='#FF0000'>";  }?>
	                                        <b>NAS Secret</b>
                                                <input value="<?php echo $nassecret ?>" name="nassecret" /> 
                                                </font><br/>

                                                <?php if (trim($nasname) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>NAS Shortname</b> 
                                                <input value="<?php echo $nasname ?>" name="nasname" /> (descriptive name)
                                                </font><br/>

        <br/>
        <h4> Advnaced NAS Attributes </h4>


                                                <?php if (trim($nastype) == "") { echo "<font color='#FF0000'>";  }?>
			<input type="checkbox" onclick="javascript:toggleShowDiv('attributesNasType')">
                                                <b>NAS Type</b>
<div id="attributesNasType" style="display:none;visibility:visible" >
						<br/>
                                                <input value="<?php echo $nastype ?>" name="nastype" />
                                                </font>
</div><br/>





                                                <?php if (trim($nasports) == "") { echo "<font color='#FF0000'>";  }?>
			<input type="checkbox" onclick="javascript:toggleShowDiv('attributesPorts')">
                                                <b>NAS Ports</b> 
<div id="attributesPorts" style="display:none;visibility:visible" >
						<br/>
                                                <input value="<?php echo $nasports ?>" name="nasports" />
                                                </font>
</div><br/>




                                                <?php if (trim($nascommunity) == "") { echo "<font color='#FF0000'>";  }?>
			<input type="checkbox" onclick="javascript:toggleShowDiv('attributesCommunity')">
                                                <b>NAS Community</b> 
<div id="attributesCommunity" style="display:none;visibility:visible" >
						<br/>
                                                <input value="<?php echo $nascommunity ?>" name="nascommunity" />
                                                </font>
</div><br/>





                                                <?php if (trim($nasdescription) == "") { echo "<font color='#FF0000'>";  }?>
			<input type="checkbox" onclick="javascript:toggleShowDiv('attributesDescription')">
                                                <b>NAS Description</b> 
<div id="attributesDescription" style="display:none;visibility:visible" >
						<br/>
                                                <input value="<?php echo $nasdescription ?>" name="nasdescription" />
                                                </font>
</div><br/>

                                                <br/><br/>
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
