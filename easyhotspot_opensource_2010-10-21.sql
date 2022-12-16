# Sequel Pro dump
# Version 2210
# http://code.google.com/p/sequel-pro
#
# Host: 127.0.01 (MySQL 5.1.38)
# Database: easyhotspot_opensource
# Generation Time: 2010-10-21 13:27:44 +0700
# ************************************************************

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table billingplan
# ------------------------------------------------------------

DROP TABLE IF EXISTS `billingplan`;

CREATE TABLE `billingplan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `amount` int(255) NOT NULL,
  `valid_for` int(11) DEFAULT NULL,
  `price` float NOT NULL,
  `IdleTimeout` int(255) NOT NULL,
  `simultaneous` int(10) NOT NULL,
  `redirect_url` varchar(255) NOT NULL,
  `bw_upload` int(255) NOT NULL,
  `bw_download` int(255) NOT NULL,
  `created_by` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

LOCK TABLES `billingplan` WRITE;
/*!40000 ALTER TABLE `billingplan` DISABLE KEYS */;
INSERT INTO `billingplan` (`id`,`name`,`type`,`amount`,`valid_for`,`price`,`IdleTimeout`,`simultaneous`,`redirect_url`,`bw_upload`,`bw_download`,`created_by`)
VALUES
	(8,'1 hour','time',60,30,1000,10,0,'',1024000,1024000,'admin'),
	(9,'1 Mega','packet',1,1,1,10,0,'',0,0,'admin');

/*!40000 ALTER TABLE `billingplan` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table ci_sessions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ci_sessions`;

CREATE TABLE `ci_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(16) NOT NULL DEFAULT '0',
  `user_agent` varchar(50) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `session_data` text,
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ci_sessions` WRITE;
/*!40000 ALTER TABLE `ci_sessions` DISABLE KEYS */;
INSERT INTO `ci_sessions` (`session_id`,`ip_address`,`user_agent`,`last_activity`,`session_data`)
VALUES
	('162f4d4302346c671f3d421ce4098e96','127.0.0.1','Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; ',1287642388,'a:8:{s:10:\"country_id\";s:1:\"0\";s:5:\"email\";s:16:\"cashier@mail.com\";s:10:\"last_visit\";s:19:\"2010-10-21 05:56:43\";s:7:\"created\";s:19:\"2010-10-21 12:56:43\";s:8:\"modified\";s:19:\"0000-00-00 00:00:00\";s:2:\"id\";s:2:\"11\";s:9:\"user_name\";s:7:\"cashier\";s:4:\"role\";s:4:\"user\";}'),
	('8bedcfd9bb415d6a44cc5eb11ec54ce1','127.0.0.1','Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; ',1287642373,'a:9:{s:2:\"id\";s:1:\"1\";s:9:\"user_name\";s:5:\"admin\";s:10:\"country_id\";s:1:\"0\";s:5:\"email\";s:18:\"root@localhost.com\";s:4:\"role\";s:10:\"superadmin\";s:10:\"last_visit\";s:19:\"2010-10-21 11:14:34\";s:7:\"created\";s:19:\"2010-10-21 11:14:34\";s:8:\"modified\";s:19:\"0000-00-00 00:00:00\";s:22:\"flash:old:flashMessage\";s:27:\"1 user edited successfully!\";}');

