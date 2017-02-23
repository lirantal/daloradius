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
 * Authors:     Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

include('../../library/checklogin.php');



/* getAjaxAutocompletePaymentName */
if(isset($_GET['getAjaxAutocompletePaymentName'])) {

        $getAjaxAutocompletePaymentName = $_GET['getAjaxAutocompletePaymentName'];

        if ( (isset($getAjaxAutocompletePaymentName)) && ($getAjaxAutocompletePaymentName) ) {

                include '../../library/opendb.php';

                $sql = "SELECT distinct(value) as value FROM ".$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'].
                        " WHERE value LIKE '" . $dbSocket->escapeSimple($getAjaxAutocompletePaymentName) . "%' ORDER BY value ASC";
                $res = $dbSocket->query($sql);

                while($row = $res->fetchRow()) {
                        echo "" . htmlspecialchars($row[0], ENT_QUOTES) . "###" . htmlspecialchars($row[0], ENT_QUOTES) . "|";
                }

                include '../../library/closedb.php';
        }

}



/* getAjaxAutocompleteContactPerson */
if(isset($_GET['getAjaxAutocompleteContactPerson'])) {

        $getAjaxAutocompleteContactPerson = $_GET['getAjaxAutocompleteContactPerson'];

	if ( (isset($getAjaxAutocompleteContactPerson)) && ($getAjaxAutocompleteContactPerson) ) {

	        include '../../library/opendb.php';

	        $sql = "SELECT distinct(contactperson), id FROM ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
			" WHERE contactperson LIKE '" . $dbSocket->escapeSimple($getAjaxAutocompleteContactPerson) . "%' ORDER BY contactperson ASC";
	        $res = $dbSocket->query($sql);

	        while($row = $res->fetchRow()) {
	                echo "" . htmlspecialchars($row[0], ENT_QUOTES) . "###" . htmlspecialchars($row[0], ENT_QUOTES) . "|";
	        }

	        include '../../library/closedb.php';
	}

}




/* getAjaxAutocompleteBatchNames */
if(isset($_GET['getAjaxAutocompleteBatchNames'])) {

        $getAjaxAutocompleteBatchNames = $_GET['getAjaxAutocompleteBatchNames'];

	if ( (isset($getAjaxAutocompleteBatchNames)) && ($getAjaxAutocompleteBatchNames) ) {

	        include '../../library/opendb.php';

	        $sql = "SELECT distinct(batch_name) as batchName FROM ".$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'].
			" WHERE batch_name LIKE '" . $dbSocket->escapeSimple($getAjaxAutocompleteBatchNames) . "%' ORDER BY batch_name ASC";
	        $res = $dbSocket->query($sql);

	        while($row = $res->fetchRow()) {
	                echo "" . htmlspecialchars($row[0], ENT_QUOTES) . "###" . htmlspecialchars($row[0], ENT_QUOTES) . "|";
	        }

	        include '../../library/closedb.php';
	}

}


/* getAjaxAutocompleteNASHost */
if(isset($_GET['getAjaxAutocompleteNASHost'])) {

        $getAjaxAutocompleteNASHost = $_GET['getAjaxAutocompleteNASHost'];

	if ( (isset($getAjaxAutocompleteNASHost)) && ($getAjaxAutocompleteNASHost) ) {

	        include '../../library/opendb.php';

	        $sql = "SELECT distinct(nasname) as nasName FROM ".$configValues['CONFIG_DB_TBL_RADNAS'].
			" WHERE nasName LIKE '" . $dbSocket->escapeSimple($getAjaxAutocompleteNASHost) . "%' ORDER BY nasName ASC";
	        $res = $dbSocket->query($sql);

	        while($row = $res->fetchRow()) {
	                echo "" . htmlspecialchars($row[0], ENT_QUOTES) . "###" . htmlspecialchars($row[0], ENT_QUOTES) . "|";
	        }

	        include '../../library/closedb.php';
	}

}


