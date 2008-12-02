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

CREATE TABLE billing_plans (
	id int(8) NOT NULL auto_increment,
	planName varchar(128) default NULL,
	planId varchar(128) default NULL,
	planType varchar(128) default NULL,
	planTimeBank varchar(128) default NULL,
	planTimeType varchar(128) default NULL,
	planTimeRefillCost varchar(128) default NULL,
	planBandwidthUp varchar(128) default NULL,
	planBandwidthDown varchar(128) default NULL,
	planTrafficTotal varchar(128) default NULL,
	planTrafficUp varchar(128) default NULL,
	planTrafficDown varchar(128) default NULL,
	planTrafficRefillCost varchar(128) default NULL,
	planRecurring varchar(128) default NULL,
	planRecurringPeriod varchar(128) default NULL,
	planCost varchar(128) default NULL,
	planSetupCost varchar(128) default NULL,
	planTax varchar(128) default NULL,
	planCurrency varchar(128) default NULL,
	planGroup varchar(128) default NULL,
	creationdate datetime default '0000-00-00 00:00:00',
	creationby varchar(128) default NULL,
	updatedate datetime default '0000-00-00 00:00:00',
	updateby varchar(128) default NULL,	
	PRIMARY KEY (id),
	KEY planName (planName)
);

CREATE TABLE `billing_paypal` (
  `id` int(8) NOT NULL auto_increment,
  `username` varchar(128) default NULL,
  `password` varchar(128) default NULL,
  `mac` varchar(128) default NULL,
  `pin` varchar(128) default NULL,
  `txnId` varchar(128) default NULL,
  `planName` varchar(128) default NULL,
  `planId` varchar(128) default NULL,
  `quantity` varchar(128) default NULL,
  `receiver_email` varchar(128) default NULL,
  `business` varchar(128) default NULL,
  `tax` varchar(128) default NULL,
  `mc_gross` varchar(128) default NULL,
  `mc_fee` varchar(128) default NULL,
  `mc_currency` varchar(128) default NULL,
  `first_name` varchar(128) default NULL,
  `last_name` varchar(128) default NULL,
  `payer_email` varchar(128) default NULL,
  `address_name` varchar(128) default NULL,
  `address_street` varchar(128) default NULL,
  `address_country` varchar(128) default NULL,
  `address_country_code` varchar(128) default NULL,
  `address_city` varchar(128) default NULL,
  `address_state` varchar(128) default NULL,
  `address_zip` varchar(128) default NULL,
  `payment_date` datetime default NULL,
  `payment_status` varchar(128) default NULL,
  `payment_address_status` varchar(128) default NULL,
  `payer_status` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



CREATE TABLE `userbillinfo` (
  `id` int(8) unsigned NOT NULL auto_increment,
  `username` varchar(64) default NULL,
  `planName` varchar(128) default NULL,
  `contactperson` varchar(200) default NULL,
  `company` varchar(200) default NULL,
  `email` varchar(200) default NULL,
  `phone` varchar(200) default NULL,
  `address` varchar(200) default NULL,
  `city` varchar(200) default NULL,
  `state` varchar(200) default NULL,
  `zip` varchar(200) default NULL,
  `paymentmethod` varchar(200) default NULL,
  `cash` varchar(200) default NULL,
  
  `creditcardname` varchar(200) default NULL,
  `creditcardnumber` varchar(200) default NULL,
  `creditcardverification` varchar(200) default NULL,
  `creditcardtype` varchar(200) default NULL,
  `creditcardexp` varchar(200) default NULL,
  
  `notes` varchar(200) default NULL,
  `changeuserbillinfo` varchar(128) default NULL,
   
  `lead` varchar(200) default NULL,
  `coupon` varchar(200) default NULL,
  `ordertaker` varchar(200) default NULL,

  `billstatus` varchar(200) default NULL,
  `lastbill` datetime default '0000-00-00 00:00:00',
  `nextbill` datetime default '0000-00-00 00:00:00',
  
  `postalinvoice` varchar(8) default NULL,
  `faxinvoice` varchar(8) default NULL,
  `emailinvoice` varchar(8) default NULL,
  
  
  `creationdate` datetime default '0000-00-00 00:00:00',
  `creationby` varchar(128) default NULL,
  `updatedate` datetime default '0000-00-00 00:00:00',
  `updateby` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


ALTER TABLE operators ADD bill_pos_list VARCHAR(32) AFTER bill_rates_list;
UPDATE operators SET bill_pos_list='yes' WHERE username='administrator';

ALTER TABLE operators ADD bill_pos_new VARCHAR(32) AFTER bill_pos_list;
UPDATE operators SET bill_pos_new='yes' WHERE username='administrator';

ALTER TABLE operators ADD bill_pos_edit VARCHAR(32) AFTER bill_pos_new;
UPDATE operators SET bill_pos_edit='yes' WHERE username='administrator';

ALTER TABLE operators ADD bill_pos_del VARCHAR(32) AFTER bill_pos_edit;
UPDATE operators SET bill_pos_del='yes' WHERE username='administrator';



CREATE TABLE `billing_history` (
  `id` int(8) unsigned NOT NULL auto_increment,
  `username` varchar(128) default NULL,
  `planName` varchar(128) default NULL,
  
  `billAmount` varchar(128) default NULL,
  `billAction` varchar(128) default NULL,
  `billPerformer` varchar(128) default NULL,
  `billReason` varchar(128) default NULL,

  `paymentmethod` varchar(200) default NULL,
  `cash` varchar(200) default NULL,
  
  `creditcardname` varchar(200) default NULL,
  `creditcardnumber` varchar(200) default NULL,
  `creditcardverification` varchar(200) default NULL,
  `creditcardtype` varchar(200) default NULL,
  `creditcardexp` varchar(200) default NULL,
  
  `coupon` varchar(200) default NULL,
  `discount` varchar(200) default NULL,
  
  `notes` varchar(200) default NULL,
  
  `creationdate` datetime default '0000-00-00 00:00:00',
  `creationby` varchar(128) default NULL,
  `updatedate` datetime default '0000-00-00 00:00:00',
  `updateby` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  KEY `username` (`username`)
)

ALTER TABLE operators ADD bill_history_query VARCHAR(32) AFTER bill_plans_list;
UPDATE operators SET bill_history_query='yes' WHERE username='administrator';