/*!40000 ALTER TABLE `ci_sessions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table expiration_account
# ------------------------------------------------------------

DROP TABLE IF EXISTS `expiration_account`;

CREATE TABLE `expiration_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `expiration_plan` varchar(255) NOT NULL,
  `created_by` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table expirationplan
# ------------------------------------------------------------

DROP TABLE IF EXISTS `expirationplan`;

CREATE TABLE `expirationplan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `price` float NOT NULL DEFAULT '0',
  `amount` varchar(255) NOT NULL,
  `bw_download` int(10) DEFAULT NULL,
  `bw_upload` int(10) DEFAULT NULL,
  `idletimeout` int(10) DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;



# Dump of table fa_country
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fa_country`;

CREATE TABLE `fa_country` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `iso` char(2) NOT NULL,
  `name` varchar(80) NOT NULL,
  `iso3` char(3) DEFAULT NULL,
  `numcode` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=240 DEFAULT CHARSET=latin1;

LOCK TABLES `fa_country` WRITE;
/*!40000 ALTER TABLE `fa_country` DISABLE KEYS */;
INSERT INTO `fa_country` (`id`,`iso`,`name`,`iso3`,`numcode`)
VALUES
	(1,'AF','Afghanistan','AFG',4),
	(2,'AL','Albania','ALB',8),
	(3,'DZ','Algeria','DZA',12),
	(4,'AS','American Samoa','ASM',16),
	(5,'AD','Andorra','AND',20),
	(6,'AO','Angola','AGO',24),
	(7,'AI','Anguilla','AIA',660),
	(8,'AQ','Antarctica',NULL,NULL),
	(9,'AG','Antigua and Barbuda','ATG',28),
	(10,'AR','Argentina','ARG',32),
	(11,'AM','Armenia','ARM',51),
	(12,'AW','Aruba','ABW',533),
	(13,'AU','Australia','AUS',36),
	(14,'AT','Austria','AUT',40),
	(15,'AZ','Azerbaijan','AZE',31),
	(16,'BS','Bahamas','BHS',44),
	(17,'BH','Bahrain','BHR',48),
	(18,'BD','Bangladesh','BGD',50),
	(19,'BB','Barbados','BRB',52),
	(20,'BY','Belarus','BLR',112),
	(21,'BE','Belgium','BEL',56),
	(22,'BZ','Belize','BLZ',84),
	(23,'BJ','Benin','BEN',204),
	(24,'BM','Bermuda','BMU',60),
	(25,'BT','Bhutan','BTN',64),
	(26,'BO','Bolivia','BOL',68),
	(27,'BA','Bosnia and Herzegovina','BIH',70),
	(28,'BW','Botswana','BWA',72),
	(29,'BV','Bouvet Island',NULL,NULL),
	(30,'BR','Brazil','BRA',76),
	(31,'IO','British Indian Ocean Territory',NULL,NULL),
	(32,'BN','Brunei Darussalam','BRN',96),
	(33,'BG','Bulgaria','BGR',100),
	(34,'BF','Burkina Faso','BFA',854),
	(35,'BI','Burundi','BDI',108),
	(36,'KH','Cambodia','KHM',116),
	(37,'CM','Cameroon','CMR',120),
	(38,'CA','Canada','CAN',124),
	(39,'CV','Cape Verde','CPV',132),
	(40,'KY','Cayman Islands','CYM',136),
	(41,'CF','Central African Republic','CAF',140),
	(42,'TD','Chad','TCD',148),
	(43,'CL','Chile','CHL',152),
	(44,'CN','China','CHN',156),
	(45,'CX','Christmas Island',NULL,NULL),
	(46,'CC','Cocos (Keeling) Islands',NULL,NULL),
	(47,'CO','Colombia','COL',170),
	(48,'KM','Comoros','COM',174),
	(49,'CG','Congo','COG',178),
	(50,'CD','Congo, the Democratic Republic of the','COD',180),
	(51,'CK','Cook Islands','COK',184),
	(52,'CR','Costa Rica','CRI',188),
	(53,'CI','Cote D\'Ivoire','CIV',384),
	(54,'HR','Croatia','HRV',191),
	(55,'CU','Cuba','CUB',192),
	(56,'CY','Cyprus','CYP',196),
	(57,'CZ','Czech Republic','CZE',203),
	(58,'DK','Denmark','DNK',208),
	(59,'DJ','Djibouti','DJI',262),
	(60,'DM','Dominica','DMA',212),
	(61,'DO','Dominican Republic','DOM',214),
	(62,'EC','Ecuador','ECU',218),
	(63,'EG','Egypt','EGY',818),
	(64,'SV','El Salvador','SLV',222),
	(65,'GQ','Equatorial Guinea','GNQ',226),
	(66,'ER','Eritrea','ERI',232),
	(67,'EE','Estonia','EST',233),
	(68,'ET','Ethiopia','ETH',231),
	(69,'FK','Falkland Islands (Malvinas)','FLK',238),
	(70,'FO','Faroe Islands','FRO',234),
	(71,'FJ','Fiji','FJI',242),
	(72,'FI','Finland','FIN',246),
	(73,'FR','France','FRA',250),
	(74,'GF','French Guiana','GUF',254),
	(75,'PF','French Polynesia','PYF',258),
	(76,'TF','French Southern Territories',NULL,NULL),
	(77,'GA','Gabon','GAB',266),
	(78,'GM','Gambia','GMB',270),
	(79,'GE','Georgia','GEO',268),
	(80,'DE','Germany','DEU',276),
	(81,'GH','Ghana','GHA',288),
	(82,'GI','Gibraltar','GIB',292),
	(83,'GR','Greece','GRC',300),
	(84,'GL','Greenland','GRL',304),
	(85,'GD','Grenada','GRD',308),
	(86,'GP','Guadeloupe','GLP',312),
	(87,'GU','Guam','GUM',316),
	(88,'GT','Guatemala','GTM',320),
	(89,'GN','Guinea','GIN',324),
	(90,'GW','Guinea-Bissau','GNB',624),
	(91,'GY','Guyana','GUY',328),
	(92,'HT','Haiti','HTI',332),
	(93,'HM','Heard Island and Mcdonald Islands',NULL,NULL),
	(94,'VA','Holy See (Vatican City State)','VAT',336),
	(95,'HN','Honduras','HND',340),
	(96,'HK','Hong Kong','HKG',344),
	(97,'HU','Hungary','HUN',348),
	(98,'IS','Iceland','ISL',352),
	(99,'IN','India','IND',356),
	(100,'ID','Indonesia','IDN',360),
	(101,'IR','Iran, Islamic Republic of','IRN',364),
	(102,'IQ','Iraq','IRQ',368),
	(103,'IE','Ireland','IRL',372),
	(104,'IL','Israel','ISR',376),
	(105,'IT','Italy','ITA',380),
	(106,'JM','Jamaica','JAM',388),
	(107,'JP','Japan','JPN',392),
	(108,'JO','Jordan','JOR',400),
	(109,'KZ','Kazakhstan','KAZ',398),
	(110,'KE','Kenya','KEN',404),
	(111,'KI','Kiribati','KIR',296),
	(112,'KP','Korea, Democratic People\'s Republic of','PRK',408),
	(113,'KR','Korea, Republic of','KOR',410),
	(114,'KW','Kuwait','KWT',414),
	(115,'KG','Kyrgyzstan','KGZ',417),
	(116,'LA','Lao People\'s Democratic Republic','LAO',418),
	(117,'LV','Latvia','LVA',428),
	(118,'LB','Lebanon','LBN',422),
	(119,'LS','Lesotho','LSO',426),
	(120,'LR','Liberia','LBR',430),
	(121,'LY','Libyan Arab Jamahiriya','LBY',434),
	(122,'LI','Liechtenstein','LIE',438),
	(123,'LT','Lithuania','LTU',440),
	(124,'LU','Luxembourg','LUX',442),
	(125,'MO','Macao','MAC',446),
	(126,'MK','Macedonia, the Former Yugoslav Republic of','MKD',807),
	(127,'MG','Madagascar','MDG',450),
	(128,'MW','Malawi','MWI',454),
	(129,'MY','Malaysia','MYS',458),
	(130,'MV','Maldives','MDV',462),
	(131,'ML','Mali','MLI',466),
	(132,'MT','Malta','MLT',470),
	(133,'MH','Marshall Islands','MHL',584),
	(134,'MQ','Martinique','MTQ',474),
	(135,'MR','Mauritania','MRT',478),
	(136,'MU','Mauritius','MUS',480),
	(137,'YT','Mayotte',NULL,NULL),
	(138,'MX','Mexico','MEX',484),
	(139,'FM','Micronesia, Federated States of','FSM',583),
	(140,'MD','Moldova, Republic of','MDA',498),
	(141,'MC','Monaco','MCO',492),
	(142,'MN','Mongolia','MNG',496),
	(143,'MS','Montserrat','MSR',500),
	(144,'MA','Morocco','MAR',504),
	(145,'MZ','Mozambique','MOZ',508),
	(146,'MM','Myanmar','MMR',104),
	(147,'NA','Namibia','NAM',516),
	(148,'NR','Nauru','NRU',520),
	(149,'NP','Nepal','NPL',524),
	(150,'NL','Netherlands','NLD',528),
	(151,'AN','Netherlands Antilles','ANT',530),
	(152,'NC','New Caledonia','NCL',540),
	(153,'NZ','New Zealand','NZL',554),
	(154,'NI','Nicaragua','NIC',558),
	(155,'NE','Niger','NER',562),
	(156,'NG','Nigeria','NGA',566),
	(157,'NU','Niue','NIU',570),
	(158,'NF','Norfolk Island','NFK',574),
	(159,'MP','Northern Mariana Islands','MNP',580),
	(160,'NO','Norway','NOR',578),
	(161,'OM','Oman','OMN',512),
	(162,'PK','Pakistan','PAK',586),
	(163,'PW','Palau','PLW',585),
	(164,'PS','Palestinian Territory, Occupied',NULL,NULL),
	(165,'PA','Panama','PAN',591),
	(166,'PG','Papua New Guinea','PNG',598),
	(167,'PY','Paraguay','PRY',600),
	(168,'PE','Peru','PER',604),
	(169,'PH','Philippines','PHL',608),
	(170,'PN','Pitcairn','PCN',612),
	(171,'PL','Poland','POL',616),
	(172,'PT','Portugal','PRT',620),
	(173,'PR','Puerto Rico','PRI',630),
	(174,'QA','Qatar','QAT',634),
	(175,'RE','Reunion','REU',638),
	(176,'RO','Romania','ROM',642),
	(177,'RU','Russian Federation','RUS',643),
	(178,'RW','Rwanda','RWA',646),
	(179,'SH','Saint Helena','SHN',654),
	(180,'KN','Saint Kitts and Nevis','KNA',659),
	(181,'LC','Saint Lucia','LCA',662),
	(182,'PM','Saint Pierre and Miquelon','SPM',666),
	(183,'VC','Saint Vincent and the Grenadines','VCT',670),
	(184,'WS','Samoa','WSM',882),
	(185,'SM','San Marino','SMR',674),
	(186,'ST','Sao Tome and Principe','STP',678),
	(187,'SA','Saudi Arabia','SAU',682),
	(188,'SN','Senegal','SEN',686),
	(189,'CS','Serbia and Montenegro',NULL,NULL),
	(190,'SC','Seychelles','SYC',690),
	(191,'SL','Sierra Leone','SLE',694),
	(192,'SG','Singapore','SGP',702),
	(193,'SK','Slovakia','SVK',703),
	(194,'SI','Slovenia','SVN',705),
	(195,'SB','Solomon Islands','SLB',90),
	(196,'SO','Somalia','SOM',706),
	(197,'ZA','South Africa','ZAF',710),
	(198,'GS','South Georgia and the South Sandwich Islands',NULL,NULL),
	(199,'ES','Spain','ESP',724),
	(200,'LK','Sri Lanka','LKA',144),
	(201,'SD','Sudan','SDN',736),
	(202,'SR','Suriname','SUR',740),
	(203,'SJ','Svalbard and Jan Mayen','SJM',744),
	(204,'SZ','Swaziland','SWZ',748),
	(205,'SE','Sweden','SWE',752),
	(206,'CH','Switzerland','CHE',756),
	(207,'SY','Syrian Arab Republic','SYR',760),
	(208,'TW','Taiwan, Province of China','TWN',158),
	(209,'TJ','Tajikistan','TJK',762),
	(210,'TZ','Tanzania, United Republic of','TZA',834),
	(211,'TH','Thailand','THA',764),
	(212,'TL','Timor-Leste',NULL,NULL),
	(213,'TG','Togo','TGO',768),
	(214,'TK','Tokelau','TKL',772),
	(215,'TO','Tonga','TON',776),
	(216,'TT','Trinidad and Tobago','TTO',780),
	(217,'TN','Tunisia','TUN',788),
	(218,'TR','Turkey','TUR',792),
	(219,'TM','Turkmenistan','TKM',795),
	(220,'TC','Turks and Caicos Islands','TCA',796),
	(221,'TV','Tuvalu','TUV',798),
	(222,'UG','Uganda','UGA',800),
	(223,'UA','Ukraine','UKR',804),
	(224,'AE','United Arab Emirates','ARE',784),
	(225,'GB','United Kingdom','GBR',826),
	(226,'US','United States','USA',840),
	(227,'UM','United States Minor Outlying Islands',NULL,NULL),
	(228,'UY','Uruguay','URY',858),
	(229,'UZ','Uzbekistan','UZB',860),
	(230,'VU','Vanuatu','VUT',548),
	(231,'VE','Venezuela','VEN',862),
	(232,'VN','Viet Nam','VNM',704),
	(233,'VG','Virgin Islands, British','VGB',92),
	(234,'VI','Virgin Islands, U.s.','VIR',850),
	(235,'WF','Wallis and Futuna','WLF',876),
	(236,'EH','Western Sahara','ESH',732),
	(237,'YE','Yemen','YEM',887),
	(238,'ZM','Zambia','ZMB',894),
	(239,'ZW','Zimbabwe','ZWE',716);

