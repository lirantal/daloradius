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
    $logAction = "";
    $logDebugSQL = "";
    $log = "visited page: ";

    include_once("lang/main.php");
    include("library/validation.php");
    include("library/layout.php");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
        $profile = (array_key_exists('profile', $_POST) && !empty(str_replace("%", "", trim($_POST['profile']))))
                 ? str_replace("%", "", trim($_POST['profile'])) : "";
        $profile_enc = (!empty($profile)) ? htmlspecialchars($profile, ENT_QUOTES, 'UTF-8') : "";
    
        if (empty($profile)) {
            // profile required
            $failureMsg = "The specified profile name is empty or invalid";
            $logAction .= "Failed creating profile [empty or invalid profile name] on page: ";
        } else {
            
            include_once('include/management/populate_selectbox.php');
            $groups = array_keys(get_groups());
            include('library/opendb.php');
            
            if (in_array($profile, $groups)) {
                // invalid profile name
                $failureMsg = "This profile name [<strong>$profile_enc</strong>] is already in use";
                $logAction .= "Failed creating profile [$profile, name already in use] on page: ";
            } else {
    
                include("library/attributes.php");
                $skipList = array( "profile", "submit", "csrf_token" );
                $count = handleAttributes($dbSocket, $profile, $skipList, true, 'group');

                if ($count > 0) {
                    $successMsg = "Added new profile: <b> $profile_enc </b>";
                    $logAction .= "Successfully added a new profile [$profile] on page: ";
                } else {
                    $failureMsg = "Failed creating profile [$profile_enc], invalid or empty attributes list";
                    $logAction .= "Failed creating profile [$profile], invalid or empty attributes list] on page: ";
                }

            } // profile non-existent
            
            include('library/closedb.php');
            
        } // profile name not empty    
    }


    // print HTML prologue
    $extra_css = array(
        // css tabs stuff
        "css/tabs.css"
    );
    
    $extra_js = array(
        "library/javascript/ajax.js",
        "library/javascript/dynamic_attributes.js",
        "library/javascript/ajaxGeneric.js",
        "library/javascript/productive_funcs.js",
        // js tabs stuff
        "library/javascript/tabs.js"
    );
    
    $title = t('Intro','mngradprofilesnew.php');
    $help = t('helpPage','mngradprofilesnew');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);
    
    include("menu-mng-rad-profiles.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    if (!isset($successMsg)) {
    
        $input_descriptors1 = array();
        
        $input_descriptors1[] = array(
                                        "name" => "profile",
                                        "caption" => "Profile Name",
                                        "type" => "text",
                                        "value" => ((isset($profile)) ? $profile : "")
                                     );

        //~ $input_descriptors1[] = array(
                                        //~ "type" => "submit",
                                        //~ "name" => "submit",
                                        //~ "value" => t('buttons','apply')
                                  //~ );
?>

<form name="newusergroup" method="POST">
    <fieldset>
        <h302><?= t('title','ProfileInfo') ?></h302>

        <ul style="margin: 30px auto">

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
