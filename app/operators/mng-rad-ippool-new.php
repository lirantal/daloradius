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

    include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', 'common', 'includes', 'config_read.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'check_operator_perm.php' ]);
    $operator = $_SESSION['operator_user'];

    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
    include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'layout.php' ]);
    include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'populate_selectbox.php' ]);

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    require_once __DIR__ . '/library/ip_pool_pages_pdo.php';
    $pool_name=$framedipaddress='';
    if (($_SERVER['REQUEST_METHOD'] ?? '')==='POST') {
        if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) || !dalo_check_csrf_token($_POST['csrf_token'])) {
            $failureMsg='CSRF token error';
        } else {
            try {
                $fields=dalo_ippool_fields($_POST);
                $pool_name=$fields['pool_name']; $framedipaddress=$fields['framedipaddress'];
                $pdo=dalo_pdo_connect($configValues,$_SESSION['location_name'] ?? 'default');
                $item_id=dalo_ippool_save($pdo,$configValues,$fields);
                if ($item_id===false) { $failureMsg=sprintf('The chosen %s is already contained in a pool',t('all','IPAddress')); }
                else {
                    $successMsg=sprintf('Successfully added a new ippool item (item id: %s) [<a href="mng-rad-ippool-edit.php?item=%s" title="Edit">Edit</a>]',
                                        htmlspecialchars($item_id,ENT_QUOTES,'UTF-8'),rawurlencode($item_id));
                    $logAction.='Successfully added a new ippool item on page: ';
                }
            } catch (Throwable $e) { $failureMsg='Unable to create IP pool item'; }
        }
    }

    // print HTML prologue
    $title = t('Intro','mngradippoolnew.php');
    $help = t('helpPage','mngradippoolnew');

    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'actionMessages.php' ]);

    if (!isset($successMsg)) {
         // descriptors 0
        $input_descriptors0 = array();

        $input_descriptors0[] = array(
                                        'name' => 'pool_name',
                                        'caption' => t('all','PoolName'),
                                        'type' => 'text',
                                        'value' => (isset($pool_name) ? $pool_name : ""),
                                        'required' => true
                                     );

        $input_descriptors0[] = array(
                                        'name' => 'framedipaddress',
                                        'caption' => t('all','IPAddress'),
                                        'type' => 'text',
                                        'value' => (isset($framedipaddress) ? $framedipaddress : ""),
                                        'pattern' => trim(IP_REGEX, '/'),
                                        'required' => true
                                     );

        // descriptors 1
        $input_descriptors1 = array();

        $input_descriptors1[] = array(
                                        "name" => "csrf_token",
                                        "type" => "hidden",
                                        "value" => dalo_csrf_token(),
                                     );

        $input_descriptors1[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                      );
        open_form();

        // fieldset 0
        $fieldset0_descriptor = array(
                                        "title" => t('title','IPPoolInfo'),
                                     );

        open_fieldset($fieldset0_descriptor);

        foreach ($input_descriptors0 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_fieldset();

        foreach ($input_descriptors1 as $input_descriptor) {
            print_form_component($input_descriptor);
        }

        close_form();

    }

    print_back_to_previous_page();

    include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_CONFIG'], 'logging.php' ]);
    print_footer_and_html_epilogue();

?>
