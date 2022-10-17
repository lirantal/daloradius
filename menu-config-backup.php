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
if (strpos($_SERVER['PHP_SELF'], '/menu-config-backup.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Config";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $langCode ?>" lang="<?= $langCode ?>">
<head>
    <title>daloRADIUS :: <?= $m_active ?></title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">

    <link rel="stylesheet" href="css/1.css" media="screen">
    <script src="library/javascript/pages_common.js"></script>
</head>

<body>
    <div id="wrapper">
        <div id="innerwrapper">

<?php
	include_once ("include/menu/menu-items.php");
	include_once ("include/menu/config-subnav.php");
    
    $menu_elements = array(
        "config-backup-managebackups.php" => array(t('button','ManageBackups'), "images/icons/configMaintenance.png"),
        "config-backup-createbackups.php" => array(t('button','CreateBackups'), "images/icons/configMaintenance.png"),
    );
?>      

            <div id="sidebar">
                <h2>Configuration</h2>
                
                <h3>Backup</h3>
                <ul class="subnav">
<?php
                $tabindex = 1;
                foreach ($menu_elements as $href => $items) {
                    list($caption, $src) = $items;
                    printf('<li><a href="%s" title="%s" tabindex="%s"><b>&raquo;</b><img style="border: 0; margin-right: 5px" src="%s">%s</a></li>',
                           $href, strip_tags($caption), $tabindex, $src, $caption);
                    $tabindex++;
                }
?>

                </ul><!-- .subnav -->
            </div><!-- #sidebar -->
