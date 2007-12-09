-- MySQL dump 10.10
--
-- Host: localhost    Database: radius
-- ------------------------------------------------------
-- Server version	5.0.22-Debian_0ubuntu6.06-log

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
-- Table structure for table `hotspots`
--

DROP TABLE IF EXISTS `hotspots`;
CREATE TABLE `hotspots` (
  `id` bigint(20) NOT NULL auto_increment,
  `name` varchar(32) default NULL,
  `mac` varchar(32) default NULL,
  `geocode` varchar(128) default NULL,
  `owner` varchar(32) default NULL,
  `email_owner` varchar(32) default NULL,
  `manager` varchar(32) default NULL,
  `email_manager` varchar(32) default NULL,
  `address` varchar(128) default NULL,
  `company` varchar(32) default NULL,
  `phone1` varchar(32) default NULL,
  `phone2` varchar(32) default NULL,
  `type` varchar(32) default NULL,
  `website` varchar(32) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hotspots`
--


/*!40000 ALTER TABLE `hotspots` DISABLE KEYS */;
LOCK TABLES `hotspots` WRITE;
INSERT INTO `hotspots` VALUES (2,'railroad_coffee','00:00:00:aa:aa:aa','42.5530802889558, -72.421875','GI Joes','_NONE_','gijoe@hotmail.com','_NONE_','pobox 9021, 22122','americasarmy.com','123456','_NONE_','Coffee Shop','_NONE_'),(3,'spa_internet','00:00:00:bb:bb:bb','38.27268853598097, -80.859375',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(4,'Hotel Blueberry','00:00:00:cc:cc:cc','40.97989806962013, -73.125',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(11,'llll','00:00','55.7765730186677, -98.4375','lalala',NULL,'lilili',NULL,'blablabl',NULL,NULL,NULL,NULL,NULL);
UNLOCK TABLES;
/*!40000 ALTER TABLE `hotspots` ENABLE KEYS */;

--
-- Table structure for table `nas`
--

DROP TABLE IF EXISTS `nas`;
CREATE TABLE `nas` (
  `id` int(10) NOT NULL auto_increment,
  `nasname` varchar(128) NOT NULL default '',
  `shortname` varchar(32) default NULL,
  `type` varchar(30) default 'other',
  `ports` int(5) default NULL,
  `secret` varchar(60) NOT NULL default 'secret',
  `community` varchar(50) default NULL,
  `description` varchar(200) default 'RADIUS Client',
  PRIMARY KEY  (`id`),
  KEY `nasname` (`nasname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `nas`
--


/*!40000 ALTER TABLE `nas` DISABLE KEYS */;
LOCK TABLES `nas` WRITE;
INSERT INTO `nas` VALUES (3,'mydomain.com','other','other',0,'2zxdas','','this is a nas for testing purposes...');
UNLOCK TABLES;
/*!40000 ALTER TABLE `nas` ENABLE KEYS */;

--
-- Table structure for table `operators`
--

DROP TABLE IF EXISTS `operators`;
CREATE TABLE `operators` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(32) default NULL,
  `password` varchar(32) default NULL,
  `mng_search` varchar(32) default NULL,
  `mng_batch` varchar(32) default NULL,
  `mng_del` varchar(32) default NULL,
  `mng_edit` varchar(32) default NULL,
  `mng_new` varchar(32) default NULL,
  `mng_new_quick` varchar(32) default NULL,
  `mng_list_all` varchar(32) default NULL,
  `mng_hs_del` varchar(32) default NULL,
  `mng_hs_edit` varchar(32) default NULL,
  `mng_hs_new` varchar(32) default NULL,
  `mng_hs_list` varchar(32) default NULL,
  `mng_rad_nas_del` varchar(32) default NULL,
  `mng_rad_nas_edit` varchar(32) default NULL,
  `mng_rad_nas_new` varchar(32) default NULL,
  `mng_rad_nas_list` varchar(32) default NULL,
  `mng_rad_usergroup_del` varchar(32) default NULL,
  `mng_rad_usergroup_edit` varchar(32) default NULL,
  `mng_rad_usergroup_new` varchar(32) default NULL,
  `mng_rad_usergroup_list_user` varchar(32) default NULL,
  `mng_rad_usergroup_list` varchar(32) default NULL,
  `mng_rad_groupcheck_del` varchar(32) default NULL,
  `mng_rad_groupcheck_list` varchar(32) default NULL,
  `mng_rad_groupcheck_new` varchar(32) default NULL,
  `mng_rad_groupcheck_edit` varchar(32) default NULL,
  `mng_rad_groupreply_del` varchar(32) default NULL,
  `mng_rad_groupreply_list` varchar(32) default NULL,
  `mng_rad_groupreply_new` varchar(32) default NULL,
  `mng_rad_groupreply_edit` varchar(32) default NULL,
  `rep_online` varchar(32) default NULL,
  `rep_topusers` varchar(32) default NULL,
  `rep_username` varchar(32) default NULL,
  `rep_lastconnect` varchar(32) default NULL,
  `rep_logs_radius` varchar(32) default NULL,
  `rep_stat_radius` varchar(32) default NULL,
  `rep_stat_server` varchar(32) default NULL,
  `rep_logs_system` varchar(32) default NULL,
  `rep_logs_boot` varchar(32) default NULL,
  `rep_logs_daloradius` varchar(32) default NULL,
  `acct_active` varchar(32) default NULL,
  `acct_username` varchar(32) default NULL,
  `acct_all` varchar(32) default NULL,
  `acct_date` varchar(32) default NULL,
  `acct_ipaddress` varchar(32) default NULL,
  `acct_nasipaddress` varchar(32) default NULL,
  `acct_hotspot_accounting` varchar(32) default NULL,
  `acct_hotspot_compare` varchar(32) default NULL,
  `acct_custom_query` varchar(32) default NULL, 
  `bill_persecond` varchar(32) default NULL,
  `bill_prepaid` varchar(32) default NULL,
  `bill_rates_del` varchar(32) default NULL,
  `bill_rates_new` varchar(32) default NULL,
  `bill_rates_edit` varchar(32) default NULL,
  `bill_rates_list` varchar(32) default NULL,
  `gis_editmap` varchar(32) default NULL,
  `gis_viewmap` varchar(32) default NULL,
  `graphs_alltime_logins` varchar(32) default NULL,
  `graphs_alltime_traffic_compare` varchar(32) default NULL,
  `graphs_overall_download` varchar(32) default NULL,
  `graphs_overall_upload` varchar(32) default NULL,
  `graphs_overall_logins` varchar(32) default NULL,
  `config_db` varchar(32) default NULL,
  `config_interface` varchar(32) default NULL,
  `config_lang` varchar(32) default NULL,
  `config_logging` varchar(32) default NULL,
  `config_maint_test_user` varchar(32) default NULL,
  `config_maint_disconnect_user` varchar(32) default NULL,
  `config_operators_del` varchar(32) default NULL,
  `config_operators_edit` varchar(32) default NULL,
  `config_operators_list` varchar(32) default NULL,
  `config_operators_new` varchar(32) default NULL,
  `firstname` varchar(32) default NULL,
  `lastname` varchar(32) default NULL,
  `title` varchar(32) default NULL,
  `department` varchar(32) default NULL,
  `company` varchar(32) default NULL,
  `phone1` varchar(32) default NULL,
  `phone2` varchar(32) default NULL,
  `email1` varchar(32) default NULL,
  `email2` varchar(32) default NULL,
  `messenger1` varchar(32) default NULL,
  `messenger2` varchar(32) default NULL,
  `notes` varchar(128) default NULL,
  `lastlogin` datetime default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `operators`
--


/*!40000 ALTER TABLE `operators` DISABLE KEYS */;
LOCK TABLES `operators` WRITE;
INSERT INTO `operators` VALUES (1,'administrator','radius','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','Sys','Administrator','','','','','','','','','','','0000-00-00 00:00:00'),(2,'liran','1234','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','Liran','Tal','Developer','daloRADIUS','Enginx','','','liran.tal@gmail.com','liran@enginx.com','','','','0000-00-00 00:00:00');
UNLOCK TABLES;
/*!40000 ALTER TABLE `operators` ENABLE KEYS */;

--
-- Table structure for table `radacct`
--

DROP TABLE IF EXISTS `radacct`;
CREATE TABLE `radacct` (
  `RadAcctId` bigint(21) NOT NULL auto_increment,
  `AcctSessionId` varchar(32) NOT NULL default '',
  `AcctUniqueId` varchar(32) NOT NULL default '',
  `UserName` varchar(64) NOT NULL default '',
  `Realm` varchar(64) default '',
  `NASIPAddress` varchar(15) NOT NULL default '',
  `NASPortId` varchar(15) default NULL,
  `NASPortType` varchar(32) default NULL,
  `AcctStartTime` datetime NOT NULL default '0000-00-00 00:00:00',
  `AcctStopTime` datetime NOT NULL default '0000-00-00 00:00:00',
  `AcctSessionTime` int(12) default NULL,
  `AcctAuthentic` varchar(32) default NULL,
  `ConnectInfo_start` varchar(50) default NULL,
  `ConnectInfo_stop` varchar(50) default NULL,
  `AcctInputOctets` bigint(12) default NULL,
  `AcctOutputOctets` bigint(12) default NULL,
  `CalledStationId` varchar(50) NOT NULL default '',
  `CallingStationId` varchar(50) NOT NULL default '',
  `AcctTerminateCause` varchar(32) NOT NULL default '',
  `ServiceType` varchar(32) default NULL,
  `FramedProtocol` varchar(32) default NULL,
  `FramedIPAddress` varchar(15) NOT NULL default '',
  `AcctStartDelay` int(12) default NULL,
  `AcctStopDelay` int(12) default NULL,
  PRIMARY KEY  (`RadAcctId`),
  KEY `UserName` (`UserName`),
  KEY `FramedIPAddress` (`FramedIPAddress`),
  KEY `AcctSessionId` (`AcctSessionId`),
  KEY `AcctUniqueId` (`AcctUniqueId`),
  KEY `AcctStartTime` (`AcctStartTime`),
  KEY `AcctStopTime` (`AcctStopTime`),
  KEY `NASIPAddress` (`NASIPAddress`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `radacct`
--


/*!40000 ALTER TABLE `radacct` DISABLE KEYS */;
LOCK TABLES `radacct` WRITE;
INSERT INTO `radacct` VALUES (1,'450D4F23287600','6c3a819077eddddc','admin','','192.168.100.1','0','Virtual','2006-09-17 09:35:31','2006-09-17 09:35:48',17,'RADIUS','','',769,33,'00:00:00:aa:aa:aa','','User-Request','Framed-User','PPP','10.67.15.14',0,0),(2,'450D510128ED00','008eb335edd6929c','admin','','192.168.100.1','0','Virtual','2006-09-17 09:43:29','2006-09-17 09:43:34',5,'RADIUS','','',865,33,'00:00:00:aa:aa:aa','','User-Request','Framed-User','PPP','10.67.15.1',0,0),(3,'4510EBBE341400','71dbcdabcb329bf3','al','','192.168.100.1','0','Virtual','2006-09-20 03:20:30','2006-09-20 03:20:50',20,'RADIUS','','',833,172,'','','User-Request','Framed-User','PPP','10.67.15.1',0,0),(4,'4510EBE9346300','a61a5144b5e6c020','admin','','192.168.100.1','1','Virtual','2006-09-20 03:21:13','2006-09-20 03:21:18',5,'RADIUS','','',769,33,'00:00:00:aa:aa:aa','','User-Request','Framed-User','PPP','10.67.15.3',0,0),(5,'00000001463CAC58','0b15d952ff91e7d9','test','','0.0.0.0','1','','2007-05-05 12:10:00','2007-05-05 12:11:48',108,'','','',3840,0,'00:00:00:bb:bb:bb','','','Framed-User','PPP','10.10.10.1',0,0),(6,'00000002463CACC9','37b179fdc79c6481','test','','0.0.0.0','2','','2007-05-05 12:11:53','2007-05-05 12:11:57',4,'','','',984,0,'00:00:00:bb:bb:bb','','','Framed-User','PPP','10.10.10.1',0,0),(7,'00000003463CB0CB','f8aea6c17898989d','test','','0.0.0.0','1','','2007-05-05 12:28:59','2007-05-05 14:47:27',8308,'','','',11591,0,'00:00:00:bb:bb:bb','','','Framed-User','PPP','10.10.10.1',0,0),(8,'00000004463CD1AA','c826b37a1d3728f3','test','','0.0.0.0','1','','2007-05-05 14:49:14','2007-05-05 15:20:17',1863,'','','',4298,0,'00:00:00:bb:bb:bb','','','Framed-User','PPP','10.10.10.1',0,0),(9,'00000005463D51EF','6878bfe6a16bc8ea','admin','','0.0.0.0','1','','2007-05-05 23:56:31','2007-05-06 05:09:26',18775,'','','',21488,0,'00:00:00:aa:aa:aa','','','Framed-User','PPP','10.10.10.2',0,0),(10,'00000006463D9B4B','d130d29bae27c90b','liran','','0.0.0.0','2','','2007-05-06 05:09:32','2007-05-06 10:04:36',17705,'','','',23255,0,'00:00:00:aa:aa:aa','','','Framed-User','PPP','10.10.10.3',1,0),(11,'00000007463D9B6E','d135c1218bc578a9','danny','','0.0.0.0','1','','2007-05-06 05:10:06','2007-05-06 10:04:36',17670,'','','',20974,217281820,'00:00:00:bb:bb:bb','','','Framed-User','PPP','10.10.10.4',0,0),(12,'00000008463DE134','e8069847b4e4938f','liran','','0.0.0.0','2','','2007-05-06 10:07:48','2007-05-06 10:53:29',2741,'','','',7143,72154704,'00:00:00:aa:aa:aa','','','Framed-User','PPP','10.10.10.3',0,0),(13,'00000009463DEBF0','f9e61040b890e188','danny','','0.0.0.0','1','','2007-05-06 10:53:37','2007-05-06 11:50:36',3420,'','','',5930,47475588,'00:00:00:bb:bb:bb','','','Framed-User','PPP','10.10.10.4',1,0),(14,'0000000A463DEC0E','36747670cefa3ce3','liran','','0.0.0.0','2','','2007-05-06 10:54:06','2007-05-06 11:50:36',3390,'','','',6064,0,'00:00:00:aa:aa:aa','','','Framed-User','PPP','10.10.10.3',0,0),(15,'0000000B463DF960','b7c7d75ba3e60a71','liran','','0.0.0.0','1','','2007-05-06 11:50:57','2007-05-06 12:42:36',3100,'','','',7721,0,'00:00:00:aa:aa:aa','','','Framed-User','PPP','10.10.10.3',1,0),(16,'0000000C463DF964','3856cd1828da56c2','danny','','0.0.0.0','2','','2007-05-06 11:51:01','2007-05-06 12:42:36',3096,'','','',6809,0,'00:00:00:bb:bb:bb','','','Framed-User','PPP','10.10.10.4',1,0),(17,'0000000D463E1F2D','9495de07811d6e44','danny','','0.0.0.0','2','','2007-05-06 14:32:15','2007-05-06 23:27:22',32109,'','','',35546,0,'00:00:00:bb:bb:bb','','','Framed-User','PPP','10.10.10.4',2,0),(18,'0000000E463EA27D','86c37252fd84c35b','danny','','0.0.0.0','2','','2007-05-06 23:52:29','2007-05-07 11:36:55',42266,'','','',46366,0,'00:00:00:bb:bb:bb','','','Framed-User','PPP','10.10.10.4',0,0),(19,'0000000F463F48D2','bc1045df09089ed1','danny','','0.0.0.0','2','','2007-05-07 11:42:11','2007-05-07 11:44:33',143,'','','',4074,2789568,'00:00:00:bb:bb:bb','','','Framed-User','PPP','10.10.10.4',1,0),(20,'00000010463F496A','7e21aad529dfc7f8','liran','','0.0.0.0','1','','2007-05-07 11:44:42','2007-05-09 12:33:44',175742,'','','',180295,0,'00:00:00:aa:aa:aa','','','Framed-User','PPP','10.10.10.3',0,0),(21,'00000001468A3736','ffafcc4cf56c71a8','danny','','0.0.0.0','1','','2007-07-03 07:47:02','2007-07-03 08:02:38',936,'','','',4223,0,'00:00:00:bb:bb:bb','','','Framed-User','PPP','10.10.10.1',0,0),(22,'00000001468A3B1C','5e617e4464aedafc','danny','','0.0.0.0','1','','2007-07-03 08:03:40','0000-00-00 00:00:00',0,'','','',0,0,'00:00:00:bb:bb:bb','','','Framed-User','PPP','10.10.10.1',0,0),(23,'00000001468A3B7C','60f26d1b206f4d11','danny','','0.0.0.0','1','','2007-07-03 08:05:16','2007-07-03 13:19:00',18824,'','','',24692,0,'00:00:00:bb:bb:bb','','Admin-Reset','Framed-User','PPP','10.10.10.1',0,0),(24,'00000002468A3F6D','48131027e409bdd5','liran','','0.0.0.0','2','','2007-07-03 08:22:05','2007-07-03 13:19:01',17816,'','','',21050,0,'00:00:00:aa:aa:aa','','Admin-Reset','Framed-User','PPP','10.10.10.2',0,0),(25,'00000003468A8AE8','6d04d7ae2706bec4','liran','','0.0.0.0','1','','2007-07-03 13:44:08','2007-07-03 13:44:09',1,'','','',464,0,'00:00:00:aa:aa:aa','','Admin-Reset','Framed-User','PPP','10.10.10.2',0,0),(26,'00000004468A8B18','2a338b62db6ec051','liran','','0.0.0.0','1','','2007-07-03 13:44:56','2007-07-03 23:23:18',34702,'','','',37073,0,'00:00:00:aa:aa:aa','','Admin-Reset','Framed-User','PPP','10.10.10.2',0,0),(27,'00000005468B12B7','8141b20a630b993b','test','','0.0.0.0','1','','2007-07-03 23:23:35','2007-07-04 11:09:22',42347,'','','',46443,0,'00:00:00:bb:bb:bb','','Admin-Reset','Framed-User','PPP','10.10.10.3',0,0),(28,'00000006468B12BF','f2deabf8b5296318','liran','','0.0.0.0','2','','2007-07-03 23:23:45','2007-07-04 11:09:08',42325,'','','',45248,0,'00:00:00:aa:aa:aa','','Admin-Reset','Framed-User','PPP','10.10.10.2',2,0),(29,'00000007468BB822','1adde36e0066ccc2','liran','','0.0.0.0','3','','2007-07-04 11:09:25','2007-07-04 12:26:55',4653,'','','',9203,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.2',3,0),(30,'00000008468BB828','042313f5d3a3b54b','test','','0.0.0.0','2','','2007-07-04 11:09:35','2007-07-04 12:26:57',4649,'','','',8693,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.3',7,0),(31,'00000009468BCA61','a11d34b937e1be37','lala','','0.0.0.0','2','','2007-07-04 12:27:13','2007-07-04 12:27:29',16,'','','',4074,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.4',0,0),(32,'0000000A468BCA93','6c85de9de908531b','hznDEgs','','0.0.0.0','2','','2007-07-04 12:28:04','2007-07-04 14:11:57',6234,'','','',12567,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.5',1,0),(33,'0000000B468BD4BF','78a20288de7812c6','lala','','0.0.0.0','3','','2007-07-04 13:11:28','2007-07-04 14:11:59',3632,'','','',7996,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.4',1,0),(34,'0000000C468BE2F2','33e089b834052f5b','lala','','0.0.0.0','1','','2007-07-04 14:12:05','2007-07-05 11:05:37',75215,'','','',186036,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.4',3,0),(35,'0000000D468BE2F6','d6274b5a2012223c','hznDEgs','','0.0.0.0','3','','2007-07-04 14:12:24','2007-07-05 11:05:22',75196,'','','',184922,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.5',18,0),(36,'0000000E468D08C1','7db54ba986808ae9','hznDEgs','','0.0.0.0','2','','2007-07-05 11:05:41','2007-07-05 11:05:50',13,'','','',1944,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.5',4,0),(37,'0000000F468D0AE2','d4b20d0492cea146','hznDEgs','','0.0.0.0','2','','2007-07-05 11:14:42','2007-07-05 11:14:47',5,'','','',1408,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.5',0,0),(38,'00000010468D0AF4','e335e3cfbe1aff0d','hznDEgs','','0.0.0.0','1','','2007-07-05 11:15:00','2007-07-06 02:17:11',54131,'','','',169496,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.5',0,0),(39,'00000011468DDE78','20b780550e869a10','liran','','0.0.0.0','1','','2007-07-06 02:17:28','2007-07-06 15:01:52',45864,'','','',275859,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.2',0,0),(40,'00000012468F9949','aae658465dfd1aea','liran','','0.0.0.0','1','','2007-07-07 09:46:49','2007-07-07 11:03:27',4598,'','','',8064,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.2',0,0),(41,'00000013468FCC71','99c39d00983c4d07','liran','','0.0.0.0','1','','2007-07-07 13:25:05','2007-07-08 00:28:58',39833,'','','',42829,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.2',0,0),(42,'000000144690E27E','a9d8a45364b8ef68','test','','0.0.0.0','2','','2007-07-08 09:11:26','2007-07-08 11:53:11',9705,'','','',13825,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.3',0,0),(43,'000000154691086C','ca7fef751653315c','liran','','0.0.0.0','1','','2007-07-08 11:53:19','0000-00-00 00:00:00',0,'','','',0,0,'','','','Framed-User','PPP','10.10.10.2',3,0),(44,'000000164695F69D','9cd7ef0dbfa8c830','liran','','0.0.0.0','1','','2007-07-12 05:38:37','2007-07-12 05:49:01',624,'','','',5070,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.2',0,0),(45,'00000017469A2D8B','f5ed7dd20d738c6e','liran','','0.0.0.0','1','','2007-07-15 10:22:03','2007-07-15 10:41:57',1194,'','','',48292,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.2',0,0),(46,'00000018469A2D90','c76cbedc59fd5812','test','','0.0.0.0','2','','2007-07-15 10:22:08','2007-07-15 10:41:55',1187,'','','',47183,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.3',0,0),(47,'0000000146ADEA4D','8b934d7e0d78ed36','liri','','0.0.0.0','1','','2007-07-30 09:40:29','2007-07-30 09:40:52',23,'','','',2018,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.1',0,0),(48,'0000000146FA40D0','ebdd8fd39a95a3f3','liri','','0.0.0.0','1','','2007-09-26 07:21:52','2007-09-26 07:24:31',159,'','','',4034,0,'','','Admin-Reset','Framed-User','PPP','10.10.10.1',0,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `radacct` ENABLE KEYS */;

--
-- Table structure for table `radcheck`
--

DROP TABLE IF EXISTS `radcheck`;
CREATE TABLE `radcheck` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `UserName` varchar(64) NOT NULL default '',
  `Attribute` varchar(32) NOT NULL default '',
  `op` char(2) NOT NULL default '==',
  `Value` varchar(253) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `UserName` (`UserName`(32))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `radcheck`
--


/*!40000 ALTER TABLE `radcheck` DISABLE KEYS */;
LOCK TABLES `radcheck` WRITE;
INSERT INTO `radcheck` VALUES (2,'admin','User-Password','==','admin12347'),(3,'admin','Max-All-Session',':=','86400'),(4,'liran','User-Password','==','1234'),(5,'danny','User-Password','==','1234'),(6,'test','User-Password','==','1234'),(8,'hznDEgs','User-Password','==','EXNzyur1'),(9,'hznDEgs','Expiration',':=','22 May 2008'),(10,'hznDEgs','Max-All-Session',':=','86400'),(23,'danny','Max-All-Session',':=','9999999'),(22,'danny','Expiration',':=','Jul 10 2010'),(24,'danny','Called-Station-Id',':=','00aabbccddcc'),(25,'danny','Calling-Station-Id',':=','00aabb33sdd1'),(26,'lala','Password','==','yaya'),(27,'4343','User-Password','==','4343'),(35,'anQxFBqe','User-Password','==','WDSJMQVF'),(34,'jIcZIEXY','Max-All-Session',':=','1800'),(33,'jIcZIEXY','User-Password','==','HSzEGW8'),(31,'nu2jqfrJ','User-Password','==','rYyVZqG'),(32,'nu2jqfrJ','Max-All-Session',':=','86400'),(36,'anQxFBqe','Expiration',':=','03 Jul 2007'),(37,'dd_7r7f','User-Password','==','ued0'),(38,'dd_7r7f','Expiration',':=','02 Jul 2007'),(39,'dd_7r7f','Max-All-Session',':=','86400'),(40,'dd_4kfy','User-Password','==','hiat'),(41,'dd_4kfy','Expiration',':=','02 Jul 2007'),(42,'dd_4kfy','Max-All-Session',':=','86400'),(54,'BgJA5eK','Max-All-Session',':=','1209600'),(53,'BgJA5eK','User-Password','==','3hGyILJQ'),(52,'liri','Crypt-Password','==','NEZw9YHncFJww'),(56,'ss_uiv6','User-Password','==','o2zk'),(57,'yy_yce6','User-Password','==','bupdue'),(58,'yy_yce6','Max-All-Session',':=','259200'),(59,'yy_se86','User-Password','==','5uwx47'),(60,'yy_se86','Max-All-Session',':=','259200'),(61,'uaJ6rRcz','User-Password','==','yHQVJCm'),(62,'liran1','User-Password','==','dZNAdHx'),(63,'batch1_bitm','User-Password','==','a46q'),(64,'batch2_jcfb','User-Password','==','txwh'),(65,'batch2_ckij','User-Password','==','4tvp'),(66,'ImEJpQ','User-Password','==','AiUjUPG'),(67,'liran2','Cleartext-Password',':=','liran2'),(69,'lilili','User-Password','==','4cjLjHHk'),(70,'lilili','Expiration',':=','02 Jul 2007'),(84,'zana','Calling-Station-Id','=','00:00:00:00:00:00'),(83,'zana','Max-All-Session','=','604800'),(82,'zana','Chap-Password','==','222222222222'),(89,'TESTER_t4ft3t8a','User-Password','==','4ruekkjy'),(88,'TESTER_8fj0vgqm','User-Password','==','8rerd4sm'),(90,'7VOei9Rv','User-Password','==','MYUYvXN'),(91,'7VOei9Rv','Max-All-Session',':=','604800'),(92,'ANrWf49Q','User-Password','==','G88pX9x'),(93,'ANrWf49Q','Max-All-Session',':=','1814400'),(94,'3GzQfe9C','User-Password','==','YwuXQKbX'),(95,'6F5fI5Fz','User-Password','==','gRT8cvG'),(96,'kH5Ee8hr','User-Password','==','jDFKrxj'),(97,'WPJ7fCKD','User-Password','==','iwtD2uqb'),(98,'QrZnEYA7','User-Password','==','mzKy3vW'),(99,'t3XfG7ua','User-Password','==','z9xzbA8'),(100,'OGjYwO','User-Password','==','uXIyUa2G'),(101,'OGjYwO','Max-All-Session',':=','259200'),(131,'tata','User-Password','==','CVuxjcf');
UNLOCK TABLES;
/*!40000 ALTER TABLE `radcheck` ENABLE KEYS */;

--
-- Table structure for table `radgroupcheck`
--

DROP TABLE IF EXISTS `radgroupcheck`;
CREATE TABLE `radgroupcheck` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `GroupName` varchar(64) NOT NULL default '',
  `Attribute` varchar(32) NOT NULL default '',
  `op` char(2) NOT NULL default '==',
  `Value` varchar(253) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `GroupName` (`GroupName`(32))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `radgroupcheck`
--


/*!40000 ALTER TABLE `radgroupcheck` DISABLE KEYS */;
LOCK TABLES `radgroupcheck` WRITE;
INSERT INTO `radgroupcheck` VALUES (1,'ggg5','aaa5vvvv','!*','vvv599'),(3,'ggg522','ddd','+=','ddd'),(5,'ggg21','ff','==','fff'),(6,'g2','ff','=','fff');
UNLOCK TABLES;
/*!40000 ALTER TABLE `radgroupcheck` ENABLE KEYS */;

--
-- Table structure for table `radgroupreply`
--

DROP TABLE IF EXISTS `radgroupreply`;
CREATE TABLE `radgroupreply` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `GroupName` varchar(64) NOT NULL default '',
  `Attribute` varchar(32) NOT NULL default '',
  `op` char(2) NOT NULL default '=',
  `Value` varchar(253) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `GroupName` (`GroupName`(32))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `radgroupreply`
--


/*!40000 ALTER TABLE `radgroupreply` DISABLE KEYS */;
LOCK TABLES `radgroupreply` WRITE;
INSERT INTO `radgroupreply` VALUES (1,'dsa','dda','==','ddddd'),(2,'iou','kkkk','+=','2322'),(4,'g2','a266666666666666','=','v2'),(6,'g3','a3','=','v3'),(7,'g4','a4','==','v4'),(8,'g5','a5',':=','v5'),(9,'g6','a6','==','v6'),(10,'g7','a7',':=','v7'),(11,'g8','a8','==','v8'),(14,'g1','1111','=','222211111666');
UNLOCK TABLES;
/*!40000 ALTER TABLE `radgroupreply` ENABLE KEYS */;

--
-- Table structure for table `radpostauth`
--

DROP TABLE IF EXISTS `radpostauth`;
CREATE TABLE `radpostauth` (
  `id` int(11) NOT NULL auto_increment,
  `user` varchar(64) NOT NULL default '',
  `pass` varchar(64) NOT NULL default '',
  `reply` varchar(32) NOT NULL default '',
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `radpostauth`
--


/*!40000 ALTER TABLE `radpostauth` DISABLE KEYS */;
LOCK TABLES `radpostauth` WRITE;
INSERT INTO `radpostauth` VALUES (1,'test','1234','Access-Accept','2007-05-05 16:09:41'),(2,'test','1234','Access-Accept','2007-05-05 16:10:00'),(3,'test','1234','Access-Accept','2007-05-05 16:11:53'),(4,'test','1234','Access-Accept','2007-05-05 16:28:59'),(5,'test','1234','Access-Accept','2007-05-05 18:49:14'),(6,'admin','admin','Access-Accept','2007-05-06 03:56:31'),(7,'liran','liran','Access-Accept','2007-05-06 09:09:32'),(8,'danny','danny','Access-Accept','2007-05-06 09:10:06'),(9,'liran','liran','Access-Accept','2007-05-06 14:07:48'),(10,'danny','danny','Access-Accept','2007-05-06 14:53:37'),(11,'liran','liran','Access-Accept','2007-05-06 14:54:06'),(12,'liran','liran','Access-Accept','2007-05-06 15:50:57'),(13,'danny','danny','Access-Accept','2007-05-06 15:51:01'),(14,'danny','danny','Access-Accept','2007-05-06 18:32:14'),(15,'danny','danny','Access-Accept','2007-05-07 03:52:29'),(16,'danny','danny','Access-Accept','2007-05-07 15:42:11'),(17,'liran','liran','Access-Accept','2007-05-07 15:44:42'),(18,'danny','1234','Access-Accept','2007-07-03 11:47:02'),(19,'danny','1234','Access-Accept','2007-07-03 12:03:40'),(20,'danny','1234','Access-Accept','2007-07-03 12:05:16'),(21,'liran','liran','Access-Accept','2007-07-03 12:22:05'),(22,'liran','liran','Access-Accept','2007-07-03 17:44:08'),(23,'liran','liran','Access-Accept','2007-07-03 17:44:56'),(24,'test','1234','Access-Accept','2007-07-04 03:23:35'),(25,'liran','liran','Access-Accept','2007-07-04 03:23:45'),(26,'liran','liran','Access-Accept','2007-07-04 15:09:25'),(27,'test','1234','Access-Accept','2007-07-04 15:09:35'),(28,'lala','yaya','Access-Accept','2007-07-04 16:27:13'),(29,'hznDEgs','EXNzyur1','Access-Accept','2007-07-04 16:28:04'),(30,'lala','yaya','Access-Accept','2007-07-04 17:11:28'),(31,'lala','yaya','Access-Accept','2007-07-04 18:12:05'),(32,'hznDEgs','EXNzyur1','Access-Accept','2007-07-04 18:12:23'),(33,'hznDEgs','EXNzyur1','Access-Accept','2007-07-05 15:05:40'),(34,'hznDEgs','EXNzyur1','Access-Accept','2007-07-05 15:14:42'),(35,'hznDEgs','EXNzyur1','Access-Accept','2007-07-05 15:15:00'),(36,'liran','liran','Access-Accept','2007-07-06 06:17:28'),(37,'liran','liran','Access-Accept','2007-07-07 13:46:49'),(38,'liran','liran','Access-Accept','2007-07-07 17:25:05'),(39,'test','1234','Access-Accept','2007-07-08 13:11:26'),(40,'liran','liran','Access-Accept','2007-07-08 15:53:19'),(41,'liran','1234','Access-Accept','2007-07-12 09:38:37'),(42,'liran','1234','Access-Accept','2007-07-15 14:22:03'),(43,'test','1234','Access-Accept','2007-07-15 14:22:08'),(44,'liran','1234','Access-Accept','2007-07-29 15:54:34'),(45,'liri','liri','Access-Accept','2007-07-30 13:40:29'),(46,'liran','1234','Access-Accept','2007-08-04 08:26:10'),(47,'liran','1234','Access-Accept','2007-08-04 08:29:33'),(48,'liran','1234','Access-Accept','2007-08-04 08:30:31'),(49,'liran','1234','Access-Accept','2007-08-04 08:31:08'),(50,'liran','1234','Access-Accept','2007-08-04 08:32:19'),(51,'liran','1234','Access-Accept','2007-08-04 08:32:50'),(52,'liran','1234','Access-Accept','2007-08-04 08:33:21'),(53,'liran','1234','Access-Accept','2007-08-04 08:33:38'),(54,'liran','1234','Access-Accept','2007-08-04 08:33:50'),(55,'liran','1234','Access-Accept','2007-08-04 08:33:58'),(56,'liran','1234','Access-Accept','2007-08-04 08:34:01'),(57,'liran','1234','Access-Accept','2007-08-04 08:34:19'),(58,'liran','1234','Access-Accept','2007-08-04 08:37:04'),(59,'liran','1234','Access-Accept','2007-08-04 08:38:05'),(60,'liran','1234','Access-Accept','2007-08-04 08:38:20'),(61,'liran','1234','Access-Accept','2007-08-04 08:38:32'),(62,'liran','1234','Access-Accept','2007-08-04 08:39:11'),(63,'liran','1234','Access-Accept','2007-08-04 08:40:57'),(64,'liran','1234','Access-Accept','2007-08-04 08:40:59'),(65,'liran','1234','Access-Accept','2007-08-04 08:41:25'),(66,'liran','1234','Access-Accept','2007-08-04 08:41:56'),(67,'liran','1234','Access-Accept','2007-08-04 08:42:38'),(68,'liran','1234','Access-Accept','2007-08-04 08:42:46'),(69,'liran','1234','Access-Accept','2007-08-04 08:43:13'),(70,'liran','1234','Access-Accept','2007-08-04 08:43:22'),(71,'liran','1234','Access-Accept','2007-08-04 08:45:14'),(72,'liran','1234','Access-Accept','2007-08-04 08:54:07'),(73,'liran','1234','Access-Accept','2007-08-04 08:55:48'),(74,'liran','1234','Access-Accept','2007-08-04 08:58:01'),(75,'liran','1234','Access-Accept','2007-08-04 08:58:16'),(76,'liran','1234','Access-Accept','2007-08-04 09:00:28'),(77,'liran','1234','Access-Accept','2007-08-04 09:00:36'),(78,'liran','1234','Access-Accept','2007-08-04 09:01:54'),(79,'liran','1234','Access-Accept','2007-08-04 09:04:11'),(80,'liri','liri','Access-Accept','2007-09-26 11:21:52');
UNLOCK TABLES;
/*!40000 ALTER TABLE `radpostauth` ENABLE KEYS */;

--
-- Table structure for table `radreply`
--

DROP TABLE IF EXISTS `radreply`;
CREATE TABLE `radreply` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `UserName` varchar(64) NOT NULL default '',
  `Attribute` varchar(32) NOT NULL default '',
  `op` char(2) NOT NULL default '=',
  `Value` varchar(253) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `UserName` (`UserName`(32))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `radreply`
--


/*!40000 ALTER TABLE `radreply` DISABLE KEYS */;
LOCK TABLES `radreply` WRITE;
INSERT INTO `radreply` VALUES (1,'admin','Session-Timeout',':=','222'),(2,'admin','Idle-Timeout',':=','600'),(3,'liran','Session-Timeout',':=','3600'),(4,'liran','Idle-Timeout',':=','222'),(5,'hznDEgs','Session-Timeout',':=','86400'),(6,'hznDEgs','Idle-Timeout',':=','86400'),(7,'danny','Session-Timeout',':=','1111'),(8,'danny','Idle-Timeout',':=','666666'),(9,'danny','WISPr-Bandwidth-Max-Down',':=','750kbit'),(10,'danny','WISPr-Bandwidth-Max-Up',':=','750kbit'),(11,'danny','WISPr-Redirection-URL',':=','www.cisco.com'),(15,'jIcZIEXY','Session-Timeout',':=','1800'),(14,'nu2jqfrJ','Session-Timeout',':=','86400'),(17,'BgJA5eK','Session-Timeout',':=','1209600'),(18,'yy_yce6','Session-Timeout',':=','259200'),(19,'yy_yce6','WISPr-Redirection-URL',':=','http://www.enginx.com'),(20,'yy_se86','Session-Timeout',':=','259200'),(21,'yy_se86','WISPr-Redirection-URL',':=','http://www.enginx.com'),(24,'zana','Session-Timeout','=','604800'),(25,'TESTER_8fj0vgqm','Session-Timeout','=','86400'),(26,'TESTER_8fj0vgqm','Idle-Timeout','=','259200'),(27,'TESTER_t4ft3t8a','Session-Timeout','=','86400'),(28,'TESTER_t4ft3t8a','Idle-Timeout','=','259200'),(29,'7VOei9Rv','Session-Timeout',':=','604800'),(30,'ANrWf49Q','Session-Timeout',':=','1814400'),(31,'OGjYwO','Session-Timeout',':=','259200');
UNLOCK TABLES;
/*!40000 ALTER TABLE `radreply` ENABLE KEYS */;

--
-- Table structure for table `rates`
--

DROP TABLE IF EXISTS `rates`;
CREATE TABLE `rates` (
  `id` bigint(20) NOT NULL auto_increment,
  `type` varchar(32) default NULL,
  `cardbank` double default NULL,
  `rate` double default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rates`
--


/*!40000 ALTER TABLE `rates` DISABLE KEYS */;
LOCK TABLES `rates` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `rates` ENABLE KEYS */;

--
-- Table structure for table `usergroup`
--

DROP TABLE IF EXISTS `usergroup`;
CREATE TABLE `usergroup` (
  `UserName` varchar(64) NOT NULL default '',
  `GroupName` varchar(64) NOT NULL default '',
  `priority` int(11) NOT NULL default '1',
  KEY `UserName` (`UserName`(32))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `usergroup`
--


/*!40000 ALTER TABLE `usergroup` DISABLE KEYS */;
LOCK TABLES `usergroup` WRITE;
INSERT INTO `usergroup` VALUES ('liran','vv',1),('liran','ppppp',1),('dsadsa','hhhh',1),('liran','vvv',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `usergroup` ENABLE KEYS */;

--
-- Table structure for table `userinfo`
--

DROP TABLE IF EXISTS `userinfo`;
CREATE TABLE `userinfo` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `username` varchar(30) default NULL,
  `firstname` varchar(200) default NULL,
  `lastname` varchar(200) default NULL,
  `email` varchar(200) default NULL,
  `department` varchar(200) default NULL,
  `company` varchar(200) default NULL,
  `workphone` varchar(200) default NULL,
  `homephone` varchar(200) default NULL,
  `mobilephone` varchar(200) default NULL,
  `notes` varchar(200) default NULL,
  PRIMARY KEY  (`id`),
  KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `userinfo`
--


/*!40000 ALTER TABLE `userinfo` DISABLE KEYS */;
LOCK TABLES `userinfo` WRITE;
INSERT INTO `userinfo` VALUES (1,'liran','liran','tal','test@gmail.com','none','vvcvc','00','01','02','vbvcbvcbc'),(2,'nTAxLCPI','ss','ss','ss','cccv','vv','ww','22','ff',''),(3,'nTAxLCPI','das','das','cv','a','','','','',''),(4,'nTAxLCPI','d','d','d','n','b','v','n','n','n'),(5,'nTAxLCPI','v','v','','b','vv','d','','',''),(6,'nTAxLCPI','b','bbb','b','b','bb','bvv','bb','b','vv'),(7,'tata','test1','test2','m','m','m','m','m','m','m');
UNLOCK TABLES;
/*!40000 ALTER TABLE `userinfo` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

