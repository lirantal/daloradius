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
if (strpos($_SERVER['PHP_SELF'], '/include/management/attributes.php') !== false) {
    header('Location: ../../index.php');
    exit;
}

include('library/opendb.php');

$vendors = array( "" );
$sql = sprintf("SELECT DISTINCT(Vendor) AS Vendor
                  FROM %s
                 WHERE Vendor<>'' AND Vendor IS NOT NULL
                 ORDER BY Vendor ASC",
               $configValues['CONFIG_DB_TBL_DALODICTIONARY']);
$res = $dbSocket->query($sql);
while ($row = $res->fetchRow()) {
    $vendors[] = $row[0];
}

$attributes = array();
if (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) && strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) == "yes") {
    $sql = sprintf("SELECT DISTINCT(attribute)
                      FROM %s
                     WHERE attribute<>'' AND attribute IS NOT NULL
                     ORDER BY attribute ASC",
                    $configValues['CONFIG_DB_TBL_DALODICTIONARY']);

    $res = $dbSocket->query($sql);
    while ($row = $res->fetchRow()) {
        $attributes[] = $row[0];
    }

}

include('library/closedb.php');

$_fieldset0_descriptor = array(
                                "title" => t('title','Attributes') . " (dictionary)",
                             );

//
$_input_descriptors0 = array();
$_input_descriptors0[] = array(
                                "name" => "vendor_attribute_selection_type",
                                "caption" => "Select attribute from vendor/attribute list",
                                "type" => "radio",
                                "onclick" => "toggleAttributeSelectbox()",
                                "checked" => true
                              );

$_input_descriptors0[] = array(
                                "name" => "dictVendors0",
                                "caption" => "Vendor",
                                "type" => "select",
                                "onchange" => "getAttributesList(this,'dictAttributesDatabase')",
                                "options" => $vendors,
                              );

$_input_descriptors0[] = array(
                                "name" => "reloadAttributes",
                                "value" => "Reload Vendors",
                                "type" => "button",
                                "onclick" => "getVendorsList('dictVendors0')",
                              );

$_input_descriptors0[] = array(
                                "name" => "dictAttributesDatabase",
                                "caption" => "Attribute",
                                "type" => "select",
                              );

$_input_descriptors0[] = array(
                                "name" => "addAttributesVendor",
                                "value" => "Add Attribute",
                                "type" => "button",
                                "onclick" => "parseAttribute(1)",
                              );

$_fieldset1_descriptor = array(
                                "title" => t('title','Attributes') . " (custom)",
                             );

$_input_descriptors1[] = array(
                                "name" => "vendor_attribute_selection_type",
                                "caption" => "Use autocomplete to select the attribute",
                                "type" => "radio",
                                "onclick" => "toggleAttributeCustom()"
                              );

$_input_descriptors1[] = array(
                                "name" => "dictAttributesCustom",
                                "caption" => "Custom Attribute",
                                "type" => "text",
                                "onclick" => "toggleAttributeCustom()",
                                "datalist" => $attributes,
                                "disabled" => true
                              );

$_input_descriptors1[] = array(
                                "name" => "addAttributesCustom",
                                "value" => "Add Attribute",
                                "type" => "button",
                                "onclick" => "parseAttribute(2)",
                                "disabled" => true
                              );

$_input_descriptors2 = array();

$_input_descriptors2[] = array(
                                'type' => 'submit',
                                'name' => 'submit',
                                'value' => t('buttons','apply')
                             );

$_input_descriptors2[] = array(
                                "name" => "divCounter",
                                "value" => "0",
                                "type" => "hidden",
                              );

open_form();
    
open_fieldset($_fieldset0_descriptor);

foreach ($_input_descriptors0 as $input_descriptor) {
    print_form_component($input_descriptor);
}

close_fieldset();

open_fieldset($_fieldset1_descriptor);

foreach ($_input_descriptors1 as $input_descriptor) {
    print_form_component($input_descriptor);
}

close_fieldset();

foreach ($_input_descriptors2 as $input_descriptor) {
    print_form_component($input_descriptor);
}

close_form();

?>

<div id="divContainer"></div>
