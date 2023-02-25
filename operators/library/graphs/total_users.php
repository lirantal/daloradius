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
 * Description:    this extension is used to count all the records
 *                 (or table entries) in the radcheck table
 *
 * Authors:	       Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include('../checklogin.php');

    include('../../../common/includes/db_open.php');

    $sql = sprintf("SELECT COUNT(DISTINCT(username)) FROM %s", $configValues['CONFIG_DB_TBL_RADCHECK']);
    $res = $dbSocket->query($sql);

    $labels = array( "" );
    $values = array( intval($res->fetchRow()[0]) );

    include('../../../common/includes/db_close.php');

    // draw the graph
    include_once('../../../common/library/jpgraph/jpgraph.php');
    include_once('../../../common/library/jpgraph/jpgraph_bar.php');

    // create the graph
    $graph = new Graph(1024, 384, 'auto');
    $graph->SetScale('textint');
    $graph->clearTheme();
    $graph->SetFrame(false);
    $graph->SetTickDensity(TICKD_SPARSE, TICKD_SPARSE);
    $graph->img->SetMargin(110, 20, 20, 110);
    $graph->title->Set("total users");

    // setup x-axis
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
