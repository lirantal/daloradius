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
if (strpos($_SERVER['PHP_SELF'], '/menu-reports-batch.php') !== false) {
    header("Location: index.php");
    exit;
}

$m_active = "Reports";

?>


<?php
    include_once("include/menu/menu-items.php");
    include_once("include/menu/reports-subnav.php");
    include_once("include/management/autocomplete.php");
?>

            <div id="sidebar">
                <h2>Batch Users</h2>
                
                <h3>List</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= strip_tags(t('button','BatchHistory')) ?>" tabindex="1" href="rep-batch-list.php">
                            <b>&raquo;</b><?= t('button','BatchHistory') ?>
                        </a>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','BatchDetails')) ?>" tabindex="2" href="javascript:document.batch_name_details.submit();">
                            <b>&raquo;</b><?= t('button','BatchDetails') ?>
                        </a>
                        <form name="batch_name_details" action="rep-batch-details.php" method="GET" class="sidebar">
                            <input tabindex="3" name="batch_name" type="text" id="batchNameDetails" <?= ($autoComplete) ? 'autocomplete="off"' : "" ?>
                                tooltipText="<?= t('Tooltip','BatchName') ?>"
                                value="<?= (isset($batch_name_details)) ? $batch_name_details : "" ?>">
                        </form>
                    </li>
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->

<script>
<?php
    if ($autoComplete) {
?>
    varautoComEdit = new DHTMLSuite.autoComplete();
    autoComEdit.add('batchNameDetails','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteBatchNames');

<?php
    }
?>
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>
