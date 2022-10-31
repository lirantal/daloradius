<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Authors:    Liran Tal <liran@enginx.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');

    include_once('library/config_read.php');
    $log = "visited page: ";
    $logDebugSQL = "";
    
    if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) &&
        dalo_check_csrf_token($_POST['csrf_token'])) {

        $type = (array_key_exists('type', $_POST) && isset($_POST['type']) &&
                 in_array(strtolower($_POST['type']), array("del", "add")))
              ? strtolower($_POST['type']) : "";

        include('library/opendb.php');

        if ($type == "add") {
            (isset($_POST['hotspotname'])) ? $hotspot_name = $_POST['hotspotname'] : $hotspot_name = " ";
            (isset($_POST['hotspotmac'])) ? $hotspot_mac = $_POST['hotspotmac'] : $hotspot_mac = " ";
            (isset($_POST['hotspotgeo'])) ? $hotspot_geo = $_POST['hotspotgeo'] : $hotspot_geo = " ";

            $sql = sprintf("INSERT INTO %s (name, mac, geocode) VALUES (?, ?, ?)", $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
            $stmt = $dbSocket->prepare($sql);
            $data = array($hotspot_name, $hotspot_mac, $hotspot_geo);
            $res = $dbSocket->execute($stmt, $data);
            $logDebugSQL .= "$sql;\n";

            $successMsg = "Added new Hotspot's Geo-Location information for hotspot: <b> $hotspot_name </b>";
        }

        if ($type == "del") {
            (isset($_POST['hotspotid'])) ? $hotspot_id = $_POST['hotspotid'] : $hotspot_id = -1;

            $sql = sprintf("DELETE FROM %s WHERE id=?", $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
            $stmt = $dbSocket->prepare($sql);
            $res = $dbSocket->execute($stmt, $hotspot_id);
            $logDebugSQL .= "$sql;\n";

            $successMsg = "Deleted Hotspot's Geo-Location information for hotspot: <b> $hotspot_name </b>";

        }
    
        include('library/closedb.php');
    
    }
    
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','giseditmap.php');
    $help = t('helpPage','giseditmap');
    
    $extra_css = array("https://unpkg.com/leaflet@1.9.2/dist/leaflet.css");
    $extra_js = array("https://unpkg.com/leaflet@1.9.2/dist/leaflet.js");

    print_html_prologue($title, $langCode, $extra_css, $extra_js);
    
    include("menu-gis.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
?>

    <div id="map" style="width: 800px; height: 600px; margin: 20px auto"></div>

<script>
//<![CDATA[

window.onload = function() {
    var map = L.map('map').setView([51.505, -0.09], 13);
    var group = L.featureGroup().addTo(map);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/light_all/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);
    
    map.on('click', function(e){
        var geopoint = e.latlng.lat + "," + e.latlng.lng;
        var add_val = confirm("<?= t('messages','gisedit4'); ?>" + " Geocode: " + geopoint)
        if (add_val) {
            var hotspot_name = prompt("<?= t('messages','gisedit5'); ?>", "")
            if (hotspot_name != null && hotspot_name != "") {
                var hotspot_mac = prompt("<?= t('messages','gisedit6') ; ?>", "")
                if (hotspot_mac != null && hotspot_mac != "") {
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
        var remove_val = confirm("<?= t('messages','gisedit2'); ?>")
        if (remove_val) {
            var hotspot_name = prompt("<?= t('messages','gisedit3'); ?>", "")
            if (hotspot_name != null && hotspot_name != "" && hotspot_name == e.target.options.title) {
                e.target.remove();
                document.editmaps.type.value = "del";
                document.editmaps.hotspotid.value = e.target.options.id;
                document.editmaps.submit();
            }
        }
    }

<?php
    include('library/opendb.php');

    $sql = sprintf("SELECT id, name, mac, geocode
                      FROM %s
                     WHERE (geocode <> '' AND geocode IS NOT NULL)", $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";

    while ($row = $res->fetchRow()) {
        $rowlen = count($row);
        
        for ($i = 0; $i < $rowlen; $i++) {
            $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
        }
        
        printf("L.marker([%s], {id: %s, title: '%s'}).addTo(group).bindTooltip('%s').on('click', remove);",
               $row[3], $row[0], $row[1], $row[1]);
    }
    
    include('library/closedb.php');
?>
    map.fitBounds(group.getBounds());
}

//]]>
</script>

<form name="editmaps" style="display: hidden" method="POST">
    <input type="hidden" name="type" value="">
    <input type="hidden" name="hotspotid" value="">
    <input type="hidden" name="hotspotname" value="">
    <input type="hidden" name="hotspotmac" value="">
    <input type="hidden" name="hotspotgeo" value="">
    <input name="csrf_token" type="hidden" value="<?= dalo_csrf_token() ?>">
</form>

        </div><!-- #contentnorightbar -->
        
        <div id="footer">
<?php
    include('include/config/logging.php');
    include('page-footer.php');
?>
        </div><!-- #footer -->
    </div>
</div>

</body>
</html>