/* getAjaxAutocompleteHGHost */
if(isset($_GET['getAjaxAutocompleteHGHost'])) {

	$getAjaxAutocompleteHGHost = $_GET['getAjaxAutocompleteHGHost'];

	if ( (isset($getAjaxAutocompleteHGHost)) && ($getAjaxAutocompleteHGHost) ) {

		include '../../library/opendb.php';

		$sql = "SELECT distinct(nasipaddress) as nasipaddress FROM ".$configValues['CONFIG_DB_TBL_RADHG'].
		" WHERE nasipaddress LIKE '" . $dbSocket->escapeSimple($getAjaxAutocompleteHGHost) . "%' ORDER BY nasipaddress ASC";
		$res = $dbSocket->query($sql);

		while($row = $res->fetchRow()) {
				echo "" . htmlspecialchars($row[0], ENT_QUOTES) . "###" . htmlspecialchars($row[0], ENT_QUOTES) . "|";
		}

		include '../../library/closedb.php';
	}

}



/* getAjaxAutocompleteGroupName */
if(isset($_GET['getAjaxAutocompleteGroupName'])) {

        $getAjaxAutocompleteGroupName = $_GET['getAjaxAutocompleteGroupName'];

	if ( (isset($getAjaxAutocompleteGroupName)) && ($getAjaxAutocompleteGroupName) ) {

	        include '../../library/opendb.php';

	        $sql = "(SELECT distinct(GroupName) AS GroupName FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY'].
			" WHERE GroupName LIKE '" . $dbSocket->escapeSimple($getAjaxAutocompleteGroupName) . "%' ORDER BY GroupName ASC) ".
                        " UNION (SELECT distinct(GroupName) AS GroupName FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK'].
			" WHERE GroupName LIKE '" . $dbSocket->escapeSimple($getAjaxAutocompleteGroupName) . "%' ORDER BY GroupName ASC)";

	        $res = $dbSocket->query($sql);

	        while($row = $res->fetchRow()) {
	                echo "" . htmlspecialchars($row[0], ENT_QUOTES) . "###" . htmlspecialchars($row[0], ENT_QUOTES) . "|";
	        }

	        include '../../library/closedb.php';
	}

}





/* getAjaxAutocompleteRateName */
if(isset($_GET['getAjaxAutocompleteRateName'])) {

        $getAjaxAutocompleteRateName = $_GET['getAjaxAutocompleteRateName'];

	if ( (isset($getAjaxAutocompleteRateName)) && ($getAjaxAutocompleteRateName) ) {

	        include '../../library/opendb.php';

	        $sql = "SELECT distinct(rateName) as rateName FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES'].
			" WHERE rateName LIKE '" . $dbSocket->escapeSimple($getAjaxAutocompleteRateName) . "%' ORDER BY rateName ASC";
	        $res = $dbSocket->query($sql);

	        while($row = $res->fetchRow()) {
	                echo "" . htmlspecialchars($row[0], ENT_QUOTES) . "###" . htmlspecialchars($row[0], ENT_QUOTES) . "|";
	        }

	        include '../../library/closedb.php';
	}

}




/* getAjaxAutocompleteBillingPlans
 * returns billing plan names from the billing_plans table matching the getAjaxAutocompleteBillingPlans value wildcad
 */
if(isset($_GET['getAjaxAutocompleteBillingPlans'])) {

        $getAjaxAutocompleteBillingPlans = $_GET['getAjaxAutocompleteBillingPlans'];

	if ( (isset($getAjaxAutocompleteBillingPlans)) && ($getAjaxAutocompleteBillingPlans) ) {

	        include '../../library/opendb.php';

	        $sql = "SELECT distinct(planName) as planName FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
			" WHERE planName LIKE '" . $dbSocket->escapeSimple($getAjaxAutocompleteBillingPlans) . "%' ORDER BY planName ASC";
	        $res = $dbSocket->query($sql);

	        while($row = $res->fetchRow()) {
	                echo "" . htmlspecialchars($row[0], ENT_QUOTES) . "###" . htmlspecialchars($row[0], ENT_QUOTES) . "|";
	        }

	        include '../../library/closedb.php';
	}

}



/* getAjaxAutocompleteHotspots
 * returns hotspot names from the hotspot table matching the getAjaxAutocompleteHotspots value wildcad
 */
