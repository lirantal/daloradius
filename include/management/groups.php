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
 * Description:    this file extends user management pages
 *                 (specifically edit user page) to allow group management.
 *                 Essentially, this extention populates groups into tables
 * 
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/management/groups.php') !== false) {
    header('Location: ../../index.php');
    exit;
}


if (!isset($groupTerminology)) {
    $groupTerminology = "Group";
    $groupTerminologyPriority = "GroupPriority";
}

?>

<fieldset>
    <h302><?= $groupTerminology ?> Assignment</h302>
    <br/>

    <h301>Associated <?= $groupTerminology ?>s</h301>
    <br/>

    <ul>

<?php

    $sql = sprintf("SELECT groupname, priority FROM %s WHERE username='%s' ORDER BY priority DESC",
                   $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username));
    $res = $dbSocket->query($sql);

    if ($res->numRows() == 0) {
        printf('<div style="text-align: center">%s</div>', t('messages','nogroupdefinedforuser'));
    } else {

        $counter = 1;

        while ($row = $res->fetchRow()) {
            
            foreach ($row as $i => $v) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }
            
            list($groupname, $priority) = $row;
            
            echo '<li class="fieldset">';
            
            $id = "usergroup" . $counter;
            
            printf('<label for="%s" class="form">%s #%s</label>', $id, t('all')[$groupTerminology], $counter);
            printf('<input type="text" value="%s" id="%s" class="form" disabled>', $groupname, $id);
            printf('<input type="hidden" value="%s" name="groups[]">', $groupname);
            
            $id = "group_priority" . $counter;
            
            printf('<label for="%s" class="form">%s</label>', $id, t('all')[$groupTerminologyPriority]);
            printf('<input type="number" class="integer" min="0" value="%s" name="groups_priority[]" id="%s">', $row[1], $id);
            
            echo '</li>';
            
            $counter++;

        } //while

    } // if-else
?>

    </ul>
</fieldset>