/*!40000 ALTER TABLE `fa_country` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table fa_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fa_user`;

CREATE TABLE `fa_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(45) NOT NULL,
  `country_id` int(11) DEFAULT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `banned` tinyint(1) NOT NULL DEFAULT '0',
  `forgotten_password_code` varchar(50) DEFAULT NULL,
  `last_visit` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_FI_1` (`country_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

LOCK TABLES `fa_user` WRITE;
/*!40000 ALTER TABLE `fa_user` DISABLE KEYS */;
INSERT INTO `fa_user` (`id`,`user_name`,`country_id`,`password`,`email`,`role`,`banned`,`forgotten_password_code`,`last_visit`,`created`,`modified`)
VALUES
	(1,'admin',0,'8709d7dc8f2ba1f9bd2b7140ff7078c2','root@localhost.com','superadmin',0,NULL,'2010-10-21 05:59:54','2010-10-21 12:59:54','0000-00-00 00:00:00'),
	(11,'cashier',0,'dc0c10aac30781cdad78df2306dfd555','cashier@mail.com','user',0,NULL,'2010-10-21 05:59:10','2010-10-21 12:59:10','0000-00-00 00:00:00');

/*!40000 ALTER TABLE `fa_user` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table fa_user_profile
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fa_user_profile`;

CREATE TABLE `fa_user_profile` (
  `id` int(11) NOT NULL,
  `field_1` varchar(50) DEFAULT NULL,
  `field_2` varchar(50) DEFAULT NULL,
  `call_me_nicely` varchar(3) DEFAULT NULL,
  `view_prepaid_voucher` int(1) NOT NULL DEFAULT '1',
  `create_prepaid_voucher` int(1) NOT NULL DEFAULT '1',
  `update_prepaid_voucher` int(1) NOT NULL DEFAULT '1',
  `delete_prepaid_voucher` int(1) NOT NULL DEFAULT '1',
  `view_postpaid_account` int(1) NOT NULL DEFAULT '1',
  `create_postpaid_account` int(1) NOT NULL DEFAULT '1',
  `update_postpaid_account` int(1) NOT NULL DEFAULT '1',
  `delete_postpaid_account` int(1) NOT NULL DEFAULT '1',
  `view_invoice` int(1) NOT NULL DEFAULT '1',
  `create_invoice` int(1) NOT NULL,
  `delete_invoice` int(1) NOT NULL DEFAULT '1',
  `view_statistic` int(1) NOT NULL DEFAULT '1',
  `view_online_user` int(1) NOT NULL DEFAULT '1',
  `delete_online_user` int(1) NOT NULL DEFAULT '1',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `fa_user_profile` WRITE;
/*!40000 ALTER TABLE `fa_user_profile` DISABLE KEYS */;
INSERT INTO `fa_user_profile` (`id`,`field_1`,`field_2`,`call_me_nicely`,`view_prepaid_voucher`,`create_prepaid_voucher`,`update_prepaid_voucher`,`delete_prepaid_voucher`,`view_postpaid_account`,`create_postpaid_account`,`update_postpaid_account`,`delete_postpaid_account`,`view_invoice`,`create_invoice`,`delete_invoice`,`view_statistic`,`view_online_user`,`delete_online_user`)
VALUES
	(11,'Foo Bar','Foo Bar','123',1,1,1,1,1,1,1,1,1,1,0,1,1,1);

