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

    include('library/check_operator_perm.php');
    include_once('library/config_read.php');

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    $username = (array_key_exists('username', $_GET) && !empty(str_replace("%", "", trim($_GET['username']))))
              ? str_replace("%", "", trim($_GET['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    
    // feed the sidebar
    $usernameList = $username_enc;
    
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','mngradusergrouplist');
    $help = t('helpPage','mngradusergrouplist');
    
    print_html_prologue($title, $langCode);

    include ("menu-mng-rad-usergroup.php");
    
    $cols = array(
        "username" => t('all','Username'),
        "fullname" => t('all','Name'),
        t('all','Groupname') . " (" . t('all','Priority') . ")",
        "selected"
    );
    
    $colspan = count($cols);
    $half_colspan = intdiv($colspan, 2);
                 
    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }
    
    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "asc";
               
    // start printing content
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include('library/opendb.php');
    include('include/management/pages_common.php');
                                                          
    $sql = sprintf("SELECT COUNT(DISTINCT(username)) FROM %s", $configValues['CONFIG_DB_TBL_RADUSERGROUP']);
    if (!empty($username)) {
        $sql .= sprintf(" WHERE username LIKE '%s%%'", $dbSocket->escapeSimple($username));
    }
    
    $res = $dbSocket->query($sql);
    $numrows = $res->fetchrow()[0];
    
    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */
        
        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                              // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
        
        /* END */
                     
        $records = array();
        
        $nested_query = sprintf("SELECT DISTINCT(rug.username) FROM %s AS rug", $configValues['CONFIG_DB_TBL_RADUSERGROUP']);
        if (!empty($username)) {
            $nested_query .= sprintf(" WHERE rug.username LIKE '%s%%'", $dbSocket->escapeSimple($username));
        }
        
        $sql0 = "SELECT dui.username AS username, CONCAT(firstname, ' ', lastname) AS fullname
                   FROM %s AS dui
                  WHERE dui.username IN (%s)
                  ORDER BY %s %s LIMIT %s, %s";
        $sql0 = sprintf($sql0, $configValues['CONFIG_DB_TBL_DALOUSERINFO'],
                               $nested_query,
                               $orderBy, $orderType, $offset, $rowsPerPage);
        $res0 = $dbSocket->query($sql0);

        $per_page_numrows = $res0->numRows();

        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = (!empty($username_enc) ? "&username=" . urlencode($username_enc) : "");
        
        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "mng-rad-usergroup-del.php";
?>
<form name="listall" method="POST" action="<?= $action ?>">
    <table border="0" class="table1">
        <thead>
<?php
        // page numbers are shown only if there is more than one page
        if ($drawNumberLinks) {
            echo '<tr style="background-color: white">';
            printf('<td style="text-align: left" colspan="%s">go to page: ', $colspan);
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $partial_query_string);
            echo '</td>' . '</tr>';
        }
?>

            <tr>
                <th style="text-align: left" colspan="<?= $colspan ?>">
<?php
        printTableFormControls('usergroup[]', $action);
?>
                </th>
            </tr>
<?php
        // second line of table header
        echo "<tr>";
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);
        echo "</tr>";
?>
        </thead>
        
        <tbody>
<?php
        
        function print_user_group_prio($this_username, $this_groupname, $this_priority) {
            $onclick='javascript:return false;';
        
            $li_style = 'margin: 7px auto';
            $tooltipText_format = '<ul style="list-style-type: none">'
                                . sprintf('<li style="%s">', $li_style) 
                                . '<a class="toolTip" href="mng-rad-usergroup-edit.php?username=%s&group=%s">%s</a>'
                                . '</li>'
                                . sprintf('<li style="%s">', $li_style)
                                . '<a class="toolTip" href="mng-rad-usergroup-list-user.php?username=%s&group=%s">%s</a>'
                                . '</li>'
                                . '</ul>';
            
            $tooltipText = sprintf($tooltipText_format, urlencode($this_username), urlencode($this_groupname), t('Tooltip','EditUserGroup'),
                                                        urlencode($this_username), urlencode($this_groupname), t('Tooltip','ListUserGroups'));
            $tooltipText = sprintf("tooltipText='%s'", $tooltipText);

            printf('<td><a class="tablenovisit" href="#" onclick="%s" %s>%s</a> (%s)</td>', $onclick, $tooltipText, $this_groupname, $this_priority);
            printf('<td><input type="checkbox" name="usergroup[]" value="%s||%s"></td>',
                   urlencode($this_username), urlencode($this_groupname));
        }
        
        while ($row0 = $res0->fetchRow()) {
            $row0len = count($row0);
        
            // escape row elements
            for ($i = 0; $i < $row0len; $i++) {
                $row0[$i] = htmlspecialchars($row0[$i], ENT_QUOTES, 'UTF-8');
            }
        
            list($this_username, $fullname) = $row0;
            $records[$this_username] = array(
                'fullname' => $fullname,
                'groups' => array()
            );
            
            $sql1 = sprintf("SELECT groupname, priority FROM %s WHERE username='%s' ORDER BY priority DESC, groupname ASC",
                            $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($this_username));
            $res1 = $dbSocket->query($sql1);
            
            while ($row1 = $res1->fetchRow()) {
                $row1len = count($row1);
        
                // escape row elements
                for ($i = 0; $i < $row1len; $i++) {
                    $row1[$i] = htmlspecialchars($row1[$i], ENT_QUOTES, 'UTF-8');
                }
            
                list($this_groupname, $this_priority) = $row1;
                $records[$this_username]['groups'][] = array( 'groupname' => $this_groupname, 'priority' => $this_priority );
            }
        }
                
        foreach ($records as $this_username => $data) {
            $rowspan = count($data['groups']);
            $group = $data['groups'][0];
            
            echo "<tr>";
            
            printf('<td rowspan="%s">%s</td>', $rowspan, $this_username);
            printf('<td rowspan="%s">%s</td>', $rowspan, $data['fullname']);
            
            print_user_group_prio($this_username, $group['groupname'], $group['priority']);
            
            echo "</tr>";
            
            if ($rowspan > 1) {
                for ($i = 1; $i < $rowspan; $i++) {
                    $group = $data['groups'][$i];
                    echo "<tr>";
                    print_user_group_prio($this_username, $group['groupname'], $group['priority']);                    
                    echo "</tr>";
                }
            }
        }
?>
        </tbody>
<?php
        // tfoot
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
        printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links);
?>
    </table>
    
    <input name="csrf_token" type="hidden" value="<?= dalo_csrf_token() ?>">
    
</form>

<?php
    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
    
    include('library/closedb.php');

    include('include/config/logging.php');
    
    $inline_extra_js = "
var tooltipObj = new DHTMLgoodies_formTooltip();
tooltipObj.setTooltipPosition('right');
tooltipObj.setPageBgColor('#EEEEEE');
tooltipObj.setTooltipCornerSize(15);
tooltipObj.initFormFieldTooltip();";
    
    print_footer_and_html_epilogue($inline_extra_js);
?>
