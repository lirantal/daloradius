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
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/management/contactinfo.php') !== false) {
    header("Location: ../../index.php");
    exit;
}

$input_descriptors1 = array();
$input_descriptors1[] = array( "name" => "ownername", "caption" => t('ContactInfo','OwnerName'),
                               "type" => "text", "value" => ((isset($owner)) ? $owner : ""), );
$input_descriptors1[] = array( "name" => "emailowner", "caption" => t('ContactInfo','OwnerEmail'),
                               "type" => "text", "value" => ((isset($email_owner)) ? $email_owner : "") , );
$input_descriptors1[] = array( "name" => "managername", "caption" => t('ContactInfo','ManagerName'),
                               "type" => "text", "value" => ((isset($manager)) ? $manager : "") , );
$input_descriptors1[] = array( "name" => "emailmanager", "caption" => t('ContactInfo','ManagerEmail'),
                               "type" => "text", "value" => ((isset($email_manager)) ? $email_manager : "") , );
$input_descriptors1[] = array( "name" => "company", "caption" => t('ContactInfo','Company'),
                               "type" => "text", "value" => ((isset($company)) ? $company : "") , );
$input_descriptors1[] = array( "name" => "address", "caption" => t('ContactInfo','Address'),
                               "type" => "textarea", "content" => ((isset($address)) ? $address : ""), );                               
$input_descriptors1[] = array( "name" => "phone1", "caption" => t('ContactInfo','Phone1'),
                               "type" => "text", "value" => ((isset($phone1)) ? $phone1 : "") , );
$input_descriptors1[] = array( "name" => "phone2", "caption" => t('ContactInfo','Phone2'),
                               "type" => "text", "value" => ((isset($phone2)) ? $phone2 : "") , );
$input_descriptors1[] = array( "name" => "hotspot_type", "caption" => t('ContactInfo','HotspotType'),
                               "type" => "text", "value" => ((isset($hotspot_type)) ? $hotspot_type : "") , );
$input_descriptors1[] = array( "name" => "companywebsite", "caption" => t('ContactInfo','CompanyWebsite'),
                               "type" => "text", "value" => ((isset($companywebsite)) ? $companywebsite : "") , );
$input_descriptors1[] = array( "name" => "companyemail", "caption" => t('ContactInfo','CompanyEmail'),
                               "type" => "text", "value" => ((isset($companyemail)) ? $companyemail : "") , );
$input_descriptors1[] = array( "name" => "companyphone", "caption" => t('ContactInfo','CompanyPhone'),
                               "type" => "text", "value" => ((isset($companyphone)) ? $companyphone : "") , );
$input_descriptors1[] = array( "name" => "companycontact", "caption" => t('ContactInfo','CompanyContact'),
                               "type" => "text", "value" => ((isset($companycontact)) ? $companycontact : "") , );

$input_descriptors2 = array();
$input_descriptors2[] = array( 'name' => 'creationdate', 'caption' => t('all','CreationDate'), 'type' => 'text',
                               'disabled' => true, 'value' => ((isset($creationdate)) ? $creationdate : '') );
$input_descriptors2[] = array( 'name' => 'creationby', 'caption' => t('all','CreationBy'), 'type' => 'text',
                               'disabled' => true, 'value' => ((isset($creationby)) ? $creationby : '') );
$input_descriptors2[] = array( 'name' => 'updatedate', 'caption' => t('all','UpdateDate'), 'type' => 'text',
                               'disabled' => true, 'value' => ((isset($updatedate)) ? $updatedate : '') );
$input_descriptors2[] = array( 'name' => 'updateby', 'caption' => t('all','UpdateBy'), 'type' => 'text',
                               'disabled' => true, 'value' => ((isset($updateby)) ? $updateby : '') );

$button_descriptor = array(
                            "type" => "submit",
                            "name" => "submit",
                            "value" => t('buttons','apply')
                          );

?>

<fieldset>
    <h302>Contact Info</h302>
    <h301>Contact Info</h301>

    <ul>
<?php

    foreach ($input_descriptors1 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

?>
    </ul>
</fieldset>

<fieldset>
    <h302>Other</h302>
    <h301>Other</h301>
    <ul>
<?php

    foreach ($input_descriptors2 as $input_descriptor) {
        print_form_component($input_descriptor);
    }

?>
    </ul>
</fieldset>

<?php
    print_form_component($button_descriptor);
?>
