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

?>

<fieldset>
    <h302> <?= t('title','Attributes') ?> </h302>
    
    <br>

    <input checked type="radio" name="vendor_attribute_selection_type" onclick="toggleAttributeSelectbox()">
    <b> Locate Attribute via Vendor/Attribute </b>
    
    <br>

    <ul>
        <li class="fieldset">
            <label for="dictVendors0" class="form">Vendor:</label>
            <select id="dictVendors0" onchange="getAttributesList(this,'dictAttributesDatabase')" class="form">
                <option value="">Select Vendor...</option>
<?php
            include('library/opendb.php');

            $sql = sprintf("SELECT DISTINCT(Vendor) AS Vendor
                              FROM %s
                             WHERE Vendor <> '' AND Vendor IS NOT NULL
                             ORDER BY Vendor ASC",
                           $configValues['CONFIG_DB_TBL_DALODICTIONARY']);
            $res = $dbSocket->query($sql);

            while ($row = $res->fetchRow()) {
                $vendor = htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8');
                printf('<option value="%s">%s</option>', $vendor, $vendor);
            }

            include('library/closedb.php');
?>
            </select>
            <input type="button" name="reloadAttributes" id="reloadAttributes" value="Reload Vendors"
                onclick="getVendorsList('dictVendors0');" class="button">
        </li>
        
        <li class="fieldset">
            <label for="attribute" class="form">Attribute:</label>
            <select id="dictAttributesDatabase" class="form"></select>
            <input type="button" name="addAttributes" value="Add Attribute" id="addAttributesVendor"
                onclick="javascript:parseAttribute(1);" class="button">
        </li>
    </ul>
    
    <br>
    
    <input type="radio" name="vendor_attribute_selection_type" onclick="toggleAttributeCustom()">
    <b> Quickly Locate attribute with autocomplete input</b>
    
    <br>

    <ul>
        <li class="fieldset">
            <label for="attribute" class="form">Custom Attribute:</label>
            <input disabled type="text" id="dictAttributesCustom" autocomplete="off">
        
<?php

            include_once('library/config_read.php');

            if (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE']) &&
                strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) == "yes") {

                include_once("include/management/autocomplete.php");

                echo "
<script>
autoComEdit.add('dictAttributesCustom','include/management/dynamicAutocomplete.php','_large','getAjaxAutocompleteAttributes');
</script>
";
            }

?>
            <input disabled type="button" name="addAttributes" value="Add Attribute" id="addAttributesCustom"
                onclick="parseAttribute(2);" class="button">
        </li>
    </ul>

</fieldset>

<input type="hidden" value="0" id="divCounter">
<div id="divContainer"></div>
