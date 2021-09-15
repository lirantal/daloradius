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
 * Description:
 *		Main language file control
 *
 * Authors:
 *      Liran Tal <liran@enginx.com>
 *
 *******************************************************************************
 */

// Load language dictionary according to:
//
// 1. Load default language.
// 2. Try to load the language according to configuration. If the language file does
//    not exists, or it's the default one, do nothing.
//    If it's loaded, the missing dictionary entries will remain the ones from
//    the default language.

// Load configuration
require_once(__DIR__ . '/../library/daloradius.conf.php');

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
