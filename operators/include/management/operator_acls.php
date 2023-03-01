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
 * Description:    Used to provide a listing of the available pages which
 *                 operators may have access to as taken from
 *                 the operators table in the database
 * 
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/management/operator_acls.php') !== false) {
    header("Location: ../../index.php");
    exit;
}

function drawOperatorACLs($operator_id = "") {

    include('../common/includes/db_open.php');
    
    // init table layout
    echo '<table class="table table-striped">'
       . '<thead>'
       . '<tr><th colspan="4">Permission to access sections</th></tr>'
       . '<tr>'
       . '<th>Category</th>'
       . '<th>Section</th>'
       . '<th>Page</th>'
       . '<th>Access</th>'
       . '</tr>'
       . '</thead>'
       . '<tbody>';
                        
    $sql = sprintf("SELECT DISTINCT(opf.file), opf.category, opf.section, opa.access
                     FROM %s AS opf LEFT JOIN %s AS opa ON opf.file=opa.file",
                   $configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES'],
                   $configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL']);
            
    if (!empty($operator_id)) {
        $sql .= sprintf(" WHERE opa.operator_id = %d", intval($operator_id));
    }
    
    $sql .= " ORDER BY opf.category, opf.section ASC";
    
    $res = $dbSocket->query($sql);
    
    while ($row = $res->fetchRow()) {
        
        foreach ($row as $i => $v) {
            $row[$i] = htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        }
        
        list($file, $category, $section, $access) = $row;
        $access = intval($access);
        
        echo '<tr>';
        printf('<td>%s</td><td>%s</td><td>%s</td>', $category, $section, $file);
        
        echo '<td>';
        printf('<select class="form-select" name="ACL_%s">', $file);
        printf('<option value="1"%s>Granted</option>', (($access === 1) ? " selected" : ""));
        printf('<option value="0"%s>Denied</option>', (($access !== 1) ? " selected" : ""));
        echo '</select>'
           . '</td>'
           . '</tr>';
        
    }
    
    echo '</tbody>'
       . '</table>';
    
    include('../common/includes/db_close.php');
}

?>
