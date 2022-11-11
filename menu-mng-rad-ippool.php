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
if (strpos($_SERVER['PHP_SELF'], '/menu-mng-rad-ippool.php') !== false) {
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
    include_once ("include/menu/menu-items.php");
	include_once ("include/menu/management-subnav.php");
?>
		
            <div id="sidebar">

                <h2>Management</h2>
                
                <h3>IP Pools</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= strip_tags(t('button','ListIPPools')) ?>" href="mng-rad-ippool-list.php" tabindex="1">
                            <b>&raquo;</b><?= t('button','ListIPPools') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','NewIPPool')) ?>" href="mng-rad-ippool-new.php" tabindex="2">
                            <b>&raquo;</b><?= t('button','NewIPPool') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','EditIPPool')) ?>" href="javascript:document.mngradippooledit.submit();" tabindex="3">
                            <b>&raquo;</b><?= t('button','EditIPPool') ?>
                        </a>
                        <form name="mngradippooledit" action="mng-rad-ippool-edit.php" method="GET" class="sidebar">
                            <input name="poolname" type="text" tooltipText="<?= t('Tooltip','PoolName'); ?><br>"
                                value="<?= (isset($poolname)) ? $poolname : "" ?>" tabindex="4">
                            <input name="ipaddressold" type="text" tooltipText="<?= t('Tooltip','IPAddress'); ?><br>"
                            value="<?= (isset($ipaddressold)) ? $ipaddressold : "" ?>" tabindex="5">
                        </form>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','RemoveIPPool')) ?>" href="mng-rad-ippool-del.php" tabindex="6">
                            <b>&raquo;</b><?= t('button','RemoveIPPool') ?>
                        </a>
                    </li>
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->

<script>
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>
