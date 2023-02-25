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
 * Authors:    Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/menu/sidebar.php') !== false) {
    header("Location: ../../index.php");
    exit;
}

$autocomplete = (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                 strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) === "yes");

$cat_subcat_tree = array(
                            "home" => array(),
                            "mng" => array( "batch", "hs", "rad-nas", "rad-usergroup", "rad-groups",
                                            "rad-profiles", "rad-hunt", "rad-attributes", "rad-realms", "rad-ippool", ),
                            "rep" => array( "logs", "stat", "batch", "hb", ),
                            "acct" => array( "plans", "custom", "hotspot", "maintenance", ),
                            "bill" => array( "plans", "rates", "merchant", "history", "invoice", "payments", ),
                            "gis" => array(),
                            "graphs" => array(),
                            "config" => array( "reports", "maint", "operators", "backup", ),
                            "help" => array(),
                        );

$allowed_categories = array_keys($cat_subcat_tree);

$basename = basename($_SERVER['PHP_SELF'], ".php");
$tmp = explode("-", $basename);


if (count($tmp) > 1 && !in_array($tmp[0], $allowed_categories)) {
    exit;
}

$detected_category = $tmp[0];

if ($detected_category == "mng") {
    if ($tmp[1] == "rad" && count($tmp) > 2) {
        if ($tmp[2] == "proxys") {
            $detected_subcategory = "rad-realms";
        } else if ($tmp[2] == "groupcheck" || $tmp[2] == "groupreply") {
            $detected_subcategory = "rad-groups";
        } else {
            $detected_subcategory = "rad-" . $tmp[2];
        }
            
    } else {
        $detected_subcategory = $tmp[1];
    }
} else if ($detected_category == "bill") {
    if ($tmp[1] == "payment" && count($tmp) > 2) {
        $detected_subcategory = "payments";
    } else {
        $detected_subcategory = $tmp[1];
    }
} else {
    $detected_subcategory = $tmp[1];
}

if (!in_array($detected_subcategory, $cat_subcat_tree[$detected_category])) {
    $detected_subcategory = "default";
}

$sidebar_file = sprintf("include/menu/sidebar/%s/%s.php", $detected_category, $detected_subcategory);
if (file_exists($sidebar_file)) {
    include($sidebar_file);
    menu_print($menu);
}




//~ mng-main
//~ mng-users
//~ mng-batch
//~ mng-hs
//~ mng-rad-nas
//~ mng-rad-usergroup
//~ mng-rad-profiles
//~ mng-rad-attributes
//~ mng-rad-realms
//~ mng-rad-ippool

//~ rep-main
//~ rep-general
//~ rep-logs
//~ rep-status
//~ rep-batch
//~ rep-hb

//~ acct-main
//~ acct-general
//~ acct-plans
//~ acct-custom
//~ acct-hs
//~ acct-maint

//~ bill-main
//~ bill-pos
//~ bill-plans
//~ bill-rates
//~ bill-merchant
//~ bill-history
//~ bill-invoice
//~ bill-payments

//~ gis-main
//~ gis-map-view (new)
//~ gis-map-edit (new)

//~ graphs-main

//~ config-main
//~ config-general
//~ config-reports
//~ config-maint
//~ config-operators
//~ config-backup

