-- MySQL dump 10.10
--
-- Host: localhost    Database: radius
-- ------------------------------------------------------
-- Server version	5.0.22-Debian_0ubuntu6.06-log

ALTER TABLE userbillinfo ADD hotspotlocation VARCHAR(32) AFTER planName;

ALTER TABLE operators ADD bill_merchant_transactions VARCHAR(32) AFTER bill_rates_list;
UPDATE operators SET bill_merchant_transactions='yes' WHERE username='administrator';

ALTER TABLE operators ADD config_user VARCHAR(32) AFTER config_db;
UPDATE operators SET config_user='yes' WHERE username='administrator';

ALTER TABLE operators ADD graphs_logged_users VARCHAR(32) AFTER graphs_overall_logins;
UPDATE operators SET graphs_logged_users='yes' WHERE username='administrator';

ALTER TABLE operators ADD acct_plans_usage VARCHAR(32) AFTER acct_custom_query;
UPDATE operators SET acct_plans_usage='yes' WHERE username='administrator';


CREATE TABLE `billing_merchant` (
  `id` int(8) NOT NULL auto_increment,
  `username` varchar(128) NOT NULL default '',
  `password` varchar(128) NOT NULL default '',
  `mac` varchar(200) NOT NULL default '',
  `pin` varchar(200) NOT NULL default '',
  `txnId` varchar(200) NOT NULL default '',
  `planName` varchar(128) NOT NULL default '',
  `planId` varchar(200) NOT NULL default '',
  `quantity` varchar(200) NOT NULL default '',
  `business_email` varchar(200) NOT NULL default '',
  `business_id` varchar(200) NOT NULL default '',
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
  `payment_address_status` varchar(200) NOT NULL default '',
  `vendor_type` varchar(200) NOT NULL default '',
  `payer_status` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE operators DROP COLUMN bill_paypal_transactions;

ALTER TABLE operators ADD mng_import_users VARCHAR(32) AFTER mng_new_quick;
UPDATE operators SET mng_import_users='yes' WHERE username='administrator';

DROP TABLE operators;

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
  `creationdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `creationby` varchar(128) default NULL,
  `updatedate` datetime NOT NULL default '0000-00-00 00:00:00',
  `updateby` varchar(128) default NULL,
  PRIMARY KEY  (`id`)
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
