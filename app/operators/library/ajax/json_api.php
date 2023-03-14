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

// datatype => allowed actions
$whitelist = array();
$whitelist["usernames"] = array( "list" );

$data = array();


if (isset($_GET['datatype']) && in_array(strtolower(trim($_GET['datatype'])), array_keys($whitelist))) {
    $datatype = strtolower(trim($_GET['datatype']));
    
    if (isset($_GET['action']) && in_array(strtolower(trim($_GET['action'])), $whitelist[$datatype])) {
    
        $action = strtolower(trim($_GET['action']));
        
        switch ($datatype) {
            
            default:
            case "usernames": {
                $allowed_tables = array( 'CONFIG_DB_TBL_RADCHECK', 'CONFIG_DB_TBL_RADREPLY', 'CONFIG_DB_TBL_RADACCT',
                                         'CONFIG_DB_TBL_DALOUSERINFO', 'CONFIG_DB_TBL_DALOUSERBILLINFO' );
                
                switch ($action) {

                    default:
                    case "list":
                        if (isset($_GET['username']) && strlen(trim($_GET['username'])) >= 3) {

                            include('../../../common/includes/db_open.php');

                            // init username
                            $username = trim($_GET['username']);

                            // init table
                            $key = (isset($_GET['table']) && in_array(strtoupper(trim($_GET['table'])), $allowed_tables))
                                 ? strtoupper(trim($_GET['table'])) : $allowed_tables[0];
                            
                            $table = $configValues[$key];

                            // perform query
                            $sql = sprintf("SELECT DISTINCT(username) FROM %s WHERE username LIKE '%%%s%%' ORDER BY username ASC",
                                           $table, $dbSocket->escapeSimple($username));
                            $res = $dbSocket->query($sql);
                            while ( $row = $res->fetchrow() ) {
                                $data[] = $row[0];
                            }

                            include('../../../common/includes/db_close.php');
                        }

                        break; // case "list"
                }
            
            
                break; // case "usernames"
            }
                
            // case "other category"
        }
    
    }
}




header("Content-Type: application/json");
echo json_encode($data);
exit();
