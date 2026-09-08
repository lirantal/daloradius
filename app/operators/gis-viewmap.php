<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
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
 * Authors:    Liran Tal <liran@lirantal.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);
    $operator = $_SESSION['operator_user'];

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);

    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    // print HTML prologue
    $title = t('Intro','gisviewmap.php');
    $help = t('helpPage','gisviewmap');

    $extra_css = array("https://unpkg.com/leaflet@1.9.4/dist/leaflet.css");

    // loaded at the bottom of the page
    $extra_js = array("https://unpkg.com/leaflet@1.9.4/dist/leaflet.js");

    print_html_prologue($title, $langCode, $extra_css);

    print_title_and_help($title, $help);

    // print map div
    echo '<div id="map" style="width: 800px; height: 600px; margin: 20px auto"></div>' . "\n";

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);

    $sql = sprintf("SELECT id, name, mac, geocode
                      FROM %s
                     WHERE (geocode <> '' AND geocode IS NOT NULL)", $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";

    $markers_js = "";
    $first_lat = null;
    $first_lng = null;
    $marker_count = 0;

    // flags that make a PHP value safe to embed as a literal inside an inline <script> block
    $json_flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE;

    while ($row = $res->fetchRow()) {
        list($id, $name, $mac, $geocode) = $row;

        // geocode is stored as "lat,lng"; skip anything that doesn't parse cleanly
        if (!preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/', (string) $geocode, $coords)) {
            continue;
        }
        $lat = (float) $coords[1];
        $lng = (float) $coords[2];

        if ($marker_count === 0) {
            $first_lat = $lat;
            $first_lng = $lng;
        }
        $marker_count++;

        $name_html = htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8');
        $mac_html  = htmlspecialchars((string) $mac, ENT_QUOTES, 'UTF-8');
        $geo_html  = htmlspecialchars((string) $geocode, ENT_QUOTES, 'UTF-8');

        // Now, create a simple popup.
        // The original program provided a tabbed popup.
        $popup = sprintf('<strong>Hotspot Name</strong>: %s<br>'
                       . '<strong>MAC Addr</strong>: %s<br>'
                       . '<strong>Geocode</strong>: %s<br>'
                       . '<a href="acct-hotspot-compare.php">%s</a>'
                       . '<br>'
                       . '<a href="acct-hotspot-accounting.php?hotspot[]=%s">%s</a>',
                         $name_html, $mac_html, $geo_html,
                         htmlspecialchars(t('Intro','accthotspotcompare.php'), ENT_QUOTES, 'UTF-8'),
                         urlencode((string) $name),
                         htmlspecialchars(t('Intro','accthotspot.php'), ENT_QUOTES, 'UTF-8'));

        // HTML-escaped for display, then JSON-encoded for the surrounding JavaScript context
        $markers_js .= sprintf("L.marker(%s).addTo(group).bindTooltip(%s).bindPopup(%s);\n",
                               json_encode(array($lat, $lng)),
                               json_encode($name_html, $json_flags),
                               json_encode($popup, $json_flags));
    }

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);

    // center on the first available hotspot coordinate, otherwise fall back to a
    // default view (Area della Ricerca CNR di Pisa, San Cataldo)
    $map_center = ($marker_count > 0) ? json_encode(array($first_lat, $first_lng)) : "[43.71805, 10.42284]";

    $inline_extra_js = <<<EOF
window.onload = function() {
    var map = L.map('map').setView({$map_center}, 16);
    var group = L.featureGroup().addTo(map);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        subdomains: 'abc',
        maxZoom: 19
    }).addTo(map);

EOF;

    $inline_extra_js .= $markers_js;

    // when more than one hotspot is mapped, zoom to fit them all
    if ($marker_count > 1) {
        $inline_extra_js .= "\n    map.fitBounds(group.getBounds());\n";
    }
    $inline_extra_js .= "}\n";

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    print_footer_and_html_epilogue($inline_extra_js, $extra_js);
