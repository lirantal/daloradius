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
 * Description:    dinamically handles groups/profiles via ajax
 * 
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */


include('../../library/checklogin.php');
include('pages_common.php');


// divCounter and divContainer are required
if (
        array_key_exists('divCounter', $_GET) &&
        array_key_exists('divContainer', $_GET) &&
        
        preg_match("/^[A-Za-z][A-Za-z0-9_-]+$/", $_GET['divCounter']) !== false &&
        preg_match("/^[A-Za-z][A-Za-z0-9_-]+$/", $_GET['divContainer']) !== false
   ) {
    
    $divContainer = $_GET['divContainer'];
    $divCounter = $_GET['divCounter'];
    
    // at the moment we have only two actions
    $action = "";
    if (isset($_GET['delGroups'])) {
        $action = 'delGroups';
    } else if (isset($_GET['getGroups'])) {
        $action = 'getGroups';
    } else {
        $action = 'getGroups';
    }
    
    switch ($action) {

        case 'delGroups':
            
            echo <<<EOF
    var divContainer = document.getElementById('$divContainer'),
        childGroup = document.getElementById('divContainerGroups$divCounter');
    divContainer.removeChild(childGroup);
EOF;
            
            break;
        
        default:
        case 'getGroups':
        
            if (array_key_exists('elemName', $_GET) && preg_match("/^[A-Za-z][A-Za-z0-9_-]+$/", $_GET['elemName']) !== false) {
                $elemName = $_GET['elemName'];
                
                $name = "Group";
            
                switch ($divContainer) {
                    case "divContainerProfiles":
                        $name = "Profile";
                        break;        
                    
                    default:
                    case "divContainerGroups":
                        $name = "Group";
                        break;
                }
            
                include('../../library/opendb.php');
            
                $tables = array(
                                 $configValues['CONFIG_DB_TBL_RADGROUPCHECK'],
                                 $configValues['CONFIG_DB_TBL_RADGROUPREPLY'],
                                 $configValues['CONFIG_DB_TBL_RADUSERGROUP']
                               );

                $sql_pieces = array();
                
                foreach ($tables as $table) {
                    $sql_pieces[] = sprintf("SELECT DISTINCT(groupname) FROM %s", $table);
                }

                $sql = implode(" UNION ", $sql_pieces);
                $res = $dbSocket->query($sql);

                $groups = array();
                
                while ($row = $res->fetchRow()) {
                    $groupname = $row[0];
                    
                    if (!array_key_exists($groupname, $groups)) {
                        $groups[$groupname] = $groupname;
                    }
                }
                
                include('../../library/closedb.php');
                
                if (count($groups) > 0) {
                    
                    echo <<<EOF
        var divContainer = document.getElementById('$divContainer');
        var groups = '<label for="$name" class="form">$name</label><select class="form" id="$name" name="$elemName">';
EOF;
                    
                    foreach ($groups as $group) {
                        $group = htmlspecialchars($group, ENT_QUOTES, 'UTF-8');
                        echo <<<EOF
            groups += '<option value="$group">$group</option>';

EOF;
                    }
                    
                    $del_group_link_id = sprintf("del_group_link_id_%d", rand());
                    $onclick = sprintf("ajaxGeneric('include/management/dynamic_groups.php', 'delGroups', '%s', 'divCounter=%s');",
                                       $divContainer, $divCounter);
                    
                    echo <<<EOF
        groups += '</select> <a class="tablenovisit" id="$del_group_link_id" href="#">Del</a>';

        var childGroup = document.createElement('div');
        childGroup.setAttribute('id','divContainerGroups$divCounter');
        childGroup.innerHTML = groups;
        divContainer.appendChild(childGroup);
        
        document.getElementById('$del_group_link_id').setAttribute("onclick", "$onclick");
        
EOF;
                }

            }
        
            break;
    }
    
}
