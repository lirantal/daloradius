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
 * Authors:        Neville <nev@itsnev.co.uk>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include('../checklogin.php');

    include('../../../common/includes/db_open.php');

    $sql = sprintf("SELECT n.shortname, COUNT(DISTINCT(ra.username))
                      FROM %s AS ra, %s AS n
                     WHERE n.nasname = ra.nasipaddress
                       AND (ra.acctstoptime IS NULL OR ra.acctstoptime = '0000-00-00 00:00:00')
                     GROUP BY ra.nasipaddress",
                   $configValues['CONFIG_DB_TBL_RADACCT'], $configValues['CONFIG_DB_TBL_RADNAS']);

    $res = $dbSocket->query($sql);

    $values = array();
    $labels = array();

    while($row = $res->fetchRow()) {
        $labels[] = strval($row[0]);
        $values[] = intval($row[1]);
    }

    include('../../../common/includes/db_close.php');

    include_once('../../../common/library/jpgraph/jpgraph.php');
    include_once('../../../common/library/jpgraph/jpgraph_bar.php');

    // create the graph
    $graph = new Graph(1024, 384, 'auto');
    $graph->SetScale('textint');
    $graph->clearTheme();
    $graph->SetFrame(false);
    $graph->SetTickDensity(TICKD_SPARSE, TICKD_SPARSE);
    $graph->img->SetMargin(110, 20, 20, 110);
    $graph->title->Set("per-NAS online users");

    // setup x-axis
    $graph->xaxis->title->Set("NAS");
    $graph->xaxis->title->SetMargin(60);
    $graph->xaxis->SetLabelAngle(60);
    $graph->xaxis->SetTickLabels($labels);
    $graph->xaxis->HideLastTickLabel();

    // setup y-axis
    $graph->yaxis->title->Set("users");
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
