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

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include_once('../common/includes/config_read.php');
    //~ include('library/check_operator_perm.php');

    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
    include("../common/includes/layout.php");

    // validate this parameter before including menu
    $username = (array_key_exists('username', $_GET) && !empty(str_replace("%", "", trim($_GET['username']))))
              ? str_replace("%", "", trim($_GET['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    
    $cols = array(
                    'id' => t('all','ID'),
                    'username' => t('all','Username'),
                    'attribute' => t('all','Attribute'),
                    'op',
                    'value' => t('all','Value'),
                    t('all','Action')
                 );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);

    // validating user passed parameters

    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($cols)))
             ? $_GET['orderBy'] : array_keys($cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  preg_match(ORDER_TYPE_REGEX, $_GET['orderType']) !== false)
               ? strtolower($_GET['orderType']) : "asc";
    
    $log = "visited page: ";
    $logQuery = "performed query for ";
    if (!empty($username)) {
         $logQuery .= "username(s) starting with [$username] ";
    } else {
        $logQuery .= "all usernames ";
    }
    $logQuery .= "on page: ";

    
    // print HTML prologue
    $title = t('Intro','repusername.php');
    $help = t('helpPage','repusername') . " " . $username_enc;
    
    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include('../common/includes/db_open.php');
    include('include/management/pages_common.php');
    
    $arr = array();

    $sql = sprintf("SELECT id, username, attribute, op, value FROM %s WHERE username='%s' ORDER BY %s %s", 
                   $configValues['CONFIG_DB_TBL_RADCHECK'], $dbSocket->escapeSimple($username), $orderBy, $orderType);
    $arr[] = array( 
                    'sql' => $sql,
                    'caption' => t('captions','radcheckrecords')
                  );
    
    $sql = sprintf("SELECT id, username, attribute, op, value FROM %s WHERE username='%s' ORDER BY %s %s", 
                   $configValues['CONFIG_DB_TBL_RADREPLY'], $dbSocket->escapeSimple($username), $orderBy, $orderType);
    $arr[] = array( 
                    'sql' => $sql,
                    'caption' => t('captions','radreplyrecords')
                  );
    
    $total_numrows = 0;
    foreach ($arr as $item) {
        $res = $dbSocket->query($item['sql']);
        $logDebugSQL .= $item['sql'] . ";\n";
        
        $numrows = $res->numRows();
        
        if ($numrows > 0) {
            printf('<h4 style="margin-top: 10px">%s</h4>', $item['caption']);
            
            // print table top
            print_table_top();
            
            // second line of table header
            printTableHead($cols, $orderBy, $orderType);

            // closes table header, opens table body
            print_table_middle();

            $csrf_token = dalo_csrf_token();

            while ($row = $res->fetchRow()) {
                $rowlen = count($row);
                
                echo "<tr>";
                for ($i = 0; $i < $rowlen; $i++) {
                    printf("<td>%s</td>", htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8'));
                }
                
                $this_username = htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8');
                echo '<td>';
                $formId = $this_username . "-form-del";
                printf('<form id="%s" style="display: none" method="POST" action="mng-del.php">', $formId);
                printf('<input type="hidden" name="username[]" value="%s">', $this_username);
                printf('<input type="hidden" name="csrf_token" value="%s">', $csrf_token);
                echo '</form>';
                
                $onclick = sprintf("document.getElementById('%s').submit()", $formId);
                printf('<a href="mng-edit.php?username=%s">%s</a>&nbsp;<a href="#" onclick="%s">%s</a>',
                       urlencode($this_username), t('all','edit'), $onclick, t('all','del'));
                echo '</td>';
                echo "</tr>";
            }

            print_table_bottom();
        }
        
        $total_numrows += $numrows;
    }
    
    include('../common/includes/db_close.php');

    if ($total_numrows == 0) {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }

    include('include/config/logging.php');
    print_footer_and_html_epilogue();

?>
