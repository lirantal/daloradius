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
if (strpos($_SERVER['PHP_SELF'], '/menu-mng-rad-hunt.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Management";

?>

<body>
    <script src="library/javascript/rounded-corners.js"></script>
    <script src="library/javascript/form-field-tooltip.js"></script>
    <link rel="stylesheet" href="css/form-field-tooltip.css" media="screen">

    <div id="wrapper">
        <div id="innerwrapper">

<?php
    include_once("include/menu/menu-items.php");
	include_once("include/menu/management-subnav.php");
    include_once("include/management/autocomplete.php");
?>
		
            <div id="sidebar">

                <h2>Management</h2>
                
                <h3>HuntGroup Management</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= strip_tags(t('button','ListHG')) ?>" href="mng-rad-hunt-list.php" tabindex="1">
                            <b>&raquo;</b><?= t('button','ListHG') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','NewHG')) ?>" href="mng-rad-hunt-new.php" tabindex="2">
                            <b>&raquo;</b><?= t('button','NewHG') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','EditHG')) ?>" href="javascript:document.mngradhuntedit.submit();" tabindex="3">
                            <b>&raquo;</b><?= t('button','EditHG') ?>
                        </a>
                        <form name="mngradhuntedit" action="mng-rad-hunt-edit.php" method="GET" class="sidebar">
                            <input name="nasipaddress" type="text" id="nashostEdit" tabindex="4"
                                <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','hgNasIpAddress'); ?><br>">
                            <input name="groupname" type="text" value="" tabindex="5"
                                    tooltipText="<?= t('Tooltip','hgGroupName'); ?><br>">                                            
                        </form>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','RemoveHG')) ?>" href="mng-rad-hunt-del.php" tabindex="6">
                            <b>&raquo;</b><?= t('button','RemoveHG') ?>
                        </a>
                    </li>
                    
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->

<script>
<?php
    if ($autoComplete) {
?>

    var autoComEdit = new DHTMLSuite.autoComplete();
    autoComEdit.add('nashostEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteHGHost');
    
<?php
    }
?>
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>

