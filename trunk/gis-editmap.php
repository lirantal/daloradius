<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


    if (isset($_REQUEST['type']))
		$type = $_REQUEST['type'];
    else 
        $type = "";

    include_once('library/config_read.php');
    $log = "visited page: ";
	
    include 'library/opendb.php';

	if ($type == "add") {
		(isset($_REQUEST['hotspotname'])) ? $hotspot_name = $_REQUEST['hotspotname'] : $hotspot_name = " ";
		(isset($_REQUEST['hotspotmac'])) ? $hotspot_mac = $_REQUEST['hotspotmac'] : $hotspot_mac = " ";
		(isset($_REQUEST['hotspotgeo'])) ? $hotspot_geo = $_REQUEST['hotspotgeo'] : $hotspot_geo = " ";

		$hotspot_geo = substr($hotspot_geo, 1);
		$hotspot_geo = substr($hotspot_geo, 0, strlen($hotspot_geo)-1);

		$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." values (0, '$hotspot_name', '$hotspot_mac', '$hotspot_geo');";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		$actionStatus = "success";
		$actionMsg = "Added new Hotspot's Geo-Location information for hotspot: <b> $hotspotname </b>";

	}

	if ($type == "del") {
		(isset($_REQUEST['hotspotname'])) ? $hotspot_name = $_REQUEST['hotspotname'] : $hotspot_name = " ";
			
		$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." WHERE name='$hotspot_name'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		$actionStatus = "success";
		$actionMsg = "Deleted Hotspot's Geo-Location information for hotspot: <b> $hotspotname </b>";

	}		

	include 'library/closedb.php';
		
?>


<?php
	
	include ("menu-gis.php");
	
?>		
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][giseditmap.php]; ?></a></h2>
				
				</p>
				</p>
<br/>
<br/>

<div id="map" style="width: 800px; height: 600px"></div>

<?php
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


//map.setCenter(new GLatLng(35.460669951495305, -81.5625), 13, G_HYBRID_MAP);
map.setCenter(new GLatLng(0,0), 1, G_HYBRID_MAP);

function createMarker(point,html) {
        var marker = new GMarker(point);
        GEvent.addListener(marker, "click", function() {
          map.setCenter(point, 16);
          marker.openInfoWindowHtml(html);
        });
        return marker;
}


map.openInfoWindow(map.getCenter(), document.createTextNode("<?echo $l[messages][gisedit1]; ?>"));

GEvent.addListener(map, "click", function(marker, point) {
  var geopoint = point;
  if (marker) {
	  var remove_val=confirm("<? echo $l[messages][gisedit2]; ?>")
        if (remove_val==true) {
		var hotspot_name=prompt("<? echo $l[messages][gisedit3]; ?>","")
            if (hotspot_name!=null && hotspot_name!="") {
	            map.removeOverlay(marker);
	            document.editmaps.type.value = "del";
		    document.editmaps.hotspotname.value = hotspot_name;
		    document.editmaps.submit();
	        }
	    }
  } else {
	  var add_val=confirm("<? echo $l[messages][gisedit4]; ?>" + " Geocode: " + geopoint)
        if (add_val==true) {

		var hotspot_name=prompt("<? echo $l[messages][gisedit5]; ?>","")
            if (hotspot_name!=null && hotspot_name!="") {

		    var hotspot_mac=prompt("<? echo $l[messages][gisedit6] ; ?>","")
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
	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." WHERE geocode > ''";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	while($row = $res->fetchRow()) {
                echo "
		var point_$row[0] = new GLatLng($row[3]);
		var marker_$row[0] = createMarker(point_$row[0], '$row[1]');

		map.addOverlay(marker_$row[0]);
                        ";


        }
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
