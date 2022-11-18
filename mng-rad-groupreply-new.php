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
    $logAction = "";
    $logDebugSQL = "";

    // we import validation facilities
    include_once("library/validation.php");

    include_once('include/management/populate_selectbox.php');
    

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
        $groupname = (array_key_exists('groupname', $_POST) && isset($_POST['groupname']))
                 ? trim(str_replace("%", "", $_POST['groupname'])) : "";
        $groupname_enc = (!empty($groupname)) ? htmlspecialchars($groupname, ENT_QUOTES, 'UTF-8') : "";
    
        if (empty($groupname)) {
            // profile required
            $failureMsg = "The specified group name is empty or invalid";
            $logAction .= "Failed creating a new group reply mapping [empty or invalid group name] on page: ";
        } else {
            
            include('library/opendb.php');
            
            $groups = array_keys(get_groups());
            if (!in_array($groupname, $groups)) {
                // invalid profile name
                $failureMsg = "The chosen group [<strong>$groupname_enc</strong>] does not exist";
                $logAction .= "Failed creating group reply mapping [$groupname, does not exist] on page: ";
            } else {
    
                include("library/attributes.php");
                $skipList = array( "groupname", "submit", "csrf_token" );
                $count = handleAttributes($dbSocket, $groupname, $skipList, true, 'group');

                if ($count > 0) {
                    $successMsg = "Added new group reply mapping for <strong>$groupname_enc</strong>";
                    $logAction .= "Successfully added a new group [$groupname] on page: ";
                } else {
                    $failureMsg = "Failed creating group [$groupname_enc], invalid or empty attributes list";
                    $logAction .= "Failed creating group [$groupname], invalid or empty attributes list] on page: ";
                }

            } // profile non-existent
            
            include('library/closedb.php');
            
        } // profile name not empty    
    }


    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $extra_js = array(
        "library/javascript/ajax.js",
        "library/javascript/dynamic_attributes.js",
        "library/javascript/ajaxGeneric.js",
    );
    
    $title = t('Intro','mngradgroupreplynew.php');
    $help = t('helpPage','mngradgroupreplynew');
    
    print_html_prologue($title, $langCode, array(), $extra_js);

    include("menu-mng-rad-groups.php");
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    if (!isset($successMsg)) {
        
        // set form component descriptors
        $input_descriptors1 = array();
        
        $groups = get_groups();
        array_unshift($groups , '');
        $input_descriptors1[] = array(
                                        "name" => "groupname",
                                        "caption" => t('all','Groupname'),
                                        "type" => "select",
                                        "options" => $groups,
                                        "selected_value" => ((isset($groupname)) ? $groupname : "")
                                     );
                                     
        //~ $input_descriptors1[] = array(
                                        //~ "type" => "submit",
                                        //~ "name" => "submit",
                                        //~ "value" => t('buttons','apply')
                                     //~ );
?>
<form name="newgroupreply" method="POST">
    <fieldset>
        <h302><?= t('title','GroupInfo') ?></h302>
        
        <ul>
<?php
            foreach ($input_descriptors1 as $input_descriptor) {
                print_form_component($input_descriptor);
            }
?>

        </ul>

    </fieldset>

<?php
    include_once('include/management/attributes.php');
?>

</form>

<?php
    }
    
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
