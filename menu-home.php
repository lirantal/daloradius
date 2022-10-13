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
$this_file = '/menu-home.php';
if (strpos($_SERVER['PHP_SELF'], $this_file) !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

if (preg_match("/^\/menu\-([a-z]+)\.php$/", $this_file, $matches) !== false) {
    $title = ucfirst($matches[1]);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $langCode ?>" lang="<?= $langCode ?>">
<head>
    <title>daloRADIUS <?= (isset($title)) ? ":: $title" : "" ?></title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="css/1.css" media="screen">
</head>
 
<body>

    <div id="wrapper">
        <div id="innerwrapper">
		
<?php
	$m_active = "Home";
	include_once("include/menu/menu-items.php");
	include_once("include/menu/home-subnav.php");
    
    $status_subnav = array(
                            'rep-stat-server.php' => t('button','ServerStatus'),
                            'rep-stat-services.php' => t('button','ServicesStatus'),
                            'rep-lastconnect.php' => t('button','LastConnectionAttempts')
                          );
                          
    $logs_subnav = array(
                            'rep-logs-radius.php' => t('button','RadiusLog'),
                            'rep-logs-system.php' => t('button','SystemLog')
                        );
    
?>      

            <div id="sidebar">
                <h2>Home</h2>
                
                <h3>Status</h3>
                <ul class="subnav">
<?php
foreach ($status_subnav as $href => $label) {
    printf('<li><a href="%s" title="%s"><b>&raquo;</b>%s</a></li>', $href, strip_tags($label), $label);
}
?>

                </ul><!-- .subnav -->

                <h3>Logs</h3>
                <ul class="subnav">
<?php
foreach ($logs_subnav as $href => $label) {
    printf('<li><a href="%s" title="%s"><b>&raquo;</b>%s</a></li>', $href, strip_tags($label), $label);
}
?>

                </ul><!-- .subnav -->
	
                <h3>Support</h3>

                <p class="news">
                    daloRADIUS<br>
                    RADIUS Management 
                    <a target="_blank" href="https://github.com/lirantal/daloradius" class="more">Read More &raquo;</a>
                </p>
            </div><!-- #sidebar -->

