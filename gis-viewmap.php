<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	include_once('library/config_read.php');
    $log = "visited page: ";

?>


<?php
	
	include ("menu-gis.php");
	
?>
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><? echo $l['Intro']['gisviewmap.php']; ?>
		<h144>+</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['gisviewmap'] ?>
			<br/>
		</div>
		<br/>

<div id="map" style="width: 800px; height: 600px"></div>

<?php
        include 'library/opendb.php';
?>

<script type="text/javascript">

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
	document.createTextNode("<? echo $l['messages']['gisviewwelcome']; ?>"));



// Create our "tiny" marker icon
var iconRed = new GIcon();
iconRed.image = "http://labs.google.com/ridefinder/images/mm_20_red.png";
iconRed.shadow = "http://labs.google.com/ridefinder/images/mm_20_shadow.png";
iconRed.iconSize = new GSize(12, 20);
iconRed.shadowSize = new GSize(22, 20);
iconRed.iconAnchor = new GPoint(6, 20);
iconRed.infoWindowAnchor = new GPoint(5, 1);


var iconBlue = new GIcon();
iconBlue.image = "http://labs.google.com/ridefinder/images/mm_20_blue.png";
iconBlue.shadow = "http://labs.google.com/ridefinder/images/mm_20_shadow.png";
iconBlue.iconSize = new GSize(12, 20);
iconBlue.shadowSize = new GSize(22, 20);
iconBlue.iconAnchor = new GPoint(6, 20);
iconBlue.infoWindowAnchor = new GPoint(5, 1);


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
        var marker = new GMarker(point, iconRed);
        GEvent.addListener(marker, "click", function() {
          map.setCenter(point, 4);
          marker.openInfoWindowHtml(html);
        });
        return marker;
}


<?php

	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." WHERE geocode > ''";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";

	while($row = $res->fetchRow()) {
                echo "
		var point_$row[0] = new GLatLng($row[3]);

		// original createMarker function which creates a simple marker
		// var marker_$row[0] = createMarker(point_$row[0], '$row[1]');

		// the new function provides a tabbed marker to be created 


		var marker_$row[0] = createTabbedMarker(point_$row[0], ['<b> Hotspot Name: </b> $row[1] <br/> \
					<b> Mac Addr: </b> $row[2] <br/> \
					 <b> Geo Loc: </b> $row[3] <br/>', '<a href=acct-hotspot-compare.php> Hotspot Comparison </a> \
					<br/> <a href=acct-hotspot-accounting.php?hotspot=$row[1]> Hotspot Statistics </a> <br/> '], ['Info','Statistics']);


		map.addOverlay(marker_$row[0]);
                        ";


        }
?>


 }
}

</script>

<?php
    include 'library/closedb.php';
?>


<?php
	include('include/config/logging.php');
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
