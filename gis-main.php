<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

   if (isset($_REQUEST["submit"])) {
	$googleMapsCode = $_REQUEST['code'];
	writeGoogleMapsCode($googleMapsCode);
    }

    function writeGoogleMapsCode($googleMapsCode) {
	$myfile = "library/googlemaps.php";
	if ($fh = fopen($myfile, 'w') ) {
		$strCode = "<script src='http://maps.google.com/maps?file=api&amp;v=2&amp;key=" . $googleMapsCode . "'
			type='text/javascript'></script>";
		fwrite($fh, $strCode);
	        fclose($fh);
                echo "
                                <script language='JavaScript'>
                                <!--
                                alert('Successfully updated GoogleMaps API Registration code');
                                -->
                                </script>
                                ";

	} else {
                        echo "<font color='#FF0000'>error: could not open the file <b> $myfile </b> for writing!<br/></font>";
			echo "Check file permissions. The file should be writable by the webserver's user/group<br/>";
                        echo "
                                <script language='JavaScript'>
                                <!--
                                alert('could not open the file <b> $myfile </b> for writing!\\nCheck file permissions.');
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

						<li><a href="bill-main.php"><em>B</em>illing</a></li>
						
						<li><a href="gis-main.php" class="active"><em>GIS</em></a></li>
						<li><a href="graph-main.php"><em>G</em>raphs</a></li>

						<li><a href="help-main.php"><em>H</em>elp</a></li>
				
				</ul>
				<ul id="subnav">
				
						<li>Welcome, <?php echo $operator; ?></li>

						<li><a href="logout.php">[logout]</a></li>
				
				</ul>
		
		</div>
		
		<div id="sidebar">
		
				<h2>GIS</h2>
				
				<h3>GIS Mapping</h3>
				<ul class="subnav">
				
						<li><a href="gis-viewmap.php"><b>&raquo;</b>View MAP</a></li>
						<li><a href="gis-editmap.php"><b>&raquo;</b>Edit MAP</a></li>		
				</ul>
		
				<h3>Settings</h3>
				<ul class="subnav">
				
						<li><a href="javascript:document.gisregister.submit();"/><b>&raquo;</b>Register GoogleMaps API<a>
							<form name="gisregister" action="gis-main.php" method="get" class="sidebar">
							<input name="code" type="text">
							<input name="submit" type="submit" value="Register code">
							</form></li>

				</ul>
				
				<br/><br/>
				<h2>Search</h2>
				
				<input name="" type="text" value="Search" />
		
		</div>
		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">GIS Mapping</a></h2>
				
				<p>

				GIS Mapping provides visual mappings of the hotspot location across the world's map using Google Maps API. <br/>
				In the Management page you are able to add new hotspot entries to the database where there is also a field
				called Geolocation, this is the numeric value that the Google Maps API uses in order to pin-point the exact
				location of that hotspot on the map.<br/><br/>

				2 Modes of Operation are provided: One is the View MAP mode which enables 'surfing' through the world map
				and view the current locations of the hotspots in the database and another one - Edit MAP - which is the mode
				that one can use in order to create hotspot's visually by simply left-clicking on the map or removing 
				existing hotspot entries by left-clicking on existing hotspot flags.<br/><br/>

				Another important issue is that each computer on the network requires a unique Registration code which you 
				can obtain from Google Maps API page by providing the complete web address to the hosted directory of
				daloRADIUS application on your server. Once you have obtained that code from Google, simply paste it in the
				Registration box and click the 'Register code' button to write it.<br/>
				Then you may be able to use Google Maps services. <br/><br/>
				
				

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
