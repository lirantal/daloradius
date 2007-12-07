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
) ENGINE=MyISAM;

--
-- Table structure for table `nas`
--

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
) ENGINE=MyISAM;

--
-- Table structure for table `operators`
--

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
) ENGINE=MyISAM;

LOCK TABLES `operators` WRITE;
INSERT INTO `operators` VALUES (1,'administrator','radius','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','Sys','Administrator','','','','','','','','','','','0000-00-00 00:00:00'),(2,'liran','1234','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','Liran','Tal','Developer','daloRADIUS','Enginx','','','liran.tal@gmail.com','liran@enginx.com','','','','0000-00-00 00:00:00');
UNLOCK TABLES;

--
-- Table structure for table `radacct`
--

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
) ENGINE=MyISAM;

--
-- Table structure for table `radcheck`
--


CREATE TABLE `radcheck` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `UserName` varchar(64) NOT NULL default '',
  `Attribute` varchar(32) NOT NULL default '',
  `op` char(2) NOT NULL default '==',
  `Value` varchar(253) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `UserName` (`UserName`(32))
) ENGINE=MyISAM;

--
-- Table structure for table `radgroupcheck`
--

CREATE TABLE `radgroupcheck` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `GroupName` varchar(64) NOT NULL default '',
  `Attribute` varchar(32) NOT NULL default '',
  `op` char(2) NOT NULL default '==',
  `Value` varchar(253) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `GroupName` (`GroupName`(32))
) ENGINE=MyISAM;

--
-- Table structure for table `radgroupreply`
--

CREATE TABLE `radgroupreply` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `GroupName` varchar(64) NOT NULL default '',
  `Attribute` varchar(32) NOT NULL default '',
  `op` char(2) NOT NULL default '=',
  `Value` varchar(253) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `GroupName` (`GroupName`(32))
) ENGINE=MyISAM;


--
-- Table structure for table `radpostauth`
--

CREATE TABLE `radpostauth` (
  `id` int(11) NOT NULL auto_increment,
  `user` varchar(64) NOT NULL default '',
  `pass` varchar(64) NOT NULL default '',
  `reply` varchar(32) NOT NULL default '',
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

--
-- Table structure for table `radreply`
--

CREATE TABLE `radreply` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `UserName` varchar(64) NOT NULL default '',
  `Attribute` varchar(32) NOT NULL default '',
  `op` char(2) NOT NULL default '=',
  `Value` varchar(253) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `UserName` (`UserName`(32))
) ENGINE=MyISAM;

--
-- Table structure for table `rates`
--

CREATE TABLE `rates` (
  `id` bigint(20) NOT NULL auto_increment,
  `type` varchar(32) default NULL,
  `cardbank` double default NULL,
  `rate` double default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

--
-- Table structure for table `usergroup`
--

CREATE TABLE `usergroup` (
  `UserName` varchar(64) NOT NULL default '',
  `GroupName` varchar(64) NOT NULL default '',
  `priority` int(11) NOT NULL default '1',
  KEY `UserName` (`UserName`(32))
) ENGINE=MyISAM;

--
-- Table structure for table `userinfo`
--

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
) ENGINE=MyISAM;