/*!40000 ALTER TABLE `fa_user_profile` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table fa_user_temp
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fa_user_temp`;

CREATE TABLE `fa_user_temp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(45) NOT NULL,
  `country_id` int(11) DEFAULT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `activation_code` varchar(50) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_FI_1` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table invoice
# ------------------------------------------------------------

DROP TABLE IF EXISTS `invoice`;

CREATE TABLE `invoice` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `realname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `used` int(255) NOT NULL,
  `bill_by` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `current_total` decimal(10,4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;



# Dump of table invoice_detail
# ------------------------------------------------------------

DROP TABLE IF EXISTS `invoice_detail`;

CREATE TABLE `invoice_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `realname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `start` datetime NOT NULL,
  `stop` datetime NOT NULL,
  `used` int(11) NOT NULL,
  `bill_by` varchar(255) NOT NULL,
  `total` decimal(10,4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;



# Dump of table nas
# ------------------------------------------------------------

DROP TABLE IF EXISTS `nas`;

CREATE TABLE `nas` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nasname` varchar(128) NOT NULL,
  `shortname` varchar(32) DEFAULT NULL,
  `type` varchar(30) DEFAULT 'other',
  `ports` int(5) DEFAULT NULL,
  `secret` varchar(60) NOT NULL DEFAULT 'secret',
  `community` varchar(50) DEFAULT NULL,
  `description` varchar(200) DEFAULT 'RADIUS Client',
  PRIMARY KEY (`id`),
  KEY `nasname` (`nasname`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table postpaid_account
# ------------------------------------------------------------

DROP TABLE IF EXISTS `postpaid_account`;

CREATE TABLE `postpaid_account` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `realname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `bill_by` varchar(255) NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `IdleTimeout` int(10) NOT NULL,
  `valid_until` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=latin1;

LOCK TABLES `postpaid_account` WRITE;
/*!40000 ALTER TABLE `postpaid_account` DISABLE KEYS */;
INSERT INTO `postpaid_account` (`id`,`realname`,`username`,`password`,`bill_by`,`created_by`,`IdleTimeout`,`valid_until`)
VALUES
	(45,'z','z','z','time','admin',5,'January 29 2010 24:00:00'),
	(47,'l','l','l','time','admin',5,'January 30 2010 24:00:00'),
	(48,'Halo','halo','halo','time','cashier',5,'October 31 2010 24:00:00'),
	(49,'sdsdada','sdasda','weqeq','time','cashier',5,'November 11 2010 24:00:00');

