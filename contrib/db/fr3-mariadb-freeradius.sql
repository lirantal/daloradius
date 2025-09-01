##############################################################################################
#                                                                                            #
#  schema.sql                       rlm_sql - FreeRADIUS SQL Module                          #
#                                                                                            #
#     Database schema for MySQL rlm_sql module                                               #
#                                                                                            #
#     To load:                                                                               #
#         mysql -uroot -prootpass radius < schema.sql                                        #
#                                                                                            #
#                                   Mike Machado <mike@innercite.com>                        #
#                                                                                            #
#     some additions/adaptations for daloRADIUS have been made by:                           #
#                                                                                            #
#                                   Liran Tal <liran.tal@gmail.com>                          #
#                                   Filippo Maria Del Prete <filippo.delprete@gmail.com>     #
#                                   Filippo Lauria <filippo.lauria@iit.cnr.it>               #
#                                                                                            #
##############################################################################################

#
# Table structure for table 'radacct'
#

CREATE TABLE IF NOT EXISTS radacct (
  radacctid bigint(21) NOT NULL AUTO_INCREMENT,
  acctsessionid varchar(64) NOT NULL DEFAULT '',
  acctuniqueid varchar(32) NOT NULL DEFAULT '',
  username varchar(64) NOT NULL DEFAULT '',
  groupname varchar(64) NOT NULL DEFAULT '',
  realm varchar(64) DEFAULT '',
  nasipaddress varchar(15) NOT NULL DEFAULT '',
  nasportid varchar(32) DEFAULT NULL,
  nasporttype varchar(32) DEFAULT NULL,
  acctstarttime datetime NULL DEFAULT NULL,
  acctupdatetime datetime NULL DEFAULT NULL,
  acctstoptime datetime NULL DEFAULT NULL,
  acctinterval int(12) DEFAULT NULL,
  acctsessiontime int(12) unsigned DEFAULT NULL,
  acctauthentic varchar(32) DEFAULT NULL,
  connectinfo_start varchar(50) DEFAULT NULL,
  connectinfo_stop varchar(50) DEFAULT NULL,
  acctinputoctets bigint(20) DEFAULT NULL,
  acctoutputoctets bigint(20) DEFAULT NULL,
  calledstationid varchar(50) NOT NULL DEFAULT '',
  callingstationid varchar(50) NOT NULL DEFAULT '',
  acctterminatecause varchar(32) NOT NULL DEFAULT '',
  servicetype varchar(32) DEFAULT NULL,
  framedprotocol varchar(32) DEFAULT NULL,
  framedipaddress varchar(15) NOT NULL DEFAULT '',
  framedipv6address varchar(45) NOT NULL DEFAULT '',
  framedipv6prefix varchar(45) NOT NULL DEFAULT '',
  framedinterfaceid varchar(44) NOT NULL DEFAULT '',
  delegatedipv6prefix varchar(45) NOT NULL DEFAULT '',
  class varchar(64) DEFAULT NULL,
  PRIMARY KEY (radacctid),
  UNIQUE KEY acctuniqueid (acctuniqueid),
  KEY username (username),
  KEY framedipaddress (framedipaddress),
  KEY framedipv6address (framedipv6address),
  KEY framedipv6prefix (framedipv6prefix),
  KEY framedinterfaceid (framedinterfaceid),
  KEY delegatedipv6prefix (delegatedipv6prefix),
  KEY acctsessionid (acctsessionid),
  KEY acctsessiontime (acctsessiontime),
  KEY acctstarttime (acctstarttime),
  KEY acctinterval (acctinterval),
  KEY acctstoptime (acctstoptime),
  KEY nasipaddress (nasipaddress),
  INDEX bulk_close (acctstoptime, nasipaddress, acctstarttime)
);

#
# Table structure for table 'radcheck'
#

CREATE TABLE IF NOT EXISTS radcheck (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  username varchar(64) NOT NULL DEFAULT '',
  attribute varchar(64)  NOT NULL DEFAULT '',
  op char(2) NOT NULL DEFAULT '==',
  value varchar(253) NOT NULL DEFAULT '',
  PRIMARY KEY  (id),
  KEY username (username(32))
);

#
# Table structure for table 'radgroupcheck'
#

CREATE TABLE IF NOT EXISTS radgroupcheck (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  groupname varchar(64) NOT NULL DEFAULT '',
  attribute varchar(64)  NOT NULL DEFAULT '',
  op char(2) NOT NULL DEFAULT '==',
  value varchar(253)  NOT NULL DEFAULT '',
  PRIMARY KEY  (id),
  KEY groupname (groupname(32))
);

#
# Table structure for table 'radgroupreply'
#

