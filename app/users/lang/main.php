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
 * Description:    User portal languages management
 *
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

include_once(__DIR__ . '/../../common/includes/validation.php');

$cookieName = 'daloradius_language';

if (array_key_exists($cookieName, $_COOKIE) && !empty(trim($_COOKIE[$cookieName])) &&
    in_array(strtolower(trim($_COOKIE[$cookieName])), array_keys($users_valid_languages))) {
    $selectedLanguage = $_COOKIE[$cookieName];
} else {
    $selectedLanguage = 'en';
    //~ 31536000 = 365 * 24 * 60 * 60 
    setcookie($cookieName, $selectedLanguage, time() + 31536000);
}

// Declare global array with language keys
global $l;

$l = array();

$langFile = __DIR__ . '/' . $selectedLanguage . '.php';

require_once($langFile);

// $langCode can be used in html tag elements like lang and/or xml:lang
$langCode = str_replace("_", "-", $selectedLanguage);

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
