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
if (strpos($_SERVER['PHP_SELF'], '/menu-config-operators.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Config";

?>

<body>
    <div id="wrapper">
        <div id="innerwrapper">
		
<?php
    include_once ("include/menu/menu-items.php");
    include_once ("include/menu/config-subnav.php");
?>      

            <div id="sidebar">
                <h2>Configuration</h2>
	
                <h3>Management</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= strip_tags(t('button','ListOperators')) ?>" tabindex="1" href="config-operators-list.php">
                            <b>&raquo;</b><?= t('button','ListOperators') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','NewOperator')) ?>" tabindex="2" href="config-operators-new.php">
                            <b>&raquo;</b><?= t('button','NewOperator') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','EditOperator')) ?>" tabindex="3" href="javascript:document.mngedit.submit();">
                            <b>&raquo;</b><?= t('button','EditOperator') ?>
                        </a>
                        <form name="mngedit" action="config-operators-edit.php" method="GET" class="sidebar">
                            <input name="operator_username" type="text" tooltipText="<?= t('Tooltip','OperatorName'); ?><br>">
                        </form>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','RemoveOperator')) ?>" tabindex="4" href="config-operators-del.php">
                            <b>&raquo;</b><?= t('button','RemoveOperator') ?>
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
