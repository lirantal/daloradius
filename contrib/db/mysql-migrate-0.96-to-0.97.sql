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

ALTER TABLE operators ADD mng_rad_attributes_list VARCHAR(32);
ALTER TABLE operators ADD mng_rad_attributes_new VARCHAR(32);
ALTER TABLE operators ADD mng_rad_attributes_edit VARCHAR(32);
ALTER TABLE operators ADD mng_rad_attributes_del VARCHAR(32);
UPDATE operators SET mng_rad_attributes_list='yes' WHERE username='administrator';
UPDATE operators SET mng_rad_attributes_new='yes' WHERE username='administrator';
UPDATE operators SET mng_rad_attributes_edit='yes' WHERE username='administrator';
UPDATE operators SET mng_rad_attributes_del='yes' WHERE username='administrator';



