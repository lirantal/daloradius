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
 * Description:    The purpose of this extension is to handle exports of different
 *                 kinds like CSV andother formats to the user's browser so that
 *                 they can download a local copy of the tables listing mostly
 * 
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include('../../library/checklogin.php');

    $redirect = (array_key_exists('PREV_LIST_PAGE', $_SESSION) && !empty(trim($_SESSION['PREV_LIST_PAGE'])))
              ? trim($_SESSION['PREV_LIST_PAGE'])
              : "../../index.php";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('accounts', $_POST) && !empty($_POST['accounts']) && is_array($_POST['accounts'])) {
            
            $content = "";
            foreach ($_POST['accounts'] as $account) {
                $content .= implode(",", $account) . "\r\n";
            }
            
            $filename_prefix = (array_key_exists('batch_name', $_POST) && !empty(trim($_POST['batch_name'])) &&
                                preg_match("/^[\w\-. ]+$/", trim($_POST['batch_name'])) !== false)
                             ? trim($_POST['batch_name']) : "users";
            
            header("Content-type: text/csv; charset=utf-8");
            header(sprintf("Content-disposition: csv; filename=%s__%s.csv; size=%s", $filename_prefix, date("Ymd"), strlen($content)));
            print $content;
            exit;
        }
    }
    
    header("Location: $redirect");
?>
