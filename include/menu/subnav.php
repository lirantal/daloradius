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
 * Authors:    Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/menu/subnav.php') !== false) {
    header("Location: ../../index.php");
    exit;
}

// load subnav category. valid categories are:
// "acct", "bill", "gis", "help", "mng", "rep", "config", "graphs", "home"

$subnav = array();

// home subnav elements
$subnav["home"] = array();

// graphs subnav elements
$subnav["graphs"] = array();

// config subnav elements
$subnav["config"] = array(
                            'General' => 'config-main.php',
                            'Reporting' => 'config-reports.php',
                            'Maintenance' => 'config-maint.php',
                            'Operators' => 'config-operators.php',
                            'Backup' => 'config-backup.php',
                        );

// rep subnav elements
$subnav["rep"] = array(
                            'General' => 'rep-main.php',
                            'Logs' => 'rep-logs.php',
                            'Status' => 'rep-stat.php',
                            'Batch Users' => 'rep-batch.php',
                            'Dashboard' => 'rep-hb.php',
                        );

// mng subnav elements
$subnav["mng"] = array(
                            'Users' => 'mng-users.php',
                            'Batch Users' => 'mng-batch.php',
                            'Hotspots' => 'mng-hs.php',
                            'Nas' => 'mng-rad-nas.php',
                            'User-Groups' => 'mng-rad-usergroup.php',
                            'Profiles' => 'mng-rad-profiles.php',
                            'HuntGroups' => 'mng-rad-hunt.php',
                            'Attributes' => 'mng-rad-attributes.php',
                            'Realm/Proxy' => 'mng-rad-realms.php',
                            'IP-Pool' => 'mng-rad-ippool.php',
                        );

// help subnav elements
$subnav["help"] = array();

// gis subnav elements
$subnav["gis"] = array();

// bill subnav elements
$subnav["bill"] = array(
                            'POS' => 'bill-pos.php',
                            'Plans' => 'bill-plans.php',
                            'Rates' => 'bill-rates.php',
                            'Merchant-Transactions' => 'bill-merchant.php',
                            'Billing-History' => 'bill-history.php',
                            'Invoices' => 'bill-invoice.php',
                            'Payments' => 'bill-payments.php',
                        );

// acct subnav elements
$subnav["acct"] = array(
                            'General' => 'acct-main.php',
                            'Plans' => 'acct-plans.php',
                            'Custom' => 'acct-custom.php',
                            'Hotspot' => 'acct-hotspot.php',
                            'Maintenance' => 'acct-maintenance.php',
                        );




// detect category from the PHP_SELF name
$basename = basename($_SERVER['PHP_SELF']);
$detect_category = substr($basename, 0, strpos($basename, '-'));
if (!in_array($detect_category, array_keys($subnav))) {
    $detect_category = "home";
}

if (!empty($detect_category) && count($subnav[$detect_category]) > 0) {

?>

<nav class="border-bottom text-bg-light py-1">
    <div class="d-flex">
        <ul class="nav ms-4">
<?php
            foreach ($subnav[$detect_category] as $label => $href) {
                $label = htmlspecialchars(strip_tags(trim(t('submenu', $label))), ENT_QUOTES, 'UTF-8');
                printf('<li><a class="nav-link link-dark px-2" href="%s">%s</a></li>', urlencode($href), $label);
            }
?>
        </ul>
    </div>
</nav>

<?php

}
