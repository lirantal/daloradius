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
 * Description:    This file extends the user management pages
 *                 (new user, batch addusers, edit user, quick add user and possibly others)
 *                 by adding a section for user information
 * 
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/management/userinfo.php') !== false) {
    header("Location: ../../index.php");
    exit;
}

    $input_descriptors1 = array();

    $input_descriptors1[] = array(
                                    'id' => 'firstname',
                                    'name' => 'firstname',
                                    'caption' => t('ContactInfo','FirstName'),
                                    'type' => 'text',
                                    'value' => ((isset($ui_firstname)) ? $ui_firstname : ''),
                                );

    $input_descriptors1[] = array(
                                    'id' => 'lastname',
                                    'name' => 'lastname',
                                    'caption' => t('ContactInfo','LastName'),
                                    'type' => 'text',
                                    'value' => ((isset($ui_lastname)) ? $ui_lastname : ''),
                                );

    $input_descriptors1[] = array(
                                    'id' => 'email',
                                    'name' => 'email',
                                    'caption' => t('ContactInfo','Email'),
                                    'type' => 'text',
                                    'value' => ((isset($ui_email)) ? $ui_email : ''),
                                );
    
    $input_descriptors1[] = array(
                                    'id' => 'copycontact',
                                    'name' => 'copycontact',
                                    'caption' => 'Copy contact information to billing',
                                    'type' => 'checkbox',
                                    'onclick' => 'copyUserBillInfo(this)'
                                 );

    $input_descriptors2 = array();
    $input_descriptors2[] = array( 'id' => 'department', 'caption' => t('ContactInfo','Department'), 'type' => 'text',
                                   'value' => ((isset($ui_department)) ? $ui_department : ''), 'name' => 'department' );
    $input_descriptors2[] = array( 'id' => 'company', 'caption' => t('ContactInfo','Company'), 'type' => 'text',
                                   'value' => ((isset($ui_company)) ? $ui_company : ''), 'name' => 'company' );
    $input_descriptors2[] = array( 'id' => 'workphone', 'caption' => t('ContactInfo','WorkPhone'), 'type' => 'text',
                                   'value' => ((isset($ui_workphone)) ? $ui_workphone : ''), 'name' => 'workphone' );
    $input_descriptors2[] = array( 'id' => 'homephone', 'caption' => t('ContactInfo','HomePhone'), 'type' => 'text',
                                   'value' => ((isset($ui_homephone)) ? $ui_homephone : ''), 'name' => 'homephone' );
    $input_descriptors2[] = array( 'id' => 'mobilephone', 'caption' => t('ContactInfo','MobilePhone'), 'type' => 'text',
                                   'value' => ((isset($ui_mobilephone)) ? $ui_mobilephone : ''), 'name' => 'mobilephone' );
    $input_descriptors2[] = array( 'id' => 'address', 'caption' => t('ContactInfo','Address'), 'type' => 'text',
                                   'value' => ((isset($ui_address)) ? $ui_address : ''), 'name' => 'address' );
    $input_descriptors2[] = array( 'id' => 'city', 'caption' => t('ContactInfo','City'), 'type' => 'text',
                                   'value' => ((isset($ui_city)) ? $ui_city : ''), 'name' => 'city' );
    $input_descriptors2[] = array( 'id' => 'state', 'caption' => t('ContactInfo','State'), 'type' => 'text',
                                   'value' => ((isset($ui_state)) ? $ui_state : ''), 'name' => 'state' );
    $input_descriptors2[] = array( 'id' => 'country', 'caption' => t('ContactInfo','Country'), 'type' => 'text',
                                   'value' => ((isset($ui_country)) ? $ui_country : ''), 'name' => 'country' );
    $input_descriptors2[] = array( 'id' => 'zip', 'caption' => t('ContactInfo','Zip'), 'type' => 'text',
                                   'value' => ((isset($ui_zip)) ? $ui_zip : ''), 'name' => 'zip' );

    $input_descriptors3 = array();
    
    $input_descriptors3[] = array(
                                    "type" => "textarea",
                                    "id" => "notes",
                                    "name" => "notes",
                                    "caption" => t('ContactInfo','Notes'),
                                    "content" => ((isset($ui_notes)) ? $ui_notes : "")
                                 );
    
    $input_descriptors3[] = array( 'id' => 'userupdate', 'caption' => t('ContactInfo','EnableUserUpdate'),
                                   'type' => 'checkbox', 'name' => 'changeUserInfo',
                                   'value' => ((isset($ui_changeuserinfo)) ? $ui_changeuserinfo : ''),
                                   'checked' => ($ui_changeuserinfo == 1) );

    $input_descriptors3[] = array( 'id' => 'userupdate', 'caption' => t('ContactInfo','EnablePortalLogin'),
                                   'type' => 'checkbox', 'name' => 'enableUserPortalLogin',
                                   'value' => ((isset($ui_enableUserPortalLogin)) ? $ui_enableUserPortalLogin : ''),
                                   'checked' => ($ui_enableUserPortalLogin == 1) );

    $input_descriptors3[] = array( 'id' => 'portalLoginPassword', 'caption' => t('ContactInfo','PortalLoginPassword'),
                                   'type' => 'text', 'name' => 'portalLoginPassword',
                                   'value' => ((isset($ui_PortalLoginPassword)) ? $ui_PortalLoginPassword : '') );

    $input_descriptors3[] = array( 'name' => 'creationdate', 'caption' => t('all','CreationDate'), 'type' => 'text',
                                   'disabled' => true, 'value' => ((isset($ui_creationdate)) ? $ui_creationdate : '') );

    $input_descriptors3[] = array( 'name' => 'creationby', 'caption' => t('all','CreationBy'), 'type' => 'text',
                                   'disabled' => true, 'value' => ((isset($ui_creationby)) ? $ui_creationby : '') );

    $input_descriptors3[] = array( 'name' => 'updatedate', 'caption' => t('all','UpdateDate'), 'type' => 'text',
                                   'disabled' => true, 'value' => ((isset($ui_updatedate)) ? $ui_updatedate : '') );

    $input_descriptors3[] = array( 'name' => 'updateby', 'caption' => t('all','UpdateBy'), 'type' => 'text',
                                   'disabled' => true, 'value' => ((isset($ui_updateby)) ? $ui_updateby : '') );
?>

<fieldset>
    
    <h302><?= t('title','ContactInfo') ?></h302>
    
    <h301>Personal</h301>
    
    <ul>

<?php

    foreach ($input_descriptors1 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

?>
    </ul>

    <h301><?= t('all','Business') ?></h301>

    <ul>
<?php

    foreach ($input_descriptors2 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

?>
    </ul>

    <h301>Other</h301>

    <ul>

<?php
    foreach ($input_descriptors3 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
?>

    </ul>
</fieldset>

<?php
    echo $customApplyButton;
?>