/*!40000 ALTER TABLE `postpaid_account` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table postpaid_account_bill
# ------------------------------------------------------------

DROP VIEW IF EXISTS `postpaid_account_bill`;

CREATE TABLE `postpaid_account_bill` (
   `realname` VARCHAR(255) NOT NULL,
   `username` VARCHAR(255) NOT NULL,
   `password` VARCHAR(255) NOT NULL,
   `start` DATETIME DEFAULT NULL,
   `stop` DATETIME DEFAULT NULL,
   `time_used` DECIMAL(14) DEFAULT NULL,
   `packet_used` DECIMAL(24) DEFAULT NULL,
   `bill_by` VARCHAR(255) NOT NULL,
   `time_price` DOUBLE DEFAULT NULL,
   `packet_price` DOUBLE DEFAULT NULL
) ENGINE=MyISAM;;



# Dump of table postpaid_account_list
# ------------------------------------------------------------

DROP VIEW IF EXISTS `postpaid_account_list`;

CREATE TABLE `postpaid_account_list` (
   `id` INT(255) NOT NULL DEFAULT '0',
   `realname` VARCHAR(255) NOT NULL,
   `username` VARCHAR(255) NOT NULL,
   `password` VARCHAR(255) NOT NULL,
   `time_used` DECIMAL(36) DEFAULT NULL,
   `packet_used` DECIMAL(46) DEFAULT NULL,
   `bill_by` VARCHAR(255) NOT NULL,
   `time_price` DOUBLE DEFAULT NULL,
   `packet_price` DOUBLE DEFAULT NULL,
   `valid_until` VARCHAR(255) NOT NULL
) ENGINE=MyISAM;;



# Dump of table postplan
# ------------------------------------------------------------

DROP TABLE IF EXISTS `postplan`;

CREATE TABLE `postplan` (
  `id` int(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` float DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `postplan` WRITE;
/*!40000 ALTER TABLE `postplan` DISABLE KEYS */;
INSERT INTO `postplan` (`id`,`name`,`price`)
VALUES
	(1,'packet',0.1),
	(2,'time',0.1),
	(3,'bw_download',32000),
	(4,'bw_upload',16000),
	(5,'idletimeout',5);

