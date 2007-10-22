-- daloRADIUS 0.9-4 database scheme for MySQL
-- Liran Tal <liran.tal@gmail.com>
--
-- The file includes only the tables that daloRADIUS itself makes use of
-- which are hotspots, operators, rates and userinfo.


--
-- Table structure for table `hotspots`
--

CREATE TABLE `hotspots` (
  `id` bigint(20) NOT NULL auto_increment,
  `name` varchar(32) default NULL,
  `mac` varchar(32) default NULL,
  `geocode` varchar(128) default NULL,
  `owner` varchar(32) default NULL,
  `email_owner` varchar(32) default NULL,
  `manager` varchar(32) default NULL,
  `email_manager` varchar(32) default NULL,
  `address` varchar(128) default NULL,
  `company` varchar(32) default NULL,
  `phone1` varchar(32) default NULL,
  `phone2` varchar(32) default NULL,
  `type` varchar(32) default NULL,
  `website` varchar(32) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

--
-- Table structure for table `operators`
--

CREATE TABLE `operators` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(32) default NULL,
  `password` varchar(32) default NULL,
  `mng_search` varchar(32) default NULL,
  `mng_batch` varchar(32) default NULL,
  `mng_del` varchar(32) default NULL,
  `mng_edit` varchar(32) default NULL,
  `mng_new` varchar(32) default NULL,
  `mng_new_quick` varchar(32) default NULL,
  `mng_list_all` varchar(32) default NULL,
  `mng_hs_del` varchar(32) default NULL,
  `mng_hs_edit` varchar(32) default NULL,
  `mng_hs_new` varchar(32) default NULL,
  `mng_hs_list` varchar(32) default NULL,
  `mng_rad_nas_del` varchar(32) default NULL,
  `mng_rad_nas_edit` varchar(32) default NULL,
  `mng_rad_nas_new` varchar(32) default NULL,
  `mng_rad_nas_list` varchar(32) default NULL,
  `mng_rad_usergroup_del` varchar(32) default NULL,
  `mng_rad_usergroup_edit` varchar(32) default NULL,
  `mng_rad_usergroup_new` varchar(32) default NULL,
  `mng_rad_usergroup_list_user` varchar(32) default NULL,
  `mng_rad_usergroup_list` varchar(32) default NULL,
  `mng_rad_groupcheck_del` varchar(32) default NULL,
  `mng_rad_groupcheck_list` varchar(32) default NULL,
  `mng_rad_groupcheck_new` varchar(32) default NULL,
  `mng_rad_groupcheck_edit` varchar(32) default NULL,
  `mng_rad_groupreply_del` varchar(32) default NULL,
  `mng_rad_groupreply_list` varchar(32) default NULL,
  `mng_rad_groupreply_new` varchar(32) default NULL,
  `mng_rad_groupreply_edit` varchar(32) default NULL,
  `rep_online` varchar(32) default NULL,
  `rep_topusers` varchar(32) default NULL,
  `rep_username` varchar(32) default NULL,
  `rep_lastconnect` varchar(32) default NULL,
  `rep_logs_radius` varchar(32) default NULL,
  `rep_stat_radius` varchar(32) default NULL,
  `rep_stat_server` varchar(32) default NULL,
  `rep_logs_system` varchar(32) default NULL,
  `rep_logs_boot` varchar(32) default NULL,
  `rep_logs_daloradius` varchar(32) default NULL,
  `acct_active` varchar(32) default NULL,
  `acct_username` varchar(32) default NULL,
  `acct_all` varchar(32) default NULL,
  `acct_date` varchar(32) default NULL,
  `acct_ipaddress` varchar(32) default NULL,
  `acct_nasipaddress` varchar(32) default NULL,
  `acct_hotspot_accounting` varchar(32) default NULL,
  `acct_hotspot_compare` varchar(32) default NULL,
  `bill_persecond` varchar(32) default NULL,
  `bill_prepaid` varchar(32) default NULL,
  `bill_rates_del` varchar(32) default NULL,
  `bill_rates_new` varchar(32) default NULL,
  `bill_rates_edit` varchar(32) default NULL,
  `bill_rates_list` varchar(32) default NULL,
  `gis_editmap` varchar(32) default NULL,
  `gis_viewmap` varchar(32) default NULL,
  `graphs_alltime_logins` varchar(32) default NULL,
  `graphs_alltime_traffic_compare` varchar(32) default NULL,
  `graphs_overall_download` varchar(32) default NULL,
  `graphs_overall_upload` varchar(32) default NULL,
  `graphs_overall_logins` varchar(32) default NULL,
  `config_db` varchar(32) default NULL,
  `config_interface` varchar(32) default NULL,
  `config_lang` varchar(32) default NULL,
  `config_logging` varchar(32) default NULL,
  `config_maint_test_user` varchar(32) default NULL,
  `config_operators_del` varchar(32) default NULL,
  `config_operators_edit` varchar(32) default NULL,
  `config_operators_list` varchar(32) default NULL,
  `config_operators_new` varchar(32) default NULL,
  `firstname` varchar(32) default NULL,
  `lastname` varchar(32) default NULL,
  `title` varchar(32) default NULL,
  `department` varchar(32) default NULL,
  `company` varchar(32) default NULL,
  `phone1` varchar(32) default NULL,
  `phone2` varchar(32) default NULL,
  `email1` varchar(32) default NULL,
  `email2` varchar(32) default NULL,
  `messenger1` varchar(32) default NULL,
  `messenger2` varchar(32) default NULL,
  `notes` varchar(128) default NULL,
  `lastlogin` datetime default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

LOCK TABLES `operators` WRITE;
INSERT INTO `operators` VALUES (1,'administrator','radius','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','Sy
s','Administrator','','','','','','','','','','','0000-00-00 00:00:00'),(2,'liran','1234','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes'
,'yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes',
'yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','yes','
yes','yes','yes','yes','yes','Liran','Tal','Developer','daloRADIUS','Enginx','','','liran.tal@gmail.com','liran@enginx.com','','','','0000-00-00 00:00:00');
UNLOCK TABLES;

--
-- Table structure for table `rates`
--

CREATE TABLE `rates` (
  `id` bigint(20) NOT NULL auto_increment,
  `type` varchar(32) default NULL,
  `cardbank` double default NULL,
  `rate` double default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

--
-- Table structure for table `userinfo`
--

CREATE TABLE `userinfo` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `username` varchar(30) default NULL,
  `firstname` varchar(200) default NULL,
  `lastname` varchar(200) default NULL,
  `email` varchar(200) default NULL,
  `department` varchar(200) default NULL,
  `company` varchar(200) default NULL,
  `workphone` varchar(200) default NULL,
  `homephone` varchar(200) default NULL,
  `mobilephone` varchar(200) default NULL,
  `notes` varchar(200) default NULL,
  PRIMARY KEY  (`id`),
  KEY `username` (`username`)
) ENGINE=MyISAM;
