-- MySQL dump 10.10
--
-- Host: localhost    Database: radius
-- ------------------------------------------------------
-- Server version	5.0.22-Debian_0ubuntu6.06-log

ALTER TABLE userinfo ADD changeuserinfo VARCHAR(128) AFTER notes;

ALTER TABLE operators ADD mng_rad_profiles_duplicate VARCHAR(32) AFTER mng_rad_profiles_edit;
UPDATE operators SET mng_rad_profiles_duplicate='yes' WHERE username='administrator';

