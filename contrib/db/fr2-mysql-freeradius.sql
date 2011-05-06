###########################################################################
# $Id$            													      #
#                                                                         #
#  schema.sql                       rlm_sql - FreeRADIUS SQL Module       #
#                                                                         #
#     Database schema for MySQL rlm_sql module                            #
#                                                                         #
#     To load:                                                            #
#         mysql -uroot -prootpass radius < schema.sql                     #
#                                                                         #
#                                   Mike Machado <mike@innercite.com>     #
#						modified: 	Liran Tal <liran.tal@gmail.com>		  # 
###########################################################################
#
# Table structure for table 'radacct'
#

CREATE TABLE radacct (
  radacctid bigint(21) NOT NULL auto_increment,
  acctsessionid varchar(64) NOT NULL default '',
  acctuniqueid varchar(32) NOT NULL default '',
  username varchar(64) NOT NULL default '',
  groupname varchar(64) NOT NULL default '',
  realm varchar(64) default '',
  nasipaddress varchar(15) NOT NULL default '',
  nasportid varchar(15) default NULL,
  nasporttype varchar(32) default NULL,
  acctstarttime datetime NULL default NULL,
  acctstoptime datetime NULL default NULL,
  acctsessiontime int(12) default NULL,
  acctauthentic varchar(32) default NULL,
  connectinfo_start varchar(50) default NULL,
  connectinfo_stop varchar(50) default NULL,
  acctinputoctets bigint(20) default NULL,
  acctoutputoctets bigint(20) default NULL,
  calledstationid varchar(50) NOT NULL default '',
  callingstationid varchar(50) NOT NULL default '',
  acctterminatecause varchar(32) NOT NULL default '',
  servicetype varchar(32) default NULL,
  framedprotocol varchar(32) default NULL,
  framedipaddress varchar(15) NOT NULL default '',
  acctstartdelay int(12) default NULL,
  acctstopdelay int(12) default NULL,
  xascendsessionsvrkey varchar(10) default NULL,
  PRIMARY KEY  (radacctid),
  KEY username (username),
  KEY framedipaddress (framedipaddress),
  KEY acctsessionid (acctsessionid),
  KEY acctsessiontime (acctsessiontime),
  KEY acctuniqueid (acctuniqueid),
  KEY acctstarttime (acctstarttime),
  KEY acctstoptime (acctstoptime),
  KEY nasipaddress (nasipaddress)
) ;

#
# Table structure for table 'radcheck'
#

CREATE TABLE radcheck (
  id int(11) unsigned NOT NULL auto_increment,
  username varchar(64) NOT NULL default '',
  attribute varchar(64)  NOT NULL default '',
  op char(2) NOT NULL DEFAULT '==',
  value varchar(253) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY username (username(32))
) ;

#
# Table structure for table 'radgroupcheck'
#

CREATE TABLE radgroupcheck (
  id int(11) unsigned NOT NULL auto_increment,
  groupname varchar(64) NOT NULL default '',
  attribute varchar(64)  NOT NULL default '',
  op char(2) NOT NULL DEFAULT '==',
  value varchar(253)  NOT NULL default '',
  PRIMARY KEY  (id),
  KEY groupname (groupname(32))
) ;

#
# Table structure for table 'radgroupreply'
#

CREATE TABLE radgroupreply (
  id int(11) unsigned NOT NULL auto_increment,
  groupname varchar(64) NOT NULL default '',
  attribute varchar(64)  NOT NULL default '',
  op char(2) NOT NULL DEFAULT '=',
  value varchar(253)  NOT NULL default '',
  PRIMARY KEY  (id),
  KEY groupname (groupname(32))
) ;

#
# Table structure for table 'radreply'
#

CREATE TABLE radreply (
  id int(11) unsigned NOT NULL auto_increment,
  username varchar(64) NOT NULL default '',
  attribute varchar(64) NOT NULL default '',
  op char(2) NOT NULL DEFAULT '=',
  value varchar(253) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY username (username(32))
) ;


#
# Table structure for table 'radusergroup'
#

CREATE TABLE radusergroup (
  username varchar(64) NOT NULL default '',
  groupname varchar(64) NOT NULL default '',
  priority int(11) NOT NULL default '1',
  KEY username (username(32))
) ;

#
# Table structure for table 'radpostauth'
#

CREATE TABLE radpostauth (
  id int(11) NOT NULL auto_increment,
  username varchar(64) NOT NULL default '',
  pass varchar(64) NOT NULL default '',
  reply varchar(32) NOT NULL default '',
  authdate timestamp NOT NULL,
  PRIMARY KEY  (id)
) ;

#
# Table structure for table 'radippool'
#
CREATE TABLE radippool ( 
  id                    int(11) unsigned NOT NULL auto_increment,
  pool_name             varchar(30) NOT NULL,
  framedipaddress       varchar(15) NOT NULL default '',
  nasipaddress          varchar(15) NOT NULL default '',
  calledstationid       VARCHAR(30) NOT NULL,
  callingstationid      VARCHAR(30) NOT NULL,
  expiry_time           DATETIME NULL default NULL,
  username              varchar(64) NOT NULL default '',
  pool_key              varchar(30) NOT NULL,
  PRIMARY KEY (id),
  KEY radippool_poolname_expire (pool_name, expiry_time),
  KEY framedipaddress (framedipaddress),
  KEY radippool_nasip_poolkey_ipaddress (nasipaddress, pool_key, framedipaddress)
) ENGINE=InnoDB;

#
# Table structure for table 'nas'
#
CREATE TABLE nas (
  id int(10) NOT NULL auto_increment,
  nasname varchar(128) NOT NULL,
  shortname varchar(32),
  type varchar(30) DEFAULT 'other',
  ports int(5),
  secret varchar(60) DEFAULT 'secret' NOT NULL,
  server varchar(64),
  community varchar(50),
  description varchar(200) DEFAULT 'RADIUS Client',
  PRIMARY KEY (id),
  KEY nasname (nasname)
);



#
# WiMAX Table structure for table 'wimax',
# which replaces the "radpostauth" table.
#

CREATE TABLE wimax (
  id int(11) NOT NULL auto_increment,
  username varchar(64) NOT NULL default '',
  authdate timestamp NOT NULL,
  spi varchar(16) NOT NULL default '',
  mipkey varchar(400) NOT NULL default '',
  lifetime int(12) default NULL,
  PRIMARY KEY  (id),
  KEY username (username),
  KEY spi (spi)
) ;

#
# cui table 
#
CREATE TABLE `cui` (
  `clientipaddress` varchar(15) NOT NULL default '',
  `callingstationid` varchar(50) NOT NULL default '',
  `username` varchar(64) NOT NULL default '',
  `cui` varchar(32) NOT NULL default '',
  `creationdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `lastaccounting` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`username`,`clientipaddress`,`callingstationid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


# table contributed by Filippo Maria Del Prete
# for defining hunt groups via daloradius interface 
CREATE TABLE IF NOT EXISTS `radhuntgroup` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `groupname` varchar(64) NOT NULL default '',
  `nasipaddress` varchar(15) NOT NULL default '',
  `nasportid` varchar(15) default NULL,
  PRIMARY KEY  (`id`),
  KEY `nasipaddress` (`nasipaddress`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
