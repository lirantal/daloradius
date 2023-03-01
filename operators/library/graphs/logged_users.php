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
 * Description:    if called specifing only year and month, this script
 *                 produces a barchart containing min/max number of user
 *                 accounted on every day of the specified month;
 *                 if a full date is specified, the barchart
 *                 reports the per-hour distribution of users accounted
 *                 on the specified date.
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Tiago Ratto <tiagoratto@gmail.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

include('../checklogin.php');

$day = (array_key_exists('day', $_GET) && isset($_GET['day']) &&
        intval($_GET['day']) > 0 && intval($_GET['day']) <= 31)
     ? intval($_GET['day']) : "";

$current_month = intval(date('m'));
$month = (array_key_exists('month', $_GET) && isset($_GET['month']) &&
          intval($_GET['month']) > 0 && intval($_GET['month']) <= 12)
       ? intval($_GET['month']) : $current_month;

$current_year = intval(date('Y'));
$year = (array_key_exists('year', $_GET) && isset($_GET['year']) &&
         intval($_GET['year']) > 1970 && intval($_GET['year']) <= $current_year)
      ? intval($_GET['year']) : $current_year;



include('../../../common/includes/db_open.php');

include_once('../../../common/library/jpgraph/jpgraph.php');
include_once('../../../common/library/jpgraph/jpgraph_bar.php');

// pre-create the graph
$graph = new Graph(1024, 384, 'auto');
$graph->SetScale('textint');
$graph->clearTheme();
$graph->SetFrame(false);
$graph->SetTickDensity(TICKD_SPARSE, TICKD_SPARSE);
$graph->img->SetMargin(110, 20, 20, 110);
$graph->title->SetMargin(20);

// pre-set x-axis
$graph->xaxis->title->SetMargin(60);
$graph->xaxis->SetLabelAngle(60);
$graph->xaxis->HideLastTickLabel();

// pre-set y-axis
$graph->yaxis->title->SetMargin(40);
$graph->yaxis->SetLabelAngle(45);
$graph->yaxis->scale->SetGrace(25);
$graph->yaxis->title->Set("accounted users");

if (!empty($day)) {
    $date_obj = new DateTime();
    $date_obj->setDate($year, $month, $day);
    $date_str = $date_obj->format('Y-m-d');

    // we get a table containing starting_day, starting_hour and ending_day, ending_hour
    $sql = sprintf("SELECT DATE(acctstarttime) AS starting_day, HOUR(acctstarttime) AS starting_hour,
                           HOUR(DATE_ADD(acctstoptime, INTERVAL 1 HOUR)) AS ending_day, DATE(acctstoptime) AS ending_hour
                      FROM %s
                     WHERE acctstarttime <= '%s'
                       AND (acctstoptime >= '%s' OR (acctsessiontime = 0 AND acctinputoctets = 0 AND acctoutputoctets = 0))",
                   $configValues['CONFIG_DB_TBL_RADACCT'], $date_str, $date_str);

    $result = $dbSocket->query($sql);

    $values = array();
    while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {

        // if starting day == ending day, we need to count on a per-hour basis up to ending_hour,
        // otherwise we consider the whole day up to 23:59
        $ending_hour = ($row['starting_day'] == $row['ending_day']) ? intval($row['ending_hour']) : 23;

        for ($i = $row['starting_hour']; $i <= $ending_hour; $i++) {
            $label = $i;
            $values[$label] = (in_array($label, array_keys($values))) ? $values[$label] + 1 : 1;
        }
    }

    // we set labels and fill empty $values spots
    $labels = array();

    for ($i = 0; $i <= 23; $i++) {
        if (!array_key_exists($i, $values)) {
            $values[$i] = 0;
        }
        $labels[$i] = sprintf("%s:00-%s:59", $i, $i);
    }

    // we sort the values
    ksort($values);

    // set graph title
    $graph_title = sprintf("hour distribution of users accounted on %s", $date_str);

    $xtitle = "time slot";

    // create the linear plot
    $plot = new BarPlot($values);

    $plot->value->SetFormat('%d');
    $plot->value->Show();
    $plot->value->SetAngle(45);

} else {
    // setup starting and ending dates
    $startdate_obj = new DateTime();
    $startdate_obj->setDate($year, $month, 1);
    $startdate_obj->setTime(0, 0);
    $startdate_str = $startdate_obj->format('Y-m-d');

    $enddate_obj = clone $startdate_obj;
    $enddate_obj->add(new DateInterval("P1M"));
    $enddate_str = $enddate_obj->format('Y-m-d');

    $tot_values = array();
    $min_values = array();
    $max_values = array();

    // iterate through each day of the selected interval
    for ($dt_obj = $startdate_obj; $dt_obj <= $enddate_obj; $dt_obj->modify('+1 day')) {
        $date = $dt_obj->format('Y-m-d');

        $sql = sprintf("SELECT HOUR(acctstarttime) AS h, COUNT(DISTINCT(radacctid)) FROM %s
                         WHERE DATE(acctstarttime) <= '%s'
                           AND (DATE(acctstoptime) >= '%s'OR (acctsessiontime = 0 AND acctinputoctets = 0 AND acctoutputoctets = 0))
                         GROUP BY h", $configValues['CONFIG_DB_TBL_RADACCT'], $date, $date);
        $result = $dbSocket->query($sql);

        while ($row = $result->fetchRow()) {
            $counter = intval($row[1]);

            // populate data arrays
            $tot_values[$date] = (array_key_exists($date, $tot_values)) ? $tot_values[$date] + $counter : $counter;

            if (!array_key_exists($date, $min_values)) {
                $min_values[$date] = $counter;
            } else {
                if ($min_values[$date] > $counter) {
                    $min_values[$date] = $counter;
                }
            }

            if (!array_key_exists($date, $max_values)) {
                $max_values[$date] = $counter;
            } else {
                if ($max_values[$date] < $counter) {
                    $max_values[$date] = $counter;
                }
            }
        }
    }

    ksort($tot_values);
    ksort($min_values);
    ksort($max_values);

    $labels = array();
    foreach ($tot_values as $label => $value) {
        $labels[] = sprintf("%s\n(%s)", $label, $value);
    }

    // finish setting up graph
    $graph_title = sprintf("min/max per-day accounted users from %s to %s", $startdate_str, $enddate_str);

    $xtitle = "time slot (total hits)";

    // setup plots
    $bplot_max = new BarPlot(array_values($max_values));
    $bplot_min = new BarPlot(array_values($min_values));

    $bplot_min->value->Show();
    $bplot_min->value->SetFormat('%d');
    $bplot_min->value->SetAngle(45);

    $bplot_max->value->Show();
    $bplot_max->value->SetFormat('%d');
    $bplot_max->value->SetAngle(45);

    $plot = new GroupBarPlot(array($bplot_min, $bplot_max));
}

// finish setting up graph
$graph->title->Set($graph_title);

// set x-axis labels
$graph->xaxis->SetTickLabels($labels);
$graph->xaxis->title->Set($xtitle);

// add the plot to the graph
$graph->Add($plot);

// display the graph
$graph->Stroke();

include('../../../common/includes/db_close.php');

?>
