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

/* getAjaxAutocompleteHotspots
 * returns hotspot names from the hotspot table matching the getAjaxAutocompleteHotspots value wildcad
 */
if(isset($_GET['getAjaxAutocompleteHotspots'])) {

        $getAjaxAutocompleteHotspots = $_GET['getAjaxAutocompleteHotspots'];

	if ( (isset($getAjaxAutocompleteHotspots)) && ($getAjaxAutocompleteHotspots) ) {

	        include '../../library/opendb.php';

	        $sql = "SELECT distinct(Name) as Hotspot FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].
			" WHERE Name LIKE '$getAjaxAutocompleteHotspots%' ORDER BY Name ASC";
	        $res = $dbSocket->query($sql);

	        while($row = $res->fetchRow()) {
	                echo "$row[0]###$row[0]|";
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
			" WHERE Username LIKE '$getAjaxAutocompleteUsernames%' ORDER BY Username ASC";
	        $res = $dbSocket->query($sql);

	        while($row = $res->fetchRow()) {
	                echo "$row[0]###$row[0]|";
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

	        $sql = "SELECT distinct(Attribute) as Attribute FROM dictionary WHERE Attribute LIKE '$getAjaxAutocompleteAttributes%' ".
                "ORDER BY Vendor ASC";
	        $res = $dbSocket->query($sql);

	        while($row = $res->fetchRow()) {
	                echo "$row[0]###$row[0]|";
	        }

	        include '../../library/closedb.php';

	}

}



?>
