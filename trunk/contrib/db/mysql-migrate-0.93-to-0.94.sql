-- MySQL dump 10.10
--
-- Host: localhost    Database: radius
-- ------------------------------------------------------
-- Server version	5.0.22-Debian_0ubuntu6.06-log

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
) ENGINE=MyISAM;
