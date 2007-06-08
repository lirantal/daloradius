<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	$username = $_POST['username'];
	$startdate = $_POST['startdate'];
	$enddate = $_POST['enddate'];

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
		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Users Accounting</a></h2>
				
				<p>
				</p>



<?php

        include 'library/config.php';
        include 'library/opendb.php';

        $sql = "SELECT radacct.RadAcctId, hotspots.name, radacct.UserName, radacct.FramedIPAddress, radacct.AcctStartTime, radacct.AcctStopTime, radacct.AcctSessionTime, radacct.AcctInputOctets, radacct.AcctOutputOctets, radacct.AcctTerminateCause, radacct.NASIPAddress FROM radacct LEFT JOIN hotspots ON radacct.calledstationid = hotspots.mac WHERE AcctStartTime>'$startdate' and AcctStartTime<'$enddate' and UserName like '$username'";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());


	$counter=0;
	$bytesin=0;
	$bytesout=0;
	$megabytesout=0;
	$megabytesin=0;
	$session_seconds=0;
	$session_minutes=0;

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='15'>Records</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
                        <th scope='col'> ID </th>
                        <th scope='col'> HotSpot </th>
                        <th scope='col'> Username </th>
                        <th scope='col'> IP Address</th>
                        <th scope='col'> Start Time </th>
                        <th scope='col'> Stop Time </th>
                        <th scope='col'> Total Time </th>
                        <th scope='col'> Upload (Bytes) </th>
                        <th scope='col'> Download (Bytes) </th>
                        <th scope='col'> Termination </th>
                        <th scope='col'> NAS IP Address </th>
                        <th scope='col'> Action </th>
                </tr> </thread>";

        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[0] </td>
                        <td> $nt[1] </td>
                        <td> $nt[2] </td>
                        <td> $nt[3] </td>
                        <td> $nt[4] </td>
                        <td> $nt[5] </td>
                        <td> $nt[6] </td>
                        <td> $nt[7] </td>
                        <td> $nt[8] </td>
                        <td> $nt[9] </td>
                        <td> $nt[10] </td>
                        <td> <a href='mng-edit.php?username=$nt[UserName]'> edit </a> </td>
                </tr>";

	        $counter++;
	        $session_seconds += $nt[5];
//	        $session_minutes = int($session_seconds / 60);
	        $bytesin= $bytesin + $nt[6];
	        $bytesout= $bytesout + $nt[7];
//	        $megabytesin = int($bytesin / 1000000);
//	        $megabytesout = int($bytesout / 1000000);
	
        }
        echo "</table>";

        mysql_free_result($res);
        include 'library/closedb.php';
?>
			
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
