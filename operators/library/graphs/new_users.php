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
 * Description:    this extension creates a pie chart of new users
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Maria Del Prete <filippo.delprete@gmail.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include('../checklogin.php');
    include('../../lang/main.php');
    include('../validation.php');

    // we validate starting and ending dates
    $startdate = (array_key_exists('startdate', $_GET) && !empty(trim($_GET['startdate'])) &&
                  preg_match(DATE_REGEX, trim($_GET['startdate']), $m) !== false &&
                  checkdate($m[2], $m[3], $m[1]))
               ? trim($_GET['startdate']) : "";

    $enddate = (array_key_exists('enddate', $_GET) && !empty(trim($_GET['enddate'])) &&
                preg_match(DATE_REGEX, trim($_GET['enddate']), $m) !== false &&
                checkdate($m[2], $m[3], $m[1]))
             ? trim($_GET['enddate']) : "";

    include('../../../common/includes/db_open.php');

    $limit = 24;

    $sql_WHERE_pieces = array();
    if (!empty($enddate)) {
        $sql_WHERE_pieces[] = sprintf("CreationDate <= '%s'", $dbSocket->escapeSimple($enddate));
    }

    if (!empty($startdate)) {
        $sql_WHERE_pieces[] = sprintf("CreationDate >= '%s'", $dbSocket->escapeSimple($startdate));
    }

    $sql_WHERE = (count($sql_WHERE_pieces) > 0) ? " WHERE " . implode(" AND ", $sql_WHERE_pieces) : "";

    $sql = sprintf("SELECT COUNT(*), CONCAT(YEAR(CreationDate), ' ', LEFT(MONTHNAME(CreationDate), 3)) AS period,
                           CAST(CONCAT(YEAR(CreationDate), '-', MONTH(CreationDate), '-01') AS DATE) AS month
                      FROM %s", $configValues['CONFIG_DB_TBL_DALOUSERINFO'])
                 . $sql_WHERE . " GROUP BY month ORDER BY month";
    $res = $dbSocket->query($sql);

    $labels = array();
    $values = array();

    while ($row = $res->fetchRow()) {
        $values[] = intval($row[0]);
        $labels[] = strval($row[1]);
    }

    include('../../../common/includes/db_close.php');

    $title = "new users amount";
    $xtitle = "per-month distribution";
    $ytitle = "users";


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
    $plot->value->SetFormat('%d');
    $plot->value->SetAngle(45);

    // add the plot to the graph
    $graph->Add($plot);

    // display the graph
    $graph->Stroke();

?>
