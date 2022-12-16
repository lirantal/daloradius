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
if (strpos($_SERVER['PHP_SELF'], '/menu-bill-history.php') !== false) {
    header("Location: index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Billing";

include_once("include/menu/menu-items.php");
include_once("include/menu/billing-subnav.php");

?>

            <div id="sidebar">
                <h2>Billing</h2>
                
                <h3>Track Billing History</h3>
                <ul class="subnav">
                    <li>
                        <form name="billhistory" action="bill-history-query.php" method="GET" class="sidebar">
                            <input class="sidebutton" type="submit" name="submit" value="<?= t('button','ProcessQuery') ?>">
                            
                            <br><br>
<?php
/*                            <h109><?= t('button','BetweenDates') ?></h109><br>
                            <label style="user-select: none" for="startdate"><?= t('all','StartingDate') ?></label>
                            <input name="startdate" type="date" id="startdate" tooltipText="<?= t('Tooltip','Date') ?>"
                                value="<?= (isset($billing_date_startdate)) ? $billing_date_startdate : date("Y-m-01") ?>">

                            <label style="user-select: none" for="enddate"><?= t('all','EndingDate') ?></label>
                            <input name="enddate" type="date" id="enddate" tooltipText="<?= t('Tooltip','Date') ?>"
                                value="<?= (isset($billing_date_enddate)) ? $billing_date_enddate : date("Y-m-t") ?>">

                            <br><br>
*/

                            $descr = array(
                                            "caption" => t('all','Username'),
                                            "type" => "text",
                                            "name" => "username",
                                            "value" => ((isset($billing_history_username)) ? $billing_history_username : ""),
                                          );
                                          
                            print_form_component($descr);

                            $descr = array(
                                            "caption" => t('all','BillAction'),
                                            "type" => "select",
                                            "name" => "billaction",
                                            "options" => $valid_billactions,
                                            "selected_value" => ((isset($billaction)) ? $billaction : $valid_billactions[0]),
                                          );
                            
                            print_form_component($descr);
?>

                            <br><br>
<?php
                            
                            $descr = array(
                                            "caption" => t('button','AccountingFieldsinQuery'),
                                            "type" => "select",
                                            "name" => "sqlfields[]",
                                            "id" => "sqlfields",
                                            "options" => $bill_history_query_options_all,
                                            "selected_value" => ((isset($sqlfields)) ? $sqlfields : $bill_history_query_options_default),
                                            "multiple" => true
                                          );
                            
                            print_form_component($descr);
                            
?>
                            <a style="display: inline" href="#" onclick="select('all')">Select All</a>
                            <a style="display: inline" href="#" onclick="select('none')">Select None</a>
                            <br><br>

<?php
                            $descr = array(
                                            "caption" => t('button','OrderBy'),
                                            "type" => "select",
                                            "name" => "orderBy",
                                            "options" => $bill_history_query_options_all,
                                            "selected_value" => ((isset($orderBy)) ? $orderBy : $bill_history_query_options_all[0])
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
    function select(what) {
        var selected = (what == 'all'),
            sqlfields = document.getElementById('sqlfields');
    
        for (var i = 0; i < sqlfields.options.length; i++) {
            sqlfields.options[i].selected = selected;
        }
    }
    
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>
