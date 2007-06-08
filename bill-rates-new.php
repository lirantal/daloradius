<?php
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];


	$type = "";
	$cardbank = "";
	$rate = "";

	if ($type == "") { $type = ""; }
	if ($cardbank == "") { $cardbank = ""; }
	if ($rate == "") { $rate = ""; }

	
	if (isset($_POST['submit'])) {

		$type = $_POST['type'];
		$cardbank = $_POST['cardbank'];
		$rate = $_POST['rate'];

		include 'library/config.php';
		include 'library/opendb.php';

		$sql = "SELECT * FROM rates WHERE type='$type'";
		$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

		if (mysql_num_rows($res) == 0) {
		
			if (trim($type) != "" and trim($cardbank) != "") {

				// insert username/password
				$sql = "INSERT INTO rates VALUES (0, '$type', $cardbank, $rate)";
				$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
		
				echo "success<br/>";
	
			}
		} 
		
		include 'library/closedb.php';

	}



?>

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
						
						<li><a href="mng-main.php"><em>M</em>anagment</a></li>
						
						<li><a href="rep-main.php"><em>R</em>eports</a></li>
						
						<li><a href="acct-main.php"><em>A</em>ccounting</a></li>

						<li><a href="bill-main.php" class="active"><em>B</em>illing</a></li>
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
		
				<h2>Billing</h2>
				
				<h3>Billing Engine</h3>
				<ul class="subnav">
				
						<li><a href="javascript:document.billprepaidhotspot.submit();""><b>&raquo;</b>Prepaid Accounting<a>
							<form name="billprepaidhotspot" action="bill-prepaid.php" method="get" class="sidebar">
							<select name="hotspot" size="3">
								<option value='%'> all
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
?>							</select><br/>


						<br/>Filter by date

                                                        <input name="startdate" type="text" id="startdate" value="2006-01-01">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'startdate', 'chooserSpan', 1950, 2010, 'Y-m-d', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
                                                        <input name="enddate" type="text" id="enddate" value="2006-12-01">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'enddate', 'chooserSpan', 1950, 2010, 'Y-m-d', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>


							</form></li>


						<li><a href="javascript:document.billpersecondhotspot.submit();""><b>&raquo;</b>Per-second Accounting<a>
							<form name="billpersecondhotspot" action="bill-persecond.php" method="get" class="sidebar">
							<select name="ps-hotspot" size="3">
								<option value='%'> all
<?php

        include 'library/config.php';
        include 'library/opendb.php';

	// Grabing the list of hotspots 

	$sql = "select name from hotspots";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        while($nt = mysql_fetch_array($res)) {
                echo "
                        <option value='$nt[0]'> $nt[0]
			";

	}

        mysql_free_result($res);
        include 'library/closedb.php';
?>							</select><br/>


						<br/>Filter by date
                                                        <input name="ps-startdate" type="text" id="ps-startdate" value="2006-01-01">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'ps-startdate', 'chooserSpan', 1950, 2010, 'Y-m-d', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
                                                        <input name="ps-enddate" type="text" id="ps-enddate" value="2006-12-01">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'ps-enddate', 'chooserSpan', 1950, 2010, 'Y-m-d', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>

								</form></li>


						<li><a href="mng-del.php"><b>&raquo;</b>Remove User</a></li>	
				</ul>
		
				<h3>Rates Management</h3>
				<ul class="subnav">
				
						<li><a href="bill-rates-list.php"><b>&raquo;</b>Show rates</a></li>
						<li><a href="bill-rates-new.php"><b>&raquo;</b>New rate</a></li>
						<li><a href="javascript:document.billratesedit.submit();""><b>&raquo;</b>Edit Rate<a>
							<form name="billratesedit" action="bill-rates-edit.php" method="get" class="sidebar">
							<input name="type" type="text">
							</form></li>


						<li><a href="bill-rates-del.php"><b>&raquo;</b>Delete rate</a></li>
				</ul>
				
				<br/><br/>
				<h2>Search</h2>
				
				<input name="" type="text" value="Search" />
		
		</div>
		
		
		
		<div id="contentnorightbar">

				<h2 id="Intro">New Rate entry</h2>
				
				<p>
				Fill below the details for the new rate entry
				<br/><br/>
<?php
		if (trim($type) == "") { echo "error: missing type <br/>";  }
		if (trim($cardbank) == "") { echo "error: missing cardbank <br/>";  }
		if (trim($rate) == "") { echo "error: missing rate <br/>";  }
?>
				</p>
				<form action="bill-rates-new.php" method="post">

						<b>Type</b>
						<input value="<?php echo $type ?>" name="type"/><br/>

						<b>Cardbank</b>
						<input value="<?php echo $cardbank ?>" name="cardbank" /><br/>

						<b>Rate</b>
						<input value="<?php echo $rate ?>" name="rate" /><br/>

						<br/>
						<input type="submit" name="submit" value="Apply"/>

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
