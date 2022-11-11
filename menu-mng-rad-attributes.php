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

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/menu-mng-rad-attributes.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Management";

include_once("include/menu/menu-items.php");
include_once("include/menu/management-subnav.php");
include_once("include/management/autocomplete.php");
?>
        
            <div id="sidebar">

                <h2>Management</h2>
                
                <h3>Attributes Management</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= strip_tags(t('button','ListAttributesforVendor')) ?>" href="javascript:document.mngradattributeslist.submit();">
                            <b>&raquo;</b><?= t('button','ListAttributesforVendor') ?></a>
                        <form name="mngradattributeslist" action="mng-rad-attributes-list.php" method="GET" class="sidebar">
<?php
                            include('include/management/populate_selectbox.php');
                            populate_vendors("Select Vendor", "vendor", "generic");
?>
                        </form>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','NewVendorAttribute')) ?>" href="mng-rad-attributes-new.php">
                            <b>&raquo;</b><?= t('button','NewVendorAttribute') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','EditVendorAttribute')) ?>" href="javascript:document.mngradattributesedit.submit();">
                            <b>&raquo;</b><?= t('button','EditVendorAttribute') ?>
                        </a>
                        <form name="mngradattributesedit" action="mng-rad-attributes-edit.php" method="GET" class="sidebar">
                            <input name="vendor" type="text" id="vendornameEdit" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','VendorName'); ?><br>"
                            value="<?= (isset($vendor)) ? $vendor : "" ?>">
                            <input name="attribute" type="text" id="attributenameEdit" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','AttributeName'); ?><br>" value="<?= (isset($attribute)) ? $attribute : "" ?>">
                        </form>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','SearchVendorAttribute')) ?>" href="javascript:document.mngradattributessearch.submit();" >
                            <b>&raquo;</b><?= t('button','SearchVendorAttribute') ?>
                        </a>
                        <form name="mngradattributessearch" action="mng-rad-attributes-search.php" method="GET" class="sidebar">
                            <input name="attribute" type="text" id="attributenameSearch" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','AttributeName'); ?><br>" value="<?= (isset($attribute)) ? $attribute : "" ?>">
                        </form>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','RemoveVendorAttribute')) ?>" href="mng-rad-attributes-del.php">
                            <b>&raquo;</b><?= t('button','RemoveVendorAttribute') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','ImportVendorDictionary')) ?>" href="mng-rad-attributes-import.php">
                            <b>&raquo;</b><?= t('button','ImportVendorDictionary') ?>
                        </a>
                    </li>
                    
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->

<script>
<?php
    if ($autoComplete) {
?>
var autoComEdit = new DHTMLSuite.autoComplete();
autoComEdit.add('attributenameSearch','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteAttributes');

autoComEdit = new DHTMLSuite.autoComplete();
autoComEdit.add('attributenameEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteAttributes');

autoComEdit = new DHTMLSuite.autoComplete();
autoComEdit.add('vendornameEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteVendorName');
<?php
    }
?>
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>
