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
if (strpos($_SERVER['PHP_SELF'], '/menu-mng-rad-nas.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Management";

?>

    <script src="library/javascript/rounded-corners.js"></script>
    <script src="library/javascript/form-field-tooltip.js"></script>
    <link rel="stylesheet" href="css/form-field-tooltip.css" media="screen">

<?php
    include_once("include/menu/menu-items.php");
	include_once("include/menu/management-subnav.php");
    include_once("include/management/autocomplete.php");
?>
		
            <div id="sidebar">

                <h2>Management</h2>

                <h3>NAS Management</h3>
                <ul class="subnav">
                    <li>
                        <a href="mng-rad-nas-list.php" title="<?= t('button','ListNAS') ?>" tabindex="1">
                            <b>&raquo;</b><?= t('button','ListNAS') ?>
                        </a>
                    </li>
                    
                    <li>
                        <a href="mng-rad-nas-new.php" title="<?= t('button','NewNAS') ?>" tabindex="2">
                            <b>&raquo;</b><?= t('button','NewNAS') ?>
                        </a>
                    </li>
                    
                    <li>
                        <a href="javascript:document.mngradnasedit.submit();" title="<?= t('button','EditNAS') ?>" tabindex="3">
                            <b>&raquo;</b><?= t('button','EditNAS') ?>
                        </a>
                        
                        <form name="mngradnasedit" action="mng-rad-nas-edit.php" method="GET" class="sidebar">
                            <input name="nasname" type="text" id="nashostEdit" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','NasName'); ?>" tabindex="4">
                        </form>
                    </li>
                    
                    <li>
                        <a href="mng-rad-nas-del.php" title="<?= t('button','RemoveNAS') ?>" tabindex="5">
                            <b>&raquo;</b><?= t('button','RemoveNAS') ?>
                        </a>
                    </li>
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->

<script>
<?php
    if ($autoComplete) {
?>
    var autoComEdit = new DHTMLSuite.autoComplete();
    autoComEdit.add('nashostEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteNASHost');
<?php
    }
?>
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>

