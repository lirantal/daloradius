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
ALTER TABLE operators ADD acct_custom_query VARCHAR(32);
UPDATE operators SET acct_custom_query='yes' WHERE username='administrator';
ALTER TABLE operators ADD config_maint_disconnect_user VARCHAR(32);
UPDATE operators SET config_maint_disconnect_user='yes' WHERE username='administrator';
