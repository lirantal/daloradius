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

    $logDebugSQL = "";

	if ($type == "add") {
		(isset($_REQUEST['hotspotname'])) ? $hotspot_name = $_REQUEST['hotspotname'] : $hotspot_name = " ";
		(isset($_REQUEST['hotspotmac'])) ? $hotspot_mac = $_REQUEST['hotspotmac'] : $hotspot_mac = " ";
		(isset($_REQUEST['hotspotgeo'])) ? $hotspot_geo = $_REQUEST['hotspotgeo'] : $hotspot_geo = " ";

		$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." (name, mac, geocode) VALUES (?, ?, ?)";
 		$stmt = $dbSocket->prepare($sql);
		$data = array($hotspot_name, $hotspot_mac, $hotspot_geo);
		$res = $dbSocket->execute($stmt, $data);
		$logDebugSQL .= $sql . "\n";

		$successMsg = "Added new Hotspot's Geo-Location information for hotspot: <b> $hotspot_name </b>";
	}

	if ($type == "del") {
		(isset($_REQUEST['hotspotid'])) ? $hotspot_id = $_REQUEST['hotspotid'] : $hotspot_id = -1;

		$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." WHERE id=?";
		$stmt = $dbSocket->prepare($sql);
		$res = $dbSocket->execute($stmt, $hotspot_id);
		$logDebugSQL .= $sql . "\n";

		$successMsg = "Deleted Hotspot's Geo-Location information for hotspot: <b> $hotspot_name </b>";

	}		

	include 'library/closedb.php';

?>


<?php
	
	include ("menu-gis.php");
	
?>		


	<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','giseditmap.php'); ?>
		<h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','giseditmap') ?>
			<br/>
		</div>
		<?php
			include_once('include/management/actionMessages.php');
		?>

<div id="map" style="width: 800px; height: 600px"></div>

<?php
	include 'library/opendb.php';
?>

<script type="text/javascript">
//<![CDATA[

function load() {
	var map = L.map('map').setView([51.505, -0.09], 13);
	var group = L.featureGroup().addTo(map);

	L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/light_all/{z}/{x}/{y}.png', {
		attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
		subdomains: 'abcd',
		maxZoom: 20
	}).addTo(map);
	map.on('click', function(e){
		var geopoint = e.latlng.lat + "," + e.latlng.lng;
		var add_val=confirm("<?php echo t('messages','gisedit4'); ?>" + " Geocode: " + geopoint)
		if (add_val==true) {
			var hotspot_name=prompt("<?php echo t('messages','gisedit5'); ?>","")
			if (hotspot_name!=null && hotspot_name!="") {
				var hotspot_mac=prompt("<?php echo t('messages','gisedit6') ; ?>","")
				if (hotspot_mac!=null && hotspot_mac!="") {
					L.marker(e.latlng).addTo(group).bindTooltip(hotspot_name);
					document.editmaps.type.value = "add";
					document.editmaps.hotspotname.value = hotspot_name;
					document.editmaps.hotspotgeo.value = geopoint.toString();
					document.editmaps.hotspotmac.value = hotspot_mac;
					document.editmaps.submit();
				}
			}
		}

	});
	function remove(e) {
		var remove_val=confirm("<?php echo t('messages','gisedit2'); ?>")
		if (remove_val==true) {
			var hotspot_name=prompt("<?php echo t('messages','gisedit3'); ?>","")
			if (hotspot_name!=null && hotspot_name!="" && hotspot_name==e.target.options.title) {
				e.target.remove();
				document.editmaps.type.value = "del";
				document.editmaps.hotspotid.value = e.target.options.id;
				document.editmaps.submit();
			}
		}
	}

<?php
	$sql = "SELECT id,name,mac,geocode FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." WHERE geocode > ''";
	$res = $dbSocket->query($sql);
	$logDebugSQL .= $sql . "\n";

	while($row = $res->fetchRow()) {
		echo "
		L.marker([$row[3]], {id: $row[0], title: '$row[1]'}).addTo(group)
			.bindTooltip('$row[1]').on('click', remove);
		";
	}
?>
	map.fitBounds(group.getBounds());
}

//]]>
</script>

<?php
    include 'library/closedb.php';
?>


<form name="editmaps" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<input type="hidden" name="type" value="">
<input type="hidden" name="hotspotid" value="">
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