if(isset($_GET['getAjaxAutocompleteHotspots'])) {

        $getAjaxAutocompleteHotspots = $_GET['getAjaxAutocompleteHotspots'];

	if ( (isset($getAjaxAutocompleteHotspots)) && ($getAjaxAutocompleteHotspots) ) {

	        include '../../library/opendb.php';

	        $sql = "SELECT distinct(Name) as Hotspot FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
			" WHERE Name LIKE '" . $dbSocket->escapeSimple($getAjaxAutocompleteHotspots) . "%' ORDER BY Name ASC";
	        $res = $dbSocket->query($sql);

	        while($row = $res->fetchRow()) {
	                echo "" . htmlspecialchars($row[0], ENT_QUOTES) . "###" . htmlspecialchars($row[0], ENT_QUOTES) . "|";
	        }

	        include '../../library/closedb.php';
	}

}



/* getAjaxAutocompleteUsernames provides a trigger to this callback routine which returns the possible usernames in the
 * radcheck table matching the getAjaxAutocompleteUsernames variable's value wildcard.
 */
if(isset($_GET['getAjaxAutocompleteUsernames'])) {

        $getAjaxAutocompleteUsernames = $_GET['getAjaxAutocompleteUsernames'];


	if ( (isset($getAjaxAutocompleteUsernames)) && ($getAjaxAutocompleteUsernames) ) {

	        include '../../library/opendb.php';

	        $sql = "SELECT distinct(Username) as Username FROM ".$configValues['CONFIG_DB_TBL_RADCHECK'].
			" WHERE Username LIKE '" . $dbSocket->escapeSimple($getAjaxAutocompleteUsernames) . "%' ORDER BY Username ASC";
	        $res = $dbSocket->query($sql);

	        while($row = $res->fetchRow()) {
	                echo "" . htmlspecialchars($row[0], ENT_QUOTES) . "###" . htmlspecialchars($row[0], ENT_QUOTES) . "|";
	        }

	        include '../../library/closedb.php';

	}

}




/* getAjaxAutocompleteAttributes - if this GET variable is set then an sql query to the database is performed
 * to retrieve all the possible attributes which match the wildcard syntax for the getAjaxAutocompleteAttributes
 * variable's value, which is meant to produce an auto-complete possible values.
 *
 * This is working in accordance to the auto-complete javascript library.
 */
if(isset($_GET['getAjaxAutocompleteAttributes'])) {

        $getAjaxAutocompleteAttributes = $_GET['getAjaxAutocompleteAttributes'];

	if ( (isset($getAjaxAutocompleteAttributes)) && ($getAjaxAutocompleteAttributes) ) {
	
	        include '../../library/opendb.php';

	        $sql = "SELECT distinct(Attribute) as Attribute FROM dictionary WHERE Attribute LIKE '" . $dbSocket->escapeSimple($getAjaxAutocompleteAttributes) . "%' ".
                "ORDER BY Vendor ASC";
	        $res = $dbSocket->query($sql);

	        while($row = $res->fetchRow()) {
	                echo "" . htmlspecialchars($row[0], ENT_QUOTES) . "###" . htmlspecialchars($row[0], ENT_QUOTES) . "|";
	        }

	        include '../../library/closedb.php';

	}

}



/* getAjaxAutocompleteVendorName */
if(isset($_GET['getAjaxAutocompleteVendorName'])) {

        $getAjaxAutocompleteVendorName = $_GET['getAjaxAutocompleteVendorName'];

	if ( (isset($getAjaxAutocompleteVendorName)) && ($getAjaxAutocompleteVendorName) ) {

	        include '../../library/opendb.php';

	        $sql = "SELECT distinct(Vendor) as VendorName FROM ".$configValues['CONFIG_DB_TBL_DALODICTIONARY'].
			" WHERE Vendor LIKE '" . $dbSocket->escapeSimple($getAjaxAutocompleteVendorName) . "%' ORDER BY VendorName ASC";
	        $res = $dbSocket->query($sql);

	        while($row = $res->fetchRow()) {
	                echo "" . htmlspecialchars($row[0], ENT_QUOTES) . "###" . htmlspecialchars($row[0], ENT_QUOTES) . "|";
	        }

	        include '../../library/closedb.php';
	}

}


?>
