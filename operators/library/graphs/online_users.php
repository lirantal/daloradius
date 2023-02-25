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
 * Description:    this extension creates a pie chart of online users
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include('../checklogin.php');

    include('../../../common/includes/db_open.php');

    // getting total users
    $sql = sprintf("SELECT COUNT(DISTINCT(username)) FROM %s", $configValues['CONFIG_DB_TBL_RADCHECK']);
    $res = $dbSocket->query($sql);
    $totalUsers = $res->fetchrow()[0];

    // get total users online
    $sql = sprintf("SELECT COUNT(DISTINCT(username))
                    FROM %s
                    WHERE AcctStopTime IS NULL
                       OR AcctStopTime = '0000-00-00 00:00:00'", $configValues['CONFIG_DB_TBL_RADACCT']);
    $res = $dbSocket->query($sql);
    $totalUsersOnline = $res->fetchrow()[0];

    include('../../../common/includes/db_close.php');

    $values = array();
    $labels = array();

    if ($totalUsers > 0) {
        $totalUsersOffline = $totalUsers - $totalUsersOnline;

        $value = intval($totalUsersOffline);
        $labels[] = sprintf("%d user(s) offline", $value);
        $values[] = $value;

        if ($totalUsersOnline > 0) {
            $value = intval($totalUsersOnline);
            $labels[] = sprintf("%d user(s) online", $value);
            $values[] = $value;
        }
    }

    include_once('../../../common/library/jpgraph/jpgraph.php');
    include_once('../../../common/library/jpgraph/jpgraph_pie.php');

    $graph = new PieGraph(1024, 768);
    $graph->SetShadow();
    $graph->legend->SetLayout(LEGEND_VERT);
    $graph->legend->SetPos(0.1, 0.1);
    $graph->clearTheme();
    $graph->SetFrame(false);
    $graph->title->Set("online/offline users");

    $plot = new PiePlot($values);
    $plot->SetTheme("water");
    $plot->SetLegends($labels);

    $graph->Add($plot);
    $graph->Stroke();

?>
