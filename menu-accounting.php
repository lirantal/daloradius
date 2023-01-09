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
if (strpos($_SERVER['PHP_SELF'], '/menu-accounting.php') !== false) {
    header("Location: index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Accounting";

include_once("include/menu/menu-items.php");
include_once("include/menu/accounting-subnav.php");
include_once("include/management/autocomplete.php");

?>    

            <div id="sidebar">
                <h2>Accounting</h2>
                
                <h3>Users Accounting</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= strip_tags(t('button','UserAccounting')) ?>" tabindex="1" href="javascript:document.acctusername.submit();">
                            <b>&raquo;</b><?= t('button','UserAccounting') ?>
                        </a>
                        
                        <form name="acctusername" action="acct-username.php" method="GET" class="sidebar">
                            <input tabindex="2" name="username" type="text" id="usernameAcct" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','Username') ?>"
                                value="<?= (isset($accounting_username)) ? $accounting_username : "" ?>">
                        </form>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','IPAccounting')) ?>" tabindex="3" href="javascript:document.acctipaddress.submit();">
                            <b>&raquo;</b><?= t('button','IPAccounting') ?>
                        </a>
                        
                        <form name="acctipaddress" action="acct-ipaddress.php" method="GET" class="sidebar">
                            <input tabindex="4" name="ipaddress" type="text" tooltipText="<?= t('Tooltip','IPAddress') ?>"
                                value="<?= (isset($accounting_ipaddress)) ? $accounting_ipaddress : "" ?>">
                        </form>
                    </li>
                    
                    <li>
                        <a title="<?= strip_tags(t('button','NASIPAccounting')) ?>" tabindex="5" href="javascript:document.acctnasipaddress.submit();">
                            <b>&raquo;</b><?= t('button','NASIPAccounting') ?>
                        </a>
                        
                        <form name="acctnasipaddress" action="acct-nasipaddress.php" method="GET" class="sidebar">
                            <input tabindex="6" name="nasipaddress" type="text" tooltipText="<?= t('Tooltip','IPAddress') ?>"
                                value="<?= (isset($accounting_nasipaddress)) ? $accounting_nasipaddress : "" ?>">
                        </form>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','DateAccounting')) ?>" tabindex="7" href="javascript:document.acctdate.submit();">
                            <b>&raquo;</b><?= t('button','DateAccounting') ?>
                        </a>
                        
                        <form name="acctdate" action="acct-date.php" method="GET" class="sidebar">
                            <input tabindex="8" name="username" type="text" id="usernameDate" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','Username') ?>"
                                value="<?= (isset($accounting_date_username)) ? $accounting_date_username : ""?>">
                            
                            <label style="user-select: none" for="startdate"><?= t('all','StartingDate') ?></label>
                            <input tabindex="9" name="startdate" type="date" id="startdate" tooltipText="<?= t('Tooltip','Date') ?>"
                                value="<?= (isset($accounting_date_startdate)) ? $accounting_date_startdate : date("Y-m-01") ?>">
                            
                            <label style="user-select: none" for="enddate"><?= t('all','EndingDate') ?></label>
                            <input tabindex="10" name="enddate" type="date" id="enddate" tooltipText="<?= t('Tooltip','Date') ?>"
                                value="<?= (isset($accounting_date_enddate)) ? $accounting_date_enddate : date("Y-m-t") ?>">
                        </form>
                    </li>
                    
                    <li>
                        <a title="<?= strip_tags(t('button','AllRecords')) ?>" tabindex="11" href="acct-all.php">
                            <b>&raquo;</b><?= t('button','AllRecords') ?>
                        </a>
                    </li>
                    <li>
                        <a title="<?= strip_tags(t('button','ActiveRecords')) ?>" tabindex="12" href="acct-active.php">
                            <b>&raquo;</b><?= t('button','ActiveRecords') ?>
                        </a>
                    </li>
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->

<script>
<?php
    if ($autoComplete) {
?>

    var autoComEditElements = ["usernameDate","usernameAcct"];
    for (var i = 0; i < autoComEditElements.length; i++) {
        var autoComEdit = new DHTMLSuite.autoComplete();
        autoComEdit.add(autoComEditElements[i],
                        'include/management/dynamicAutocomplete.php',
                        '_small',
                        'getAjaxAutocompleteUsernames');
    }
    
<?php
    }
?>
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>
