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
 * Description:    This script generates and displays a bar graph representing the total number of users.
 *                 It retrieves the user count from the database, creates the graph
 *                 and then renders the latter for display.
 *
 * Authors:	       Filippo Lauria <filippo.lauria@iit.cnr.it>
 *                 Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

    include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', '..', '..', 'common', 'includes', 'config_read.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'functions.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);

    $labels = [ "" ];
    $values = [ count_users($dbSocket) ];

    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);

    // draw the graph
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_LIBRARY'], 'jpgraph', 'jpgraph.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_LIBRARY'], 'jpgraph', 'jpgraph_bar.php' ]);

    // create the graph
    $graph = new Graph(1024, 384, 'auto');
    $graph->SetScale('textint');
    $graph->clearTheme();
    $graph->SetFrame(false);
    $graph->SetTickDensity(TICKD_SPARSE, TICKD_SPARSE);
    $graph->img->SetMargin(110, 20, 20, 110);
    $graph->title->Set(strtolower(t('all', 'TotalUsers')));

    // setup x-axis
    $graph->xaxis->title->SetMargin(60);
    $graph->xaxis->SetLabelAngle(60);
    $graph->xaxis->SetTickLabels($labels);
    $graph->xaxis->HideLastTickLabel();

    // setup y-axis
    $graph->yaxis->title->Set(strtolower(t('all', 'Users')));
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
