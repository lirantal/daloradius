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
 * Description:    this extension creates a pie chart of
 *                 the comparison of hotspots per unique users
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include('../checklogin.php');

    // validate parameters
    $category = (array_key_exists('category', $_GET) && isset($_GET['category']) &&
                 in_array(strtolower(trim($_GET['category'])), array( "avg_session_time", "total_session_time", "login_hits", "unique_users" )))
              ? strtolower(trim($_GET['category'])) : "unique_users";

     switch ($category) {
        case "total_session_time":
            $title = "per-hotspot total session time";
            $dbfield = "SUM(ra.acctsessiontime)";
            $label_format = "%s (%s seconds)";
            break;

        case "avg_session_time":
            $title = "per-hotspot average session time";
            $dbfield = "AVG(ra.acctsessiontime)";
            $label_format = "%s (%s seconds)";
            break;

        case "login_hits":
            $title = "per-hotspot login hits";
            $dbfield = "COUNT(ra.radacctid)";
            $label_format = "%s (%s login hits)";
            break;

        default:
        case "unique_users":
            $title = "per-hotspot unique users";
            $dbfield = "COUNT(DISTINCT(ra.username))";
            $label_format = "%s (%s unique users)";
            break;
    }

    include('../../../common/includes/db_open.php');

    $values = array();
    $labels = array();

    $sql = sprintf("SELECT hs.name AS hotspot_name, %s AS category
                      FROM %s AS ra, %s AS hs
                     WHERE ra.calledstationid = hs.mac
                     GROUP BY hs.name
                     ORDER BY category DESC", $dbfield, $configValues['CONFIG_DB_TBL_RADACCT'],
                                              $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
    $res = $dbSocket->query($sql);

    while ($row = $res->fetchRow()) {
        $value = intval($row[1]);
        $labels[] = sprintf($label_format, $row[0], $value);
        $values[] = $value;

    }

    include('../../../common/includes/db_close.php');

    include_once('../../../common/library/jpgraph/jpgraph.php');
    include_once('../../../common/library/jpgraph/jpgraph_pie.php');

    $graph = new PieGraph(1024, 768);
    $graph->SetShadow();
    $graph->legend->SetLayout(LEGEND_VERT);
    $graph->legend->SetPos(0.1, 0.1);
    $graph->clearTheme();
    $graph->SetFrame(false);
    $graph->title->Set($title);

    $plot = new PiePlot($values);
    $plot->SetTheme("water");
    $plot->SetLegends($labels);

    $graph->Add($plot);
    $graph->Stroke();

?>
