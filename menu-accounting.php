
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->

</head>
<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>
 
<body>

<?php
    include_once ("lang/main.php");
?>

<div id="wrapper">
<div id="innerwrapper">

		<div id="header">
		
				<form action="">
				<input value="Search" />
				</form>
				
				<h1><a href="index.php">daloRADIUS</a></h1>
				
				<h2>
				
						Radius Reporting, accting and Management by <a href="http://www.enginx.com">Enginx</a>
				
				</h2>
				
				<ul id="nav">
				
						<li><a href="index.php"><em>H</em>ome</a></li>
						
						<li><a href="mng-main.php"><em>M</em>anagment</a></li>
						
						<li><a href="rep-main.php"><em>R</em>eports</a></li>
						
						<li><a href="acct-main.php" class="active"><em>A</em>ccounting</a></li>

						<li><a href="bill-main.php"><em>B</em>illing</a></li>
                                                <li><a href="gis-main.php"><em>GIS</em></a></li>
						
						<li><a href="graph-main.php"><em>G</em>raphs</a></li>

						<li><a href="help-main.php"><em>H</em>elp</a></li>
				
				</ul>
				<ul id="subnav">
				
						<li>Welcome, <?php echo $operator; ?></li>

						<li><a href="logout.php">[logout]</a></li>
				
				</ul>
		
		</div>
		
		<div id="sidebar">
		
				<h2>Accounting</h2>
				
				<h3>Users Accounting</h3>
				<ul class="subnav">
				
						<li><a href="javascript:document.acctusername.submit();"><b>&raquo;</b>User Accounting<a>
							<form name="acctusername" action="acct-username.php" method="post" class="sidebar"
							<input name="username" type="text" value="username">
							</form></li>

						<li><a href="javascript:document.acctipaddress.submit();"><b>&raquo;</b>IP Accounting<a>
							<form name="acctipaddress" action="acct-ipaddress.php" method="post" class="sidebar">
							<input name="ipaddress" type="text" value="0.0.0.0">
							</form></li>

						<li><a href="javascript:document.acctnasipaddress.submit();"><b>&raquo;</b>NAS IP Accounting<a>
							<form name="acctnasipaddress" action="acct-nasipaddress.php" method="post" class="sidebar">
							<input name="nasipaddress" type="text" value="0.0.0.0">
							</form></li>

						<li><a href="javascript:document.acctdate.submit();"><b>&raquo;</b>Date Accounting<a>
							<form name="acctdate" action="acct-date.php" method="post" class="sidebar">
							<input name="username" type="text" value="username">
							<input name="startdate" type="text" id="startdate" value="2006-01-01">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'startdate', 'chooserSpan', 1950, 2010, 'Y-m-d', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>

							<input name="enddate" type="text" id="enddate" value="2006-12-01">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'enddate', 'chooserSpan', 1950, 2010, 'Y-m-d', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>

							</form></li>






						<li><a href="acct-all.php"><b>&raquo;</b>Display All Accounting Records</a></li>
						<li><a href="acct-active.php"><b>&raquo;</b>Display Active Records</a></li>


				</ul>
		

				<h3>Hotspots Accounting</h3>
				<ul class="subnav">

						<li><a href="javascript:document.accthotspot.submit();"><b>&raquo;</b>Hotspot Accounting<a>
							<form name="accthotspot" action="acct-hotspot.php" method="post" class="sidebar">
<select name="hotspot" size="3">
<?php

        include 'library/config.php';
        include 'library/opendb.php';

	// Grabing the last 

	$sql = "select name from hotspots";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        while($nt = mysql_fetch_array($res)) {
                echo "
                        <option value='$nt[0]'> $nt[0]
			";

	}

        mysql_free_result($res);
        include 'library/closedb.php';
?>							</select>
							</form></li>
				
						<li><a href="acct-hotspot-compare.php"><b>&raquo;</b>Compare Hotspots</a></li>
				</ul>
				
				<br/><br/>
				<h2>Search</h2>
				<input name="" type="text" value="Search" />
				
		
		</div>

		