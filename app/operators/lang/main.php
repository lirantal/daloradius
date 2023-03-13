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
 * Description:    Main language file control
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/lang/main.php') !== false) {
    header("Location: ../index.php");
    exit;
}

// Load language dictionary according to:
//
// 1. Load default language.
// 2. Try to load the language according to the configuration. If the language file does
//    not exists, or it's the default one, do nothing.
//    If it's loaded, the missing dictionary entries will remain the ones from
//    the default language.

// Load configuration
include_once(__DIR__ . '/../../common/includes/config_read.php');

// Declare global array with language keys
global $l;

$l = array();

// Load default language: English
$langDefault = 'en';

$langFile = __DIR__ . '/' . $langDefault . '.php';

require_once($langFile);

// Try to load language according to configuration
$langConf = $configValues['CONFIG_LANG'];

if($langConf != $langDefault) {
    $langFileConf = __DIR__ . '/' . $langConf . '.php';

    if(is_file($langFileConf)) {
        require_once($langFileConf);

        $langFile = $langFileConf;
    }
}

// $langCode can be used in html tag elements like lang and/or xml:lang
$langCode = str_replace("_", "-", pathinfo($langFile, PATHINFO_FILENAME));

// Translation function
function t($a, $b = null, $c = null, $d = null) {
    global $l;

    // added a static null at the end of the $arr
    $arr = array( $a, $b, $c, $d, null );

    // dictionary will be modified by the for loop
    $t = $l;

    // count($arr) - 1 == 4
    for ($i = 0; $i < 4; $i++) {

        $current = $arr[$i];
        $next = $arr[$i+1];


        if ($next == null && isset($t[$current])) {
            return $t[$current];
        }

        $t = $t[$current];
    }

    return "Lang Error!";
}

?>
