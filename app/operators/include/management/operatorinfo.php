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
 * Description:    This file extends the operators config pages and
 *                 adds a section for operator contact information.
 * 
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/management/operatorinfo.php') !== false) {
    header("Location: ../../index.php");
    exit;
}

$input_descriptors1 = array();

$input_descriptors1[] = array( 'name' =>'firstname', 'caption' => 'Operator Firstname', 'type' => 'text',
                               'value' => ((isset($operator_firstname)) ? $operator_firstname : ""),
                             );
$input_descriptors1[] = array( 'name' =>'lastname', 'caption' => 'Operator Lastname', 'type' => 'text',
                               'value' => ((isset($operator_lastname)) ? $operator_lastname : ""),
                             );
$input_descriptors1[] = array( 'name' =>'title', 'caption' => 'Operator Title', 'type' => 'text',
                               'value' => ((isset($operator_title)) ? $operator_title : ""),
                             );
$input_descriptors1[] = array( 'name' =>'department', 'caption' => 'Operator Department', 'type' => 'text',
                               'value' => ((isset($operator_department)) ? $operator_department : ""),
                             );
$input_descriptors1[] = array( 'name' =>'company', 'caption' => 'Operator Company', 'type' => 'text',
                               'value' => ((isset($operator_company)) ? $operator_company : ""),
                             );
$input_descriptors1[] = array( 'name' =>'phone1', 'caption' => 'Operator Phone1', 'type' => 'text',
                               'value' => ((isset($operator_phone1)) ? $operator_phone1 : ""),
                             );
$input_descriptors1[] = array( 'name' =>'phone2', 'caption' => 'Operator Phone2', 'type' => 'text',
                               'value' => ((isset($operator_phone2)) ? $operator_phone2 : ""),
                             );
$input_descriptors1[] = array( 'name' =>'email1', 'caption' => 'Operator Email1', 'type' => 'text',
                               'value' => ((isset($operator_email1)) ? $operator_email1 : ""),
                             );
$input_descriptors1[] = array( 'name' =>'email2', 'caption' => 'Operator Email2', 'type' => 'text',
                               'value' => ((isset($operator_email2)) ? $operator_email2 : ""),
                             );
$input_descriptors1[] = array( 'name' =>'messenger1', 'caption' => 'Operator Messenger1', 'type' => 'text',
                               'value' => ((isset($operator_messenger1)) ? $operator_messenger1 : ""),
                             );
$input_descriptors1[] = array( 'name' =>'messenger2', 'caption' => 'Operator Messenger2', 'type' => 'text',
                               'value' => ((isset($operator_messenger2)) ? $operator_messenger2 : ""),
                             );
$input_descriptors1[] = array( 'name' =>'notes', 'caption' => 'Operator Notes', 'type' => 'textarea',
                               'content' => ((isset($notes)) ? $notes : ""),
                             );
                             
$input_descriptors1[] = array( 'name' => 'operator_lastlogin', 'caption' => 'Operator Last Login', 'disabled' => true,
                               'type' => 'text', 'value' => ((isset($operator_lastlogin)) ? $operator_lastlogin : ""),
                             );
$input_descriptors1[] = array( 'name' => 'creationdate', 'caption' => t('all','CreationDate'), 'disabled' => true,
                               'type' => 'text', 'value' => ((isset($operator_creationdate)) ? $operator_creationdate : ""),
                             );
$input_descriptors1[] = array( 'name' => 'creationby', 'caption' => t('all','CreationBy'), 'disabled' => true,
                               'type' => 'text', 'value' => ((isset($operator_creationby)) ? $operator_creationby : ""),
                             );
$input_descriptors1[] = array( 'name' => 'updatedate', 'caption' => t('all','UpdateDate'), 'disabled' => true,
                               'type' => 'text', 'value' => ((isset($operator_updatedate)) ? $operator_updatedate : ""),
                             );
$input_descriptors1[] = array( 'name' => 'updateby', 'caption' => t('all','UpdateBy'), 'disabled' => true,
                               'type' => 'text', 'value' => ((isset($operator_updateby)) ? $operator_updateby : ""),
                             );


$submit_descriptor = array(
                                "type" => "submit",
                                "name" => "submit",
                                "value" => t('buttons','apply')
                          );                                
                            
echo "<fieldset>"
   . "<h302>Operator Details</h302>"
   . "<ul>";

foreach ($input_descriptors1 as $input_descriptor) {
    print_form_component($input_descriptor);
}

echo "</ul>"
   . "</fieldset>";

print_form_component($submit_descriptor);
?>