CREATE TABLE IF NOT EXISTS radgroupreply (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  groupname varchar(64) NOT NULL DEFAULT '',
  attribute varchar(64)  NOT NULL DEFAULT '',
  op char(2) NOT NULL DEFAULT '=',
  value varchar(253)  NOT NULL DEFAULT '',
  PRIMARY KEY  (id),
  KEY groupname (groupname(32))
);

#
# Table structure for table 'radreply'
#

CREATE TABLE IF NOT EXISTS radreply (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  username varchar(64) NOT NULL DEFAULT '',
  attribute varchar(64) NOT NULL DEFAULT '',
  op char(2) NOT NULL DEFAULT '=',
  value varchar(253) NOT NULL DEFAULT '',
  PRIMARY KEY  (id),
  KEY username (username(32))
);


#
# Table structure for table 'radusergroup'
#

CREATE TABLE IF NOT EXISTS radusergroup (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  username varchar(64) NOT NULL DEFAULT '',
  groupname varchar(64) NOT NULL DEFAULT '',
  priority int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY  (id),
  KEY username (username(32))
);

#
# Table structure for table 'radpostauth'
#
# Note: MySQL versions since 5.6.4 support fractional precision timestamps
#        which we use here. Replace the authdate definition with the following
#        if your software is too old:
#
#   authdate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
#

CREATE TABLE IF NOT EXISTS radpostauth (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(64) NOT NULL DEFAULT '',
  pass varchar(64) NOT NULL DEFAULT '',
  reply varchar(32) NOT NULL DEFAULT '',
  authdate timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  class varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY  (id)
);

#
# Table structure for table 'nas'
#

CREATE TABLE IF NOT EXISTS nas (
  id int(10) NOT NULL AUTO_INCREMENT,
  nasname varchar(128) NOT NULL,
  shortname varchar(32),
  type varchar(30) DEFAULT 'other',
  ports int(5),
  secret varchar(60) DEFAULT 'secret' NOT NULL,
  server varchar(64),
  community varchar(50),
  description varchar(200) DEFAULT 'RADIUS Client',
  require_ma varchar(4) DEFAULT 'auto',
  limit_proxy_state varchar(4) DEFAULT 'auto',
  PRIMARY KEY (id),
  KEY nasname (nasname)
);

#
# Table structure for table 'nasreload'
#
CREATE TABLE IF NOT EXISTS nasreload (
  nasipaddress varchar(15) NOT NULL,
  reloadtime datetime NOT NULL,
  PRIMARY KEY (nasipaddress)
);

#
# Table structure for table 'radippool'

CREATE TABLE IF NOT EXISTS radippool (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  pool_name VARCHAR(30) NOT NULL,
  framedipaddress VARCHAR(15) NOT NULL DEFAULT '',
  nasipaddress VARCHAR(15) NOT NULL DEFAULT '',
  calledstationid VARCHAR(30) NOT NULL DEFAULT '',
  callingstationid VARCHAR(30) NOT NULL DEFAULT '',
  expiry_time DATETIME NOT NULL DEFAULT NOW(),
  username VARCHAR(64) NOT NULL DEFAULT '',
  pool_key VARCHAR(30) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY radippool_poolname_expire (pool_name, expiry_time),
  KEY framedipaddress (framedipaddress),
  KEY radippool_nasip_poolkey_ipaddress (nasipaddress, pool_key, framedipaddress)
);

#
# Table structure for table 'wimax' (WiMAX),
# which replaces the "radpostauth" table.
#

CREATE TABLE IF NOT EXISTS wimax (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(64) NOT NULL DEFAULT '',
  authdate timestamp NOT NULL,
  spi varchar(16) NOT NULL DEFAULT '',
  mipkey varchar(400) NOT NULL DEFAULT '',
  lifetime int(12) DEFAULT NULL,
  PRIMARY KEY  (id),
  KEY username (username),
  KEY spi (spi)
) ;

#
# Table structure for table 'cui'
#

CREATE TABLE IF NOT EXISTS `cui` (
  `clientipaddress` varchar(46) NOT NULL DEFAULT '',
  `callingstationid` varchar(50) NOT NULL DEFAULT '',
  `username` varchar(64) NOT NULL DEFAULT '',
  `cui` varchar(128) NOT NULL DEFAULT '',
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastaccounting` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (`username`,`clientipaddress`,`callingstationid`)
);

#
# Table structure for table 'radhuntgroup'
# source: https://wiki.freeradius.org/guide/SQL-Huntgroup-HOWTO
#

CREATE TABLE IF NOT EXISTS radhuntgroup (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    groupname varchar(64) NOT NULL DEFAULT '',
    nasipaddress varchar(15) NOT NULL DEFAULT '',
    nasportid varchar(15) DEFAULT NULL,
    PRIMARY KEY  (id),
    KEY nasipaddress (nasipaddress)
);
