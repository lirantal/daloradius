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
    include_once('../common/includes/config_read.php');
    
    $log = "visited page: ";

    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // parameter validation
    $count = (array_key_exists('count', $_GET) && isset($_GET['count']) && intval($_GET['count']) > 0)
           ? intval($_GET['count']) : 50;

    // preg quoted before usage
    $filter = (array_key_exists('filter', $_GET) && isset($_GET['filter'])) ? $_GET['filter'] : "";


    // print HTML prologue
    $title = t('Intro','replogsboot.php');
    $help = t('helpPage','replogsboot');
    
    print_html_prologue($title, $langCode);

    $title .= sprintf(" :: %d Lines Count", $count);
    if (!empty($filter)) {
        $title .= " with filter set to " . htmlspecialchars($filter, ENT_QUOTES, 'UTF-8');
    }

    


    print_title_and_help($title, $help);

    include('library/extensions/boot_log.php');
    include_once('include/management/actionMessages.php');
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
