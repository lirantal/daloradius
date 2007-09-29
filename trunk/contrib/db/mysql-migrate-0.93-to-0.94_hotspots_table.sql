-- MySQL dump 10.10
--
-- Host: localhost    Database: radius
-- ------------------------------------------------------
-- Server version	5.0.22-Debian_0ubuntu6.06-log

--
-- Table structure for table `hotspots`
--

ALTER TABLE hotspots ADD owner VARCHAR(32), ADD email_owner VARCHAR(32), ADD manager VARCHAR(32), ADD email_manager VARCHAR(32), ADD address VARCHAR(128), ADD company VARCHAR(32), ADD phone1 VARCHAR(32), ADD phone2 VARCHAR(32), ADD type VARCHAR(32), ADD website VARCHAR(32);
