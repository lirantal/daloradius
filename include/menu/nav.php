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

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/menu/nav.php') !== false) {
    header("Location: ../../index.php");
    exit;
}

$nav = array(
                "home"   => array( 'Home', 'index.php', ),
                "mng"    => array( 'Managment', 'mng-main.php', ),
                "rep"    => array( 'Reports', 'rep-main.php', ),
                "acct"   => array( 'Accounting', 'acct-main.php', ),
                "bill"   => array( 'Billing', 'bill-main.php', ),
                "gis"    => array( 'Gis', 'gis-main.php', ),
                "graphs" => array( 'Graphs', 'graphs-main.php', ),
                "config" => array( 'Config', 'config-main.php', ),
                "help"   => array( 'Help', 'help-main.php', ),    
            );

// detect category from the PHP_SELF name
$basename = basename($_SERVER['PHP_SELF']);
$detect_category = substr($basename, 0, strpos($basename, '-'));
if (!in_array($detect_category, array_keys($nav))) {
    $detect_category = "home";
}

// draw nav elements
echo '<ul id="nav">';

foreach ($nav as $category => $arr) {
    list($label, $href) = $arr;
    
    $class = ($detect_category === $category) ? 'active' : '';
    $label = htmlspecialchars(strip_tags(trim(t('menu', $label))), ENT_QUOTES, 'UTF-8');
    
    printf('<li><a class="%s" href="%s" title="%s">%s</a></li>', $class, urlencode($href), $label, $label);

}

echo '</ul><!-- #nav -->' . "\n";

?>
