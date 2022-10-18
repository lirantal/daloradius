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

	include('checklogin.php');
	include('opendb.php');
	include('libchart/libchart.php');

	header("Content-type: image/png");

    $chart = new VerticalChart(640, 480);

    $sql = sprintf("SELECT n.shortname, COUNT(DISTINCT(ra.username))
                    FROM %s AS ra, %s AS n
                    WHERE n.nasname=ra.nasipaddress AND (ra.acctstoptime IS NULL OR ra.acctstoptime='0000-00-00 00:00:00')
                    GROUP BY ra.nasipaddress",
                   $configValues['CONFIG_DB_TBL_RADACCT'], $configValues['CONFIG_DB_TBL_RADNAS']);

    $res = $dbSocket->query($sql);
    while($row = $res->fetchRow()) {
        $chart->addPoint(new Point($row[0], $row[1]));
    }
    
    include('closedb.php');
    
    $chart->setTitle("Online users per NAS");
    $chart->render();
?>
