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
    include_once('../common/includes/config_read.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";


    // print HTML prologue
    $title = t('Intro','gisviewmap.php');
    $help = t('helpPage','gisviewmap');
    
    $extra_css = array("https://unpkg.com/leaflet@1.9.3/dist/leaflet.css");
    
    // loaded at the bottom of the page
    $extra_js = array("https://unpkg.com/leaflet@1.9.3/dist/leaflet.js");

    print_html_prologue($title, $langCode, $extra_css);
    
    print_title_and_help($title, $help);
    
    
    // print map div
    echo '<div id="map" style="width: 800px; height: 600px; margin: 20px auto"></div>' . "\n";

    $inline_extra_js = <<<EOF
window.onload = function() {
    var map = L.map('map').setView([51.505, -0.09], 13);
    var group = L.featureGroup().addTo(map);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/light_all/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);
    
    
EOF;
    
    include('../common/includes/db_open.php');

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
        
        list($id, $name, $mac, $geocode) = $row;
        
        $popup = sprintf('<strong>Hotspot Name</strong>: %s<br>'
                       . '<strong>MAC Addr</strong>: %s<br>'
                       . '<strong>Geocode</strong>: %s<br>'
                       . '<a href=acct-hotspot-compare.php>%s</a>'
                       . '<br>'
                       . '<a href="acct-hotspot-accounting.php?hotspot[]=%s">%s</a>',
                         $name, $mac, $geocode, t('Intro','accthotspotcompare.php'), $name, t('Intro','accthotspot.php'));
        
        
        // Now, create a simple popup.
        // The original program provided a tabbed popup.
        $inline_extra_js .= sprintf("L.marker([%s]).addTo(group).bindTooltip('%s').bindPopup('%s');",
                                    $geocode, $name, $popup);
    }
    
    include('../common/includes/db_close.php');
    
    $inline_extra_js .= <<<EOF

    map.fitBounds(group.getBounds());
}

EOF;
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue($inline_extra_js, $extra_js);
    
?>
