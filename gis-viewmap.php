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

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');


    include_once('library/config_read.php');
    $log = "visited page: ";
    $logDebugSQL = "";

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','gisviewmap.php');
    $help = t('helpPage','gisviewmap');
    
    $extra_css = array("https://unpkg.com/leaflet@1.9.2/dist/leaflet.css");
    $extra_js = array("https://unpkg.com/leaflet@1.9.2/dist/leaflet.js");

    print_html_prologue($title, $langCode, $extra_css, $extra_js);
    
    include("menu-gis.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
?>

    <div id="map" style="width: 800px; height: 600px; margin: 20px auto"></div>

<script>

window.onload = function() {
    var map = L.map('map').setView([51.505, -0.09], 13);
    var group = L.featureGroup().addTo(map);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/light_all/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

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
        
        $popup = sprintf('<b> Hotspot Name</b>: %s<br>'
                       . '<b>Mac Addr</b>: %s<br>'
                       . '<b>Geo Loc</b>: %s<br>'
                       . '<a href=acct-hotspot-compare.php>Hotspot Comparison</a>'
                       . '<br>'
                       . '<a href="acct-hotspot-accounting.php?hotspot=%s">Hotspot Statistics</a>',
                         $row[1], $row[2], $row[3], $row[1]);
        
        
        // Now, create a simple popup.
        // The original program provided a tabbed popup.
        printf('L.marker([%s]).addTo(group).' . "bindTooltip('%s').bindPopup('%s');", $row[3], $row[1], $popup);
    }
    
    include('library/closedb.php');
?>
    map.fitBounds(group.getBounds());
}

</script>

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
