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
 * Description:	An update/upgrade page to handle the upgrade of the database tables
 *
 *********************************************************************************************************
 */

$failureMsg = "";							// variable initializtion
$successMsg = "";							// variable initializtion

include_once('library/config_read.php');

/*
  * updateErrorHandler()
  * when an error is triggered, related to transactions performed on the database connection, this function will be triggered.
  * for the purpose of the update page it is not required to display the error on the top of the page but rather it is encapulated
  * into a variable container after each database transaction. After the update process has completed this variable provides all
  * the errors that took place as a central piece of information.
  */
function updateErrorHandler($err) {
/*
        echo("<br/><b>Database error</b><br>
                <b>Failure Message: </b>" . $err->getMessage() . "<br><b>Debug info: </b>" . $err->getDebugInfo() . "<br>");
*/
}

/* check if the configuration parameter DALORADIUS_VERSION was set. This is later on used to automatically display
  * the user with the auto-detected daloRADIUS version. Otherwise an error message is displayed, asking the user to enter
  * the  daloRADIUS version that is currently installed.
  */
if (!isset($configValues['DALORADIUS_VERSION'])) {
	$failureMsg .= "Couldn't find the configuration variable DALORADIUS_VERSION defined in <b>daloradius.conf.php</b><br/>";
	$missingVersion = "Failed detetion of daloRADIUS Version. Choose from the list";
}


if (!is_writable("library/daloradius.conf.php")) {
	$failureMsg .= "Unable to write settings to the configuration file <b>daloradius.conf.php</b>,<br/>
			Please set it writable for the webserver user and refresh the page before you continue";
}

