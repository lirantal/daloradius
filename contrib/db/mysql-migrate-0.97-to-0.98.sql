-- MySQL dump 10.10
--
-- Host: localhost    Database: radius
-- ------------------------------------------------------
-- Server version	5.0.22-Debian_0ubuntu6.06-log

ALTER TABLE userinfo ADD changeuserinfo VARCHAR(128) AFTER notes;

ALTER TABLE operators ADD mng_rad_profiles_duplicate VARCHAR(32) AFTER mng_rad_profiles_edit;
UPDATE operators SET mng_rad_profiles_duplicate='yes' WHERE username='administrator';

ALTER TABLE operators ADD mng_rad_attributes_import VARCHAR(32) AFTER mng_rad_attributes_del;
UPDATE operators SET mng_rad_attributes_import='yes' WHERE username='administrator';

ALTER TABLE operators ADD config_backup_createbackups VARCHAR(32) AFTER config_operators_new;
UPDATE operators SET config_backup_createbackups='yes' WHERE username='administrator';

ALTER TABLE operators ADD config_backup_managebackups VARCHAR(32) AFTER config_backup_createbackups;
UPDATE operators SET config_backup_managebackups='yes' WHERE username='administrator';

ALTER TABLE userinfo ADD address VARCHAR(200) AFTER mobilephone;
ALTER TABLE userinfo ADD city VARCHAR(200) AFTER address;
ALTER TABLE userinfo ADD state VARCHAR(200) AFTER city;
ALTER TABLE userinfo ADD zip VARCHAR(200) AFTER state;