/*!40000 ALTER TABLE `postplan` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table radacct
# ------------------------------------------------------------

DROP TABLE IF EXISTS `radacct`;

CREATE TABLE `radacct` (
  `radacctid` bigint(21) NOT NULL AUTO_INCREMENT,
  `acctsessionid` varchar(32) NOT NULL DEFAULT '',
  `acctuniqueid` varchar(32) NOT NULL DEFAULT '',
  `username` varchar(64) NOT NULL DEFAULT '',
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `realm` varchar(64) DEFAULT '',
  `nasipaddress` varchar(15) NOT NULL DEFAULT '',
  `nasportid` varchar(15) DEFAULT NULL,
  `nasporttype` varchar(32) DEFAULT NULL,
  `acctstarttime` datetime DEFAULT NULL,
  `acctstoptime` datetime DEFAULT NULL,
  `acctsessiontime` int(12) DEFAULT NULL,
  `acctauthentic` varchar(32) DEFAULT NULL,
  `connectinfo_start` varchar(50) DEFAULT NULL,
  `connectinfo_stop` varchar(50) DEFAULT NULL,
  `acctinputoctets` bigint(20) DEFAULT NULL,
  `acctoutputoctets` bigint(20) DEFAULT NULL,
  `calledstationid` varchar(50) NOT NULL DEFAULT '',
  `callingstationid` varchar(50) NOT NULL DEFAULT '',
  `acctterminatecause` varchar(32) NOT NULL DEFAULT '',
  `servicetype` varchar(32) DEFAULT NULL,
  `framedprotocol` varchar(32) DEFAULT NULL,
  `framedipaddress` varchar(15) NOT NULL DEFAULT '',
  `acctstartdelay` int(12) DEFAULT NULL,
  `acctstopdelay` int(12) DEFAULT NULL,
  `xascendsessionsvrkey` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`radacctid`),
  KEY `username` (`username`),
  KEY `framedipaddress` (`framedipaddress`),
  KEY `acctsessionid` (`acctsessionid`),
  KEY `acctuniqueid` (`acctuniqueid`),
  KEY `acctstarttime` (`acctstarttime`),
  KEY `acctstoptime` (`acctstoptime`),
  KEY `nasipaddress` (`nasipaddress`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table radcheck
# ------------------------------------------------------------

DROP TABLE IF EXISTS `radcheck`;

CREATE TABLE `radcheck` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `attribute` varchar(32) NOT NULL DEFAULT '',
  `op` char(2) NOT NULL DEFAULT '==',
  `value` varchar(253) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `username` (`username`(32))
) ENGINE=InnoDB AUTO_INCREMENT=205 DEFAULT CHARSET=latin1;

LOCK TABLES `radcheck` WRITE;
/*!40000 ALTER TABLE `radcheck` DISABLE KEYS */;
INSERT INTO `radcheck` (`id`,`username`,`attribute`,`op`,`value`)
VALUES
	(146,'tethaz6','Cleartext-Password',':=','caldotuk'),
	(147,'tethaz6','Expiration',':=','November 24 2009 24:00:00'),
	(148,'zerhaw7','Cleartext-Password',':=','cepdakar'),
	(149,'zerhaw7','Expiration',':=','October 26 2009 24:00:00'),
	(153,'vidvur5','Cleartext-Password',':=','dabsokul'),
	(154,'vidvur5','Expiration',':=','November 30 2009 24:00:00'),
	(155,'tiyben12','Cleartext-Password',':=','g'),
	(156,'tiyben12','Expiration',':=','November 30 2009 24:00:00'),
	(186,'z','Expiration',':=','January 29 2010 24:00:00'),
	(187,'z','Simultaneous-Use',':=','1'),
	(188,'z','Cleartext-Password',':=','z'),
	(192,'l','Expiration',':=','January 30 2010 24:00:00'),
	(193,'l','Simultaneous-Use',':=','1'),
	(194,'l','Cleartext-Password',':=','l'),
	(195,'halo','Expiration',':=','October 31 2010 24:00:00'),
	(196,'halo','Simultaneous-Use',':=','1'),
	(197,'halo','Cleartext-Password',':=','halo'),
	(198,'luwzay6','Cleartext-Password',':=','pensunek'),
	(199,'luwzay6','Expiration',':=','November 20 2010 24:00:00'),
	(200,'vakles13','Cleartext-Password',':=','luggerir'),
	(201,'vakles13','Expiration',':=','November 20 2010 24:00:00'),
	(202,'sdasda','Expiration',':=','November 11 2010 24:00:00'),
	(203,'sdasda','Simultaneous-Use',':=','1'),
	(204,'sdasda','Cleartext-Password',':=','weqeq');

/*!40000 ALTER TABLE `radcheck` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table radgroupcheck
# ------------------------------------------------------------

DROP TABLE IF EXISTS `radgroupcheck`;

CREATE TABLE `radgroupcheck` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `attribute` varchar(32) NOT NULL DEFAULT '',
  `op` char(2) NOT NULL DEFAULT '==',
  `value` varchar(253) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `groupname` (`groupname`(32))
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=latin1;

LOCK TABLES `radgroupcheck` WRITE;
/*!40000 ALTER TABLE `radgroupcheck` DISABLE KEYS */;
INSERT INTO `radgroupcheck` (`id`,`groupname`,`attribute`,`op`,`value`)
VALUES
	(23,'1 hour','Session-Timeout',':=','3600'),
	(24,'1 hour','Simultaneous-Use',':=','1'),
	(25,'1 Mega','Max-All-MB',':=','1'),
	(26,'1 Mega','Simultaneous-Use',':=','1');

/*!40000 ALTER TABLE `radgroupcheck` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table radgroupreply
# ------------------------------------------------------------

DROP TABLE IF EXISTS `radgroupreply`;

CREATE TABLE `radgroupreply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `attribute` varchar(32) NOT NULL DEFAULT '',
  `op` char(2) NOT NULL DEFAULT '=',
  `value` varchar(253) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `groupname` (`groupname`(32))
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;

LOCK TABLES `radgroupreply` WRITE;
/*!40000 ALTER TABLE `radgroupreply` DISABLE KEYS */;
INSERT INTO `radgroupreply` (`id`,`groupname`,`attribute`,`op`,`value`)
VALUES
	(22,'1 hour','WISPr-Bandwidth-Max-Down',':=','1024000'),
	(23,'1 hour','WISPr-Bandwidth-Max-Up',':=','1024000'),
	(24,'1 hour','Session-Timeout',':=','3600'),
	(25,'1 hour','Idle-Timeout',':=','600'),
	(26,'1 hour','Acct-Interim-Interval',':=','120'),
	(27,'1 Mega','ChilliSpot-Max-Total-Octets',':=','1048576'),
	(28,'1 Mega','Idle-Timeout',':=','600'),
	(29,'1 Mega','Acct-Interim-Interval',':=','120');

