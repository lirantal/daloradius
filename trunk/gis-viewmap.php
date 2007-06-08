<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

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


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<?php
	include "library/googlemaps.php";
?>


    <script type="text/javascript">

    //<![CDATA[

    function loads() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
        map.setCenter(new GLatLng(37.4419, -122.1419), 13);
      }
    }

    //]]>
    </script>


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
		
				<h2 id="Intro"><a href="#">View MAP Mode</a></h2>
				
				<p>
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

map.setCenter(new GLatLng(0, 0), 1, G_HYBRID_MAP);

map.openInfoWindow(map.getCenter(),
                   document.createTextNode("Welcome to Enginx Visual Maps"));


// Create our "tiny" marker icon
var icon = new GIcon();
icon.image = "http://labs.google.com/ridefinder/images/mm_20_red.png";
icon.shadow = "http://labs.google.com/ridefinder/images/mm_20_shadow.png";
icon.iconSize = new GSize(12, 20);
icon.shadowSize = new GSize(22, 20);
icon.iconAnchor = new GPoint(6, 20);
icon.infoWindowAnchor = new GPoint(5, 1);


      // ==================================================
      // A function to create a tabbed marker and set up the event window
      // This version accepts a variable number of tabs, passed in the arrays htmls[] and labels[]
      function createTabbedMarker(point,htmls,labels) {
        var marker = new GMarker(point);
        GEvent.addListener(marker, "click", function() {
          // adjust the width so that the info window is large enough for this many tabs
          if (htmls.length > 2) {
            htmls[0] = '<div style="width:'+htmls.length*88+'px">' + htmls[0] + '</div>';
          }
          var tabs = [];
          for (var i=0; i<htmls.length; i++) {
            tabs.push(new GInfoWindowTab(labels[i],htmls[i]));
          }
          marker.openInfoWindowTabsHtml(tabs);
        });
        return marker;
      }
      // ========================


function createMarker(point,html) {
        var marker = new GMarker(point);
        GEvent.addListener(marker, "click", function() {
          map.setCenter(point, 16);
          marker.openInfoWindowHtml(html);
        });
        return marker;
}



var point_sl1 = new GLatLng(8.497245425737093, -13.288897275924683);
// for tabbed windows
// var marker = createTabbedMarker(point, ["Tab 1 contents", "Tab 2 contents","Tab 3 contents"],["One","Two","Three"]);
//var marker_sl1 = createMarker(point_sl1, 'Solar Hotel <br/> 60 Users');

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

map.addOverlay(new GMarker(point_cus, icon));


 }
}

//]]>
</script>

<?php
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
