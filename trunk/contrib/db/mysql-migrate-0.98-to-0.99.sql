-- MySQL dump 10.10
--
-- Host: localhost    Database: radius
-- ------------------------------------------------------
-- Server version	5.0.22-Debian_0ubuntu6.06-log

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


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `operators`
--

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

--
-- Dumping data for table `operators`
--

LOCK TABLES `operators` WRITE;
/*!40000 ALTER TABLE `operators` DISABLE KEYS */;
INSERT INTO `operators` VALUES (6,'administrator','radius','','','','','','','','','','','','','2009-12-07 20:13:20','2009-12-07 20:12:33','admin','2009-12-07 20:14:01','administrator');
/*!40000 ALTER TABLE `operators` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-12-07 18:18:02



-- MySQL dump 10.11
--
-- Host: localhost    Database: dalohosting_enginx
-- ------------------------------------------------------
-- Server version	5.0.32-Debian_7etch8-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `operators_acl`
--

DROP TABLE IF EXISTS `operators_acl`;
CREATE TABLE `operators_acl` (
  `id` int(32) NOT NULL auto_increment,
  `operator_id` int(32) NOT NULL,
  `file` varchar(128) NOT NULL,
  `access` tinyint(8) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=226 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `operators_acl`
--

LOCK TABLES `operators_acl` WRITE;
/*!40000 ALTER TABLE `operators_acl` DISABLE KEYS */;
INSERT INTO `operators_acl` VALUES (114,6,'acct_custom_query',1),(115,6,'acct_active',1),(116,6,'acct_all',1),(117,6,'acct_ipaddress',1),(118,6,'acct_username',1),(119,6,'acct_date',1),(120,6,'acct_nasipaddress',1),(121,6,'acct_hotspot_accounting',1),(122,6,'acct_hotspot_compare',1),(123,6,'acct_maintenance_cleanup',1),(124,6,'acct_maintenance_delete',1),(125,6,'acct_plans_usage',1),(126,6,'bill_history_query',1),(127,6,'bill_merchant_transactions',1),(128,6,'bill_plans_list',1),(129,6,'bill_plans_del',1),(130,6,'bill_plans_edit',1),(131,6,'bill_plans_new',1),(132,6,'bill_pos_del',1),(133,6,'bill_pos_list',1),(134,6,'bill_pos_new',1),(135,6,'bill_pos_edit',1),(136,6,'bill_rates_date',1),(137,6,'bill_rates_new',1),(138,6,'bill_rates_list',1),(139,6,'bill_rates_del',1),(140,6,'bill_rates_edit',1),(141,6,'config_backup_managebackups',1),(142,6,'config_backup_createbackups',1),(143,6,'config_user',1),(144,6,'config_db',1),(145,6,'config_lang',1),(146,6,'config_interface',1),(147,6,'config_logging',1),(148,6,'config_maint_test_user',1),(149,6,'config_maint_disconnect_user',1),(150,6,'config_operators_list',1),(151,6,'config_operators_new',1),(152,6,'config_operators_del',1),(153,6,'config_operators_edit',1),(154,6,'gis_editmap',1),(155,6,'gis_viewmap',1),(156,6,'graphs_alltime_logins',1),(157,6,'graphs_overall_download',1),(158,6,'graphs_overall_logins',1),(159,6,'graphs_alltime_traffic_compare',1),(160,6,'graphs_overall_upload',1),(161,6,'graphs_logged_users',1),(162,6,'mng_rad_attributes_import',1),(163,6,'mng_rad_attributes_list',1),(164,6,'mng_rad_attributes_edit',1),(165,6,'mng_rad_attributes_del',1),(166,6,'mng_rad_attributes_new',1),(167,6,'mng_rad_attributes_search',1),(168,6,'mng_rad_groupcheck_new',1),(169,6,'mng_rad_groupreply_search',1),(170,6,'mng_rad_groupreply_list',1),(171,6,'mng_rad_groupreply_edit',1),(172,6,'mng_rad_groupcheck_search',1),(173,6,'mng_rad_groupcheck_list',1),(174,6,'mng_rad_groupcheck_edit',1),(175,6,'mng_rad_groupreply_del',1),(176,6,'mng_rad_groupreply_new',1),(177,6,'mng_rad_groupcheck_del',1),(178,6,'mng_hs_edit',1),(179,6,'mng_hs_list',1),(180,6,'mng_hs_del',1),(181,6,'mng_hs_new',1),(182,6,'mng_rad_ippool_new',1),(183,6,'mng_rad_ippool_del',1),(184,6,'mng_rad_ippool_list',1),(185,6,'mng_rad_ippool_edit',1),(186,6,'mng_rad_nas_edit',1),(187,6,'mng_rad_nas_list',1),(188,6,'mng_rad_nas_del',1),(189,6,'mng_rad_nas_new',1),(190,6,'mng_rad_profiles_edit',1),(191,6,'mng_rad_profiles_del',1),(192,6,'mng_rad_profiles_new',1),(193,6,'mng_rad_profiles_duplicate',1),(194,6,'mng_rad_profiles_list',1),(195,6,'mng_rad_proxys_new',1),(196,6,'mng_rad_proxys_del',1),(197,6,'mng_rad_proxys_list',1),(198,6,'mng_rad_proxys_edit',1),(199,6,'mng_rad_realms_new',1),(200,6,'mng_rad_realms_del',1),(201,6,'mng_rad_realms_list',1),(202,6,'mng_rad_realms_edit',1),(203,6,'mng_rad_usergroup_edit',1),(204,6,'mng_rad_usergroup_list_user',1),(205,6,'mng_rad_usergroup_del',1),(206,6,'mng_rad_usergroup_new',1),(207,6,'mng_rad_usergroup_list',1),(208,6,'mng_search',1),(209,6,'mng_del',1),(210,6,'mng_new',1),(211,6,'mng_import_users',1),(212,6,'mng_batch',1),(213,6,'mng_edit',1),(214,6,'mng_new_quick',1),(215,6,'mng_list_all',1),(216,6,'rep_lastconnect',1),(217,6,'rep_online',1),(218,6,'rep_history',1),(219,6,'rep_topusers',1),(220,6,'rep_logs_radius',1),(221,6,'rep_logs_boot',1),(222,6,'rep_logs_system',1),(223,6,'rep_logs_daloradius',1),(224,6,'rep_stat_services',1),(225,6,'rep_stat_server',1);
/*!40000 ALTER TABLE `operators_acl` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-12-07 18:17:45




-- MySQL dump 10.11
--
-- Host: localhost    Database: dalohosting_enginx
-- ------------------------------------------------------
-- Server version	5.0.32-Debian_7etch8-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `operators_acl_files`
--

DROP TABLE IF EXISTS `operators_acl_files`;
CREATE TABLE `operators_acl_files` (
  `id` int(32) NOT NULL auto_increment,
  `file` varchar(128) NOT NULL,
  `category` varchar(128) NOT NULL,
  `section` varchar(128) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=114 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `operators_acl_files`
--

LOCK TABLES `operators_acl_files` WRITE;
/*!40000 ALTER TABLE `operators_acl_files` DISABLE KEYS */;
INSERT INTO `operators_acl_files` VALUES (2,'mng_search','Management','Users'),(3,'mng_batch','Management','Users'),(4,'mng_del','Management','Users'),(5,'mng_edit','Management','Users'),(6,'mng_new','Management','Users'),(7,'mng_new_quick','Management','Users'),(8,'mng_import_users','Management','Users'),(9,'mng_list_all','Management','Users'),(10,'mng_hs_del','Management','Hotspot'),(11,'mng_hs_edit','Management','Hotspot'),(12,'mng_hs_new','Management','Hotspot'),(13,'mng_hs_list','Management','Hotspot'),(14,'mng_rad_nas_del','Management','NAS'),(15,'mng_rad_nas_edit','Management','NAS'),(16,'mng_rad_nas_new','Management','NAS'),(17,'mng_rad_nas_list','Management','NAS'),(18,'mng_rad_usergroup_del','Management','UserGroup'),(19,'mng_rad_usergroup_edit','Management','UserGroup'),(20,'mng_rad_usergroup_new','Management','UserGroup'),(21,'mng_rad_usergroup_list_user','Management','UserGroup'),(22,'mng_rad_usergroup_list','Management','UserGroup'),(23,'mng_rad_groupcheck_search','Management','Groups'),(24,'mng_rad_groupcheck_del','Management','Groups'),(25,'mng_rad_groupcheck_list','Management','Groups'),(26,'mng_rad_groupcheck_new','Management','Groups'),(27,'mng_rad_groupcheck_edit','Management','Groups'),(28,'mng_rad_groupreply_search','Management','Groups'),(29,'mng_rad_groupreply_del','Management','Groups'),(30,'mng_rad_groupreply_list','Management','Groups'),(31,'mng_rad_groupreply_new','Management','Groups'),(32,'mng_rad_groupreply_edit','Management','Groups'),(33,'mng_rad_profiles_new','Management','Profiles'),(34,'mng_rad_profiles_edit','Management','Profiles'),(35,'mng_rad_profiles_duplicate','Management','Profiles'),(36,'mng_rad_profiles_del','Management','Profiles'),(37,'mng_rad_profiles_list','Management','Profiles'),(38,'mng_rad_attributes_list','Management','Attributes'),(39,'mng_rad_attributes_new','Management','Attributes'),(40,'mng_rad_attributes_edit','Management','Attributes'),(41,'mng_rad_attributes_search','Management','Attributes'),(42,'mng_rad_attributes_del','Management','Attributes'),(43,'mng_rad_attributes_import','Management','Attributes'),(44,'mng_rad_realms_list','Management','Realms'),(45,'mng_rad_realms_new','Management','Realms'),(46,'mng_rad_realms_edit','Management','Realms'),(47,'mng_rad_realms_del','Management','Realms'),(48,'mng_rad_proxys_list','Management','Proxys'),(49,'mng_rad_proxys_new','Management','Proxys'),(50,'mng_rad_proxys_edit','Management','Proxys'),(51,'mng_rad_proxys_del','Management','Proxys'),(52,'mng_rad_ippool_list','Management','IPPool'),(53,'mng_rad_ippool_new','Management','IPPool'),(54,'mng_rad_ippool_edit','Management','IPPool'),(55,'mng_rad_ippool_del','Management','IPPool'),(56,'rep_topusers','Reporting','Core'),(57,'rep_online','Reporting','Core'),(58,'rep_lastconnect','Reporting','Core'),(59,'rep_history','Reporting','Core'),(60,'rep_logs_radius','Reporting','Logs'),(61,'rep_logs_system','Reporting','Logs'),(62,'rep_logs_boot','Reporting','Logs'),(63,'rep_logs_daloradius','Reporting','Logs'),(64,'rep_stat_services','Reporting','Status'),(65,'rep_stat_server','Reporting','Status'),(66,'acct_active','Accounting','General'),(67,'acct_username','Accounting','General'),(68,'acct_all','Accounting','General'),(69,'acct_date','Accounting','General'),(70,'acct_ipaddress','Accounting','General'),(71,'acct_nasipaddress','Accounting','General'),(72,'acct_hotspot_accounting','Accounting','Hotspot'),(73,'acct_hotspot_compare','Accounting','Hotspot'),(74,'acct_custom_query','Accounting','Custom'),(75,'acct_maintenance_cleanup','Accounting','Maintenance'),(76,'acct_maintenance_delete','Accounting','Maintenance'),(77,'bill_pos_del','Billing','POS'),(78,'bill_pos_new','Billing','POS'),(79,'bill_pos_list','Billing','POS'),(80,'bill_pos_edit','Billing','POS'),(81,'bill_rates_date','Billing','Rates'),(82,'bill_rates_del','Billing','Rates'),(83,'bill_rates_new','Billing','Rates'),(84,'bill_rates_edit','Billing','Rates'),(85,'bill_rates_list','Billing','Rates'),(86,'bill_merchant_transactions','Billing','Merchant'),(87,'bill_plans_del','Billing','Plans'),(88,'bill_plans_new','Billing','Plans'),(89,'bill_plans_edit','Billing','Plans'),(90,'bill_plans_list','Billing','Plans'),(91,'bill_history_query','Billing','History'),(92,'gis_editmap','GIS','General'),(93,'gis_viewmap','GIS','General'),(94,'graphs_alltime_logins','Graphs','General'),(95,'graphs_alltime_traffic_compare','Graphs','General'),(96,'graphs_overall_download','Graphs','General'),(97,'graphs_overall_upload','Graphs','General'),(98,'graphs_overall_logins','Graphs','General'),(99,'graphs_logged_users','Graphs','General'),(100,'config_db','Configuration','Core'),(101,'config_interface','Configuration','Core'),(102,'config_lang','Configuration','Core'),(103,'config_logging','Configuration','Core'),(104,'config_maint_test_user','Configuration','Maintenance'),(105,'config_maint_disconnect_user','Configuration','Maintenance'),(106,'config_operators_del','Configuration','Operators'),(107,'config_operators_list','Configuration','Operators'),(108,'config_operators_edit','Configuration','Operators'),(109,'config_operators_new','Configuration','Operators'),(110,'config_backup_createbackups','Configuration','Backup'),(111,'config_backup_managebackups','Configuration','Backup'),(112,'acct_plans_usage','Accounting','Plans'),(113,'config_user','Configuration','Core');
/*!40000 ALTER TABLE `operators_acl_files` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-12-07 18:18:18


INSERT INTO `operators_acl_files` VALUES (0,'mng_rad_hunt_del','Management','HuntGroups'),(0,'mng_rad_hunt_edit','Management','HuntGroups'),(0,'mng_rad_hunt_list','Management','HuntGroups'),(0,'mng_rad_hunt_new','Management','HuntGroups');
INSERT INTO `operators_acl` VALUES (0,6,'mng_rad_hunt_del',1),(0,6,'mng_rad_hunt_edit',1),(0,6,'mng_rad_hunt_list',1),(0,6,'mng_rad_hunt_new',1);

INSERT INTO `operators_acl_files` VALUES (0,'config_mail','Configuration','Core');
INSERT INTO `operators_acl` VALUES (0,6,'config_mail',1);


--
-- Table structure for table `node`
-- adopted from the meshconnect project for the sake of compatability

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



DROP TABLE IF EXISTS `nodes_settings`;
CREATE TABLE `nodes_settings` (
  `id` int(32) NOT NULL auto_increment,
  `soft_checkin_time` varchar(128) default NULL COMMENT 'time in minutes which hotspot is considered late/down (soft limit)',
  `hard_checkin_time` varchar(128) default NULL COMMENT 'time in minutes which hotspot is considered late/down (hard limit)',
  
  `creationdate` datetime default '0000-00-00 00:00:00',
  `creationby` varchar(128) default NULL,
  `updatedate` datetime default '0000-00-00 00:00:00',
  `updateby` varchar(128) default NULL,
  
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



DROP TABLE IF EXISTS `billing_notifications_settings`;
CREATE TABLE `billing_notifications_settings` (
  `id` int(32) NOT NULL auto_increment,
  `notification_name` varchar(128) default NULL COMMENT 'the notification name - an identifier to distinguish',
  `notification_delay` varchar(128) default NULL COMMENT 'notification delay in days after-which to send a notification',
  `notification_limittype` varchar(128) default NULL COMMENT 'percent',
  `notification_softlimit` varchar(128) default NULL COMMENT 'a soft limit',
  `notification_hardlimit` varchar(128) default NULL COMMENT 'a hard limit',
  
  
  `creationdate` datetime default '0000-00-00 00:00:00',
  `creationby` varchar(128) default NULL,
  `updatedate` datetime default '0000-00-00 00:00:00',
  `updateby` varchar(128) default NULL,
  
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



--
-- Table structure for table `billing_plans`
--

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



ALTER TABLE userbillinfo ADD hotspot_id int(32) AFTER planName;
ALTER TABLE userbillinfo ADD batch_id int(32) AFTER emailinvoice;
ALTER TABLE userbillinfo MODIFY nextbill date DEFAULT '0000-00-00' NOT NULL;
ALTER TABLE userbillinfo MODIFY lastbill date DEFAULT '0000-00-00' NOT NULL;
ALTER TABLE userbillinfo ADD nextinvoicedue int(32) AFTER nextbill;
ALTER TABLE userbillinfo ADD billdue int(32) AFTER nextinvoicedue;

ALTER TABLE billing_history DROP COLUMN planName;
ALTER TABLE billing_history ADD planId int(32) AFTER username;
ALTER TABLE billing_history MODIFY billAction varchar(128) DEFAULT 'Unavailable' NOT NULL;

-- ALTER TABLE billing_plans ADD planActive varchar(32) DEFAULT 'yes' NOT NULL AFTER planCurrency;
ALTER TABLE billing_plans ADD planRecurringBillingSchedule varchar(128) DEFAULT 'Fixed' NOT NULL AFTER planRecurringPeriod;

ALTER TABLE userinfo ADD enableportallogin int(32) DEFAULT 0 AFTER changeuserinfo;
ALTER TABLE userinfo ADD portalloginpassword varchar(128) DEFAULT '' AFTER changeuserinfo;

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



DROP TABLE IF EXISTS `billing_plans_profiles`;
CREATE TABLE `billing_plans_profiles` (
  `id` int(32) NOT NULL auto_increment,
  `plan_name` varchar(128) NOT NULL COMMENT 'the name of the plan',
  `profile_name` varchar(256) default NULL COMMENT 'the profile/group name',
  
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


INSERT INTO `operators_acl_files` VALUES (0,'mng_batch_add','Management','Batch'),(0,'mng_batch_list','Management','Batch'),(0,'mng_batch_del','Management','Batch');
INSERT INTO `operators_acl` VALUES (0,6,'mng_batch_add',1),(0,6,'mng_batch_list',1),(0,6,'mng_batch_del',1);






-- introducing new changes, still under review
-- very experimental  

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

INSERT INTO `invoice_status` (`id`, `value`, `notes`, `creationdate`, `creationby`, `updatedate`, `updateby`) VALUES
(1, 'open', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
(2, 'disputed', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
(3, 'draft', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
(4, 'sent', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
(5, 'paid', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
(6, 'partial', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator');




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

INSERT INTO `invoice_type` (`id`, `value`, `notes`, `creationdate`, `creationby`, `updatedate`, `updateby`) VALUES
(1, 'Plans', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
(2, 'Services', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
(3, 'Consulting', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator');



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

INSERT INTO `payment_type` (`id`, `value`, `notes`, `creationdate`, `creationby`, `updatedate`, `updateby`) VALUES
(1, 'Cash', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
(2, 'Check', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator'),
(3, 'Bank Transfer', '', '2010-05-27 00:00:00', 'operator', '2010-05-27 00:00:00', 'operator');



-- Adding ACL for the new Invoice Billing pages
INSERT INTO `operators_acl_files` VALUES (0,'bill_invoice_list','Billing','Invoice'),
(0,'bill_invoice_new','Billing','Invoice'),(0,'bill_invoice_edit','Billing','Invoice'),
(0,'bill_invoice_del','Billing','Invoice');
INSERT INTO `operators_acl` VALUES
(0,6,'bill_invoice_list',1),(0,6,'bill_invoice_new',1),(0,6,'bill_invoice_edit',1),(0,6,'bill_invoice_del',1);


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
(0,6,'bill_payment_types_new',1),(0,6,'bill_payment_types_edit',1),(0,6,'bill_payment_types_list',1),(0,6,'bill_payment_types_del',1),
(0,6,'bill_payments_list',1),(0,6,'bill_payments_edit',1),(0,6,'bill_payments_new',1),(0,6,'bill_payments_del',1);


-- Adding ACL for the new New Users reports page
INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
('rep_newusers', 'Reporting', 'Core');
INSERT INTO `operators_acl` VALUES
(0,6,'rep_newusers',1);


-- Adding ACL for the new New Users reports page
INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
('bill_invoice_report', 'Billing', 'Invoice');
INSERT INTO `operators_acl` VALUES
(0,6,'bill_invoice_report',1);

-- Adding ACL for the new Configuration options
INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
('config_reports_dashboard', 'Configuration', 'Reporting');
INSERT INTO `operators_acl` VALUES
(0,6,'config_reports_dashboard',1);



-- Adding new custom daloRADIUS groups
INSERT IGNORE INTO `radgroupcheck` (Groupname,Attribute,Op,Value) VALUES ('daloRADIUS-Disabled-Users','Auth-Type', ':=', 'Reject');


-- Adding ACL for Reports->Status->UPS page
INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
('rep_stat_ups', 'Reporting', 'Status');
INSERT INTO `operators_acl` VALUES
(0,6,'rep_stat_ups',1);

-- Adding ACL for Reports->Status->RAID page
INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
('rep_stat_raid', 'Reporting', 'Status');
INSERT INTO `operators_acl` VALUES
(0,6,'rep_stat_raid',1);

-- Adding ACL for Reports->Status->CRON page
INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
('rep_stat_cron', 'Reporting', 'Status');
INSERT INTO `operators_acl` VALUES
(0,6,'rep_stat_cron',1);


ALTER TABLE userbillinfo ADD country varchar(100) AFTER state;
ALTER TABLE userinfo ADD country varchar(100) AFTER state;

ALTER TABLE  `node` ADD  `cpu` FLOAT NOT NULL DEFAULT  '0' AFTER  `usershi` ;
