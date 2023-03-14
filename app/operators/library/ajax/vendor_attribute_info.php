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


// attribute and divContainer are required
if (array_key_exists('attribute', $_GET) && isset($_GET['attribute']) &&
    array_key_exists('divContainer', $_GET) && isset($_GET['divContainer'])) {
    
    // divContainer id must begin with a letter ([A-Za-z]) and may be followed by any number of letters,
    // digits ([0-9]), hyphens ("-"), underscores ("_").
    if (!preg_match("/[A-Za-z][A-Za-z0-9_-]+/", $_GET['divContainer'])) {
        exit;
    }
    
    $divContainer = $_GET['divContainer'];
    $attribute = str_replace("%", "", trim($_GET['attribute']));

    // at the moment we have only one action
    $action = "";
    if (isset($_GET['retAttributeInfo'])) {
        $action = 'retAttributeInfo';
    } else {
        $action = 'retAttributeInfo';
    }

    include('../../../common/includes/db_open.php');

    switch ($action) {
        
        default:
        case 'retAttributeInfo':
            $sql = sprintf("SELECT RecommendedTooltip FROM %s WHERE Attribute='%s'",
                           $configValues['CONFIG_DB_TBL_DALODICTIONARY'], $dbSocket->escapeSimple($attribute));
            $tooltip = trim($dbSocket->getOne($sql));
            $tooltip = (empty($tooltip)) ? "(n/a)" : addslashes(htmlspecialchars($tooltip, ENT_QUOTES, 'UTF-8'));
            
            echo <<<EOF

    document.getElementById('$divContainer').innerHTML = 'Description: <span style="font-weight: normal">$tooltip</span>';

EOF;
            break;
    }

    include('../../../common/includes/db_close.php');

}

?>
