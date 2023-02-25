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
 *  Description:   the purpose of this extension is to handle CSV exports to the user's desktop.
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

include('../../library/checklogin.php');

if (isset($_SESSION['export_items']) && isset($_SESSION['export_query'])) {
    $output = "";

    include_once('../../../common/includes/db_open.php');

    $sql = $_SESSION['export_query'];
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();

    if ($numrows > 0) {

        // this is the output title and header
        if (isset($_SESSION['export_title']) && !empty(trim($_SESSION['export_title']))) {
            $output .= sprintf("# %s\n", trim($_SESSION['export_title']));

            // once used we unset it
            unset($_SESSION['export_title']);

        }

        $output .= implode(", ", $_SESSION['export_items']) . "\n";

        // this is the remaining part of the output content
        while($row = $res->fetchRow()) {
            $output .= implode(",", $row) . "\n";
        }
    }

    include_once('../../../common/includes/db_close.php');


    if (!empty($output)) {
        header("Content-type: text/csv");
        header(sprintf("Content-disposition: attachment; filename=daloradius__%s.csv; size=%s", date("Ymd"), strlen($output)));
        print $output;
    }

    // once finished we unset them
    unset($_SESSION['export_items']);
    unset($_SESSION['export_query']);

}
