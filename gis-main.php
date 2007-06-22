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

<?php
	
	include ("menu-gis.php");
	
?>


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
