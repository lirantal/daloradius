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
if (strpos($_SERVER['PHP_SELF'], '/menu-accounting-custom.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Accounting";


include_once("include/menu/menu-items.php");
include_once("include/menu/accounting-subnav.php");

?>

            <div id="sidebar">
                <h2>Accounting</h2>
                
                <h3>Custom Query</h3>
                <ul class="subnav">
                
                    <li>
                        <form name="acctcustomquery" action="acct-custom-query.php" method="get" class="sidebar">
                            <input class="sidebutton" type="submit" name="submit" value="<?= t('button','ProcessQuery') ?>">
                            <br><br>
                            
                            <h109><?= t('button','BetweenDates') ?></h109><br>
                            
                            <label style="user-select: none" for="startdate"><?= t('all','StartingDate') ?></label>
                            <input name="startdate" type="date" id="startdate" tooltipText="<?= t('Tooltip','Date') ?><br>"
                                value="<?= (isset($accounting_custom_startdate)) ? $accounting_custom_startdate : date("Y-m-01") ?>">

                            <label style="user-select: none" for="enddate"><?= t('all','EndingDate') ?></label>
                            <input name="enddate" type="date" id="enddate" tooltipText="<?= t('Tooltip','Date') ?><br>"
                                value="<?= (isset($accounting_custom_enddate)) ? $accounting_custom_enddate : date("Y-m-t") ?>">
                            
                            <br><br>
                            
<?php
                                
                            $descr = array(
                                            "caption" => t('button','Where'),
                                            "type" => "select",
                                            "name" => "where_field",
                                            "options" => $acct_custom_query_options_all,
                                            "selected_value" => ((isset($where_field)) ? $where_field : $acct_custom_query_options_all[0]),
                                          );
                            
                            print_form_component($descr);
                            
                            
                            $descr = array(
                                            "caption" => "Operator",
                                            "type" => "select",
                                            "name" => "operator",
                                            "options" => array("equals", "contains"),
                                            "selected_value" => ((isset($operator)) ? $operator : "equals"),
                                          );
                            
                            print_form_component($descr);
                            
?>
                            <input type="text" name="where_value" tooltipText="<?= t('Tooltip','Filter') ?><br>"
                                value="<?= (isset($accounting_custom_value)) ? $accounting_custom_value : "" ?>">
                            <br><br>

<?php
                            $descr = array(
                                            "caption" => t('button','AccountingFieldsinQuery'),
                                            "type" => "select",
                                            "name" => "sqlfields[]",
                                            "id" => "sqlfields",
                                            "options" => $acct_custom_query_options_all,
                                            "selected_value" => ((isset($sqlfields)) ? $sqlfields : $acct_custom_query_options_default),
                                            "multiple" => true
                                          );
                            
                            print_form_component($descr);
?>
                            <a style="display: inline" href="#" onclick="select('all')">Select All</a>
                            <a style="display: inline" href="#" onclick="select('none')">Select None</a>
                            <br><br>

<script>
    function select(what) {
        var selected = (what == 'all'),
            sqlfields = document.getElementById('sqlfields');
    
        for (var i = 0; i < sqlfields.options.length; i++) {
            sqlfields.options[i].selected = selected;
        }
    }
</script>
<?php
                            $descr = array(
                                            "caption" => t('button','OrderBy'),
                                            "type" => "select",
                                            "name" => "orderBy",
                                            "options" => $acct_custom_query_options_all,
                                            "selected_value" => ((isset($orderBy)) ? $orderBy : $acct_custom_query_options_all[0])
                                          );
                            
                            print_form_component($descr);

                            $descr = array(
                                            "caption" => "Order Type",
                                            "type" => "select",
                                            "name" => "orderType",
                                            "options" => array("asc" => "Ascending", "desc" => "Descending"),
                                            "selected_value" => ((isset($orderType)) ? $orderType : "asc")
                                          );
                            
                            print_form_component($descr);
?>
                            <br>
                            
                            <input class="sidebutton" type="submit" name="submit" value="<?= t('button','ProcessQuery') ?>">
                        </form>
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
