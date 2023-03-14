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

    if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) &&
        dalo_check_csrf_token($_POST['csrf_token'])) {

        $type = (array_key_exists('type', $_POST) && isset($_POST['type']) &&
                 in_array(strtolower($_POST['type']), array("del", "add")))
              ? strtolower($_POST['type']) : "";

        include('../common/includes/db_open.php');

        if ($type == "add") {
            $hotspot_name = (array_key_exists('hotspotname', $_POST) && !empty(trim($_POST['hotspotname'])))
                          ? trim($_POST['hotspotname']) : "";

            $hotspot_mac = (
                                array_key_exists('hotspotmac', $_POST) && !empty(trim($_POST['hotspotmac'])) &&
                                preg_match(MACADDR_REGEX, trim($_POST['hotspotmac']))
                           ) ? trim($_POST['hotspotmac']) : "";

            $hotspot_geo = (
                                array_key_exists('hotspotgeo', $_POST) &&
                                !empty(trim($_POST['hotspotgeo'])) &&
                                preg_match('/^\d+(\.\d+)?,\d+(\.\d+)?$/', trim($_POST['hotspotgeo'])) !== false
                           ) ? trim($_POST['hotspotgeo']) : "";

            if (empty($hotspot_name) || empty($hotspot_mac) || empty($hotspot_geo)) {
                $failureMsg = "Invalid input";
            } else {

                $currDate = date('Y-m-d H:i:s');
                $currBy = $_SESSION['operator_user'];

                $hotspot_name_enc = htmlspecialchars($hotspot_name, ENT_QUOTES, 'UTF-8');

                $sql = sprintf("INSERT INTO %s (name, mac, geocode, creationdate, creationby, updatedate, updateby)
                                VALUES (?, ?, ?, ?, ?, NULL, NULL)", $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
                $stmt = $dbSocket->prepare($sql);
                $data = array($hotspot_name, $hotspot_mac, $hotspot_geo, $currDate, $currBy);
                $res = $dbSocket->execute($stmt, $data);
                $logDebugSQL .= "$sql;\n";

                $successMsg = sprintf("Added new geolocation information for hotspot <strong>%s</strong>. "
                                    . '<a href="mng-hs-edit.php?name=%s" title="Edit">Edit</a>', $hotspot_name_enc, urlencode($hotspot_name_enc));
            }
        }

        if ($type == "del") {

            if (array_key_exists('hotspotid', $_POST) && !empty($_POST['hotspotid']) && intval($_POST['hotspotid']) > 0) {
                $hotspot_id = intval($_POST['hotspotid']);

                // get name
                $sql = sprintf("SELECT name FROM %s WHERE id=?", $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
                $stmt = $dbSocket->prepare($sql);
                $res = $dbSocket->execute($stmt, $hotspot_id);

                $hotspot_name = $res->fetchrow()[0];
                $hotspot_name_enc = htmlspecialchars($hotspot_name, ENT_QUOTES, 'UTF-8');

                $sql = sprintf("DELETE FROM %s WHERE id=?", $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
                $stmt = $dbSocket->prepare($sql);
                $res = $dbSocket->execute($stmt, $hotspot_id);
                $logDebugSQL .= "$sql;\n";

                $successMsg = sprintf("Deleted geolocation information for hotspot <strong>%s</strong>.", $hotspot_name_enc);

            } else {
                $failureMsg = "Invalid input";
            }
        }

        include('../common/includes/db_close.php');

    }


    // print HTML prologue
    $title = t('Intro','giseditmap.php');
    $help = t('helpPage','giseditmap');

    $extra_css = array("https://unpkg.com/leaflet@1.9.3/dist/leaflet.css");

    // loaded at the bottom of the page
    $extra_js = array("https://unpkg.com/leaflet@1.9.3/dist/leaflet.js");

    print_html_prologue($title, $langCode, $extra_css);

    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

    // print map div
    echo '<div id="map" style="width: 800px; height: 600px; margin: 20px auto"></div>' . "\n";

    // init (hidden) form components
    $input_descriptors0 = array();

    $input_descriptors0[] = array(
                                    "type" => "hidden",
                                    "name" => "type"
                                 );

    $input_descriptors0[] = array(
                                    "type" => "hidden",
                                    "name" => "hotspotid"
                                 );

    $input_descriptors0[] = array(
                                    "type" => "hidden",
                                    "name" => "hotspotname"
                                 );

    $input_descriptors0[] = array(
                                    "type" => "hidden",
                                    "name" => "hotspotmac"
                                 );

    $input_descriptors0[] = array(
                                    "type" => "hidden",
                                    "name" => "hotspotgeo"
                                 );

    $input_descriptors0[] = array(
                                    "type" => "hidden",
                                    "name" => "csrf_token",
                                    "value" => dalo_csrf_token()
                                 );

    $form0_descriptor = array(
                                "name" => "editmaps",
                                "hidden" => true

                             );

    open_form($form0_descriptor);

    foreach ($input_descriptors0 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

    close_form();


    // dynamically create inline extra javascript

    $message2 = t('messages','gisedit2');
    $message3 = t('messages','gisedit3');
    $message4 = t('messages','gisedit4');
    $message5 = t('messages','gisedit5');
    $message6 = t('messages','gisedit6');

    $inline_extra_js = <<<EOF
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
        var add_val = confirm("{$message4}" + " Geocode: " + geopoint)
        if (add_val) {
            var hotspot_name = prompt("{$message5}", "")
            if (hotspot_name != null && hotspot_name != "") {
                var hotspot_mac = prompt("{$message6}", "")
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
        var remove_val = confirm("{$message2}")
        if (remove_val) {
            var hotspot_name = prompt("{$message3}", "")
            if (hotspot_name != null && hotspot_name != "" && hotspot_name == e.target.options.title) {
                e.target.remove();
                document.editmaps.type.value = "del";
                document.editmaps.hotspotid.value = e.target.options.id;
                document.editmaps.submit();
            }
        }
    }

EOF;

    // retrieve markers (to add via js)
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

        $inline_extra_js .= sprintf("L.marker([%s], {id: %s, title: '%s'}).addTo(group).bindTooltip('%s').on('click', remove);\n",
                                    $geocode, $id, $name, $name);
    }

    include('../common/includes/db_close.php');

    $inline_extra_js .= <<<EOF

    map.fitBounds(group.getBounds());
}

EOF;

    include('include/config/logging.php');
    print_footer_and_html_epilogue($inline_extra_js, $extra_js);
?>
