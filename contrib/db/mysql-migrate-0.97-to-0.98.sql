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

ALTER TABLE operators DROP COLUMN bill_prepaid;
ALTER TABLE operators CHANGE bill_persecond bill_rates_date varchar(32);

CREATE TABLE billing_rates (
  id int(11) unsigned NOT NULL auto_increment,
  rateName varchar(128) NOT NULL default '',
  rateType varchar(128) NOT NULL default '',
  rateCost int(32) NOT NULL default 0,
  creationdate datetime default '0000-00-00 00:00:00',
  creationby varchar(128) default NULL,
  updatedate datetime default '0000-00-00 00:00:00',
  updateby varchar(128) default NULL,
  PRIMARY KEY  (id),
  KEY rateName (rateName(128))
);


ALTER TABLE operators ADD bill_paypal_transactions VARCHAR(32) AFTER bill_rates_list;
UPDATE operators SET bill_paypal_transactions='yes' WHERE username='administrator';

ALTER TABLE operators ADD bill_plans_list VARCHAR(32) AFTER bill_paypal_transactions;
UPDATE operators SET bill_plans_list='yes' WHERE username='administrator';

ALTER TABLE operators ADD bill_plans_new VARCHAR(32) AFTER bill_plans_list;
UPDATE operators SET bill_plans_new='yes' WHERE username='administrator';

ALTER TABLE operators ADD bill_plans_edit VARCHAR(32) AFTER bill_plans_new;
UPDATE operators SET bill_plans_edit='yes' WHERE username='administrator';

ALTER TABLE operators ADD bill_plans_del VARCHAR(32) AFTER bill_plans_edit;
UPDATE operators SET bill_plans_del='yes' WHERE username='administrator';
