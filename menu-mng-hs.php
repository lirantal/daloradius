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
if (strpos($_SERVER['PHP_SELF'], '/menu-mng-hs.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

?>

<body>
    <script src="library/javascript/rounded-corners.js"></script>
    <script src="library/javascript/form-field-tooltip.js"></script>
    <link rel="stylesheet" tabindex="" href="css/form-field-tooltip.css" media="screen">
    
    <div id="wrapper">
        <div id="innerwrapper">

<?php
    $m_active = "Management";
    include_once("include/menu/menu-items.php");
	include_once("include/menu/management-subnav.php");
    include_once("include/management/autocomplete.php");
?>

            <div id="sidebar">

                <h2>Management</h2>
                
                <h3>Hotspots Management</h3>
                <ul class="subnav">
                
                    <li>
                        <a title="<?= strip_tags(t('button','ListHotspots')) ?>" tabindex="1" href="mng-hs-list.php">
                            <b>&raquo;</b><?= t('button','ListHotspots') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','NewHotspot')) ?>" tabindex="2" href="mng-hs-new.php">
                            <b>&raquo;</b><?= t('button','NewHotspot') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','EditHotspot')) ?>" tabindex="3" href="javascript:document.mnghsedit.submit();">
                            <b>&raquo;</b><?= t('button','EditHotspot') ?>
                        </a>
                        <form name="mnghsedit" action="mng-hs-edit.php" method="GET" class="sidebar">
                            <input name="name" type="text"  id="hotspotEdit" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','HotspotName'); ?><br>"
                                value="<?= (isset($edit_hotspotname)) ? $edit_hotspotname : "" ?>">
                        </form>
                    </li>
                        
                    <li>
                        <a title="<?= strip_tags(t('button','RemoveHotspot')) ?>" tabindex="4" href="mng-hs-del.php">
                            <b>&raquo;</b><?= t('button','RemoveHotspot') ?>
                        </a>
                    </li>
                    
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->

<script>
<?php
    if ($autoComplete) {
?>
    var autoComEdit = new DHTMLSuite.autoComplete();
    autoComEdit.add('hotspotEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteHotspots');
    
<?php
    }
?>
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>

