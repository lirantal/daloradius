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
if (strpos($_SERVER['PHP_SELF'], '/menu-reports-hb.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $langCode ?>" lang="<?= $langCode ?>">
<head>
    <title>daloRADIUS :: Heartbeat</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    
    <link rel="stylesheet" href="css/1.css" media="screen">
    <link rel="stylesheet" href="css/form-field-tooltip.css" media="screen">
    
    <script src="library/javascript/pages_common.js"></script>
    <script src="library/javascript/rounded-corners.js"></script>
    <script src="library/javascript/form-field-tooltip.js"></script>

</head>

<body>
    <div id="wrapper">
        <div id="innerwrapper">

<?php
	$m_active = "Reports";
    include_once("include/menu/menu-items.php");
	include_once("include/menu/reports-subnav.php");
?>      

            <div id="sidebar">
                <h2>Heartbeat</h2>
                
                <h3>Dashboard</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= strip_tags(t('button','Dashboard')) ?>" href="rep-hb-dashboard.php">
                            <b>&raquo;</b><?= t('button','Dashboard') ?>
                        </a>
                    </li>
                </ul>
                
                <br><br>
            </div>

