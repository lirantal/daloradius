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
 * Authors:    Liran Tal <liran@enginx.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

include('../checklogin.php');


// username and divContainer are required
if (array_key_exists('hotspot', $_GET) && isset($_GET['hotspot']) &&
    array_key_exists('divContainer', $_GET) && isset($_GET['divContainer'])) {
    
    // divContainer id must begin with a letter ([A-Za-z]) and may be followed by any number of letters,
    // digits ([0-9]), hyphens ("-"), underscores ("_").
    if (!preg_match("/[A-Za-z][A-Za-z0-9_-]+/", $_GET['divContainer'])) {
        exit;
    }
    
    $divContainer = $_GET['divContainer'];
    $hotspot = str_replace("%", "", trim($_GET['hotspot']));
    
    // at the moment we have only one action
    $action = "";
    if (isset($_GET['retHotspotGeneralStat'])) {
        $action = 'retHotspotGeneralStat';
    } else {
        $action = 'retHotspotGeneralStat';
    }

    include('../../../common/includes/db_open.php');
    include_once('../../include/management/pages_common.php');
    
    switch ($action) {
        
        default:
        case 'retHotspotGeneralStat':
            $sql = sprintf("SELECT COUNT(ra.radacctid) AS totalhits,
                                   SUM(ra.AcctInputOctets) AS sumInputOctets,
                                   SUM(ra.AcctOutputOctets) AS sumOutputOctets
                              FROM %s AS ra JOIN %s AS hs ON ra.calledstationid=hs.mac
                             WHERE hs.name='%s'
                             GROUP BY hs.name",
                           $configValues['CONFIG_DB_TBL_RADACCT'], $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'],
                           $dbSocket->escapeSimple($hotspot));
                           
            $res = $dbSocket->query($sql);
            $row = $res->fetchRow();
            
            list( $totalhits, $upload, $download ) = $row;
            
            if (empty($totalhits)) {
                $totalhits = "(n/a)";
            } else {
                $totalhits = intval($totalhits);
                
                if ($upload < 0) {
                    $upload = 0;
                }
            }
            
            if (empty($upload)) {
                $upload = "(n/a)";
            } else {
                $upload = intval($upload);
                
                if ($upload < 0) {
                    $upload = 0;
                }
                
                $upload = toxbyte($upload);
            }
        
            if (empty($download)) {
                $download = "(n/a)";
            } else {
                $download = intval($download);
                
                if ($download < 0) {
                    $download = 0;
                }
                
                $download = toxbyte($download);
            }
            
            echo <<<EOF
    var divContainer = document.getElementById('$divContainer');
    divContainer.innerHTML = '<span style="font-weight: normal">Total Uploads:</span> $upload';
    divContainer.innerHTML += '<br>';
    divContainer.innerHTML += '<span style="font-weight: normal">Total Downloads:</span> $download';
    divContainer.innerHTML += '<br>';
    divContainer.innerHTML += '<span style="font-weight: normal">Total Hits:</span> $totalhits';

EOF;
            
            break;
        
    }

    include('../../../common/includes/db_close.php');

}

?>