/*!40000 ALTER TABLE `radgroupreply` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table radpostauth
# ------------------------------------------------------------

DROP TABLE IF EXISTS `radpostauth`;

CREATE TABLE `radpostauth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `pass` varchar(64) NOT NULL DEFAULT '',
  `reply` varchar(32) NOT NULL DEFAULT '',
  `authdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table radreply
# ------------------------------------------------------------

DROP TABLE IF EXISTS `radreply`;

CREATE TABLE `radreply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `attribute` varchar(32) NOT NULL DEFAULT '',
  `op` char(2) NOT NULL DEFAULT '=',
  `value` varchar(253) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `username` (`username`(32))
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=latin1;

LOCK TABLES `radreply` WRITE;
/*!40000 ALTER TABLE `radreply` DISABLE KEYS */;
INSERT INTO `radreply` (`id`,`username`,`attribute`,`op`,`value`)
VALUES
	(53,'tethaz6','Expiration',':=','November 24 2009 24:00:00'),
	(54,'zerhaw7','Expiration',':=','October 26 2009 24:00:00'),
	(60,'vidvur5','Expiration',':=','November 30 2009 24:00:00'),
	(61,'tiyben12','Expiration',':=','November 30 2009 24:00:00'),
	(108,'z','WISPr-Bandwidth-Max-Down',':=','32000'),
	(109,'z','WISPr-Bandwidth-Max-Up',':=','16000'),
	(110,'z','Idle-Timeout',':=','300'),
	(111,'z','WISPr-Session-Terminate-Time',':=','2010-1-29T24:00:00'),
	(112,'z','Acct-Interim-Interval',':=','120'),
	(118,'l','WISPr-Bandwidth-Max-Down',':=','32000'),
	(119,'l','WISPr-Bandwidth-Max-Up',':=','16000'),
	(120,'l','Idle-Timeout',':=','300'),
	(121,'l','WISPr-Session-Terminate-Time',':=','2010-1-30T24:00:00'),
	(122,'l','Acct-Interim-Interval',':=','120'),
	(123,'halo','WISPr-Bandwidth-Max-Down',':=','32000'),
	(124,'halo','WISPr-Bandwidth-Max-Up',':=','16000'),
	(125,'halo','Idle-Timeout',':=','300'),
	(126,'halo','WISPr-Session-Terminate-Time',':=','2010-10-31T24:00:00'),
	(127,'halo','Acct-Interim-Interval',':=','120'),
	(128,'luwzay6','WISPr-Session-Terminate-Time',':=','2010-11-20T24:00:00'),
	(129,'vakles13','WISPr-Session-Terminate-Time',':=','2010-11-20T24:00:00'),
	(130,'sdasda','WISPr-Bandwidth-Max-Down',':=','32000'),
	(131,'sdasda','WISPr-Bandwidth-Max-Up',':=','16000'),
	(132,'sdasda','Idle-Timeout',':=','300'),
	(133,'sdasda','WISPr-Session-Terminate-Time',':=','2010-11-11T24:00:00'),
	(134,'sdasda','Acct-Interim-Interval',':=','120');

/*!40000 ALTER TABLE `radreply` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table radusergroup
# ------------------------------------------------------------

DROP TABLE IF EXISTS `radusergroup`;

CREATE TABLE `radusergroup` (
  `username` varchar(64) NOT NULL DEFAULT '',
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `priority` int(11) NOT NULL DEFAULT '1',
  KEY `username` (`username`(32))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `radusergroup` WRITE;
/*!40000 ALTER TABLE `radusergroup` DISABLE KEYS */;
INSERT INTO `radusergroup` (`username`,`groupname`,`priority`)
VALUES
	('tethaz6','1 hour',1),
	('zerhaw7','1 Mega',1),
	('vidvur5','1 hour',1),
	('tiyben12','1 Mega',1),
	('luwzay6','1 hour',1),
	('vakles13','1 hour',1);

/*!40000 ALTER TABLE `radusergroup` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table voucher
# ------------------------------------------------------------

DROP TABLE IF EXISTS `voucher`;

CREATE TABLE `voucher` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `billingplan` varchar(255) NOT NULL,
  `isprinted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;

LOCK TABLES `voucher` WRITE;
/*!40000 ALTER TABLE `voucher` DISABLE KEYS */;
INSERT INTO `voucher` (`id`,`username`,`password`,`billingplan`,`isprinted`)
VALUES
	(21,'tethaz6','caldotuk','1 hour',0),
	(22,'zerhaw7','cepdakar','1 Mega',0),
	(23,'vidvur5','dabsokul','1 hour',0),
	(24,'tiyben12','g','1 Mega',1),
	(26,'luwzay6','pensunek','1 hour',0),
	(27,'vakles13','luggerir','1 hour',0);

