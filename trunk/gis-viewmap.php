<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

?>


<?php
	
	include ("menu-gis.php");
	
?>
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][gisviewmap.php]; ?></a></h2>
				
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
	document.createTextNode("<? echo $l[messages][gisviewwelcome]; ?>"));


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



var point_sl1 = new GLatLng(35.460669951495305, -81.5625);
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
