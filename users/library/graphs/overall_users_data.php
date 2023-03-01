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
 * Description:    this graph extension produces a query of the overall download/upload/login
 *                 made by the logged in user on a daily, monthly and yearly basis.
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include('../checklogin.php');

    $username = (array_key_exists('login_user', $_SESSION) && !empty($_SESSION['login_user']))
              ? $_SESSION['login_user'] : "";


    if (!empty($username)) {

        include('../../../common/includes/db_open.php');

        // validate parameters
        $category = (array_key_exists('category', $_GET) && isset($_GET['category']) &&
                    in_array(strtolower(trim($_GET['category'])), array( "upload", "download", "login" )))
                 ? strtolower(trim($_GET['category'])) : "download";

        switch ($category) {
            case "login":
                $dbfield = "COUNT(AcctStartTime)";
                break;

            case "upload":
                $dbfield = "SUM(AcctInputOctets)";
                break;

            default:
            case "download":
                $dbfield = "SUM(AcctOutputOctets)";
                break;
        }

        $type = (array_key_exists('type', $_GET) && isset($_GET['type']) &&
                 in_array(strtolower($_GET['type']), array( "daily", "monthly", "yearly" )))
              ? strtolower($_GET['type']) : "daily";

        $size = (array_key_exists('size', $_GET) && isset($_GET['size']) &&
                 in_array(strtolower($_GET['size']), array( "gigabytes", "megabytes" )))
              ? strtolower($_GET['size']) : "megabytes";

        // used for presentation purpose
        $size_division = array("gigabytes" => 1073741824, "megabytes" => 1048576);

        $limit = 36;
        $labels = array();
        $values = array();


        switch ($type) {
            case "yearly":
                $selected_param = "year";
                $sql = "SELECT YEAR(AcctStartTime) AS year, %s AS category
                          FROM %s
                         WHERE username='%s' AND AcctStopTime>0
                         GROUP BY year ORDER BY year DESC LIMIT %s";
                break;

            case "monthly":
                $selected_param = "month";
                $sql = "SELECT CONCAT(LEFT(MONTHNAME(AcctStartTime), 3), ' (', YEAR(AcctStartTime), ')'),
                               %s AS category,
                               CAST(CONCAT(YEAR(AcctStartTime), '-', MONTH(AcctStartTime), '-01') AS DATE) AS month
                          FROM %s WHERE username='%s'  AND AcctStopTime>0
                         GROUP BY month ORDER BY month DESC LIMIT %s";
                break;

            default:
            case "daily":
                $selected_param = "day";
                $sql = "SELECT DATE(AcctStartTime) AS day, %s AS category
                          FROM %s
                         WHERE username='%s' AND AcctStopTime>0
                         GROUP BY day ORDER BY day DESC LIMIT %s";
                break;
        }

        $sql = sprintf($sql, $dbfield, $configValues['CONFIG_DB_TBL_RADACCT'], $dbSocket->escapeSimple($username), $limit);

        //~ echo $sql;
        //~ exit;


        $res = $dbSocket->query($sql);
        while ($row = $res->fetchRow()) {
            if ($category == "login") {
                $values[] = intval($row[1]);
            } else {
                $values[] = number_format(floatval($row[1] / $size_division[$size]), 1, ".", "");
            }

            $labels[] = strval($row[0]);
        }

        if ($category == "login") {
            $title = sprintf("login statistics for user %s", $username);
            $ytitle = sprintf("Login count");
            $format = '%d';
        } else {
            $title = sprintf("traffic in %s by user %s", $category, $username);
            $ytitle = sprintf("%s %sed", ucfirst($size), $category);
            $format = '%01.1f';
        }
        $xtitle = ucfirst($type) . " distribution";

        include_once('../../../common/library/jpgraph/jpgraph.php');
        include_once('../../../common/library/jpgraph/jpgraph_bar.php');

        // create the graph
        $graph = new Graph(1024, 384, 'auto');
        $graph->SetScale('textint');
        $graph->clearTheme();
        $graph->SetFrame(false);
        $graph->SetTickDensity(TICKD_SPARSE, TICKD_SPARSE);
        $graph->img->SetMargin(110, 20, 20, 110);
        $graph->title->Set($title);

        // setup x-axis
        $graph->xaxis->title->Set($xtitle);
        $graph->xaxis->title->SetMargin(60);
        $graph->xaxis->SetLabelAngle(60);
        $graph->xaxis->SetTickLabels($labels);
        $graph->xaxis->HideLastTickLabel();

        // setup y-axis
        $graph->yaxis->title->Set($ytitle);
        $graph->yaxis->title->SetMargin(40);
        $graph->yaxis->SetLabelAngle(45);
        $graph->yaxis->scale->SetGrace(25);

        // create the linear plot
        $plot = new BarPlot($values);
        $plot->value->Show();
        $plot->value->SetFormat($format);
        $plot->value->SetAngle(45);

        // add the plot to the graph
        $graph->Add($plot);

        // display the graph
        $graph->Stroke();

        include('../../../common/includes/db_close.php');
    }
