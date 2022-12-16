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

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('library/config_read.php');
    
    $log = "visited page: ";

    include_once("lang/main.php");
    include("library/layout.php");

    // parameter validation
    $radiusLineCount = (array_key_exists('radiusLineCount', $_GET) && isset($_GET['radiusLineCount']) &&
                        intval($_GET['radiusLineCount']) > 0)
                     ? intval($_GET['radiusLineCount']) : 50;

    // preg quoted before usage
    $radiusFilter = (array_key_exists('radiusFilter', $_GET) && isset($_GET['radiusFilter']) &&
                     in_array($_GET['radiusFilter'], array( "Auth", "Info", "Error" )))
                  ? $_GET['radiusFilter'] : "";


    // print HTML prologue
    $title = t('Intro','replogsradius.php') . " :: $radiusLineCount Lines Count";
    if (!empty($radiusFilter) && $radiusFilter !== '.+') {
        $title .= " with filter set to " . htmlspecialchars($radiusFilter, ENT_QUOTES, 'UTF-8');
    }
    $help = t('helpPage','replogsradius');
    
    print_html_prologue($title, $langCode);

    include ("menu-reports-logs.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include('library/exten-radius_log.php');
    include_once('include/management/actionMessages.php');

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
