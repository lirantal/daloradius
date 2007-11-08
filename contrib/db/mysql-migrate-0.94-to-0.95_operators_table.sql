-- MySQL dump 10.10
--
-- Host: localhost    Database: radius
-- ------------------------------------------------------
-- Server version	5.0.22-Debian_0ubuntu6.06-log

--
-- Table structure for table `operators`
--

-- we removed the rep_username column from the database
-- becasuse this page is deprecated

ALTER TABLE operators DROP COLUMN rep_username;
