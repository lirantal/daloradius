<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
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
 * Authors:        Liran Tal <liran@lirantal.com>
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
    $operator_acl_pdo = null;
    try {
        include dirname(__DIR__, 3) . '/common/includes/config_read.php';
        require_once dirname(__DIR__, 3) . '/common/includes/pdo_connection.php';
        require_once dirname(__DIR__, 2) . '/library/operator_acl_read.php';
        $operator_acl_pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        // Fetch before emitting HTML: an SQL failure cannot render a partial ACL form.
        $rows = dalo_operator_acl_rows($operator_acl_pdo, $configValues, $operator_id);
    } catch (Throwable $error) {
        error_log('Operator permission rendering failed: ' . get_class($error));
        echo '<div class="failure">Unable to load operator permissions.</div>';
        return;
    } finally {
        $operator_acl_pdo = null;
    }

    echo '<table class="table table-striped">'
       . '<thead>'
       . '<tr><th colspan="4">Permission to access sections</th></tr>'
       . '<tr><th>Category</th><th>Section</th><th>Page</th><th>Access</th></tr>'
       . '</thead><tbody>';
    foreach ($rows as $row) {
        foreach ($row as $i => $value) {
            $row[$i] = htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
        }
        list($file, $category, $section, $access) = $row;
        $access = intval($access);
        echo '<tr>';
        printf('<td>%s</td><td>%s</td><td>%s</td>', $category, $section, $file);
        printf('<td><select class="form-select" name="ACL_%s">', $file);
        printf('<option value="1"%s>Granted</option>', $access === 1 ? ' selected' : '');
        printf('<option value="0"%s>Denied</option>', $access !== 1 ? ' selected' : '');
        echo '</select></td></tr>';
    }
    echo '</tbody></table>';
}
