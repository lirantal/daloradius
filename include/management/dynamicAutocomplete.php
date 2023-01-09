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

include('../../library/checklogin.php');

// we can handle these actions
$allowedActions = array(
                            'getAjaxAutocompletePaymentName',
                            'getAjaxAutocompleteContactPerson',
                            'getAjaxAutocompleteBatchNames',
                            'getAjaxAutocompleteNASHost',
                            'getAjaxAutocompleteHGHost',
                            'getAjaxAutocompleteGroupName',
                            'getAjaxAutocompleteRateName',
                            'getAjaxAutocompleteBillingPlans',
                            'getAjaxAutocompleteHotspots',
                            'getAjaxAutocompleteUsernames',
                            'getAjaxAutocompleteAttributes',
                            'getAjaxAutocompleteVendorName',
                       );


// we set a default action
$action = $allowedActions[0];
foreach ($allowedActions as $allowedAction) {
    // if isset an allowed action we set it as chosen action
    // and exit the foreach loop
    if (isset($_GET[$allowedAction])) {
        $action = $allowedAction;
        break;
    }
}

// we set the value for the LIKE condition
$like = (array_key_exists($action, $_GET) && !empty(trim($_GET[$action]))) ? trim($_GET[$action]) : "";

// we need like to be not empty
if (empty($like)) {
    exit;
}

include('../../library/opendb.php');

$like = $dbSocket->escapeSimple($like);
$sql = "";

switch ($action) {
    
    /* getAjaxAutocompletePaymentName */
    default:
    case 'getAjaxAutocompletePaymentName':
    
        $sql = sprintf("SELECT DISTINCT(value) AS value FROM %s WHERE value LIKE '%s%%'
                         ORDER BY value ASC", $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'], $like);
    
        break;

    /* getAjaxAutocompleteContactPerson */
    case 'getAjaxAutocompleteContactPerson':
        $sql = sprintf("SELECT DISTINCT(contactperson) AS contactperson FROM %s WHERE contactperson LIKE '%s%%'
                         ORDER BY contactperson ASC", $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'], $like);
        break;
    
    /* getAjaxAutocompleteBatchNames */
    case 'getAjaxAutocompleteBatchNames':
        $sql = sprintf("SELECT DISTICT(batch_name) AS batchName FROM %s WHERE batchName LIKE '%s%%'
                         ORDER BY batchName ASC", $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'], $like);
        break;

    /* getAjaxAutocompleteNASHost */
    case 'getAjaxAutocompleteNASHost':
        $sql = sprintf("SELECT DISTINCT(nasname) AS nasName FROM %s WHERE nasName LIKE '%s%%'
                         ORDER BY nasName ASC", $configValues['CONFIG_DB_TBL_RADNAS'], $like);
    
        break;
    
        /* getAjaxAutocompleteHGHost */
    case 'getAjaxAutocompleteHGHost':
        $sql = sprintf("SELECT DISTINCT(nasipaddress) AS nasipaddress FROM %s WHERE nasipaddress LIKE '%s%%'
                         ORDER BY nasipaddress ASC", $configValues['CONFIG_DB_TBL_RADHG'], $like);
        break;

    /* getAjaxAutocompleteGroupName */
    case 'getAjaxAutocompleteGroupName':
        $sql = sprintf("SELECT DISTINCT(GroupName) AS GroupName FROM %s WHERE GroupName LIKE '%s%%'
                        ORDER BY GroupName ASC", $configValues['CONFIG_DB_TBL_RADGROUPCHECK'], $like)
             . " UNION "
             . sprintf("SELECT DISTINCT(GroupName) AS GroupName FROM %s WHERE GroupName LIKE '%s%%'
                         ORDER BY GroupName ASC", $configValues['CONFIG_DB_TBL_RADGROUPREPLY'], $like);
        break;

    /* getAjaxAutocompleteRateName */
    case 'getAjaxAutocompleteRateName':
        $sql = sprintf("SELECT DISTINCT(rateName) AS rateName FROM %s WHERE rateName LIKE '%s%%'
                         ORDER BY rateName ASC", $configValues['CONFIG_DB_TBL_DALOBILLINGRATES'], $like);
            $res = $dbSocket->query($sql);
        break;

    /* getAjaxAutocompleteBillingPlans
     * returns billing plan names from the billing_plans table
     * matching the getAjaxAutocompleteBillingPlans value wildcad
     */
    case 'getAjaxAutocompleteBillingPlans':
        $sql = sprintf("SELECT DISTINCT(planName) AS planName FROM %s WHERE planName LIKE '%s%%'
                         ORDER BY planName ASC", $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'], $like);
        break;

    /* getAjaxAutocompleteHotspots
     * returns hotspot names from the hotspot table
     * matching the getAjaxAutocompleteHotspots value wildcad
     */
    case 'getAjaxAutocompleteHotspots':
        $sql = sprintf("SELECT DISTINCT(Name) AS hotspotName FROM %s WHERE hotspotName LIKE '%s%%'
                         ORDER BY hotspotName ASC", $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'], $like);
        break;

    /* getAjaxAutocompleteUsernames provides a trigger to this callback routine
     * which returns the possible usernames in the radcheck table matching
     * the getAjaxAutocompleteUsernames variable's value wildcard.
     */
    case 'getAjaxAutocompleteUsernames':
        $sql = sprintf("SELECT DISTINCT(Username) AS Username FROM %s WHERE Username LIKE '%s%%'
                         ORDER BY Username ASC", $configValues['CONFIG_DB_TBL_RADCHECK'], $like);
        break;

    /* getAjaxAutocompleteAttributes: if this GET variable is set then an sql query to the database is performed
     * to retrieve all the possible attributes which match the wildcard syntax for the getAjaxAutocompleteAttributes
     * variable's value, which is meant to produce an auto-complete possible values.
     */
    case 'getAjaxAutocompleteAttributes':
        $sql = sprintf("SELECT DISTINCT(Attribute) AS Attribute FROM %s WHERE Attribute LIKE '%s%%'
                         ORDER BY Attribute ASC", $configValues['CONFIG_DB_TBL_DALODICTIONARY'], $like);
        break;

    /* getAjaxAutocompleteVendorName */
    case 'getAjaxAutocompleteVendorName':
        $sql = sprintf("SELECT DISTINCT(Vendor) AS vendorName FROM %s WHERE vendorName LIKE '%s%%'
                         ORDER BY vendorName ASC", $configValues['CONFIG_DB_TBL_DALODICTIONARY'], $like);
        break;

}

if (!empty($sql)) {
    $res = $dbSocket->query($sql);
    
    while ($row = $res->fetchRow()) {
        $value = htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8');
        printf("%s###%s|", $value, $value);
    }
}

include('../../library/closedb.php');

?>