if (isset($_POST['submit'])) {

	$databaseVersion = $_POST['daloradius_version'];		// daloradius's version (which is essentially the database version) which is currently running
	$upgradeErrors = array();								// variable initializtion

	include('library/opendb.php');
	$dbSocket->setErrorHandling(PEAR_ERROR_CALLBACK, 'updateErrorHandler');			// set our own callback for error handling



	/* perform conversion procedure to upgrade to version 0.9-4 of the database schema */
	if ($databaseVersion == "0.9-4") {

		$sql = "ALTER TABLE operators DROP COLUMN rep_username;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}

		$sql = "ALTER TABLE operators ADD acct_custom_query VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET acct_custom_query='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}
	
		$sql = "ALTER TABLE operators ADD config_maint_disconnect_user VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET config_maint_disconnect_user='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}
	
                /* Ending set of SQL entries */
                $databaseVersion = "0.9-5";
        } // 0.9-4



	/* perform conversion procedure to upgrade to version 0.9-5 of the database schema */
	if ($databaseVersion == "0.9-5") {

		$sql = "ALTER TABLE operators ADD mng_rad_profiles_edit VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}


		$sql = "ALTER TABLE operators ADD mng_rad_profiles_list VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_profiles_del VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_profiles_new='yes',mng_rad_profiles_edit='yes',mng_rad_profiles_list='yes',mng_rad_profiles_del='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_groupcheck_search VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_groupcheck_search='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_groupreply_search VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}  else {
                        $sql = "UPDATE operators SET mng_rad_groupreply_search='yes' WHERE username='administrator';";
                        $res = $dbSocket->query($sql);
                }


		$sql = "ALTER TABLE operators CHANGE rep_stat_radius rep_stat_services VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}


		$sql = "ALTER TABLE userinfo ADD creationdate DATETIME;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}



		$sql = "ALTER TABLE operators CHANGE lastlogin lastlogin DATETIME;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}


		$sql = "ALTER TABLE operators ADD creationdate DATETIME;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}


		/* Begining set of SQL entries */
		$sql = "
				DROP TABLE IF EXISTS `dictionary`; CREATE TABLE `dictionary` (
				  `id` int(10) NOT NULL auto_increment,
				  `Type` varchar(30) default NULL,
				  `Attribute` varchar(64) default NULL,
				  `Value` varchar(64) default NULL,
				  `Format` varchar(20) default NULL,
				  `Vendor` varchar(32) default NULL,
				  `RecommendedOP` varchar(32) default NULL,
				  `RecommendedTable` varchar(32) default NULL,
				  `RecommendedHelper` varchar(32) default NULL,
				  `RecommendedTooltip` varchar(32) default NULL,
				  PRIMARY KEY (`id`)
				);
			";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}


                /* Ending set of SQL entries */
                $databaseVersion = "0.9-6";
        } // 0.9-5


	/* perform conversion procedure to upgrade to version 0.9-6 of the database schema */
	if ($databaseVersion == "0.9-6") {

		/* Begining set of SQL entries */
		$sql = "
					CREATE TABLE realms (
					        id bigint(20) NOT NULL auto_increment,
					        realmname VARCHAR(128) default NULL,
					        type VARCHAR(32) default NULL,
					        authhost VARCHAR(256) default NULL,
					        accthost VARCHAR(256) default NULL,
					        secret VARCHAR(128) default NULL,
					        ldflag VARCHAR(64) default NULL,
					        nostrip INT(8) default NULL,
					        hints INT(8) default NULL,
					        notrealm INT(8) default NULL,
						creationdate DATETIME default NULL,
						creationby VARCHAR(128) default NULL,
						updatedate DATETIME default NULL,
						updateby VARCHAR(128) default NULL,
					        PRIMARY KEY (id)
					);
				";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}

		$sql = "
					CREATE TABLE proxys (
					        id bigint(20) NOT NULL auto_increment,
					        proxyname VARCHAR(128) default NULL,
					        retry_delay INT(8) default NULL,
					        retry_count INT(8) default NULL,
					        dead_time INT(8) default NULL,
					        default_fallback INT(8) default NULL,
						creationdate DATETIME default NULL,
						creationby VARCHAR(128) default NULL,
						updatedate DATETIME default NULL,
						updateby VARCHAR(128) default NULL,
					        PRIMARY KEY (id)
					)
				";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_attributes_list VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_attributes_list='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_attributes_new VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_attributes_new='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}
		$sql = "ALTER TABLE operators ADD mng_rad_attributes_edit VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_attributes_edit='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_attributes_search VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_attributes_search='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_attributes_del VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_attributes_del='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_realms_list VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_realms_list='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_realms_new VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_realms_new='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_realms_edit VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_realms_edit='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_realms_del VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_realms_del='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_proxys_list VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_proxys_list='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_proxys_new VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_proxys_new='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_proxys_edit VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_proxys_edit='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_proxys_del VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_proxys_del='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD acct_maintenance_cleanup VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET acct_maintenance_cleanup='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD acct_maintenance_delete VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET acct_maintenance_delete='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD rep_history VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET rep_history='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "
					ALTER TABLE hotspots CHANGE website companywebsite VARCHAR(128);
					ALTER TABLE hotspots ADD companyemail VARCHAR(128) AFTER companywebsite;
					ALTER TABLE hotspots ADD companycontact VARCHAR(32) AFTER companyemail;
					ALTER TABLE hotspots ADD companyphone VARCHAR(32) AFTER companycontact;
				";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}

		$sql = "
					ALTER TABLE hotspots ADD creationdate DATETIME AFTER companyphone;
					ALTER TABLE hotspots ADD creationby VARCHAR(128) AFTER creationdate;
					ALTER TABLE hotspots ADD updatedate DATETIME AFTER creationby;
					ALTER TABLE hotspots ADD updateby VARCHAR(128) AFTER updatedate;
				";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}

		$sql = "
					ALTER TABLE userinfo ADD creationby VARCHAR(128) AFTER creationdate;
					ALTER TABLE userinfo ADD updatedate DATETIME AFTER creationby;
					ALTER TABLE userinfo ADD updateby VARCHAR(128) AFTER updatedate;
				";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}

		$sql = "
					ALTER TABLE operators ADD creationby VARCHAR(128) AFTER creationdate;
					ALTER TABLE operators ADD updatedate DATETIME AFTER creationby;
					ALTER TABLE operators ADD updateby VARCHAR(128) AFTER updatedate;
			";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_ippool_list VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_ippool_list='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_ippool_new VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_ippool_new='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_ippool_edit VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_ippool_edit='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		$sql = "ALTER TABLE operators ADD mng_rad_ippool_del VARCHAR(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_ippool_del='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		/* Ending set of SQL entries */
		$databaseVersion = "0.9-7";
	} // 0.9-6
	
	/* perform conversion procedure to upgrade to version 0.9-7 of the database schema */
	if ($databaseVersion == "0.9-7") {

		/* Begining set of SQL entries */
	
		$sql = "ALTER TABLE userinfo ADD changeuserinfo VARCHAR(128) AFTER notes;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}


		$sql = "ALTER TABLE operators ADD mng_rad_profiles_duplicate VARCHAR(32) AFTER mng_rad_profiles_edit;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_profiles_duplicate='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}


		$sql = "ALTER TABLE operators ADD mng_rad_attributes_import VARCHAR(32) AFTER mng_rad_attributes_del;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET mng_rad_attributes_import='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}



		$sql = "ALTER TABLE operators ADD config_backup_createbackups VARCHAR(32) AFTER config_operators_new;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET config_backup_createbackups='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}


		$sql = "ALTER TABLE operators ADD config_backup_managebackups VARCHAR(32) AFTER config_backup_createbackups;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET config_backup_managebackups='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}


		$sql = "ALTER TABLE userinfo ADD address VARCHAR(200) AFTER mobilephone;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}

		$sql = "ALTER TABLE userinfo ADD city VARCHAR(200) AFTER address;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}

		$sql = "ALTER TABLE userinfo ADD state VARCHAR(200) AFTER city;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}

		$sql = "ALTER TABLE userinfo ADD zip VARCHAR(200) AFTER state;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}


		$sql = "ALTER TABLE operators DROP COLUMN bill_prepaid;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}


		$sql = "ALTER TABLE operators CHANGE bill_persecond bill_rates_date varchar(32);";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}


		$sql = " 
					CREATE TABLE billing_rates (
					  id int(11) unsigned NOT NULL auto_increment,
					  rateName varchar(128) NOT NULL default '',
					  rateType varchar(128) NOT NULL default '',
					  rateCost int(32) NOT NULL default 0,
					  creationdate datetime default '0000-00-00 00:00:00',
					  creationby varchar(128) default NULL,
					  updatedate datetime default '0000-00-00 00:00:00',
					  updateby varchar(128) default NULL,
					  PRIMARY KEY (id),
					  KEY rateName (rateName(128))
					);
				";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}

		$sql = "ALTER TABLE operators ADD bill_paypal_transactions VARCHAR(32) AFTER bill_rates_list;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET bill_paypal_transactions='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}


		$sql = "ALTER TABLE operators ADD bill_plans_list VARCHAR(32) AFTER bill_paypal_transactions;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET bill_plans_list='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}


		$sql = "ALTER TABLE operators ADD bill_plans_new VARCHAR(32) AFTER bill_plans_list;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET bill_plans_new='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}


		$sql = "ALTER TABLE operators ADD bill_plans_edit VARCHAR(32) AFTER bill_plans_new;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET bill_plans_edit='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}


		$sql = "ALTER TABLE operators ADD bill_plans_del VARCHAR(32) AFTER bill_plans_edit;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET bill_plans_del='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}


		$sql = "
					CREATE TABLE billing_plans (
						id int(8) NOT NULL auto_increment,
						planName varchar(128) default NULL,
						planId varchar(128) default NULL,
						planType varchar(128) default NULL,
						planTimeBank varchar(128) default NULL,
						planTimeType varchar(128) default NULL,
						planTimeRefillCost varchar(128) default NULL,
						planBandwidthUp varchar(128) default NULL,
						planBandwidthDown varchar(128) default NULL,
						planTrafficTotal varchar(128) default NULL,
						planTrafficUp varchar(128) default NULL,
						planTrafficDown varchar(128) default NULL,
						planTrafficRefillCost varchar(128) default NULL,
						planRecurring varchar(128) default NULL,
						planRecurringPeriod varchar(128) default NULL,
						planCost varchar(128) default NULL,
						planSetupCost varchar(128) default NULL,
						planTax varchar(128) default NULL,
						planCurrency varchar(128) default NULL,
						planGroup varchar(128) default NULL,
						creationdate datetime default '0000-00-00 00:00:00',
						creationby varchar(128) default NULL,
						updatedate datetime default '0000-00-00 00:00:00',
						updateby varchar(128) default NULL,
						PRIMARY KEY (id),
						KEY planName (planName)
					);
				";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}

		$sql = "
					CREATE TABLE `billing_paypal` (
					  `id` int(8) NOT NULL auto_increment,
					  `username` varchar(128) default NULL,
					  `password` varchar(128) default NULL,
					  `mac` varchar(128) default NULL,
					  `pin` varchar(128) default NULL,
					  `txnId` varchar(128) default NULL,
					  `planName` varchar(128) default NULL,
					  `planId` varchar(128) default NULL,
					  `quantity` varchar(128) default NULL,
					  `receiver_email` varchar(128) default NULL,
					  `business` varchar(128) default NULL,
					  `tax` varchar(128) default NULL,
					  `mc_gross` varchar(128) default NULL,
					  `mc_fee` varchar(128) default NULL,
					  `mc_currency` varchar(128) default NULL,
					  `first_name` varchar(128) default NULL,
					  `last_name` varchar(128) default NULL,
					  `payer_email` varchar(128) default NULL,
					  `address_name` varchar(128) default NULL,
					  `address_street` varchar(128) default NULL,
					  `address_country` varchar(128) default NULL,
					  `address_country_code` varchar(128) default NULL,
					  `address_city` varchar(128) default NULL,
					  `address_state` varchar(128) default NULL,
					  `address_zip` varchar(128) default NULL,
					  `payment_date` datetime default NULL,
					  `payment_status` varchar(128) default NULL,
					  `payment_address_status` varchar(128) default NULL,
					  `payer_status` varchar(128) default NULL,
					  PRIMARY KEY (`id`),
					  KEY `username` (`username`)
					);
				";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}


		$sql = "
					CREATE TABLE `userbillinfo` (
					  `id` int(8) unsigned NOT NULL auto_increment,
					  `username` varchar(64) default NULL,
					  `planName` varchar(128) default NULL,
					  `contactperson` varchar(200) default NULL,
					  `company` varchar(200) default NULL,
					  `email` varchar(200) default NULL,
					  `phone` varchar(200) default NULL,
					  `address` varchar(200) default NULL,
					  `city` varchar(200) default NULL,
					  `state` varchar(200) default NULL,
					  `zip` varchar(200) default NULL,
					  `paymentmethod` varchar(200) default NULL,
					  `cash` varchar(200) default NULL,
					  `creditcardname` varchar(200) default NULL,
					  `creditcardnumber` varchar(200) default NULL,
					  `creditcardverification` varchar(200) default NULL,
					  `creditcardtype` varchar(200) default NULL,
					  `creditcardexp` varchar(200) default NULL,
					  `notes` varchar(200) default NULL,
					  `changeuserbillinfo` varchar(128) default NULL,
					  `lead` varchar(200) default NULL,
					  `coupon` varchar(200) default NULL,
					  `ordertaker` varchar(200) default NULL,
					  `billstatus` varchar(200) default NULL,
					  `lastbill` datetime default '0000-00-00 00:00:00',
					  `nextbill` datetime default '0000-00-00 00:00:00',
					  `postalinvoice` varchar(8) default NULL,
					  `faxinvoice` varchar(8) default NULL,
					  `emailinvoice` varchar(8) default NULL,
					  `creationdate` datetime default '0000-00-00 00:00:00',
					  `creationby` varchar(128) default NULL,
					  `updatedate` datetime default '0000-00-00 00:00:00',
					  `updateby` varchar(128) default NULL,
					  PRIMARY KEY (`id`),
					  KEY `username` (`username`)
					);
				";

		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}


		$sql = "ALTER TABLE operators ADD bill_pos_list VARCHAR(32) AFTER bill_rates_list;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET bill_pos_list='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}


		$sql = "ALTER TABLE operators ADD bill_pos_new VARCHAR(32) AFTER bill_pos_list;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET bill_pos_new='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}


		$sql = "ALTER TABLE operators ADD bill_pos_edit VARCHAR(32) AFTER bill_pos_new;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET bill_pos_edit='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}


		$sql = "ALTER TABLE operators ADD bill_pos_del VARCHAR(32) AFTER bill_pos_edit;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET bill_pos_del='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}


		$sql = "
					CREATE TABLE `billing_history` (
					  `id` int(8) unsigned NOT NULL auto_increment,
					  `username` varchar(128) default NULL,
					  `planName` varchar(128) default NULL,
					  `billAmount` varchar(128) default NULL,
					  `billAction` varchar(128) default NULL,
					  `billPerformer` varchar(128) default NULL,
					  `billReason` varchar(128) default NULL,
					  `paymentmethod` varchar(200) default NULL,
					  `cash` varchar(200) default NULL,
					  `creditcardname` varchar(200) default NULL,
					  `creditcardnumber` varchar(200) default NULL,
					  `creditcardverification` varchar(200) default NULL,
					  `creditcardtype` varchar(200) default NULL,
					  `creditcardexp` varchar(200) default NULL,
					  `coupon` varchar(200) default NULL,
					  `discount` varchar(200) default NULL,
					  `notes` varchar(200) default NULL,
					  `creationdate` datetime default '0000-00-00 00:00:00',
					  `creationby` varchar(128) default NULL,
					  `updatedate` datetime default '0000-00-00 00:00:00',
					  `updateby` varchar(128) default NULL,
					  PRIMARY KEY (`id`),
					  KEY `username` (`username`)
					);
				";


		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}


		$sql = "ALTER TABLE operators ADD bill_history_query VARCHAR(32) AFTER bill_plans_list;";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "UPDATE operators SET bill_history_query='yes' WHERE username='administrator';";
			$res = $dbSocket->query($sql);
		}

		/* Ending set of SQL entries */

		/* We continue to also upgrade the configuration parameters for 0.9-8 */

		if (!isset($configValues['CONFIG_DB_TBL_DALOBILLINGPAYPAL']))
			$configValues['CONFIG_DB_TBL_DALOBILLINGPAYPAL'] = 'billing_paypal';

		if (!isset($configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']))
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'] = 'billing_plans';

		if (!isset($configValues['CONFIG_DB_TBL_DALOBILLINGRATES']))
			$configValues['CONFIG_DB_TBL_DALOBILLINGRATES'] = 'billing_rates';

		if (!isset($configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY']))
			$configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY'] = 'billing_history';

		if (!isset($configValues['CONFIG_DB_TBL_DALOUSERBILLINFO']))
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'] = 'userbillinfo';

		if (!isset($configValues['FREERADIUS_VERSION']))
			$configValues['FREERADIUS_VERSION'] = '1';

		if (!isset($configValues['DALORADIUS_VERSION']))
			$configValues['DALORADIUS_VERSION'] = '0.9-8';

		/* Ending configuration parameters upgrade */

		$databaseVersion = "0.9-8";
	} // 0.9-7

	include 'library/closedb.php';


	
	/* if this is an upgrade from a previous version like 0.9-7 then there is no daloradius.conf.php
	   file created and so we need to create one... */

	if (!file_exists("library/daloradius.conf.php")) {
		$configValues['DALORADIUS_VERSION'] = '0.9-8';
		$configValues['FREERADIUS_VERSION'] = '1';
		$configValues['CONFIG_DB_ENGINE'] = 'mysql';
		$configValues['CONFIG_DB_HOST'] = '127.0.0.1';
		$configValues['CONFIG_DB_USER'] = 'root';
		$configValues['CONFIG_DB_PASS'] = '';
		$configValues['CONFIG_DB_NAME'] = 'radius';
		$configValues['CONFIG_DB_TBL_RADCHECK'] = 'radcheck';
		$configValues['CONFIG_DB_TBL_RADREPLY'] = 'radreply';
		$configValues['CONFIG_DB_TBL_RADGROUPREPLY'] = 'radgroupreply';
		$configValues['CONFIG_DB_TBL_RADGROUPCHECK'] = 'radgroupcheck';
		$configValues['CONFIG_DB_TBL_RADUSERGROUP'] = 'usergroup';
		$configValues['CONFIG_DB_TBL_RADNAS'] = 'nas';
		$configValues['CONFIG_DB_TBL_RADPOSTAUTH'] = 'radpostauth';
		$configValues['CONFIG_DB_TBL_RADACCT'] = 'radacct';
		$configValues['CONFIG_DB_TBL_RADIPPOOL'] = 'radippool';
		$configValues['CONFIG_DB_TBL_DALOOPERATOR'] = 'operators';
		$configValues['CONFIG_DB_TBL_DALORATES'] = 'billing_rates';
		$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'] = 'hotspots';
		$configValues['CONFIG_DB_TBL_DALOUSERINFO'] = 'userinfo';
		$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'] = 'userbillinfo';
		$configValues['CONFIG_DB_TBL_DALODICTIONARY'] = 'dictionary';
		$configValues['CONFIG_DB_TBL_DALOREALMS'] = 'realms';
		$configValues['CONFIG_DB_TBL_DALOPROXYS'] = 'proxys';
		$configValues['CONFIG_FILE_RADIUS_PROXY'] = '/etc/freeradius/proxy.conf';
		$configValues['CONFIG_PATH_RADIUS_DICT'] = '/usr/share/freeradius';
		$configValues['CONFIG_PATH_DALO_VARIABLE_DATA'] = '/var/www/daloradius-svn/var';
		$configValues['CONFIG_DB_PASSWORD_ENCRYPTION'] = 'cleartext';
		$configValues['CONFIG_LANG'] = 'en';
		$configValues['CONFIG_LOG_PAGES'] = 'yes';
		$configValues['CONFIG_LOG_ACTIONS'] = 'yes';
		$configValues['CONFIG_LOG_QUERIES'] = 'yes';
		$configValues['CONFIG_DEBUG_SQL'] = 'yes';
		$configValues['CONFIG_DEBUG_SQL_ONPAGE'] = 'yes';
		$configValues['CONFIG_LOG_FILE'] = '/tmp/daloradius.log';
		$configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] = 'no';
		$configValues['CONFIG_IFACE_TABLES_LISTING'] = '25';
		$configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] = 'yes';
		$configValues['CONFIG_IFACE_AUTO_COMPLETE'] = 'yes';
		$configValues['CONFIG_MAINT_TEST_USER_RADIUSSERVER'] = '127.0.0.1';
		$configValues['CONFIG_MAINT_TEST_USER_RADIUSPORT'] = '1812';
		$configValues['CONFIG_MAINT_TEST_USER_NASPORT'] = '0';
		$configValues['CONFIG_MAINT_TEST_USER_RADIUSSECRET'] = 'testing123';
		$configValues['CONFIG_LOCATIONS'] = 'Array';
		$configValues['CONFIG_DB_TBL_DALOBILLINGPAYPAL'] = 'billing_paypal';
		$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'] = 'billing_plans';
		$configValues['CONFIG_DB_TBL_DALOBILLINGRATES'] = 'billing_rates';
		$configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY'] = 'billing_history';
	}

	
	$configValues['DALORADIUS_VERSION'] = $databaseVersion;		// after finishing with upgrade, update the daloRADIUS version parameter in library/daloradius.conf.php
	include ("library/config_write.php");						// save the new database version for daloRADIUS in the config file.

	$updateStatus = "true";
	$successMsg .= "<br/>Finished upgrade procedure to version $databaseVersion.
			<br/><br/><a href='index.php'>Return</a> to daloRADIUS Platform login.";
	
	// append to the failureMsg variable all the errors which took place during the database queries.
	// the failureMsg variable is then echo'ed while in the div element for the action Messages
	foreach($upgradeErrors as $error) {
		$failureMsg .= $error."<br/>";
	}

} // if 'submit'

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script src="library/javascript/rounded-corners.js" type="text/javascript"></script>
<script src="library/javascript/form-field-tooltip.js" type="text/javascript"></script>
<script type="text/javascript" src="library/javascript/ajax.js"></script>
<script type="text/javascript" src="library/javascript/ajaxGeneric.js"></script>
<script src="library/javascript/rounded-corners.js" type="text/javascript"></script>
<script src="library/javascript/form-field-tooltip.js" type="text/javascript"></script>
<link rel="stylesheet" href="css/form-field-tooltip.css" type="text/css" media="screen,projection" />

