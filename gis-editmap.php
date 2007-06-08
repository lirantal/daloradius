<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
    $type = $_REQUEST['type'];

        include 'library/config.php';
        include 'library/opendb.php';

	if ($type == "add") {
		$hotspot_name = $_REQUEST['hotspotname'];
 		$hotspot_mac = $_REQUEST['hotspotmac'];
		$hotspot_geo = $_REQUEST['hotspotgeo'];

		if (!$hotspot_geo) { $hotspot_geo = " "; }
		if (!$hotspot_name) { $hotspot_geo = " "; }
		if (!$hotspot_mac) { $hotspot_geo = " "; }

	        $hotspot_geo = substr($hotspot_geo, 1);
	        $hotspot_geo = substr($hotspot_geo, 0, strlen($hotspot_geo)-1);
	
        	$sql = "INSERT INTO hotspots values (0, '$hotspot_name', '$hotspot_mac', '$hotspot_geo');";
	        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	}

	if ($type == "del") {
		$hotspot_name = $_REQUEST['hotspotname'];
		
		$sql = "DELETE FROM hotspots WHERE name='$hotspot_name'";
		$res = mysql_query($sql) or die('Query failed: ' . mysql_error());
	}		

        include 'library/closedb.php';

/*
    require "library/datepicker/class.datepicker.php";

    // instantiate the object
    $db=new datepicker();

    // set the selectable range
    // users will not be able to select dates outside those ranges

    // set the first day of week to Monday
    // users from the United States will set this to 0 (Sunday)
    $db->firstDayOfWeek = 0;

    // set the format in which the date to be returned
    $db->dateFormat = "Y-m-d";
*/

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<?php
	include "library/googlemaps.php";
?>

</head>
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<body onload="load()" onunload="GUnload()">
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
				
						<li><a href="javascript:registerGoogleMaps(document.gisregister.code.value)"><b>&raquo;</b>Register GoogleMaps API<a>

							<form name="gisregister" action="" class="sidebar">
							<input name="code" type="text">
							</form></li>

				</ul>
				
				<br/><br/>
				<h2>Search</h2>
				
				<input name="" type="text" value="Search" />
		
		</div>
		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Edit MAP Mode</a></h2>
				
				</p>
				</p>
<br/>
<br/>

<div id="map" style="width: 800px; height: 600px"></div>

<?php
    include 'library/config.php';
    include 'library/opendb.php';
?>

<script type="text/javascript">
//<![CDATA[


    function load() {
      if (GBrowserIsCompatible()) {
        map = new GMap2(document.getElementById("map"));


map.addControl(new GMapTypeControl());
map.addControl(new GLargeMapControl());
map.addControl(new GScaleControl());
map.addControl(new GOverviewMapControl());
map.enableDoubleClickZoom();
map.enableContinuousZoom();


//map.setCenter(new GLatLng(8.497245425737093, -13.288897275924683), 13, G_HYBRID_MAP);
map.setCenter(new GLatLng(0,0), 1, G_HYBRID_MAP);

function createMarker(point,html) {
        var marker = new GMarker(point);
        GEvent.addListener(marker, "click", function() {
          map.setCenter(point, 16);
          marker.openInfoWindowHtml(html);
        });
        return marker;
}


map.openInfoWindow(map.getCenter(), document.createTextNode("Welcome, you are currently in Edit mode"));

GEvent.addListener(map, "click", function(marker, point) {
  var geopoint = point;
  if (marker) {
    var remove_val=confirm("Remove current marker from map and database?")
        if (remove_val==true) {
            var hotspot_name=prompt("Please enter name of HotSpot","")
            if (hotspot_name!=null && hotspot_name!="") {
	            map.removeOverlay(marker);
	            document.editmaps.type.value = "del";
		    document.editmaps.hotspotname.value = hotspot_name;
		    document.editmaps.submit();
	        }
	    }
  } else {
        var add_val=confirm("Add current marker to database?" + " Geocode: " + geopoint)
        if (add_val==true) {

            var hotspot_name=prompt("Please enter name of HotSpot","")
            if (hotspot_name!=null && hotspot_name!="") {

                    var hotspot_mac=prompt("Please enter the MAC Address of the Hotspot","")
                    if (hotspot_mac!=null && hotspot_mac!="") {
                            map.addOverlay(new GMarker(point));
				document.editmaps.type.value = "add";
				document.editmaps.hotspotname.value = hotspot_name;
				document.editmaps.hotspotgeo.value = geopoint;
				document.editmaps.hotspotmac.value = hotspot_mac;
			    	document.editmaps.submit();
                    }
            }



        }
  }
});



<?php
    $sql = "SELECT * FROM hotspots WHERE geocode > ''";
    $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

        while($nt = mysql_fetch_array($res)) {
                echo "
		var point_$nt[0] = new GLatLng($nt[3]);
		var marker_$nt[0] = createMarker(point_$nt[0], '$nt[1]');

		map.addOverlay(marker_$nt[0]);
                        ";


        }
    mysql_free_result($res);
?>

var add = new GLatLng();
map.addOverlay(new GMarker(point_cus, icon));


 }
}

//]]>
</script>

<?php
    include 'library/closedb.php';
?>


<form name="editmaps" action="<?php print("$PHP_SELF"); ?>">
<input type="hidden" name="type" value="">
<input type="hidden" name="hotspotname" value="">
<input type="hidden" name="hotspotmac" value="">
<input type="hidden" name="hotspotgeo" value="">
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
