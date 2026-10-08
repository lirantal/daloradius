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
 * Authors:    Liran Tal <liran@lirantal.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */
 
    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('../common/includes/config_read.php');
    include_once("lang/main.php");
    include_once("../common/includes/validation.php");
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
                  is_string($_GET['orderType']) && in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "asc";


    // print HTML prologue
    $extra_js = array(
        "static/js/request.js",
        "static/js/readonly_info.js",
    );
    
    $title = t('Intro','billposlist.php');
    $help = t('helpPage','billposlist');
    
    print_html_prologue($title, $langCode, array(), $extra_js);

    // Keep the raw filter identity; bind it in SQL and escape it only for display.
    $planname = (array_key_exists('planname', $_GET) && is_string($_GET['planname']))
              ? $_GET['planname'] : '';
    
    $planname_enc = ($planname !== '')
                  ? htmlspecialchars($planname, ENT_QUOTES, 'UTF-8')
                  : "";

    if ($planname_enc !== '') {
        $title .=  " :: " . $planname_enc;
    }

    // start printing content
    print_title_and_help($title, $help);
    echo '<div id="returnMessages"></div>';


    require_once('library/catalog_reads_pdo.php');
    include('include/management/pages_common.php');

    $catalog_pdo = null; $numrows = 0; $rows = array(); $values = array();
    try {
        dalo_catalog_read_inputs($_GET, array('orderBy', 'orderType', 'planname'));
        $catalog_pdo = dalo_catalog_read_open($configValues);
        list($sql, $values) = dalo_catalog_pos_query($catalog_pdo, $configValues, $planname);
        $numrows = (int)dalo_catalog_read_rows($catalog_pdo, "SELECT COUNT(*) FROM ($sql) AS catalog_count", $values)[0][0];
        if ($numrows > 0) {
            include('include/management/pages_numbering.php');
            $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == 'yes' && $maxPage > 1;
            $sql .= " ORDER BY $orderBy $orderType LIMIT :offset, :limit";
            $values[':offset'] = (int)$offset; $values[':limit'] = (int)$rowsPerPage;
            $rows = dalo_catalog_read_rows($catalog_pdo, $sql, $values);
            $logDebugSQL .= "$sql;\n";
        }
    } catch (Throwable $error) {
        dalo_catalog_read_failure($error); $numrows = 0; $rows = array();
    } finally { $catalog_pdo = null; }

    if ($numrows > 0) {
        $per_page_numrows = count($rows);

        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = ($planname_enc !== '')
                              ? sprintf("&planname=%s", urlencode($planname)) : "";
                              
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
                            'partial_query_string' => $partial_query_string,
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
        foreach ($rows as $row) {
            $raw_identity = (string)$row[0];
            $rowlen = count($row);
        
            // escape row elements
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars((string)$row[$i], ENT_QUOTES, 'UTF-8');
            }
            
            list($username, $id, $value, $attribute, $contactperson, $billstatus, $planname, $company, $firstname, $disabled) = $row;
            
            // we try to get the type of this user
            if ($attribute == 'Auth-Type' && $value == 'Accept') {
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
            $param = sprintf('username=%s', urlencode($raw_identity));
            $onclick = "daloInfo.user('$ajax_id','$param')";
            $tooltip = array(
                                'subject' => sprintf('%s%s<span class="badge bg-primary ms-1">%s</span>', $img, $badge, $username),
                                'onclick' => $onclick,
                                'ajax_id' => $ajax_id,
                                'actions' => array(),
                            );
            $tooltip['actions'][] = array( 'href' => sprintf('bill-pos-edit.php?username=%s', urlencode($raw_identity), ), 'label' => t('Tooltip','UserEdit'), );
            
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
        if (!isset($failureMsg)) { $failureMsg = "Nothing to display"; }
        include_once("include/management/actionMessages.php");
    }
    


    include('include/config/logging.php');
    
    print_footer_and_html_epilogue();
?>