<body>
<?php
	$m_active = "Update";
	include_once("lang/main.php");
?>

<div id="wrapper">
<div id="innerwrapper">
		
                <div id="header">

                                <h1><a href="index.php"> <img src="images/daloradius_small.png" border=0/></a></h1>
                                <h2>
                                	Radius Management, Reporting and Accounting by <a href="http://www.enginx.com">Enginx</a>                                
                                </h2>
                                <ul id="nav">
				<a name='top'></a>
				<li><a href="index.php"><em>H</em>ome</a></a></li>
				<li><a href="update.php" class="active"><em>U</em>pdate</a></a></li>
                                </ul>
                                <ul id="subnav">
					<div id="logindiv" style="text-align: right;">
                                                <li>daloRADIUS Update/Upgrade</li><br/>
					</div>
                                </ul>
								
                </div>

      

<div id="sidebar">

	<h2>Database Update</h2>

	<h3>Quick-Access</h3>

	<ul class="subnav">

		<li><a href="index.php"><b>&raquo;</b>Home</a></li>
		<li><a href="update.php"><b>&raquo;</b>Update</a></li>

	</ul>
	
	<h3>Support</h3>

	<p class="news">
		daloRADIUS <br/>
		RADIUS Management Platform
		<a href="http://www.enginx.com" class="more">Read More &raquo;</a>
	</p>


	<h2>Search</h2>

	<input name="" type="text" value="Search" />

