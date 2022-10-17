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

	
	
	/* perform conversion procedure to upgrade to version 0.9-9 of the database schema */
	if ($databaseVersion == "0.9-8") {

		/* Begining set of SQL entries */
	
		$sql = "
		DROP TABLE IF EXISTS `billing_merchant`;
		CREATE TABLE `billing_merchant` (
		  `id` int(8) NOT NULL auto_increment,
		  `username` varchar(128) NOT NULL default '',
		  `password` varchar(128) NOT NULL default '',
		  `mac` varchar(200) NOT NULL default '',
		  `pin` varchar(200) NOT NULL default '',
		  `txnId` varchar(200) NOT NULL default '',
		  `planName` varchar(128) NOT NULL default '',
		  `planId` int(32) NOT NULL,
		  `quantity` varchar(200) NOT NULL default '',
		  `business_email` varchar(200) NOT NULL default '',
		  `business_id` varchar(200) NOT NULL default '',
		  `txn_type` varchar(200) NOT NULL default '',
		  `txn_id` varchar(200) NOT NULL default '',
		  `payment_type` varchar(200) NOT NULL default '',
		  `payment_tax` varchar(200) NOT NULL default '',
		  `payment_cost` varchar(200) NOT NULL default '',
		  `payment_fee` varchar(200) NOT NULL default '',
		  `payment_total` varchar(200) NOT NULL default '',
		  `payment_currency` varchar(200) NOT NULL default '',
		  `first_name` varchar(200) NOT NULL default '',
		  `last_name` varchar(200) NOT NULL default '',
		  `payer_email` varchar(200) NOT NULL default '',
		  `payer_address_name` varchar(200) NOT NULL default '',
		  `payer_address_street` varchar(200) NOT NULL default '',
		  `payer_address_country` varchar(200) NOT NULL default '',
		  `payer_address_country_code` varchar(200) NOT NULL default '',
		  `payer_address_city` varchar(200) NOT NULL default '',
		  `payer_address_state` varchar(200) NOT NULL default '',
		  `payer_address_zip` varchar(200) NOT NULL default '',
		  `payment_date` datetime NOT NULL default '00-00-0000 00:00:00',
		  `payment_status` varchar(200) NOT NULL default '',
		  `pending_reason` varchar(200) NOT NULL default '',
		  `reason_code` varchar(200) NOT NULL default '',
		  `receipt_ID` varchar(200) NOT NULL default '',
		  `payment_address_status` varchar(200) NOT NULL default '',
		  `vendor_type` varchar(200) NOT NULL default '',
		  `payer_status` varchar(200) NOT NULL default '',
		  PRIMARY KEY  (`id`),
		  KEY `username` (`username`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}
	
		$sql = "
		DROP TABLE IF EXISTS `operators`;
		CREATE TABLE `operators` (
		  `id` int(32) NOT NULL auto_increment,
		  `username` varchar(32) NOT NULL,
		  `password` varchar(32) NOT NULL,
		  `firstname` varchar(32) NOT NULL,
		  `lastname` varchar(32) NOT NULL,
		  `title` varchar(32) NOT NULL,
		  `department` varchar(32) NOT NULL,
		  `company` varchar(32) NOT NULL,
		  `phone1` varchar(32) NOT NULL,
		  `phone2` varchar(32) NOT NULL,
		  `email1` varchar(32) NOT NULL,
		  `email2` varchar(32) NOT NULL,
		  `messenger1` varchar(32) NOT NULL,
		  `messenger2` varchar(32) NOT NULL,
		  `notes` varchar(128) NOT NULL,
		  `lastlogin` datetime default '0000-00-00 00:00:00',
		  `creationdate` datetime default '0000-00-00 00:00:00',
		  `creationby` varchar(128) default NULL,
		  `updatedate` datetime default '0000-00-00 00:00:00',
		  `updateby` varchar(128) default NULL,
		  PRIMARY KEY  (`id`),
		  KEY username (`username`)
		) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "
			LOCK TABLES `operators` WRITE;
			/*!40000 ALTER TABLE `operators` DISABLE KEYS */;
			INSERT INTO `operators` VALUES (1,'administrator','radius','','','','','','','','','','','','','2009-12-07 20:13:20','2009-12-07 20:12:33','admin','2009-12-07 20:14:01','administrator');
			/*!40000 ALTER TABLE `operators` ENABLE KEYS */;
			UNLOCK TABLES;
			/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
			";
			$res = $dbSocket->query($sql);			
		}

		$sql = "
		DROP TABLE IF EXISTS `operators_acl`;
		CREATE TABLE `operators_acl` (
		  `id` int(32) NOT NULL auto_increment,
		  `operator_id` int(32) NOT NULL,
		  `file` varchar(128) NOT NULL,
		  `access` tinyint(8) NOT NULL default '0',
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=226 DEFAULT CHARSET=latin1;
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "
			LOCK TABLES `operators_acl` WRITE;
			/*!40000 ALTER TABLE `operators_acl` DISABLE KEYS */;
			INSERT INTO `operators_acl` VALUES (114,1,'acct_custom_query',1),(115,1,'acct_active',1),(116,1,'acct_all',1),(117,1,'acct_ipaddress',1),(118,1,'acct_username',1),(119,1,'acct_date',1),(120,1,'acct_nasipaddress',1),(121,1,'acct_hotspot_accounting',1),(122,1,'acct_hotspot_compare',1),(123,1,'acct_maintenance_cleanup',1),(124,1,'acct_maintenance_delete',1),(125,1,'acct_plans_usage',1),(126,1,'bill_history_query',1),(127,1,'bill_merchant_transactions',1),(128,1,'bill_plans_list',1),(129,1,'bill_plans_del',1),(130,1,'bill_plans_edit',1),(131,1,'bill_plans_new',1),(132,1,'bill_pos_del',1),(133,1,'bill_pos_list',1),(134,1,'bill_pos_new',1),(135,1,'bill_pos_edit',1),(136,1,'bill_rates_date',1),(137,1,'bill_rates_new',1),(138,1,'bill_rates_list',1),(139,1,'bill_rates_del',1),(140,1,'bill_rates_edit',1),(141,1,'config_backup_managebackups',1),(142,1,'config_backup_createbackups',1),(143,1,'config_user',1),(144,1,'config_db',1),(145,1,'config_lang',1),(146,1,'config_interface',1),(147,1,'config_logging',1),(148,1,'config_maint_test_user',1),(149,1,'config_maint_disconnect_user',1),(150,1,'config_operators_list',1),(151,1,'config_operators_new',1),(152,1,'config_operators_del',1),(153,1,'config_operators_edit',1),(154,1,'gis_editmap',1),(155,1,'gis_viewmap',1),(156,1,'graphs_alltime_logins',1),(157,1,'graphs_overall_download',1),(158,1,'graphs_overall_logins',1),(159,1,'graphs_alltime_traffic_compare',1),(160,1,'graphs_overall_upload',1),(161,1,'graphs_logged_users',1),(162,1,'mng_rad_attributes_import',1),(163,1,'mng_rad_attributes_list',1),(164,1,'mng_rad_attributes_edit',1),(165,1,'mng_rad_attributes_del',1),(166,1,'mng_rad_attributes_new',1),(167,1,'mng_rad_attributes_search',1),(168,1,'mng_rad_groupcheck_new',1),(169,1,'mng_rad_groupreply_search',1),(170,1,'mng_rad_groupreply_list',1),(171,1,'mng_rad_groupreply_edit',1),(172,1,'mng_rad_groupcheck_search',1),(173,1,'mng_rad_groupcheck_list',1),(174,1,'mng_rad_groupcheck_edit',1),(175,1,'mng_rad_groupreply_del',1),(176,1,'mng_rad_groupreply_new',1),(177,1,'mng_rad_groupcheck_del',1),(178,1,'mng_hs_edit',1),(179,1,'mng_hs_list',1),(180,1,'mng_hs_del',1),(181,1,'mng_hs_new',1),(182,1,'mng_rad_ippool_new',1),(183,1,'mng_rad_ippool_del',1),(184,1,'mng_rad_ippool_list',1),(185,1,'mng_rad_ippool_edit',1),(186,1,'mng_rad_nas_edit',1),(187,1,'mng_rad_nas_list',1),(188,1,'mng_rad_nas_del',1),(189,1,'mng_rad_nas_new',1),(190,1,'mng_rad_profiles_edit',1),(191,1,'mng_rad_profiles_del',1),(192,1,'mng_rad_profiles_new',1),(193,1,'mng_rad_profiles_duplicate',1),(194,1,'mng_rad_profiles_list',1),(195,1,'mng_rad_proxys_new',1),(196,1,'mng_rad_proxys_del',1),(197,1,'mng_rad_proxys_list',1),(198,1,'mng_rad_proxys_edit',1),(199,1,'mng_rad_realms_new',1),(200,1,'mng_rad_realms_del',1),(201,1,'mng_rad_realms_list',1),(202,1,'mng_rad_realms_edit',1),(203,1,'mng_rad_usergroup_edit',1),(204,1,'mng_rad_usergroup_list_user',1),(205,1,'mng_rad_usergroup_del',1),(206,1,'mng_rad_usergroup_new',1),(207,1,'mng_rad_usergroup_list',1),(208,1,'mng_search',1),(209,1,'mng_del',1),(210,1,'mng_new',1),(211,1,'mng_import_users',1),(212,1,'mng_batch',1),(213,1,'mng_edit',1),(214,1,'mng_new_quick',1),(215,1,'mng_list_all',1),(216,1,'rep_lastconnect',1),(217,1,'rep_online',1),(218,1,'rep_history',1),(219,1,'rep_topusers',1),(220,1,'rep_logs_radius',1),(221,1,'rep_logs_boot',1),(222,1,'rep_logs_system',1),(223,1,'rep_logs_daloradius',1),(224,1,'rep_stat_services',1),(225,1,'rep_stat_server',1);
			/*!40000 ALTER TABLE `operators_acl` ENABLE KEYS */;
			UNLOCK TABLES;
			/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
			";
			$res = $dbSocket->query($sql);
		}
		
		$sql = "
		DROP TABLE IF EXISTS `operators_acl_files`;
		CREATE TABLE `operators_acl_files` (
		  `id` int(32) NOT NULL auto_increment,
		  `file` varchar(128) NOT NULL,
		  `category` varchar(128) NOT NULL,
		  `section` varchar(128) NOT NULL,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=114 DEFAULT CHARSET=latin1;
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "
			LOCK TABLES `operators_acl_files` WRITE;
			/*!40000 ALTER TABLE `operators_acl_files` DISABLE KEYS */;
			INSERT INTO `operators_acl_files` VALUES (2,'mng_search','Management','Users'),(3,'mng_batch','Management','Users'),(4,'mng_del','Management','Users'),(5,'mng_edit','Management','Users'),(6,'mng_new','Management','Users'),(7,'mng_new_quick','Management','Users'),(8,'mng_import_users','Management','Users'),(9,'mng_list_all','Management','Users'),(10,'mng_hs_del','Management','Hotspot'),(11,'mng_hs_edit','Management','Hotspot'),(12,'mng_hs_new','Management','Hotspot'),(13,'mng_hs_list','Management','Hotspot'),(14,'mng_rad_nas_del','Management','NAS'),(15,'mng_rad_nas_edit','Management','NAS'),(16,'mng_rad_nas_new','Management','NAS'),(17,'mng_rad_nas_list','Management','NAS'),(18,'mng_rad_usergroup_del','Management','UserGroup'),(19,'mng_rad_usergroup_edit','Management','UserGroup'),(20,'mng_rad_usergroup_new','Management','UserGroup'),(21,'mng_rad_usergroup_list_user','Management','UserGroup'),(22,'mng_rad_usergroup_list','Management','UserGroup'),(23,'mng_rad_groupcheck_search','Management','Groups'),(24,'mng_rad_groupcheck_del','Management','Groups'),(25,'mng_rad_groupcheck_list','Management','Groups'),(26,'mng_rad_groupcheck_new','Management','Groups'),(27,'mng_rad_groupcheck_edit','Management','Groups'),(28,'mng_rad_groupreply_search','Management','Groups'),(29,'mng_rad_groupreply_del','Management','Groups'),(30,'mng_rad_groupreply_list','Management','Groups'),(31,'mng_rad_groupreply_new','Management','Groups'),(32,'mng_rad_groupreply_edit','Management','Groups'),(33,'mng_rad_profiles_new','Management','Profiles'),(34,'mng_rad_profiles_edit','Management','Profiles'),(35,'mng_rad_profiles_duplicate','Management','Profiles'),(36,'mng_rad_profiles_del','Management','Profiles'),(37,'mng_rad_profiles_list','Management','Profiles'),(38,'mng_rad_attributes_list','Management','Attributes'),(39,'mng_rad_attributes_new','Management','Attributes'),(40,'mng_rad_attributes_edit','Management','Attributes'),(41,'mng_rad_attributes_search','Management','Attributes'),(42,'mng_rad_attributes_del','Management','Attributes'),(43,'mng_rad_attributes_import','Management','Attributes'),(44,'mng_rad_realms_list','Management','Realms'),(45,'mng_rad_realms_new','Management','Realms'),(46,'mng_rad_realms_edit','Management','Realms'),(47,'mng_rad_realms_del','Management','Realms'),(48,'mng_rad_proxys_list','Management','Proxys'),(49,'mng_rad_proxys_new','Management','Proxys'),(50,'mng_rad_proxys_edit','Management','Proxys'),(51,'mng_rad_proxys_del','Management','Proxys'),(52,'mng_rad_ippool_list','Management','IPPool'),(53,'mng_rad_ippool_new','Management','IPPool'),(54,'mng_rad_ippool_edit','Management','IPPool'),(55,'mng_rad_ippool_del','Management','IPPool'),(56,'rep_topusers','Reporting','Core'),(57,'rep_online','Reporting','Core'),(58,'rep_lastconnect','Reporting','Core'),(59,'rep_history','Reporting','Core'),(60,'rep_logs_radius','Reporting','Logs'),(61,'rep_logs_system','Reporting','Logs'),(62,'rep_logs_boot','Reporting','Logs'),(63,'rep_logs_daloradius','Reporting','Logs'),(64,'rep_stat_services','Reporting','Status'),(65,'rep_stat_server','Reporting','Status'),(66,'acct_active','Accounting','General'),(67,'acct_username','Accounting','General'),(68,'acct_all','Accounting','General'),(69,'acct_date','Accounting','General'),(70,'acct_ipaddress','Accounting','General'),(71,'acct_nasipaddress','Accounting','General'),(72,'acct_hotspot_accounting','Accounting','Hotspot'),(73,'acct_hotspot_compare','Accounting','Hotspot'),(74,'acct_custom_query','Accounting','Custom'),(75,'acct_maintenance_cleanup','Accounting','Maintenance'),(76,'acct_maintenance_delete','Accounting','Maintenance'),(77,'bill_pos_del','Billing','POS'),(78,'bill_pos_new','Billing','POS'),(79,'bill_pos_list','Billing','POS'),(80,'bill_pos_edit','Billing','POS'),(81,'bill_rates_date','Billing','Rates'),(82,'bill_rates_del','Billing','Rates'),(83,'bill_rates_new','Billing','Rates'),(84,'bill_rates_edit','Billing','Rates'),(85,'bill_rates_list','Billing','Rates'),(86,'bill_merchant_transactions','Billing','Merchant'),(87,'bill_plans_del','Billing','Plans'),(88,'bill_plans_new','Billing','Plans'),(89,'bill_plans_edit','Billing','Plans'),(90,'bill_plans_list','Billing','Plans'),(91,'bill_history_query','Billing','History'),(92,'gis_editmap','GIS','General'),(93,'gis_viewmap','GIS','General'),(94,'graphs_alltime_logins','Graphs','General'),(95,'graphs_alltime_traffic_compare','Graphs','General'),(96,'graphs_overall_download','Graphs','General'),(97,'graphs_overall_upload','Graphs','General'),(98,'graphs_overall_logins','Graphs','General'),(99,'graphs_logged_users','Graphs','General'),(100,'config_db','Configuration','Core'),(101,'config_interface','Configuration','Core'),(102,'config_lang','Configuration','Core'),(103,'config_logging','Configuration','Core'),(104,'config_maint_test_user','Configuration','Maintenance'),(105,'config_maint_disconnect_user','Configuration','Maintenance'),(106,'config_operators_del','Configuration','Operators'),(107,'config_operators_list','Configuration','Operators'),(108,'config_operators_edit','Configuration','Operators'),(109,'config_operators_new','Configuration','Operators'),(110,'config_backup_createbackups','Configuration','Backup'),(111,'config_backup_managebackups','Configuration','Backup'),(112,'acct_plans_usage','Accounting','Plans'),(113,'config_user','Configuration','Core');
			/*!40000 ALTER TABLE `operators_acl_files` ENABLE KEYS */;
			UNLOCK TABLES;
			/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
			";
			$res = $dbSocket->query($sql);
		}
		
		$sql = "
				INSERT INTO `operators_acl_files` VALUES (0,'mng_rad_hunt_del','Management','HuntGroups'),(0,'mng_rad_hunt_edit','Management','HuntGroups'),(0,'mng_rad_hunt_list','Management','HuntGroups'),(0,'mng_rad_hunt_new','Management','HuntGroups');
				INSERT INTO `operators_acl` VALUES (0,1,'mng_rad_hunt_del',1),(0,1,'mng_rad_hunt_edit',1),(0,1,'mng_rad_hunt_list',1),(0,1,'mng_rad_hunt_new',1);
		
				INSERT INTO `operators_acl_files` VALUES (0,'config_mail','Configuration','Core');
				INSERT INTO `operators_acl` VALUES (0,1,'config_mail',1);
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}
		
		$sql = "
		CREATE TABLE IF NOT EXISTS `node` (
		
		-- Generic fields 
		
		`id` int(11) NOT NULL auto_increment,
		`time` datetime NOT NULL default '00-00-0000 00:00:00' COMMENT 'Time of last checkin',
		
		-- Fields used in the front end; ignored by checkin-batman
		
		`netid` int(11) NOT NULL,
		`name` varchar(100) NOT NULL,
		`description` varchar(100) NOT NULL,
		`latitude` varchar(20) NOT NULL,
		`longitude` varchar(20) NOT NULL,
		`owner_name` varchar(50) NOT NULL COMMENT 'node owner''s name',
		`owner_email` varchar(50) NOT NULL COMMENT 'node owner''s email address',
		`owner_phone` varchar(25) NOT NULL COMMENT 'node owner''s phone number',
		`owner_address` varchar(100) NOT NULL COMMENT 'node owner''s address',
		`approval_status` varchar(1) NOT NULL COMMENT 'approval status: A (accepted), R (rejected), P (pending)',
		
		
		-- Fields which directly correspond to fields in ROBIN; exactly the same names; MUST be updated/accessed by checkin-batman
		-- NOTE: The ROBIN fields 'rank', 'ssid', and 'pssid' are currently not used.
		
		`ip` varchar(20) NOT NULL COMMENT 'ROBIN',
		`mac` varchar(20) UNIQUE NOT NULL COMMENT 'ROBIN',
		`uptime` varchar(100) NOT NULL COMMENT 'ROBIN',
		`robin` varchar(20) NOT NULL COMMENT 'ROBIN: robin version',
		`batman` varchar(20) NOT NULL COMMENT 'ROBIN: batman version',
		`memfree` varchar(20) NOT NULL COMMENT 'ROBIN',
		`nbs` mediumtext NOT NULL COMMENT 'ROBIN: neighbor list',
		`gateway` varchar(20) NOT NULL COMMENT 'ROBIN: nearest gateway',
		`gw-qual` varchar(20) NOT NULL COMMENT 'ROBIN: quality of nearest gateway',
		`routes` mediumtext NOT NULL COMMENT 'ROBIN: route to nearest gateway',
		`users` char(3) NOT NULL COMMENT 'ROBIN: current number of users',
		`kbdown` varchar(20) NOT NULL COMMENT 'ROBIN: downloaded kb',
		`kbup` varchar(20) NOT NULL COMMENT 'ROBIN: uploaded kb',
		`hops` varchar(3) NOT NULL COMMENT 'ROBIN: hops to gateway',
		`rank` varchar(3) NOT NULL COMMENT 'ROBIN: ???, not currently used for anything',
		`ssid` varchar(20) NOT NULL COMMENT 'ROBIN: ssid, not currently used for anything',
		`pssid` varchar(20) NOT NULL COMMENT 'ROBIN: pssid, not currently used for anything',
		
		
		-- Fields which are computed based on fields in ROBIN; must be MUST be updated/accessed by checkin-batman
		
		`gateway_bit` tinyint(1) NOT NULL COMMENT 'ROBIN derivation: is this node a gateway?',
		`memlow` varchar(20) NOT NULL COMMENT 'ROBIN derivation: lowest reported memory on the node',
		`usershi` char(3) NOT NULL COMMENT 'ROBIN derivation: highest number of users',
		
		
		
		-- Begin daloRADIUS related node information
		-- implemented for supporting non-mesh devices (openwrt/dd-wrt, etc) with daloRADIUS's
		-- custome script for checking-in
		
		  `wan_iface` varchar(128) default NULL,
		  `wan_ip` varchar(128) default NULL,
		  `wan_mac` varchar(128) default NULL,
		  `wan_gateway` varchar(128) default NULL,
		  `wifi_iface` varchar(128) default NULL,
		  `wifi_ip` varchar(128) default NULL,
		  `wifi_mac` varchar(128) default NULL,
		  `wifi_ssid` varchar(128) default NULL,
		  `wifi_key` varchar(128) default NULL,
		  `wifi_channel` varchar(128) default NULL,
		  `lan_iface` varchar(128) default NULL,
		  `lan_mac` varchar(128) default NULL,
		  `lan_ip` varchar(128) default NULL,
		  -- `uptime` varchar(128) default NULL,
		  -- `memfree` varchar(128) default NULL,
		  `wan_bup` varchar(128) default NULL,
		  `wan_bdown` varchar(128) default NULL,
		  `firmware` varchar(128) default NULL,
		  `firmware_revision` varchar(128) default NULL,
		  
		  -- `nas_mac` varchar(128) default NULL,
		
		 PRIMARY KEY  (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='node database' AUTO_INCREMENT=1 ;
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}
		
		$sql = "
		DROP TABLE IF EXISTS `billing_plans`;
		CREATE TABLE `billing_plans` (
		  `id` int(8) NOT NULL auto_increment,
		  `planName` varchar(128) default NULL,
		  `planId` varchar(128) default NULL,
		  `planType` varchar(128) default NULL,
		  `planTimeBank` varchar(128) default NULL,
		  `planTimeType` varchar(128) default NULL,
		  `planTimeRefillCost` varchar(128) default NULL,
		  `planBandwidthUp` varchar(128) default NULL,
		  `planBandwidthDown` varchar(128) default NULL,
		  `planTrafficTotal` varchar(128) default NULL,
		  `planTrafficUp` varchar(128) default NULL,
		  `planTrafficDown` varchar(128) default NULL,
		  `planTrafficRefillCost` varchar(128) default NULL,
		  `planRecurring` varchar(128) default NULL,
		  `planRecurringPeriod` varchar(128) default NULL,
		  `planCost` varchar(128) default NULL,
		  `planSetupCost` varchar(128) default NULL,
		  `planTax` varchar(128) default NULL,
		  `planCurrency` varchar(128) default NULL,
		  `planGroup` varchar(128) default NULL,
		  `planActive` varchar(32) DEFAULT 'yes' NOT NULL,
		  `creationdate` datetime default '0000-00-00 00:00:00',
		  `creationby` varchar(128) default NULL,
		  `updatedate` datetime default '0000-00-00 00:00:00',
		  `updateby` varchar(128) default NULL,
		  PRIMARY KEY  (`id`),
		  KEY `planName` (`planName`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;
				
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}
		
		$sql = "
		ALTER TABLE userbillinfo ADD hotspot_id int(32) AFTER planName;
		ALTER TABLE userbillinfo ADD batch_id int(32) AFTER emailinvoice;
		ALTER TABLE userbillinfo MODIFY nextbill date DEFAULT '0000-00-00' NOT NULL;
		ALTER TABLE userbillinfo MODIFY lastbill date DEFAULT '0000-00-00' NOT NULL;
		ALTER TABLE userbillinfo ADD nextinvoicedue int(32) AFTER nextbill;
		ALTER TABLE userbillinfo ADD billdue int(32) AFTER nextinvoicedue;
		ALTER TABLE billing_history DROP COLUMN planName;
		ALTER TABLE billing_history ADD planId int(32) AFTER username;
		ALTER TABLE billing_history MODIFY billAction varchar(128) DEFAULT 'Unavailable' NOT NULL;
		ALTER TABLE billing_plans ADD planRecurringBillingSchedule varchar(128) DEFAULT 'Fixed' NOT NULL AFTER planRecurringPeriod;
		ALTER TABLE userinfo ADD enableportallogin int(32) DEFAULT 0 AFTER changeuserinfo;
		ALTER TABLE userinfo ADD portalloginpassword varchar(128) DEFAULT '' AFTER changeuserinfo;
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}
		
		$sql = "
		DROP TABLE IF EXISTS `batch_history`;
		CREATE TABLE `batch_history` (
		  `id` int(32) NOT NULL auto_increment,
		  `batch_name` varchar(64) default NULL COMMENT 'an identifier name of the batch instance',
		  `batch_description` varchar(256) default NULL COMMENT 'general description of the entry',
		  `hotspot_id` int(32) default 0 COMMENT 'the hotspot business id associated with this batch instance',
		  `batch_status` varchar(128) default 'Pending' NOT NULL COMMENT 'the batch status',
		  
		  `creationdate` datetime default '0000-00-00 00:00:00',
		  `creationby` varchar(128) default NULL,
		  `updatedate` datetime default '0000-00-00 00:00:00',
		  `updateby` varchar(128) default NULL,
		  
		  PRIMARY KEY  (`id`),
		  KEY `batch_name` (`batch_name`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "
			INSERT INTO `operators_acl_files` VALUES (0,'mng_batch_add','Management','Batch'),(0,'mng_batch_list','Management','Batch'),(0,'mng_batch_del','Management','Batch');
			INSERT INTO `operators_acl` VALUES (0,1,'mng_batch_add',1),(0,1,'mng_batch_list',1),(0,1,'mng_batch_del',1);
			";
			$res = $dbSocket->query($sql);
		}
		
		$sql = "
		DROP TABLE IF EXISTS `billing_plans_profiles`;
		CREATE TABLE `billing_plans_profiles` (
		  `id` int(32) NOT NULL auto_increment,
		  `plan_name` varchar(128) NOT NULL COMMENT 'the name of the plan',
		  `profile_name` varchar(256) default NULL COMMENT 'the profile/group name',
		  
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}
		
		$sql = "
		DROP TABLE IF EXISTS `invoice`;
		CREATE TABLE `invoice` (
		  `id` int(32) NOT NULL auto_increment,
		  `user_id` int(32) default NULL COMMENT 'user id of the userbillinfo table',
		  `batch_id` int(32) default NULL COMMENT 'batch id of the batch_history table',
		  `date` datetime NOT NULL default '0000-00-00 00:00:00',
		  `status_id` int(10) NOT NULL default '1' COMMENT 'the status of the invoice from invoice_status',
		  `type_id` int(10) NOT NULL default '1' COMMENT 'the type of the invoice from invoice_type',
		  
		  `notes` varchar(128) NOT NULL COMMENT 'general notes/description',
		  `creationdate` datetime default '0000-00-00 00:00:00',
		  `creationby` varchar(128) default NULL,
		  `updatedate` datetime default '0000-00-00 00:00:00',
		  `updateby` varchar(128) default NULL,
		  
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "
				-- Adding ACL for the new Invoice Billing pages
				INSERT INTO `operators_acl_files` VALUES (0,'bill_invoice_list','Billing','Invoice'),
				(0,'bill_invoice_new','Billing','Invoice'),(0,'bill_invoice_edit','Billing','Invoice'),
				(0,'bill_invoice_del','Billing','Invoice');
				INSERT INTO `operators_acl` VALUES
				(0,1,'bill_invoice_list',1),(0,1,'bill_invoice_new',1),(0,1,'bill_invoice_edit',1),(0,1,'bill_invoice_del',1);
				";
			$res = $dbSocket->query($sql);
		}
		
		$sql = "
		DROP TABLE IF EXISTS `invoice_items`;
		CREATE TABLE `invoice_items` (
		  `id` int(32) NOT NULL auto_increment,
		  `invoice_id` int(32) NOT NULL COMMENT 'invoice id of the invoices table',
		  `plan_id` int(32) default NULL COMMENT 'the plan_id of the billing_plans table',
		
		
		  `amount` decimal(10,2) NOT NULL default '0.00' COMMENT 'the amount cost of an item',
		  `tax_amount` decimal(10,2) NOT NULL default '0.00' COMMENT 'the tax amount for an item',
		  `total` decimal(10,2) NOT NULL default '0.00' COMMENT 'the total amount',
		  
		  
		  `notes` varchar(128) NOT NULL COMMENT 'general notes/description',
		  `creationdate` datetime default '0000-00-00 00:00:00',
		  `creationby` varchar(128) default NULL,
		  `updatedate` datetime default '0000-00-00 00:00:00',
		  `updateby` varchar(128) default NULL,
		  
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}
		
		$sql = "
		DROP TABLE IF EXISTS `invoice_status`;
		CREATE TABLE `invoice_status` (
		  `id` int(10) NOT NULL auto_increment,
		  `value` varchar(32) NOT NULL default '' COMMENT 'status value',
		  
		  `notes` varchar(128) NOT NULL COMMENT 'general notes/description',
		  `creationdate` datetime default '0000-00-00 00:00:00',
		  `creationby` varchar(128) default NULL,
		  `updatedate` datetime default '0000-00-00 00:00:00',
		  `updateby` varchar(128) default NULL,
		  
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "
			INSERT INTO `invoice_status` (`id`, `value`, `notes`, `creationdate`, `creationby`, `updatedate`, `updateby`) VALUES
			(1, 'open', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
			(2, 'disputed', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
			(3, 'draft', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
			(4, 'sent', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
			(5, 'paid', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
			(6, 'partial', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator');
			";
			$res = $dbSocket->query($sql);
		}
		
		$sql = "
		DROP TABLE IF EXISTS `invoice_type`;
		CREATE TABLE `invoice_type` (
		  `id` int(10) NOT NULL auto_increment,
		  `value` varchar(32) NOT NULL default '' COMMENT 'type value',
		  
		  `notes` varchar(128) NOT NULL COMMENT 'general notes/description',
		  `creationdate` datetime default '0000-00-00 00:00:00',
		  `creationby` varchar(128) default NULL,
		  `updatedate` datetime default '0000-00-00 00:00:00',
		  `updateby` varchar(128) default NULL,
		  
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "
			INSERT INTO `invoice_type` (`id`, `value`, `notes`, `creationdate`, `creationby`, `updatedate`, `updateby`) VALUES
			(1, 'Plans', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
			(2, 'Services', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
			(3, 'Consulting', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator');
			";
			$res = $dbSocket->query($sql);
		}
		
		$sql = "
		DROP TABLE IF EXISTS `payment`;
		CREATE TABLE `payment` (
		  `id` int(32) NOT NULL auto_increment,
		  `invoice_id` int(32) NOT NULL COMMENT 'invoice id of the invoices table',
		  `amount` decimal(10,2) NOT NULL COMMENT 'the amount paid',
		  `date` datetime NOT NULL default '0000-00-00 00:00:00',
		  `type_id` int(10) NOT NULL default '1' COMMENT 'the type of the payment from payment_type',
		
		  `notes` varchar(128) NOT NULL COMMENT 'general notes/description', 
		  `creationdate` datetime default '0000-00-00 00:00:00',
		  `creationby` varchar(128) default NULL,
		  `updatedate` datetime default '0000-00-00 00:00:00',
		  `updateby` varchar(128) default NULL,
		  
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}
		
		$sql = "
			DROP TABLE IF EXISTS `payment_type`;
			CREATE TABLE `payment_type` (
			  `id` int(10) NOT NULL auto_increment,
			  `value` varchar(32) NOT NULL default '' COMMENT 'type value',
			  
			  `notes` varchar(128) NOT NULL COMMENT 'general notes/description',
			  `creationdate` datetime default '0000-00-00 00:00:00',
			  `creationby` varchar(128) default NULL,
			  `updatedate` datetime default '0000-00-00 00:00:00',
			  `updateby` varchar(128) default NULL,
			  
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		} else {
			$sql = "
			INSERT INTO `payment_type` (`id`, `value`, `notes`, `creationdate`, `creationby`, `updatedate`, `updateby`) VALUES
			(1, 'Cash', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
			(2, 'Check', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
			(3, 'Bank Transfer', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator');
			
			-- Adding ACL for the new Payment Billing pages
			INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
			('bill_payment_types_new', 'Billing', 'Payment Types'),
			('bill_payment_types_edit', 'Billing', 'Payment Types'),
			('bill_payment_types_list', 'Billing', 'Payment Types'),
			('bill_payment_types_del', 'Billing', 'Payment Types'),
			('bill_payments_list', 'Billing', 'Payments'),
			('bill_payments_edit', 'Billing', 'Payments'),
			('bill_payments_new', 'Billing', 'Payments'),
			('bill_payments_del', 'Billing', 'Payments');
			INSERT INTO `operators_acl` VALUES
			(0,1,'bill_payment_types_new',1),(0,1,'bill_payment_types_edit',1),(0,1,'bill_payment_types_list',1),(0,1,'bill_payment_types_del',1),
			(0,1,'bill_payments_list',1),(0,1,'bill_payments_edit',1),(0,1,'bill_payments_new',1),(0,1,'bill_payments_del',1);
			
			";
			$res = $dbSocket->query($sql);
		}
		

		
		$sql = "
		-- Adding ACL for the new New Users reports page
			INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
			('rep_newusers', 'Reporting', 'Core');
			INSERT INTO `operators_acl` VALUES
			(0,1,'rep_newusers',1);
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}
		
		$sql = "
		-- Adding ACL for the new New Users reports page
		INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
		('bill_invoice_report', 'Billing', 'Invoice');
		INSERT INTO `operators_acl` VALUES
		(0,1,'bill_invoice_report',1);
		
		-- Adding ACL for the new Configuration options
		INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
		('config_reports_dashboard', 'Configuration', 'Reporting');
		INSERT INTO `operators_acl` VALUES
		(0,1,'config_reports_dashboard',1);
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}
		
		$sql = "
		-- Adding new custom daloRADIUS groups
		INSERT IGNORE INTO `radgroupcheck` (Groupname,Attribute,Op,Value) VALUES ('daloRADIUS-Disabled-Users','Auth-Type', ':=', 'Reject');
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}
		
		$sql = "
		-- Adding ACL for Reports->Status->UPS page
		INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
		('rep_stat_ups', 'Reporting', 'Status');
		INSERT INTO `operators_acl` VALUES
		(0,1,'rep_stat_ups',1);
		
		-- Adding ACL for Reports->Status->RAID page
		INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
		('rep_stat_raid', 'Reporting', 'Status');
		INSERT INTO `operators_acl` VALUES
		(0,1,'rep_stat_raid',1);
		
		-- Adding ACL for Reports->Status->CRON page
		INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
		('rep_stat_cron', 'Reporting', 'Status');
		INSERT INTO `operators_acl` VALUES
		(0,1,'rep_stat_cron',1);
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}
		
		$sql = "
		ALTER TABLE userbillinfo ADD country varchar(100) AFTER state;
		ALTER TABLE userinfo ADD country varchar(100) AFTER state;
		
		ALTER TABLE  `node` ADD  `cpu` FLOAT NOT NULL DEFAULT  '0' AFTER  `usershi` ;
				
		";
		$res = $dbSocket->query($sql);
	
		if (DB::isError($res)) {
			$errorMsg = $res->getMessage() ." ". $res->getDebugInfo();
			array_push($upgradeErrors, $errorMsg);
		}

		/* Ending set of SQL entries */

		/* We continue to also upgrade the configuration parameters for 0.9-8 */
		
		$configValues['DALORADIUS_VERSION'] = '0.9-9';
		
		$configValues['CONFIG_DB_TBL_RADUSERGROUP'] = 'radusergroup';
		$configValues['CONFIG_DB_TBL_RADHG'] = 'radhuntgroup';
		$configValues['CONFIG_DB_TBL_RADPOSTAUTH'] = 'radpostauth';
		$configValues['CONFIG_DB_TBL_RADIPPOOL'] = 'radippool';	
		
		$configValues['CONFIG_DB_TBL_DALOOPERATORS'] = 'operators';
		$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'] = 'operators_acl';
		$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES'] = 'operators_acl_files';
		
		$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'] = 'batch_history';
		$configValues['CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES'] = 'billing_plans_profiles';
		$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'] = 'invoice';
		$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'] = 'invoice_items';
		$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS'] = 'invoice_status';
		$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICETYPE'] = 'invoice_type';
		$configValues['CONFIG_DB_TBL_DALOPAYMENTS'] = 'payment';
		$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'] = 'payment_type';
		$configValues['CONFIG_DB_TBL_DALONODE'] = 'node';
		
		$configValues['CONFIG_MAIL_SMTPADDR'] = '127.0.0.1';
		$configValues['CONFIG_MAIL_SMTPPORT'] = '25';
		$configValues['CONFIG_MAIL_SMTPAUTH'] = '';
		$configValues['CONFIG_MAIL_SMTPFROM'] = 'root@daloradius.xdsl.by';
		$configValues['CONFIG_DASHBOARD_DALO_SECRETKEY'] = 'sillykey';
		$configValues['CONFIG_DASHBOARD_DALO_DEBUG'] = '1';
		$configValues['CONFIG_DASHBOARD_DALO_DELAYSOFT'] = '5';
		$configValues['CONFIG_DASHBOARD_DALO_DELAYHARD'] = '15';
		
		
			/* Ending configuration parameters upgrade */

		$databaseVersion = "0.9-9";
	} // 0.9-8
	
	include 'library/closedb.php';


	
	/* if this is an upgrade from a previous version like 0.9-7 then there is no daloradius.conf.php
	   file created and so we need to create one... */

	if (!file_exists("library/daloradius.conf.php")) {
		$configValues['DALORADIUS_VERSION'] = '0.9-9';
		$configValues['FREERADIUS_VERSION'] = '2';
		$configValues['CONFIG_DB_ENGINE'] = 'mysql';
		$configValues['CONFIG_DB_HOST'] = 'localhost';
		$configValues['CONFIG_DB_USER'] = 'root';
		$configValues['CONFIG_DB_PASS'] = '';
		$configValues['CONFIG_DB_NAME'] = 'radius';
		$configValues['CONFIG_DB_TBL_RADCHECK'] = 'radcheck';
		$configValues['CONFIG_DB_TBL_RADREPLY'] = 'radreply';
		$configValues['CONFIG_DB_TBL_RADGROUPREPLY'] = 'radgroupreply';
		$configValues['CONFIG_DB_TBL_RADGROUPCHECK'] = 'radgroupcheck';
		$configValues['CONFIG_DB_TBL_RADUSERGROUP'] = 'radusergroup';
		$configValues['CONFIG_DB_TBL_RADNAS'] = 'nas';
		$configValues['CONFIG_DB_TBL_RADHG'] = 'radhuntgroup';
		$configValues['CONFIG_DB_TBL_RADPOSTAUTH'] = 'radpostauth';
		$configValues['CONFIG_DB_TBL_RADACCT'] = 'radacct';
		$configValues['CONFIG_DB_TBL_RADIPPOOL'] = 'radippool';
		$configValues['CONFIG_DB_TBL_DALOOPERATORS'] = 'operators';
		$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'] = 'operators_acl';
		$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES'] = 'operators_acl_files';
		$configValues['CONFIG_DB_TBL_DALORATES'] = 'rates';
		$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'] = 'hotspots';
		$configValues['CONFIG_DB_TBL_DALOUSERINFO'] = 'userinfo';
		$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'] = 'userbillinfo';
		$configValues['CONFIG_DB_TBL_DALODICTIONARY'] = 'dictionary';
		$configValues['CONFIG_DB_TBL_DALOREALMS'] = 'realms';
		$configValues['CONFIG_DB_TBL_DALOPROXYS'] = 'proxys';
		$configValues['CONFIG_DB_TBL_DALOBILLINGPAYPAL'] = 'billing_paypal';
		$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT'] = 'billing_merchant';
		$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'] = 'billing_plans';
		$configValues['CONFIG_DB_TBL_DALOBILLINGRATES'] = 'billing_rates';
		$configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY'] = 'billing_history';
		$configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'] = 'batch_history';
		$configValues['CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES'] = 'billing_plans_profiles';
		$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'] = 'invoice';
		$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'] = 'invoice_items';
		$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS'] = 'invoice_status';
		$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICETYPE'] = 'invoice_type';
		$configValues['CONFIG_DB_TBL_DALOPAYMENTS'] = 'payment';
		$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'] = 'payment_type';
		$configValues['CONFIG_DB_TBL_DALONODE'] = 'node';
		$configValues['CONFIG_FILE_RADIUS_PROXY'] = '/etc/freeradius/proxy.conf';
		$configValues['CONFIG_PATH_RADIUS_DICT'] = '';
		$configValues['CONFIG_PATH_DALO_VARIABLE_DATA'] = '/var/www/daloradius/var';
		$configValues['CONFIG_DB_PASSWORD_ENCRYPTION'] = 'cleartext';
		$configValues['CONFIG_LANG'] = 'en';
		$configValues['CONFIG_LOG_PAGES'] = 'no';
		$configValues['CONFIG_LOG_ACTIONS'] = 'no';
		$configValues['CONFIG_LOG_QUERIES'] = 'no';
		$configValues['CONFIG_DEBUG_SQL'] = 'no';
		$configValues['CONFIG_DEBUG_SQL_ONPAGE'] = 'no';
		$configValues['CONFIG_LOG_FILE'] = '/tmp/daloradius.log';
		$configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] = 'no';
		$configValues['CONFIG_IFACE_TABLES_LISTING'] = '25';
		$configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] = 'yes';
		$configValues['CONFIG_IFACE_AUTO_COMPLETE'] = 'yes';
		$configValues['CONFIG_MAINT_TEST_USER_RADIUSSERVER'] = '127.0.0.1';
		$configValues['CONFIG_MAINT_TEST_USER_RADIUSPORT'] = '1812';
		$configValues['CONFIG_MAINT_TEST_USER_NASPORT'] = '0';
		$configValues['CONFIG_MAINT_TEST_USER_RADIUSSECRET'] = 'testing123';
		$configValues['CONFIG_USER_ALLOWEDRANDOMCHARS'] = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
		$configValues['CONFIG_MAIL_SMTPADDR'] = '127.0.0.1';
		$configValues['CONFIG_MAIL_SMTPPORT'] = '25';
		$configValues['CONFIG_MAIL_SMTPAUTH'] = '';
		$configValues['CONFIG_MAIL_SMTPFROM'] = 'root@daloradius.xdsl.by';
		$configValues['CONFIG_DASHBOARD_DALO_SECRETKEY'] = 'sillykey';
		$configValues['CONFIG_DASHBOARD_DALO_DEBUG'] = '1';
		$configValues['CONFIG_DASHBOARD_DALO_DELAYSOFT'] = '5';
		$configValues['CONFIG_DASHBOARD_DALO_DELAYHARD'] = '15';
		$configValues['CONFIG_MAIL_SMTP_FROMEMAIL'] = '';
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
                                	Radius Management, Reporting and Accounting by <a href="https://github.com/lirantal/daloradius">Liran Tal</a>                                
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
		<a href="https://github.com/lirantal/daloradius" class="more">Read More &raquo;</a>
	</p>



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
		</li>


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
		</li>


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
		</li>	


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
		<option value="0.9-8">0.9-8</option>
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
	".t('all','copyright2')."
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
