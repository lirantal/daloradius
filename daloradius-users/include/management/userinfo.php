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

$_input_descriptors0 = array();
$_input_descriptors0[] = array(
                                'id' => 'firstname',
                                'name' => 'firstname',
                                'caption' => t('ContactInfo','FirstName'),
                                'type' => 'text',
                                'value' => ((isset($ui_firstname)) ? $ui_firstname : ''),
                            );

$_input_descriptors0[] = array(
                                'id' => 'lastname',
                                'name' => 'lastname',
                                'caption' => t('ContactInfo','LastName'),
                                'type' => 'text',
                                'value' => ((isset($ui_lastname)) ? $ui_lastname : ''),
                            );

$_input_descriptors0[] = array(
                                'id' => 'email',
                                'name' => 'email',
                                'caption' => t('ContactInfo','Email'),
                                'type' => 'email',
                                'value' => ((isset($ui_email)) ? $ui_email : ''),
                            );
                            
$_input_descriptors1 = array();
$_input_descriptors1[] = array( 'id' => 'department', 'caption' => t('ContactInfo','Department'), 'type' => 'text',
                               'value' => ((isset($ui_department)) ? $ui_department : ''), 'name' => 'department' );
$_input_descriptors1[] = array( 'id' => 'company', 'caption' => t('ContactInfo','Company'), 'type' => 'text',
                               'value' => ((isset($ui_company)) ? $ui_company : ''), 'name' => 'company' );
$_input_descriptors1[] = array( 'id' => 'workphone', 'caption' => t('ContactInfo','WorkPhone'), 'type' => 'text',
                               'value' => ((isset($ui_workphone)) ? $ui_workphone : ''), 'name' => 'workphone' );
$_input_descriptors1[] = array( 'id' => 'homephone', 'caption' => t('ContactInfo','HomePhone'), 'type' => 'text',
                               'value' => ((isset($ui_homephone)) ? $ui_homephone : ''), 'name' => 'homephone' );
$_input_descriptors1[] = array( 'id' => 'mobilephone', 'caption' => t('ContactInfo','MobilePhone'), 'type' => 'text',
                               'value' => ((isset($ui_mobilephone)) ? $ui_mobilephone : ''), 'name' => 'mobilephone' );
$_input_descriptors1[] = array( 'id' => 'address', 'caption' => t('ContactInfo','Address'), 'type' => 'text',
                               'value' => ((isset($ui_address)) ? $ui_address : ''), 'name' => 'address' );
$_input_descriptors1[] = array( 'id' => 'city', 'caption' => t('ContactInfo','City'), 'type' => 'text',
                               'value' => ((isset($ui_city)) ? $ui_city : ''), 'name' => 'city' );
$_input_descriptors1[] = array( 'id' => 'state', 'caption' => t('ContactInfo','State'), 'type' => 'text',
                               'value' => ((isset($ui_state)) ? $ui_state : ''), 'name' => 'state' );
$_input_descriptors1[] = array( 'id' => 'country', 'caption' => t('ContactInfo','Country'), 'type' => 'text',
                               'value' => ((isset($ui_country)) ? $ui_country : ''), 'name' => 'country' );
$_input_descriptors1[] = array( 'id' => 'zip', 'caption' => t('ContactInfo','Zip'), 'type' => 'text',
                               'value' => ((isset($ui_zip)) ? $ui_zip : ''), 'name' => 'zip' );

// fieldset
$_fieldset0_descriptor = array(
                                "title" => t('title','ContactInfo'),
                              );
                              
open_fieldset($_fieldset0_descriptor);

foreach ($_input_descriptors0 as $input_descriptor) {
    print_form_component($input_descriptor);
}

close_fieldset();

unset($_input_descriptors0);

// fieldset
$_fieldset1_descriptor = array(
                                "title" => "Business Info",
                              );

open_fieldset($_fieldset1_descriptor);

foreach ($_input_descriptors1 as $input_descriptor) {
    print_form_component($input_descriptor);
}

close_fieldset();

unset($_input_descriptors1);
