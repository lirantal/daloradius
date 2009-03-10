-- MySQL dump 10.10
--
-- Host: localhost    Database: radius
-- ------------------------------------------------------
-- Server version	5.0.22-Debian_0ubuntu6.06-log

ALTER TABLE userbillinfo ADD hotspotlocation VARCHAR(32) AFTER planName;

ALTER TABLE operators ADD bill_merchant_transactions VARCHAR(32) AFTER bill_rates_list;
UPDATE operators SET bill_merchant_transactions='yes' WHERE username='administrator';

CREATE TABLE `billing_merchant` (
  `id` int(8) NOT NULL auto_increment,
  `username` varchar(128) NOT NULL default '',
  `password` varchar(128) NOT NULL default '',
  `mac` varchar(200) NOT NULL default '',
  `pin` varchar(200) NOT NULL default '',
  `txnId` varchar(200) NOT NULL default '',
  `planName` varchar(128) NOT NULL default '',
  `planId` varchar(200) NOT NULL default '',
  `quantity` varchar(200) NOT NULL default '',
  `business_email` varchar(200) NOT NULL default '',
  `business_id` varchar(200) NOT NULL default '',
  `payment_tax` varchar(200) NOT NULL default '',
  `payment_cost` varchar(200) NOT NULL default '',
  `payment_fee` varchar(200) NOT NULL default '',
  `payment_total` varchar(200) NOT NULL default '',
  `payment_currency` varchar(200) NOT NULL default '',
  `first_name` varchar(200) NOT NULL default '',
  `last_name` varchar(200) NOT NULL default '',
  `payer_email` varchar(200) NOT NULL default '',
  `payer_address_name` varchar(200) NOT NULL default '',
  `payer_address_street` varchar(200) NOT NULL default '',
  `payer_address_country` varchar(200) NOT NULL default '',
  `payer_address_country_code` varchar(200) NOT NULL default '',
  `payer_address_city` varchar(200) NOT NULL default '',
  `payer_address_state` varchar(200) NOT NULL default '',
  `payer_address_zip` varchar(200) NOT NULL default '',
  `payment_date` datetime NOT NULL default '00-00-0000 00:00:00',
  `payment_status` varchar(200) NOT NULL default '',
  `payment_address_status` varchar(200) NOT NULL default '',
  `vendor_type` varchar(200) NOT NULL default '',
  `payer_status` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE operators DROP COLUMN bill_paypal_transactions;