</div>

		
		
		
<div id="contentnorightbar">
		
	<h2 id="Intro"><a href="#"></a></h2>
	<center>
		<h2> daloRADIUS Platform - Update </h2>
		<br/>
	</center>

                <?php
                        include_once('include/management/actionMessages.php');
                ?>
	<br/>


<?php if ((isset($updateStatus)) && ($updateStatus == "true")): ?>
	
<?php else: ?>

<form name="update" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

        <fieldset>

                <h302> PHP Extensions </h302>
                <br/>

                <ul>

		Checking required PHP Modules

                <li class='fieldset'>
                <label for='name' class='form'>
			GD Extension
		</label>
		<?php if (extension_loaded("gd")): ?>
			<b>Installed</b>
		<?php else: ?>
			<font color="red"><b>Not-installed</b></font>
		<?php endif; ?>
		<br/>


                <li class='fieldset'>
                <label for='name' class='form'>
			MySQL Extension
		</label>
		<?php if (extension_loaded("mysql")): ?>
			<b>Installed</b>
		<?php else: ?>
			<font color="red"><b>Not-installed</b></font>
		<?php endif; ?>
		<br/>


                <li class='fieldset'>
                <label for='name' class='form'>
			DB Extension
		</label>
		<?php if (extension_loaded("dba")): ?>
			<b>Installed</b>
		<?php else: ?>
			<font color="red"><b>Not-installed</b></font>
		<?php endif; ?>
		<br/>
	
                </ul>
        </fieldset>

		
        <fieldset>

                <h302> Update </h302>
                <br/>

                <ul>

                <li class='fieldset'>
                <label for='name' class='form'>

	<?php 
		/*
		if (isset($missingVersion)) {
			$option = "<option value=\"\">Please select</option>";
			echo $missingVersion;
		} else {
			$option = "<option value=\"".$configValues['DALORADIUS_VERSION']."\">".$configValues['DALORADIUS_VERSION']."</option>";
			echo "Successfully detected your daloRADIUS version as";
		}
		*/

		/* I have "broken" the auto-detect function just for the 0.9-8 release, because this is the first time we introduce
		   the DALORADIUS_VERSION parameter and it might cause confusion with the auto-detect, so for now I am specifically
		   asking the user to choose his daloradius version (where 0.9-8 is not an option since it's the NEW version) */

		$option = "<option value=\"\">Please select</option>";
		echo "Your daloRADIUS version: ";
	?>

		</label>

	<select name="daloradius_version" class='form'>
		<?php echo $option; ?>
		<option value=""></option>
		<option value="0.9-7">0.9-7</option>
		<option value="0.9-6">0.9-6</option>
		<option value="0.9-5">0.9-5</option>
		<option value="0.9-4">0.9-4</option>
	</select>

		<br/>
	
                <li class='fieldset'>
                <br/>
                <hr><br/>
		<input type='submit' name='submit' value="Update" class='button' />
                </li>

                </ul>
        </fieldset>

</form>

<?php endif; ?>	


</div>
		

<div id="footer">
		
								

<?php
echo "
	".$l['all']['copyright2']."
	<br />
	</p>
";

?>

<br />
</p>
		
		</div>
		
</div>
</div>


</body>
</html>
