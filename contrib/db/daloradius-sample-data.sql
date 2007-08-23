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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hotspots`
--


/*!40000 ALTER TABLE `hotspots` DISABLE KEYS */;
LOCK TABLES `hotspots` WRITE;
INSERT INTO `hotspots` VALUES (1,'NYC HotSpot','00:aa:bb:cc:dd:ee','40.80237530523985, -73.95309448242188'),(2,'Amsterdam HotSpot','00:aa:bb:cc:dd:ff','52.3755991766591, 4.9383544921875'),(3,'Tel Aviv HotSpot','00:aa:bb:gg:hh:yy','31.97837603690073, 34.77790832519531');
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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `operators`
--


/*!40000 ALTER TABLE `operators` DISABLE KEYS */;
LOCK TABLES `operators` WRITE;
INSERT INTO `operators` VALUES (1,'administrator','radius');
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
INSERT INTO `radacct` VALUES (1,'450D4F23287600','6c3a819077eddddc','admin','','192.168.100.1','0','Virtual','2006-09-17 09:35:31','2006-09-17 09:35:48',17,'RADIUS','','',769,33,'','','User-Request','Framed-User','PPP','10.67.15.14',0,0),(2,'450D510128ED00','008eb335edd6929c','admin','','192.168.100.1','0','Virtual','2006-09-17 09:43:29','2006-09-17 09:43:34',5,'RADIUS','','',865,33,'','','User-Request','Framed-User','PPP','10.67.15.1',0,0),(3,'4510EBBE341400','71dbcdabcb329bf3','al','','192.168.100.1','0','Virtual','2006-09-20 03:20:30','2006-09-20 03:20:50',20,'RADIUS','','',833,172,'','','User-Request','Framed-User','PPP','10.67.15.1',0,0),(4,'4510EBE9346300','a61a5144b5e6c020','admin','','192.168.100.1','1','Virtual','2006-09-20 03:21:13','2006-09-20 03:21:18',5,'RADIUS','','',769,33,'','','User-Request','Framed-User','PPP','10.67.15.3',0,0),(5,'00000001463CAC58','0b15d952ff91e7d9','test','','0.0.0.0','1','','2007-05-05 12:10:00','2007-05-05 12:11:48',108,'','','',3840,0,'','','','Framed-User','PPP','10.10.10.1',0,0),(6,'00000002463CACC9','37b179fdc79c6481','test','','0.0.0.0','2','','2007-05-05 12:11:53','2007-05-05 12:11:57',4,'','','',984,0,'','','','Framed-User','PPP','10.10.10.1',0,0),(7,'00000003463CB0CB','f8aea6c17898989d','test','','0.0.0.0','1','','2007-05-05 12:28:59','2007-05-05 14:47:27',8308,'','','',11591,0,'','','','Framed-User','PPP','10.10.10.1',0,0),(8,'00000004463CD1AA','c826b37a1d3728f3','test','','0.0.0.0','1','','2007-05-05 14:49:14','2007-05-05 15:20:17',1863,'','','',4298,0,'','','','Framed-User','PPP','10.10.10.1',0,0),(9,'00000005463D51EF','6878bfe6a16bc8ea','admin','','0.0.0.0','1','','2007-05-05 23:56:31','2007-05-06 05:09:26',18775,'','','',21488,0,'','','','Framed-User','PPP','10.10.10.2',0,0),(10,'00000006463D9B4B','d130d29bae27c90b','liran','','0.0.0.0','2','','2007-05-06 05:09:32','2007-05-06 10:04:36',17705,'','','',23255,0,'','','','Framed-User','PPP','10.10.10.3',1,0),(11,'00000007463D9B6E','d135c1218bc578a9','danny','','0.0.0.0','1','','2007-05-06 05:10:06','2007-05-06 10:04:36',17670,'','','',20974,217281820,'','','','Framed-User','PPP','10.10.10.4',0,0),(12,'00000008463DE134','e8069847b4e4938f','liran','','0.0.0.0','2','','2007-05-06 10:07:48','2007-05-06 10:53:29',2741,'','','',7143,72154704,'','','','Framed-User','PPP','10.10.10.3',0,0),(13,'00000009463DEBF0','f9e61040b890e188','danny','','0.0.0.0','1','','2007-05-06 10:53:37','2007-05-06 11:50:36',3420,'','','',5930,47475588,'','','','Framed-User','PPP','10.10.10.4',1,0),(14,'0000000A463DEC0E','36747670cefa3ce3','liran','','0.0.0.0','2','','2007-05-06 10:54:06','2007-05-06 11:50:36',3390,'','','',6064,0,'','','','Framed-User','PPP','10.10.10.3',0,0),(15,'0000000B463DF960','b7c7d75ba3e60a71','liran','','0.0.0.0','1','','2007-05-06 11:50:57','2007-05-06 12:42:36',3100,'','','',7721,0,'','','','Framed-User','PPP','10.10.10.3',1,0),(16,'0000000C463DF964','3856cd1828da56c2','danny','','0.0.0.0','2','','2007-05-06 11:51:01','2007-05-06 12:42:36',3096,'','','',6809,0,'','','','Framed-User','PPP','10.10.10.4',1,0),(17,'0000000D463E1F2D','9495de07811d6e44','danny','','0.0.0.0','2','','2007-05-06 14:32:15','2007-05-06 23:27:22',32109,'','','',35546,0,'','','','Framed-User','PPP','10.10.10.4',2,0),(18,'0000000E463EA27D','86c37252fd84c35b','danny','','0.0.0.0','2','','2007-05-06 23:52:29','2007-05-07 11:36:55',42266,'','','',46366,0,'','','','Framed-User','PPP','10.10.10.4',0,0),(19,'0000000F463F48D2','bc1045df09089ed1','danny','','0.0.0.0','2','','2007-05-07 11:42:11','2007-05-07 11:44:33',143,'','','',4074,2789568,'','','','Framed-User','PPP','10.10.10.4',1,0),(20,'00000010463F496A','7e21aad529dfc7f8','liran','','0.0.0.0','1','','2007-05-07 11:44:42','0000-00-00 00:00:00',0,'','','',0,0,'','','','Framed-User','PPP','10.10.10.3',0,0);
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
INSERT INTO `radcheck` VALUES (2,'admin','Password','==','admin'),(3,'admin','Max-All-Session',':=','86400'),(4,'liran','Password','==','liran'),(5,'danny','Password','==','danny'),(6,'test','Password','==','1234');
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
INSERT INTO `radpostauth` VALUES (1,'test','1234','Access-Accept','2007-05-05 16:09:41'),(2,'test','1234','Access-Accept','2007-05-05 16:10:00'),(3,'test','1234','Access-Accept','2007-05-05 16:11:53'),(4,'test','1234','Access-Accept','2007-05-05 16:28:59'),(5,'test','1234','Access-Accept','2007-05-05 18:49:14'),(6,'admin','admin','Access-Accept','2007-05-06 03:56:31'),(7,'liran','liran','Access-Accept','2007-05-06 09:09:32'),(8,'danny','danny','Access-Accept','2007-05-06 09:10:06'),(9,'liran','liran','Access-Accept','2007-05-06 14:07:48'),(10,'danny','danny','Access-Accept','2007-05-06 14:53:37'),(11,'liran','liran','Access-Accept','2007-05-06 14:54:06'),(12,'liran','liran','Access-Accept','2007-05-06 15:50:57'),(13,'danny','danny','Access-Accept','2007-05-06 15:51:01'),(14,'danny','danny','Access-Accept','2007-05-06 18:32:14'),(15,'danny','danny','Access-Accept','2007-05-07 03:52:29'),(16,'danny','danny','Access-Accept','2007-05-07 15:42:11'),(17,'liran','liran','Access-Accept','2007-05-07 15:44:42');
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
INSERT INTO `radreply` VALUES (1,'admin','Session-Timeout',':=','300'),(2,'admin','Idle-Timeout',':=','300'),(3,'liran','Session-Timeout',':=','300'),(4,'liran','Idle-Timeout',':=','300');
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
INSERT INTO `rates` VALUES (1,'per card 500',500,20),(2,'per card 3600',3600,50),(3,'per card 18000',18000,100),(5,'per second',1,1.5);
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
UNLOCK TABLES;
/*!40000 ALTER TABLE `usergroup` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

