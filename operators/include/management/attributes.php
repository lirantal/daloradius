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

include('../common/includes/db_open.php');

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

include('../common/includes/db_close.php');

$_fieldset0_descriptor = array(
                                "title" => t('title','Attributes'),
                             );

//

// custom valid authTypes
$valid_attrSpecMethods = array(
                            "attr-from-dict" => "Select attributes from dictionary",
                            "custom-attr" => "Insert a custom attribute"
                        );

$_input_descriptors0 = array();

$_input_descriptors0[] = array(
                                "type" =>"select",
                                "name" => "attrSpecMethod",
                                "caption" => "Attribute Specification Method",
                                "options" => $valid_attrSpecMethods,
                                "onchange" => "switchAttrSpecMethod()",
                             );

open_fieldset($_fieldset0_descriptor);

foreach ($_input_descriptors0 as $input_descriptor) {
    print_form_component($input_descriptor);
}

unset($_input_descriptors0);

close_fieldset();


// fieldset 1
$_fieldset1_descriptor = array(
                                "title" => t('title','Attributes') . " (from dictionary)",
                                "id" => 'attr-from-dict-fieldset',
                             );

open_fieldset($_fieldset1_descriptor);

// custom html element
echo <<<EOF
<div class="mb-1">
    <label for="dictVendors0" class="form-label mb-1">Vendor</label>
    
    <div class="input-group mb-3">
        <select class="form-select" name="dictVendors0" id="dictVendors0" onchange="getAttributesList(this,'dictAttributesDatabase')">
EOF;

foreach ($vendors as $v) {
    $v = htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    printf('<option value="%s">%s</option>', $v, $v);
}

echo <<<EOF
        </select>
        
        <span class="input-group-text">
            <button class="btn btn-link btn-sm" type="button" onclick="getVendorsList('dictVendors0')" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Reload">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </span>
        
        <select class="form-select" id="dictAttributesDatabase" name="dictAttributesDatabase"></select>
    </div>
</div>

EOF;

$_input_descriptors1 = array();

$_input_descriptors1[] = array(
                                "name" => "addAttributesVendor",
                                "value" => "Add Attribute",
                                "type" => "button",
                                "onclick" => "parseAttribute(1)",
                                "large" => true,
                                "icon" => "plus-circle-fill",
                                "class" => "btn-secondary",
                              );

foreach ($_input_descriptors1 as $input_descriptor) {
    print_form_component($input_descriptor);
}

unset($_input_descriptors1);

close_fieldset();

// fieldset 2

$_fieldset2_descriptor = array(
                                "title" => t('title','Attributes') . " (custom)",
                                "id" => 'custom-attr-fieldset',
                             );

$_input_descriptors2 = array();

$_input_descriptors2[] = array(
                                "name" => "dictAttributesCustom",
                                "caption" => "Custom Attribute",
                                "type" => "text",
                                "datalist" => $attributes,
                              );

$_input_descriptors2[] = array(
                                "name" => "addAttributesCustom",
                                "value" => "Add Attribute",
                                "type" => "button",
                                "onclick" => "parseAttribute(2)",
                                "large" => true,
                                "icon" => "plus-circle-fill",
                                "class" => "btn-secondary",
                              );

open_fieldset($_fieldset2_descriptor);

foreach ($_input_descriptors2 as $input_descriptor) {
    print_form_component($input_descriptor);
}

unset($_input_descriptors2);

close_fieldset();

$_input_descriptors3 = array();

$_input_descriptors3[] = array(
                                "name" => "divCounter",
                                "value" => "0",
                                "type" => "hidden",
                              );

foreach ($_input_descriptors3 as $input_descriptor) {
    print_form_component($input_descriptor);
}

echo <<<EOF
<div id="divContainer"></div>

<script>
function switchAttrSpecMethod() {
    var switcher = document.getElementById("attrSpecMethod");

    for (var i=0; i<switcher.length; i++) {
        var fieldset_id = switcher[i].value + "-fieldset",
            disabled = switcher.value != switcher[i].value,
            fieldset = document.getElementById(fieldset_id);

        fieldset.disabled = disabled;
        fieldset.style.display = (disabled) ? "none" : "block";
    }
}

window.addEventListener("load", function() { switchAttrSpecMethod(); });

</script>

EOF;
