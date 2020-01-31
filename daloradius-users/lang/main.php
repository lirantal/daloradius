<?php
/*
 *******************************************************************************
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
 *******************************************************************************
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *******************************************************************************
 */
$langDir = dirname(__FILE__);

$langList = array_filter(scandir($langDir), function($fileName) {
    global $langDir;
    
    $skipList = array(
        ".", "..", "main.php",
        "ro.php" // FIXME ro.php is currently broken and needs a fix...
    );
    
    if (in_array($fileName, $skipList)) {
        return false;
    }
    
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    return !($ext == "php" and is_file("$langDir/$fileName"));
});

include_once("$langDir/../library/daloradius.conf.php");
$langFile = $configValues["CONFIG_LANG"] . ".php";

if (!in_array($langFile, $langList)) {
    $langFile = "en.php"; // default language is english
}

// $langCode can be used in html tag elements like lang and/or xml:lang
$langCode = str_replace("_", "-", pathinfo($langFile, PATHINFO_FILENAME));
include("$langDir/$langFile");

// Translation function
function t($a, $b = null, $c = null, $d = null) {
    global $l;

    $t = null;

    if($b === null) {
        $t = isset($l[$a]) ? $l[$a] : null;
    }
    else if($c === null) {
        $t = isset($l[$a][$b]) ? $l[$a][$b] : null;
    }
    else if($d === null) {
        $t = isset($l[$a][$b][$c]) ? $l[$a][$b][$c] : null;
    }
    else {
        $t = isset($l[$a][$b][$c][$d]) ? $l[$a][$b][$c][$d] : null;
    }

    if($t === null) {
        $t = "Lang Error!";
    }

    return $t;
}

?>
