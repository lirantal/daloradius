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
if (array_key_exists('username', $_GET) && isset($_GET['username']) &&
    array_key_exists('divContainer', $_GET) && isset($_GET['divContainer'])) {
    
    // divContainer id must begin with a letter ([A-Za-z]) and may be followed by any number of letters,
    // digits ([0-9]), hyphens ("-"), underscores ("_").
    if (!preg_match("/[A-Za-z][A-Za-z0-9_-]+/", $_GET['divContainer'])) {
        exit;
    }
    
    $divContainer = $_GET['divContainer'];
    
    // user should exist
    $username = str_replace("%", "", trim($_GET['username']));
    
    // at the moment we have only one action
    $action = "";
    if (isset($_GET['retBandwidthInfo'])) {
        $action = 'retBandwidthInfo';
    } else {
        $action = 'retBandwidthInfo';
    }
    
    include('../../../common/includes/db_open.php');
    include_once('../../include/management/pages_common.php');
    
    switch ($action) {
        
        default:
        case 'retBandwidthInfo':
        
            $sql = sprintf("SELECT SUM(AcctInputOctets) AS Upload, SUM(AcctOutputOctets) AS Download FROM %s WHERE username='%s'",
                           $configValues['CONFIG_DB_TBL_RADACCT'], $dbSocket->escapeSimple($username));
            $res = $dbSocket->query($sql);
            $row = $res->fetchRow();
            
            list( $upload, $download ) = $row;
        
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
    divContainer.innerHTML = '<span style="font-weight: normal">Upload:</span> $upload';
    divContainer.innerHTML += '<br>';
    divContainer.innerHTML += '<span style="font-weight: normal">Download:</span> $download';
EOF;
        
            break;
    }
    
    include('../../../common/includes/db_close.php');

}

?>
