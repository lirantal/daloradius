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
 * Authors:    Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include('library/checklogin.php');
    $login_user = $_SESSION['login_user'];

    include_once('../common/includes/config_read.php');
    include_once("lang/main.php");
    include("../common/includes/layout.php");

    include('../common/includes/functions.php');
    require_once __DIR__ . '/library/portal_pages_pdo.php';
    include_once('../common/includes/config_read.php');
    $message = '';
    $portalPdo = null;
    try {
        $portalPdo = dalo_portal_handle($configValues);
        $message = get_message($portalPdo, "support")["content"];
    } catch (Throwable $exception) {
        http_response_code(503);
        $message = '<p>Portal message unavailable</p>';
    } finally { $portalPdo = null; }
    
    // print HTML prologue
    $title = "Help";
    print_html_prologue($title, $langCode);

    $title = "Welcome to the FiloRADIUS Help page";
    print_title_and_help($title, '');

    echo $message;

    include('include/config/logging.php');

    print_footer_and_html_epilogue();
