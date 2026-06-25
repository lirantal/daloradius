-- MySQL dump 10.11
--
-- Host: localhost    Database: radius_099
-- ------------------------------------------------------
-- Server version	5.0.51a-24+lenny3-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `batch_history`
--

DROP TABLE IF EXISTS `batch_history`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `batch_history` (
  `id` INT(32) NOT NULL AUTO_INCREMENT,
  `batch_name` VARCHAR(64) DEFAULT NULL COMMENT 'an identifier name of the batch instance',
  `batch_description` VARCHAR(256) DEFAULT NULL COMMENT 'general description of the entry',
  `hotspot_id` INT(32) DEFAULT '0' COMMENT 'the hotspot business id associated with this batch instance',
  `batch_status` VARCHAR(128) NOT NULL DEFAULT 'Pending' COMMENT 'the batch status',
  `creationdate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `creationby` VARCHAR(128) DEFAULT NULL,
  `updatedate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `updateby` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `batch_name` (`batch_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `billing_history`
--

DROP TABLE IF EXISTS `billing_history`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `billing_history` (
  `id` INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(128) DEFAULT NULL,
  `planId` INT(32) DEFAULT NULL,
  `billAmount` VARCHAR(200) DEFAULT NULL,
  `billAction` VARCHAR(128) NOT NULL DEFAULT 'Unavailable',
  `billPerformer` VARCHAR(200) DEFAULT NULL,
  `billReason` VARCHAR(200) DEFAULT NULL,
  `paymentmethod` VARCHAR(200) DEFAULT NULL,
  `cash` VARCHAR(200) DEFAULT NULL,
  `creditcardname` VARCHAR(200) DEFAULT NULL,
  `creditcardnumber` VARCHAR(200) DEFAULT NULL,
  `creditcardverification` VARCHAR(200) DEFAULT NULL,
  `creditcardtype` VARCHAR(200) DEFAULT NULL,
  `creditcardexp` VARCHAR(200) DEFAULT NULL,
  `coupon` VARCHAR(200) DEFAULT NULL,
  `discount` VARCHAR(200) DEFAULT NULL,
  `notes` VARCHAR(200) DEFAULT NULL,
  `creationdate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `creationby` VARCHAR(128) DEFAULT NULL,
  `updatedate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `updateby` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `billing_merchant`
--

DROP TABLE IF EXISTS `billing_merchant`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `billing_merchant` (
  `id` INT(8) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(128) NOT NULL DEFAULT '',
  `password` VARCHAR(128) NOT NULL DEFAULT '',
  `mac` VARCHAR(200) NOT NULL DEFAULT '',
  `pin` VARCHAR(200) NOT NULL DEFAULT '',
  `txnId` VARCHAR(200) NOT NULL DEFAULT '',
  `planName` VARCHAR(128) NOT NULL DEFAULT '',
  `planId` INT(32) NOT NULL,
  `quantity` VARCHAR(200) NOT NULL DEFAULT '',
  `business_email` VARCHAR(200) NOT NULL DEFAULT '',
  `business_id` VARCHAR(200) NOT NULL DEFAULT '',
  `txn_type` VARCHAR(200) NOT NULL DEFAULT '',
  `txn_id` VARCHAR(200) NOT NULL DEFAULT '',
  `payment_type` VARCHAR(200) NOT NULL DEFAULT '',
  `payment_tax` VARCHAR(200) NOT NULL DEFAULT '',
  `payment_cost` VARCHAR(200) NOT NULL DEFAULT '',
  `payment_fee` VARCHAR(200) NOT NULL DEFAULT '',
  `payment_total` VARCHAR(200) NOT NULL DEFAULT '',
  `payment_currency` VARCHAR(200) NOT NULL DEFAULT '',
  `first_name` VARCHAR(200) NOT NULL DEFAULT '',
  `last_name` VARCHAR(200) NOT NULL DEFAULT '',
  `payer_email` VARCHAR(200) NOT NULL DEFAULT '',
  `payer_address_name` VARCHAR(200) NOT NULL DEFAULT '',
  `payer_address_street` VARCHAR(200) NOT NULL DEFAULT '',
  `payer_address_country` VARCHAR(200) NOT NULL DEFAULT '',
  `payer_address_country_code` VARCHAR(200) NOT NULL DEFAULT '',
  `payer_address_city` VARCHAR(200) NOT NULL DEFAULT '',
  `payer_address_state` VARCHAR(200) NOT NULL DEFAULT '',
  `payer_address_zip` VARCHAR(200) NOT NULL DEFAULT '',
  `payment_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `payment_status` VARCHAR(200) NOT NULL DEFAULT '',
  `pending_reason` VARCHAR(200) NOT NULL DEFAULT '',
  `reason_code` VARCHAR(200) NOT NULL DEFAULT '',
  `receipt_ID` VARCHAR(200) NOT NULL DEFAULT '',
  `payment_address_status` VARCHAR(200) NOT NULL DEFAULT '',
  `vendor_type` VARCHAR(200) NOT NULL DEFAULT '',
  `payer_status` VARCHAR(200) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `billing_paypal`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `billing_paypal` (
  `id` INT(8) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(128) DEFAULT NULL,
  `password` VARCHAR(128) DEFAULT NULL,
  `mac` VARCHAR(200) DEFAULT NULL,
  `pin` VARCHAR(200) DEFAULT NULL,
  `txnId` VARCHAR(200) DEFAULT NULL,
  `planName` VARCHAR(128) DEFAULT NULL,
  `planId` VARCHAR(200) DEFAULT NULL,
  `quantity` VARCHAR(200) DEFAULT NULL,
  `receiver_email` VARCHAR(200) DEFAULT NULL,
  `business` VARCHAR(200) DEFAULT NULL,
  `tax` VARCHAR(200) DEFAULT NULL,
  `mc_gross` VARCHAR(200) DEFAULT NULL,
  `mc_fee` VARCHAR(200) DEFAULT NULL,
  `mc_currency` VARCHAR(200) DEFAULT NULL,
  `first_name` VARCHAR(200) DEFAULT NULL,
  `last_name` VARCHAR(200) DEFAULT NULL,
  `payer_email` VARCHAR(200) DEFAULT NULL,
  `address_name` VARCHAR(200) DEFAULT NULL,
  `address_street` VARCHAR(200) DEFAULT NULL,
  `address_country` VARCHAR(200) DEFAULT NULL,
  `address_country_code` VARCHAR(200) DEFAULT NULL,
  `address_city` VARCHAR(200) DEFAULT NULL,
  `address_state` VARCHAR(200) DEFAULT NULL,
  `address_zip` VARCHAR(200) DEFAULT NULL,
  `payment_date` DATETIME DEFAULT NULL,
  `payment_status` VARCHAR(200) DEFAULT NULL,
  `payment_address_status` VARCHAR(200) DEFAULT NULL,
  `payer_status` VARCHAR(200) DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `billing_plans`
--

DROP TABLE IF EXISTS `billing_plans`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `billing_plans` (
  `id` INT(8) NOT NULL AUTO_INCREMENT,
  `planName` VARCHAR(128) DEFAULT NULL,
  `planId` VARCHAR(128) DEFAULT NULL,
  `planType` VARCHAR(128) DEFAULT NULL,
  `planTimeBank` VARCHAR(128) DEFAULT NULL,
  `planTimeType` VARCHAR(128) DEFAULT NULL,
  `planTimeRefillCost` VARCHAR(128) DEFAULT NULL,
  `planBandwidthUp` VARCHAR(128) DEFAULT NULL,
  `planBandwidthDown` VARCHAR(128) DEFAULT NULL,
  `planTrafficTotal` VARCHAR(128) DEFAULT NULL,
  `planTrafficUp` VARCHAR(128) DEFAULT NULL,
  `planTrafficDown` VARCHAR(128) DEFAULT NULL,
  `planTrafficRefillCost` VARCHAR(128) DEFAULT NULL,
  `planRecurring` VARCHAR(128) DEFAULT NULL,
  `planRecurringPeriod` VARCHAR(128) DEFAULT NULL,
  `planRecurringBillingSchedule` VARCHAR(128) NOT NULL DEFAULT 'Fixed',
  `planCost` VARCHAR(128) DEFAULT NULL,
  `planSetupCost` VARCHAR(128) DEFAULT NULL,
  `planTax` VARCHAR(128) DEFAULT NULL,
  `planCurrency` VARCHAR(128) DEFAULT NULL,
  `planGroup` VARCHAR(128) DEFAULT NULL,
  `planActive` VARCHAR(32) NOT NULL DEFAULT 'yes',
  `creationdate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `creationby` VARCHAR(128) DEFAULT NULL,
  `updatedate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `updateby` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `planName` (`planName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `billing_plans_profiles`
--

DROP TABLE IF EXISTS `billing_plans_profiles`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `billing_plans_profiles` (
  `id` INT(32) NOT NULL AUTO_INCREMENT,
  `plan_name` VARCHAR(128) NOT NULL COMMENT 'the name of the plan',
  `profile_name` VARCHAR(256) DEFAULT NULL COMMENT 'the profile/group name',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `billing_rates`
--

DROP TABLE IF EXISTS `billing_rates`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `billing_rates` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rateName` VARCHAR(128) NOT NULL DEFAULT '',
  `rateType` VARCHAR(128) NOT NULL DEFAULT '',
  `rateCost` INT(32) NOT NULL DEFAULT '0',
  `creationdate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `creationby` VARCHAR(128) DEFAULT NULL,
  `updatedate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `updateby` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `rateName` (`rateName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `hotspots`
--

DROP TABLE IF EXISTS `hotspots`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `hotspots` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) DEFAULT NULL,
  `mac` VARCHAR(200) DEFAULT NULL,
  `geocode` VARCHAR(200) DEFAULT NULL,
  `owner` VARCHAR(200) DEFAULT NULL,
  `email_owner` VARCHAR(200) DEFAULT NULL,
  `manager` VARCHAR(200) DEFAULT NULL,
  `email_manager` VARCHAR(200) DEFAULT NULL,
  `address` VARCHAR(200) DEFAULT NULL,
  `company` VARCHAR(200) DEFAULT NULL,
  `phone1` VARCHAR(200) DEFAULT NULL,
  `phone2` VARCHAR(200) DEFAULT NULL,
  `type` VARCHAR(200) DEFAULT NULL,
  `companywebsite` VARCHAR(200) DEFAULT NULL,
  `companyemail` VARCHAR(200) DEFAULT NULL,
  `companycontact` VARCHAR(200) DEFAULT NULL,
  `companyphone` VARCHAR(200) DEFAULT NULL,
  `creationdate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `creationby` VARCHAR(128) DEFAULT NULL,
  `updatedate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `updateby` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  KEY `mac` (`mac`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `invoice`
--

DROP TABLE IF EXISTS `invoice`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `invoice` (
  `id` INT(32) NOT NULL AUTO_INCREMENT,
  `user_id` INT(32) DEFAULT NULL COMMENT 'user id of the userbillinfo table',
  `batch_id` INT(32) DEFAULT NULL COMMENT 'batch id of the batch_history table',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status_id` INT(10) NOT NULL DEFAULT '1' COMMENT 'the status of the invoice from invoice_status',
  `type_id` INT(10) NOT NULL DEFAULT '1' COMMENT 'the type of the invoice from invoice_type',
  `notes` VARCHAR(128) NOT NULL COMMENT 'general notes/description',
  `creationdate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `creationby` VARCHAR(128) DEFAULT NULL,
  `updatedate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `updateby` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `invoice_items` (
  `id` INT(32) NOT NULL AUTO_INCREMENT,
  `invoice_id` INT(32) NOT NULL COMMENT 'invoice id of the invoices table',
  `plan_id` INT(32) DEFAULT NULL COMMENT 'the plan_id of the billing_plans table',
  `amount` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT 'the amount cost of an item',
  `tax_amount` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT 'the tax amount for an item',
  `total` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT 'the total amount',
  `notes` VARCHAR(128) NOT NULL COMMENT 'general notes/description',
  `creationdate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `creationby` VARCHAR(128) DEFAULT NULL,
  `updatedate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `updateby` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `invoice_status`
--

DROP TABLE IF EXISTS `invoice_status`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `invoice_status` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `value` VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'status value',
  `notes` VARCHAR(128) NOT NULL COMMENT 'general notes/description',
  `creationdate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `creationby` VARCHAR(128) DEFAULT NULL,
  `updatedate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `updateby` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `invoice_status`
--

LOCK TABLES `invoice_status` WRITE;
/*!40000 ALTER TABLE `invoice_status` DISABLE KEYS */;
INSERT INTO `invoice_status` VALUES (1,'open','','2010-05-27 00:00:00','operator','2010-05-27 00:00:00','operator'),
                                    (2,'disputed','','2010-05-27 00:00:00','operator','2010-05-27 00:00:00','operator'),
                                    (3,'draft','','2010-05-27 00:00:00','operator','2010-05-27 00:00:00','operator'),
                                    (4,'sent','','2010-05-27 00:00:00','operator','2010-05-27 00:00:00','operator'),
                                    (5,'paid','','2010-05-27 00:00:00','operator','2010-05-27 00:00:00','operator'),
                                    (6,'partial','','2010-05-27 00:00:00','operator','2010-05-27 00:00:00','operator');
/*!40000 ALTER TABLE `invoice_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_type`
--

DROP TABLE IF EXISTS `invoice_type`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `invoice_type` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `value` VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'type value',
  `notes` VARCHAR(128) NOT NULL COMMENT 'general notes/description',
  `creationdate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `creationby` VARCHAR(128) DEFAULT NULL,
  `updatedate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `updateby` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `invoice_type`
--

LOCK TABLES `invoice_type` WRITE;
/*!40000 ALTER TABLE `invoice_type` DISABLE KEYS */;
INSERT INTO `invoice_type` VALUES (1,'Plans','','2010-05-27 00:00:00','operator','2010-05-27 00:00:00','operator'),
                                  (2,'Services','','2010-05-27 00:00:00','operator','2010-05-27 00:00:00','operator'),
                                  (3,'Consulting','','2010-05-27 00:00:00','operator','2010-05-27 00:00:00','operator');
/*!40000 ALTER TABLE `invoice_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node`
--

DROP TABLE IF EXISTS `node`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `node` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Time of last checkin',
  `netid` INT(11) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(100) NOT NULL,
  `latitude` VARCHAR(20) NOT NULL,
  `longitude` VARCHAR(20) NOT NULL,
  `owner_name` VARCHAR(50) NOT NULL COMMENT 'node owner''s name',
  `owner_email` VARCHAR(50) NOT NULL COMMENT 'node owner''s email address',
  `owner_phone` VARCHAR(25) NOT NULL COMMENT 'node owner''s phone number',
  `owner_address` VARCHAR(100) NOT NULL COMMENT 'node owner''s address',
  `approval_status` VARCHAR(1) NOT NULL COMMENT 'approval status: A (accepted), R (rejected), P (pending)',
  `ip` VARCHAR(20) NOT NULL COMMENT 'ROBIN',
  `mac` VARCHAR(20) NOT NULL COMMENT 'ROBIN',
  `uptime` VARCHAR(100) NOT NULL COMMENT 'ROBIN',
  `robin` VARCHAR(20) NOT NULL COMMENT 'ROBIN: robin version',
  `batman` VARCHAR(20) NOT NULL COMMENT 'ROBIN: batman version',
  `memfree` VARCHAR(20) NOT NULL COMMENT 'ROBIN',
  `nbs` MEDIUMTEXT NOT NULL COMMENT 'ROBIN: neighbor list',
  `gateway` VARCHAR(20) NOT NULL COMMENT 'ROBIN: nearest gateway',
  `gw-qual` VARCHAR(20) NOT NULL COMMENT 'ROBIN: quality of nearest gateway',
  `routes` MEDIUMTEXT NOT NULL COMMENT 'ROBIN: route to nearest gateway',
  `users` CHAR(3) NOT NULL COMMENT 'ROBIN: current number of users',
  `kbdown` VARCHAR(20) NOT NULL COMMENT 'ROBIN: downloaded kb',
  `kbup` VARCHAR(20) NOT NULL COMMENT 'ROBIN: uploaded kb',
  `hops` VARCHAR(3) NOT NULL COMMENT 'ROBIN: hops to gateway',
  `rank` VARCHAR(3) NOT NULL COMMENT 'ROBIN: ???, not currently used for anything',
  `ssid` VARCHAR(20) NOT NULL COMMENT 'ROBIN: ssid, not currently used for anything',
  `pssid` VARCHAR(20) NOT NULL COMMENT 'ROBIN: pssid, not currently used for anything',
  `gateway_bit` TINYINT(1) NOT NULL COMMENT 'ROBIN derivation: is this node a gateway?',
  `memlow` VARCHAR(20) NOT NULL COMMENT 'ROBIN derivation: lowest reported memory on the node',
  `usershi` CHAR(3) NOT NULL COMMENT 'ROBIN derivation: highest number of users',
  `cpu` FLOAT NOT NULL DEFAULT '0',
  `wan_iface` VARCHAR(128) DEFAULT NULL,
  `wan_ip` VARCHAR(128) DEFAULT NULL,
  `wan_mac` VARCHAR(128) DEFAULT NULL,
  `wan_gateway` VARCHAR(128) DEFAULT NULL,
  `wifi_iface` VARCHAR(128) DEFAULT NULL,
  `wifi_ip` VARCHAR(128) DEFAULT NULL,
  `wifi_mac` VARCHAR(128) DEFAULT NULL,
  `wifi_ssid` VARCHAR(128) DEFAULT NULL,
  `wifi_key` VARCHAR(128) DEFAULT NULL,
  `wifi_channel` VARCHAR(128) DEFAULT NULL,
  `lan_iface` VARCHAR(128) DEFAULT NULL,
  `lan_mac` VARCHAR(128) DEFAULT NULL,
  `lan_ip` VARCHAR(128) DEFAULT NULL,
  `wan_bup` VARCHAR(128) DEFAULT NULL,
  `wan_bdown` VARCHAR(128) DEFAULT NULL,
  `firmware` VARCHAR(128) DEFAULT NULL,
  `firmware_revision` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `mac` (`mac`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='node database';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `operators_acl_files`
--

DROP TABLE IF EXISTS `operators_acl_files`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `operators_acl_files` (
  `id` INT(32) NOT NULL AUTO_INCREMENT,
  `file` VARCHAR(128) NOT NULL,
  `category` VARCHAR(128) NOT NULL,
  `section` VARCHAR(128) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `operators_acl_files`
--

LOCK TABLES `operators_acl_files` WRITE;
/*!40000 ALTER TABLE `operators_acl_files` DISABLE KEYS */;
INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
('acct_active', 'Accounting', 'General'),
('acct_all', 'Accounting', 'General'),
('acct_custom_query', 'Accounting', 'Custom'),
('acct_date', 'Accounting', 'General'),
('acct_hotspot_accounting', 'Accounting', 'Hotspot'),
('acct_hotspot_compare', 'Accounting', 'Hotspot'),
('acct_ipaddress', 'Accounting', 'General'),
('acct_maintenance_cleanup', 'Accounting', 'Maintenance'),
('acct_maintenance_delete', 'Accounting', 'Maintenance'),
('acct_nasipaddress', 'Accounting', 'General'),
('acct_plans_usage', 'Accounting', 'Plans'),
('acct_username', 'Accounting', 'General');

INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
('bill_history_query', 'Billing', 'History'),
('bill_invoice_del', 'Billing', 'Invoice'),
('bill_invoice_edit', 'Billing', 'Invoice'),
('bill_invoice_list', 'Billing', 'Invoice'),
('bill_invoice_new', 'Billing', 'Invoice'),
('bill_invoice_report', 'Billing', 'Invoice'),
('bill_merchant_transactions', 'Billing', 'Merchant'),
('bill_payments_del', 'Billing', 'Payments'),
('bill_payments_edit', 'Billing', 'Payments'),
('bill_payments_list', 'Billing', 'Payments'),
('bill_payments_new', 'Billing', 'Payments'),
('bill_payment_types_del', 'Billing', 'Payment Types'),
('bill_payment_types_edit', 'Billing', 'Payment Types'),
('bill_payment_types_list', 'Billing', 'Payment Types'),
('bill_payment_types_new', 'Billing', 'Payment Types'),
('bill_plans_del', 'Billing', 'Plans'),
('bill_plans_edit', 'Billing', 'Plans'),
('bill_plans_list', 'Billing', 'Plans'),
('bill_plans_new', 'Billing', 'Plans'),
('bill_pos_del', 'Billing', 'POS'),
('bill_pos_edit', 'Billing', 'POS'),
('bill_pos_list', 'Billing', 'POS'),
('bill_pos_new', 'Billing', 'POS'),
('bill_rates_date', 'Billing', 'Rates'),
('bill_rates_del', 'Billing', 'Rates'),
('bill_rates_edit', 'Billing', 'Rates'),
('bill_rates_list', 'Billing', 'Rates'),
('bill_rates_new', 'Billing', 'Rates');

INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
('config_backup_createbackups', 'Configuration', 'Backup'),
('config_backup_managebackups', 'Configuration', 'Backup'),
('config_crontab', 'Configuration', 'Maintenance'),
('config_db', 'Configuration', 'Core'),
('config_interface', 'Configuration', 'Core'),
('config_lang', 'Configuration', 'Core'),
('config_logging', 'Configuration', 'Core'),
('config_mail_settings', 'Configuration', 'Mail'),
('config_mail_testing', 'Configuration', 'Mail'),
('config_maint_disconnect_user', 'Configuration', 'Maintenance'),
('config_maint_test_user', 'Configuration', 'Maintenance'),
('config_messages', 'Configuration', 'Core'),
('config_operator_2fa', 'Configuration', 'Operators'),
('config_operators_del', 'Configuration', 'Operators'),
('config_operators_edit', 'Configuration', 'Operators'),
('config_operators_list', 'Configuration', 'Operators'),
('config_operators_new', 'Configuration', 'Operators'),
('config_reports_dashboard', 'Configuration', 'Reporting'),
('config_user', 'Configuration', 'Core');

INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
('gis_editmap', 'GIS', 'General'),
('gis_viewmap', 'GIS', 'General'),
('graphs_alltime_logins', 'Graphs', 'General'),
('graphs_alltime_traffic_compare', 'Graphs', 'General'),
('graphs_logged_users', 'Graphs', 'General'),
('graphs_overall_download', 'Graphs', 'General'),
('graphs_overall_logins', 'Graphs', 'General'),
('graphs_overall_upload', 'Graphs', 'General');

INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
('mng_batch_add', 'Management', 'Batch'),
('mng_batch_del', 'Management', 'Batch'),
('mng_batch_list', 'Management', 'Batch'),
('mng_del', 'Management', 'Users'),
('mng_edit', 'Management', 'Users'),
('mng_hs_del', 'Management', 'Hotspot'),
('mng_hs_edit', 'Management', 'Hotspot'),
('mng_hs_list', 'Management', 'Hotspot'),
('mng_hs_new', 'Management', 'Hotspot'),
('mng_import_users', 'Management', 'Users'),
('mng_list_all', 'Management', 'Users'),
('mng_new', 'Management', 'Users'),
('mng_new_quick', 'Management', 'Users'),
('mng_rad_attributes_del', 'Management', 'Attributes'),
('mng_rad_attributes_edit', 'Management', 'Attributes'),
('mng_rad_attributes_import', 'Management', 'Attributes'),
('mng_rad_attributes_list', 'Management', 'Attributes'),
('mng_rad_attributes_new', 'Management', 'Attributes'),
('mng_rad_attributes_search', 'Management', 'Attributes'),
('mng_rad_groupcheck_del', 'Management', 'Groups'),
('mng_rad_groupcheck_edit', 'Management', 'Groups'),
('mng_rad_groupcheck_list', 'Management', 'Groups'),
('mng_rad_groupcheck_new', 'Management', 'Groups'),
('mng_rad_groupcheck_search', 'Management', 'Groups'),
('mng_rad_groupreply_del', 'Management', 'Groups'),
('mng_rad_groupreply_edit', 'Management', 'Groups'),
('mng_rad_groupreply_list', 'Management', 'Groups'),
('mng_rad_groupreply_new', 'Management', 'Groups'),
('mng_rad_groupreply_search', 'Management', 'Groups'),
('mng_rad_hunt_del', 'Management', 'HuntGroups'),
('mng_rad_hunt_edit', 'Management', 'HuntGroups'),
('mng_rad_hunt_list', 'Management', 'HuntGroups'),
('mng_rad_hunt_new', 'Management', 'HuntGroups'),
('mng_rad_ippool_del', 'Management', 'IPPool'),
('mng_rad_ippool_edit', 'Management', 'IPPool'),
('mng_rad_ippool_list', 'Management', 'IPPool'),
('mng_rad_ippool_new', 'Management', 'IPPool'),
('mng_rad_nas_del', 'Management', 'NAS'),
('mng_rad_nas_edit', 'Management', 'NAS'),
('mng_rad_nas_list', 'Management', 'NAS'),
('mng_rad_nas_new', 'Management', 'NAS'),
('mng_rad_profiles_del', 'Management', 'Profiles'),
('mng_rad_profiles_duplicate', 'Management', 'Profiles'),
('mng_rad_profiles_edit', 'Management', 'Profiles'),
('mng_rad_profiles_list', 'Management', 'Profiles'),
('mng_rad_profiles_new', 'Management', 'Profiles'),
('mng_rad_proxys_del', 'Management', 'Proxys'),
('mng_rad_proxys_edit', 'Management', 'Proxys'),
('mng_rad_proxys_list', 'Management', 'Proxys'),
('mng_rad_proxys_new', 'Management', 'Proxys'),
('mng_rad_realms_del', 'Management', 'Realms'),
('mng_rad_realms_edit', 'Management', 'Realms'),
('mng_rad_realms_list', 'Management', 'Realms'),
('mng_rad_realms_new', 'Management', 'Realms'),
('mng_rad_usergroup_del', 'Management', 'UserGroup'),
('mng_rad_usergroup_edit', 'Management', 'UserGroup'),
('mng_rad_usergroup_list', 'Management', 'UserGroup'),
('mng_rad_usergroup_list_user', 'Management', 'UserGroup'),
('mng_rad_usergroup_new', 'Management', 'UserGroup'),
('mng_search', 'Management', 'Users');

INSERT INTO `operators_acl_files` (`file`, `category`, `section`) VALUES
('rep_batch_details', 'Reporting', 'Batch'),
('rep_batch_list', 'Reporting', 'Batch'),
('rep_hb_dashboard', 'Reporting', 'HeartBeat'),
('rep_history', 'Reporting', 'Core'),
('rep_lastconnect', 'Reporting', 'Core'),
('rep_logs_boot', 'Reporting', 'Logs'),
('rep_logs_daloradius', 'Reporting', 'Logs'),
('rep_logs_radius', 'Reporting', 'Logs'),
('rep_logs_system', 'Reporting', 'Logs'),
('rep_newusers', 'Reporting', 'Core'),
('rep_online', 'Reporting', 'Core'),
('rep_stat_raid', 'Reporting', 'Status'),
('rep_stat_server', 'Reporting', 'Status'),
('rep_stat_services', 'Reporting', 'Status'),
('rep_stat_ups', 'Reporting', 'Status'),
('rep_topusers', 'Reporting', 'Core'),
('rep_username', 'Reporting', 'Core');

/*!40000 ALTER TABLE `operators_acl_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operators`
--

DROP TABLE IF EXISTS `operators`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `operators` (
  `id` INT(32) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(32) NOT NULL,
  `password` VARCHAR(95) NOT NULL,
  `firstname` VARCHAR(32) NOT NULL,
  `lastname` VARCHAR(32) NOT NULL,
  `title` VARCHAR(32) NOT NULL,
  `department` VARCHAR(32) NOT NULL,
  `company` VARCHAR(32) NOT NULL,
  `phone1` VARCHAR(32) NOT NULL,
  `phone2` VARCHAR(32) NOT NULL,
  `email1` VARCHAR(32) NOT NULL,
  `email2` VARCHAR(32) NOT NULL,
  `messenger1` VARCHAR(32) NOT NULL,
  `messenger2` VARCHAR(32) NOT NULL,
  `notes` VARCHAR(128) NOT NULL,
  `lastlogin` DATETIME DEFAULT '0000-00-00 00:00:00',
  `creationdate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `creationby` VARCHAR(128) DEFAULT NULL,
  `updatedate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `updateby` VARCHAR(128) DEFAULT NULL,
  `totp_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `totp_secret` VARCHAR(64) DEFAULT NULL,
  `totp_last_counter` BIGINT DEFAULT NULL,
  `totp_confirmed_at` DATETIME DEFAULT NULL,
  `totp_recovery_codes` TEXT DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `operators`
--

LOCK TABLES `operators` WRITE;
/*!40000 ALTER TABLE `operators` DISABLE KEYS */;
INSERT INTO `operators` (
    `id`,
    `username`,
    `password`,
    `firstname`,
    `lastname`,
    `title`,
    `department`,
    `company`,
    `phone1`,
    `phone2`,
    `email1`,
    `email2`,
    `messenger1`,
    `messenger2`,
    `notes`,
    `creationdate`
) VALUES (1,'administrator','radius','','','','','','','','','','','','',CURRENT_TIMESTAMP);
/*!40000 ALTER TABLE `operators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operators_acl`
--

DROP TABLE IF EXISTS `operators_acl`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `operators_acl` (
  `id` INT(32) NOT NULL AUTO_INCREMENT,
  `operator_id` INT(32) NOT NULL,
  `file` VARCHAR(128) NOT NULL,
  `access` TINYINT(8) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `operators_acl`
--

INSERT INTO `operators_acl` (`operator_id`, `file`, `access`)
SELECT 1, `file`, 1 FROM `operators_acl_files`;

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `payment` (
  `id` INT(32) NOT NULL AUTO_INCREMENT,
  `invoice_id` INT(32) NOT NULL COMMENT 'invoice id of the invoices table',
  `amount` DECIMAL(10,2) NOT NULL COMMENT 'the amount paid',
  `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `type_id` INT(10) NOT NULL DEFAULT '1' COMMENT 'the type of the payment from payment_type',
  `notes` VARCHAR(128) NOT NULL COMMENT 'general notes/description',
  `creationdate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `creationby` VARCHAR(128) DEFAULT NULL,
  `updatedate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `updateby` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `payment_type`
--

DROP TABLE IF EXISTS `payment_type`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `payment_type` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `value` VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'type value',
  `notes` VARCHAR(128) NOT NULL COMMENT 'general notes/description',
  `creationdate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `creationby` VARCHAR(128) DEFAULT NULL,
  `updatedate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `updateby` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `payment_type`
--

LOCK TABLES `payment_type` WRITE;
/*!40000 ALTER TABLE `payment_type` DISABLE KEYS */;
INSERT INTO `payment_type` VALUES (1,'Cash','','2010-05-27 00:00:00','operator','2010-05-27 00:00:00','operator'),(2,'Check','','2010-05-27 00:00:00','operator','2010-05-27 00:00:00','operator'),(3,'Bank Transfer','','2010-05-27 00:00:00','operator','2010-05-27 00:00:00','operator');
/*!40000 ALTER TABLE `payment_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proxys`
--

DROP TABLE IF EXISTS `proxys`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `proxys` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `proxyname` VARCHAR(128) DEFAULT NULL,
  `retry_delay` INT(8) DEFAULT NULL,
  `retry_count` INT(8) DEFAULT NULL,
  `dead_time` INT(8) DEFAULT NULL,
  `default_fallback` INT(8) DEFAULT NULL,
  `creationdate` DATETIME DEFAULT NULL,
  `creationby` VARCHAR(128) DEFAULT NULL,
  `updatedate` DATETIME DEFAULT NULL,
  `updateby` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `realms`
--

DROP TABLE IF EXISTS `realms`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `realms` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `realmname` VARCHAR(128) DEFAULT NULL,
  `type` VARCHAR(32) DEFAULT NULL,
  `authhost` VARCHAR(256) DEFAULT NULL,
  `accthost` VARCHAR(256) DEFAULT NULL,
  `secret` VARCHAR(128) DEFAULT NULL,
  `ldflag` VARCHAR(64) DEFAULT NULL,
  `nostrip` INT(8) DEFAULT NULL,
  `hints` INT(8) DEFAULT NULL,
  `notrealm` INT(8) DEFAULT NULL,
  `creationdate` DATETIME DEFAULT NULL,
  `creationby` VARCHAR(128) DEFAULT NULL,
  `updatedate` DATETIME DEFAULT NULL,
  `updateby` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `userbillinfo`
--

DROP TABLE IF EXISTS `userbillinfo`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `userbillinfo` (
  `id` INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(128) DEFAULT NULL,
  `planName` VARCHAR(128) DEFAULT NULL,
  `hotspot_id` INT(32) DEFAULT NULL,
  `hotspotlocation` VARCHAR(32) DEFAULT NULL,
  `contactperson` VARCHAR(200) DEFAULT NULL,
  `company` VARCHAR(200) DEFAULT NULL,
  `email` VARCHAR(200) DEFAULT NULL,
  `phone` VARCHAR(200) DEFAULT NULL,
  `address` VARCHAR(200) DEFAULT NULL,
  `city` VARCHAR(200) DEFAULT NULL,
  `state` VARCHAR(200) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `zip` VARCHAR(200) DEFAULT NULL,
  `paymentmethod` VARCHAR(200) DEFAULT NULL,
  `cash` VARCHAR(200) DEFAULT NULL,
  `creditcardname` VARCHAR(200) DEFAULT NULL,
  `creditcardnumber` VARCHAR(200) DEFAULT NULL,
  `creditcardverification` VARCHAR(200) DEFAULT NULL,
  `creditcardtype` VARCHAR(200) DEFAULT NULL,
  `creditcardexp` VARCHAR(200) DEFAULT NULL,
  `notes` VARCHAR(200) DEFAULT NULL,
  `changeuserbillinfo` VARCHAR(128) DEFAULT NULL,
  `lead` VARCHAR(200) DEFAULT NULL,
  `coupon` VARCHAR(200) DEFAULT NULL,
  `ordertaker` VARCHAR(200) DEFAULT NULL,
  `billstatus` VARCHAR(200) DEFAULT NULL,
  `lastbill` DATE NOT NULL DEFAULT '0000-00-00',
  `nextbill` DATE NOT NULL DEFAULT '0000-00-00',
  `nextinvoicedue` INT(32) DEFAULT NULL,
  `billdue` INT(32) DEFAULT NULL,
  `postalinvoice` VARCHAR(8) DEFAULT NULL,
  `faxinvoice` VARCHAR(8) DEFAULT NULL,
  `emailinvoice` VARCHAR(8) DEFAULT NULL,
  `batch_id` INT(32) DEFAULT NULL,
  `creationdate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `creationby` VARCHAR(128) DEFAULT NULL,
  `updatedate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `updateby` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `username` (`username`),
  KEY `planname` (`planName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `userinfo`
--

DROP TABLE IF EXISTS `userinfo`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE `userinfo` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(128) DEFAULT NULL,
  `firstname` VARCHAR(200) DEFAULT NULL,
  `lastname` VARCHAR(200) DEFAULT NULL,
  `email` VARCHAR(200) DEFAULT NULL,
  `department` VARCHAR(200) DEFAULT NULL,
  `company` VARCHAR(200) DEFAULT NULL,
  `workphone` VARCHAR(200) DEFAULT NULL,
  `homephone` VARCHAR(200) DEFAULT NULL,
  `mobilephone` VARCHAR(200) DEFAULT NULL,
  `address` VARCHAR(200) DEFAULT NULL,
  `city` VARCHAR(200) DEFAULT NULL,
  `state` VARCHAR(200) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `zip` VARCHAR(200) DEFAULT NULL,
  `notes` VARCHAR(200) DEFAULT NULL,
  `changeuserinfo` VARCHAR(128) DEFAULT NULL,
  `portalloginpassword` VARCHAR(128) DEFAULT '',
  `enableportallogin` INT(32) DEFAULT '0',
  `creationdate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `creationby` VARCHAR(128) DEFAULT NULL,
  `updatedate` DATETIME DEFAULT '0000-00-00 00:00:00',
  `updateby` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

-- Adding new custom daloRADIUS groups
INSERT IGNORE INTO `radgroupcheck` (`groupname`,`attribute`,`op`,`value`)
                            VALUES ('daloRADIUS-Disabled-Users','Auth-Type',':=','Reject');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `type` ENUM('login', 'support', 'dashboard') NOT NULL,
  `content` LONGTEXT NOT NULL,
  `created_on` DATETIME NULL,
  `created_by` VARCHAR(32) NULL,
  `modified_on` DATETIME NULL,
  `modified_by` VARCHAR(32) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARSET=utf8mb4;
SET character_set_client = @saved_cs_client;

--
-- Default data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (1, 'login', '<p>Dear User,<br>Welcome to the Users Portal. We are glad you joined us!</p><p>By logging in with your account username and password, you will be able to access a wide range of features. For example, you can easily edit your contact settings, update your personal information, and view some history data through visual graphs.</p><p>We take your privacy and security seriously, so please rest assured that all your data is stored securely in our database and is accessible only to you and our authorized staff.</p><p>If you need any assistance or have any questions, please do not hesitate to contact our support team. We are always happy to help!</p><p>Regards,<br/>The daloRADIUS Staff.</p>', NOW(), 'administrator', NULL, NULL);
INSERT INTO `messages` VALUES (2, 'support', '<p>Dear User,<br>We can provide support in different ways: you can email us at <strong>support@daloradius.local</strong> or you can open a new ticket through our help desk: <strong>https://helpdesk.daloradius.local</strong>.</p><p>Thank you for choosing daloRADIUS.</p><p>Best regards,<br>The daloRADIUS Support Team</p>', NOW(), 'administrator', NULL, NULL);
INSERT INTO `messages` VALUES (3, 'dashboard', '<p>Dear User,<br>We can provide support in different ways: you can email us at <strong>support@daloradius.local</strong> or you can open a new ticket through our help desk: <strong>https://helpdesk.daloradius.local</strong>.</p><p>Thank you for choosing daloRADIUS.</p><p>Best regards,<br>The daloRADIUS Support Team</p>', NOW(), 'administrator', NULL, NULL);
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

-- ==========================================
-- daloRADIUS Performance Indexes
-- ==========================================
CREATE INDEX idx_radacct_username_time ON radacct (username, acctstarttime);
CREATE INDEX idx_radpostauth_authdate ON radpostauth (authdate);
CREATE INDEX idx_radacct_status_start ON radacct (acctstoptime, acctstarttime);
CREATE INDEX idx_radacct_top_users ON radacct (acctstarttime, username, acctsessiontime, acctinputoctets, acctoutputoctets);
CREATE INDEX idx_radcheck_username_attr ON radcheck (username, attribute);
