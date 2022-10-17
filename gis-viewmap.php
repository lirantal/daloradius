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
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','gisviewmap.php'); ?>
		<h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','gisviewmap') ?>
			<br/>
		</div>
		<br/>

<div id="map" style="width: 800px; height: 600px"></div>

<?php
        include 'library/opendb.php';
?>

<script type="text/javascript">

function load() {
	var map = L.map('map').setView([51.505, -0.09], 13);
	var group = L.featureGroup().addTo(map);

	L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/light_all/{z}/{x}/{y}.png', {
		attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
		subdomains: 'abcd',
		maxZoom: 20
	}).addTo(map);

<?php

	$sql = "SELECT id,name,mac,geocode FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." WHERE geocode > ''";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";

	while($row = $res->fetchRow()) {
		// Now, create a simple popup.
		// The original program provided a tabbed popup.
                echo "
		L.marker([$row[3]]).addTo(group)
			.bindTooltip('$row[1]')
			.bindPopup('<b> Hotspot Name: </b> $row[1] <br/> \
					<b> Mac Addr: </b> $row[2] <br/> \
					<b> Geo Loc: </b> $row[3] <br/> \
					<a href=acct-hotspot-compare.php> Hotspot Comparison </a> \
					<br/> <a href=acct-hotspot-accounting.php?hotspot=$row[1]> Hotspot Statistics </a> <br/>')
                        ";

        }
?>
	map.fitBounds(group.getBounds());

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
