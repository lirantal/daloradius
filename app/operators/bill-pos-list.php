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
    include_once('../common/includes/config_read.php');
    include_once("lang/main.php");
    include("../common/includes/layout.php");

    // init logging variables
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    $logDebugSQL = "";

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    $cols = array(
                    "id" => t('all','ID'),
                    "contactperson" => t('ContactInfo','ContactPerson'),
                    "company" => t('ContactInfo','Company'),
                    "username" => t('all','Username'),
                    t('all','Password'),
                    "planname" => t('ContactInfo','PlanName')
                 );
    $colspan = count($cols);
    $half_colspan = intval($colspan / 2);
                 
    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }
    
    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "asc";


    // print HTML prologue
    $extra_js = array(
        "static/js/ajax.js",
        "static/js/ajaxGeneric.js"
    );
    
    $title = t('Intro','billposlist.php');
    $help = t('helpPage','billposlist');
    
    print_html_prologue($title, $langCode, array(), $extra_js);

    // we partially strip some character and
    // leave validation/escaping to other functions used later in the script
    $planname = (array_key_exists('planname', $_GET) && isset($_GET['planname']))
              ? str_replace("%", "", $_GET['planname']) : "";
    
    $planname_enc = (!empty($planname))
                  ? htmlspecialchars($planname, ENT_QUOTES, 'UTF-8')
                  : "";

    if (!empty($planname_enc)) {
        $title .=  " :: " . $planname_enc;
    }

    // start printing content
    print_title_and_help($title, $help);
    echo '<div id="returnMessages"></div>';


    include('../common/includes/db_open.php');
    include('include/management/pages_common.php');

    $sql_WHERE = array();
    $sql_WHERE[] = "rc.username = ui.username";
    $sql_WHERE[] = "(rc.attribute LIKE '%-Password' OR rc.attribute = 'Auth-Type')";
    
    if (!empty($planname)) {
        $sql_WHERE[] = sprintf("ubi.planname LIKE '%s%%' ", $dbSocket->escapeSimple($planname));
    }

    $sql = sprintf("SELECT DISTINCT(rc.username) AS username, rc.id, rc.value, rc.attribute, ubi.contactperson,
                           ubi.billstatus, ubi.planname, ubi.company, ui.firstname, IFNULL(rug.username, 0) AS disabled
                      FROM %s AS ui, %s AS rc
                      LEFT JOIN %s AS ubi ON rc.username=ubi.username
                      LEFT JOIN %s AS rug ON rug.username=rc.username AND rug.groupname='daloRADIUS-Disabled-Users'",
                   $configValues['CONFIG_DB_TBL_DALOUSERINFO'],
                   $configValues['CONFIG_DB_TBL_RADCHECK'],
                   $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'],
                   $configValues['CONFIG_DB_TBL_RADUSERGROUP'])
         . " WHERE " . implode(" AND ", $sql_WHERE) . "GROUP BY username";
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();

    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */
        
        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                              // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
        
        /* END */
                     
        // we execute and log the actual query
        $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        $per_page_numrows = $res->numRows();
        
        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = (!empty($planname_enc))
                              ? sprintf("&vendor=%s", urlencode($planname_enc)) : "";
                              
        // this can be passed as form attribute and 
        // printTableFormControls function parameter
        $action = "mng-del.php";
        
        // we prepare the "controls bar" (aka the table prologue bar)
        $additional_controls = array();
        $additional_controls[] = array(
                                'onclick' => "disableCheckbox('listall','library/ajax/user_actions.php')",
                                'label' => 'Disable',
                                'class' => 'btn-primary',
                              );
        $additional_controls[] = array(
                                'onclick' => "enableCheckbox('listall','library/ajax/user_actions.php')",
                                'label' => 'Enable',
                                'class' => 'btn-secondary',
                              );

        $additional_controls[] = array(
                                'onclick' => "refillSessionTimeCheckbox('listall', 'library/ajax/user_actions.php')",
                                'label' => 'Refill Session Time',
                                'class' => 'btn-secondary',
                              );
                              
        $additional_controls[] = array(
                                'onclick' => "refillSessionTrafficCheckbox('listall', 'library/ajax/user_actions.php')",
                                'label' => 'Refill Session Traffic',
                                'class' => 'btn-secondary',
                              );

        $descriptors = array();

        $descriptors['start'] = array( 'common_controls' => 'username[]', 'additional_controls' => $additional_controls );

        $params = array(
                            'num_rows' => $numrows,
                            'rows_per_page' => $rowsPerPage,
                            'page_num' => $pageNum,
                            'order_by' => $orderBy,
                            'order_type' => $orderType,
                        );
        $descriptors['center'] = array( 'draw' => $drawNumberLinks, 'params' => $params );

        print_table_prologue($descriptors);

        $form_descriptor = array( 'form' => array( 'action' => $action, 'method' => 'POST', 'name' => 'listall' ), );

        // print table top
        print_table_top($form_descriptor);

        // second line of table header
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);

        // closes table header, opens table body
        print_table_middle();

        // table content
        $count = 0;
        $td_format = '<td>%s</td>';
        while ($row = $res->fetchRow()) {
            $rowlen = count($row);
        
            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
            }
            
            list($username, $id, $value, $attribute, $contactperson, $billstatus, $planname, $company, $firstname, $disabled) = $row;
            
            // we try to get the type of this user
            if ($attribute == 'Auth-Type' && $row['auth'] == 'Accept') {
                if (preg_match(MACADDR_REGEX, $username) || preg_match(IP_REGEX, $username)) {
                    $type = 'MAC';
                } else {
                    $type = 'PIN';
                }
            } else {
                $type = 'USER';
            }
            
            $img_format = '<i class="bi bi-%s-circle-fill text-%s me-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="%s"></i>';

            $img = ($disabled)
                 ? sprintf($img_format, 'dash', 'danger', 'disabled')
                 : sprintf($img_format, 'check', 'success', 'enabled');
            
            $badge_icon = "";
            switch ($type) {
                case 'PIN':
                    $badge_icon = "123";
                    break;

                case 'MAC':
                    $badge_icon = "ethernet";
                    break;

                default:
                case 'USER':
                    $badge_icon = "person-fill";
                    break;
            }

            $badge = sprintf('<i class="bi bi-%s me-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="%s"></i>',
                             $badge_icon, strtolower($type));
            
            $auth = (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) === "yes")
                  ? "[Password is hidden]" : $value;
            
            $ajax_id = "divContainerUserInfo_" . $count;
            $param = sprintf('username=%s', urlencode($username));
            $onclick = "ajaxGeneric('library/ajax/user_info.php','retBandwidthInfo','$ajax_id','$param')";
            $tooltip = array(
                                'subject' => sprintf('%s%s<span class="badge bg-primary ms-1">%s</span>', $img, $badge, $username),
                                'onclick' => $onclick,
                                'ajax_id' => $ajax_id,
                                'actions' => array(),
                            );
            $tooltip['actions'][] = array( 'href' => sprintf('bill-pos-edit.php?username=%s', urlencode($username), ), 'label' => t('Tooltip','UserEdit'), );
            
            // create tooltip
            $tooltip = get_tooltip_list_str($tooltip);
            
            // create checkbox
            $d = array( 'name' => 'username[]', 'value' => $username, 'label' => $id );
            $checkbox = get_checkbox_str($d);
            
            // define table row
            $table_row = array( $checkbox, $contactperson, $company, $tooltip, $auth, $planname);

            // print table row
            print_table_row($table_row);

            $count++;
        }

        // close tbody,
        // print tfoot
        // and close table + form (if any)
        $table_foot = array(
                                'num_rows' => $numrows,
                                'rows_per_page' => $per_page_numrows,
                                'colspan' => $colspan,
                                'multiple_pages' => $drawNumberLinks
                           );
        $descriptor = array( 'form' => $form_descriptor, 'table_foot' => $table_foot );
        print_table_bottom($descriptor);

        // get and print "links"
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
        printLinks($links, $drawNumberLinks);

    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
    
    include('../common/includes/db_close.php');

    include('include/config/logging.php');
    
    print_footer_and_html_epilogue();
?>