/*!40000 ALTER TABLE `voucher` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table voucher_list
# ------------------------------------------------------------

DROP VIEW IF EXISTS `voucher_list`;

CREATE TABLE `voucher_list` (
   `id` INT(255) NOT NULL DEFAULT '0',
   `username` VARCHAR(255) NOT NULL,
   `password` VARCHAR(255) NOT NULL,
   `billingplan` VARCHAR(255) NOT NULL,
   `type` VARCHAR(255) NOT NULL,
   `amount` INT(255) NOT NULL,
   `valid_for` INT(11) DEFAULT NULL,
   `price` FLOAT NOT NULL,
   `valid_until` VARCHAR(253) NOT NULL DEFAULT '',
   `time_used` DECIMAL(36) DEFAULT NULL,
   `time_remain` VARBINARY(39) DEFAULT NULL,
   `packet_used` DECIMAL(46) DEFAULT NULL,
   `packet_remain` VARBINARY(49) DEFAULT NULL,
   `isprinted` TINYINT(1) NOT NULL DEFAULT '0',
   `valid` VARCHAR(5) NOT NULL DEFAULT ''
) ENGINE=MyISAM;;





DROP TABLE `voucher_list`;
CREATE ALGORITHM=UNDEFINED DEFINER=`easyhotspot`@`localhost` SQL SECURITY DEFINER VIEW `voucher_list`
AS select
   `v`.`id` AS `id`,
   `v`.`username` AS `username`,
   `v`.`password` AS `password`,
   `v`.`billingplan` AS `billingplan`,
   `b`.`type` AS `type`,
   `b`.`amount` AS `amount`,
   `b`.`valid_for` AS `valid_for`,
   `b`.`price` AS `price`,
   `rc`.`value` AS `valid_until`,(sum(`ra`.`acctsessiontime`) / 60) AS `time_used`,if((`b`.`type` = _latin1'time'),(`b`.`amount` - (sum(`ra`.`acctsessiontime`) / 60)),_latin1'null') AS `time_remain`,((sum(`ra`.`acctoutputoctets`) + sum(`ra`.`acctinputoctets`)) / 1048576) AS `packet_used`,if((`b`.`type` = _latin1'packet'),(`b`.`amount` - (sum((`ra`.`acctoutputoctets` + `ra`.`acctinputoctets`)) / 1048576)),_latin1'null') AS `packet_remain`,
   `v`.`isprinted` AS `isprinted`,if((`b`.`type` = _latin1'time'),if(((sum(`ra`.`acctsessiontime`) / 60) >= `b`.`amount`),_latin1'exp',_latin1'valid'),if((((sum(`ra`.`acctoutputoctets`) + sum(`ra`.`acctinputoctets`)) / 1048576) >= `b`.`amount`),_latin1'exp',_latin1'valid')) AS `valid`
from (((`voucher` `v` left join `radacct` `ra` on((`v`.`username` = `ra`.`username`))) join `billingplan` `b` on((`b`.`name` = `v`.`billingplan`))) join `radcheck` `rc` on((`rc`.`username` = `v`.`username`)))
where (`rc`.`attribute` = 'Expiration') group by `v`.`username`;


DROP TABLE `postpaid_account_bill`;
CREATE ALGORITHM=UNDEFINED DEFINER=`easyhotspot`@`localhost` SQL SECURITY DEFINER VIEW `postpaid_account_bill`
AS select
   `postpaid_account`.`realname` AS `realname`,
   `postpaid_account`.`username` AS `username`,
   `postpaid_account`.`password` AS `password`,
   `radacct`.`acctstarttime` AS `start`,
   `radacct`.`acctstoptime` AS `stop`,(`radacct`.`acctsessiontime` / 60) AS `time_used`,((`radacct`.`acctoutputoctets` + `radacct`.`acctinputoctets`) / 1048576) AS `packet_used`,
   `postpaid_account`.`bill_by` AS `bill_by`,(`postplan`.`price` * (`radacct`.`acctsessiontime` / 60)) AS `time_price`,(`postplan`.`price` * ((`radacct`.`acctoutputoctets` + `radacct`.`acctinputoctets`) / 1048576)) AS `packet_price`
from ((`postpaid_account` left join `radacct` on((`postpaid_account`.`username` = `radacct`.`username`))) join `postplan` on((`postplan`.`name` = `postpaid_account`.`bill_by`)));


DROP TABLE `postpaid_account_list`;
CREATE ALGORITHM=UNDEFINED DEFINER=`easyhotspot`@`localhost` SQL SECURITY DEFINER VIEW `postpaid_account_list`
AS select
   `postpaid_account`.`id` AS `id`,
   `postpaid_account`.`realname` AS `realname`,
   `postpaid_account`.`username` AS `username`,
   `postpaid_account`.`password` AS `password`,(sum(`radacct`.`acctsessiontime`) / 60) AS `time_used`,(sum((`radacct`.`acctoutputoctets` + `radacct`.`acctinputoctets`)) / 1048576) AS `packet_used`,
   `postpaid_account`.`bill_by` AS `bill_by`,(`postplan`.`price` * (sum(`radacct`.`acctsessiontime`) / 60)) AS `time_price`,(`postplan`.`price` * (sum((`radacct`.`acctoutputoctets` + `radacct`.`acctinputoctets`)) / 1048576)) AS `packet_price`,
   `postpaid_account`.`valid_until` AS `valid_until`
from ((`postpaid_account` left join `radacct` on((`postpaid_account`.`username` = `radacct`.`username`))) join `postplan` on((`postplan`.`name` = `postpaid_account`.`bill_by`))) group by `postpaid_account`.`username`;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
