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
                            "home"   => array(),
                            "pref" => array(),
                            "acct"   => array(),
                            "bill"   => array(),
                            "graphs" => array(),
                        );

$allowed_categories = array_keys($cat_subcat_tree);

$basename = basename($_SERVER['PHP_SELF'], ".php");
$tmp = explode("-", $basename);


if (count($tmp) > 1 && !in_array($tmp[0], $allowed_categories)) {
    exit;
}

$detected_category = $tmp[0];
$detected_subcategory = "default";

$sidebar_file = sprintf("include/menu/sidebar/%s/%s.php", $detected_category, $detected_subcategory);
if (file_exists($sidebar_file)) {
    include($sidebar_file);
    menu_print($menu);
}
