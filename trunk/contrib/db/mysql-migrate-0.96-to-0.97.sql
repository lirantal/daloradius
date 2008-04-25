-- MySQL dump 10.10
--
-- Host: localhost    Database: radius
-- ------------------------------------------------------
-- Server version	5.0.22-Debian_0ubuntu6.06-log

--
-- Table structure for table `operators`
--

-- we removed the rep_username column from the database
-- becasuse this page is deprecated

DROP TABLE IF EXISTS `realms`;
CREATE TABLE realms (
        id bigint(20) NOT NULL auto_increment,
        realmname VARCHAR(128) default NULL,
        type VARCHAR(32) default NULL,
        authhost VARCHAR(256) default NULL,
        accthost VARCHAR(256) default NULL,
        secret VARCHAR(128) default NULL,
        ldflag VARCHAR(64) default NULL,
        nostrip INT(8) default NULL,
        hints INT(8) default NULL,
        notrealm INT(8) default NULL,
	creationdate DATETIME default NULL,
	creationby VARCHAR(128) default NULL,
	updatedate DATETIME default NULL,
	updateby VARCHAR(128) default NULL,
        PRIMARY KEY (id)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `proxys`;
CREATE TABLE proxys (
        id bigint(20) NOT NULL auto_increment,
        proxyname VARCHAR(128) default NULL,
        retry_delay INT(8) default NULL,
        retry_count INT(8) default NULL,
        dead_time INT(8) default NULL,
        default_fallback INT(8) default NULL,
        PRIMARY KEY (id)
	creationdate DATETIME default NULL,
	creationby VARCHAR(128) default NULL,
	updatedate DATETIME default NULL,
	updateby VARCHAR(128) default NULL,
) ENGINE=MyISAM;

ALTER TABLE operators ADD mng_rad_attributes_list VARCHAR(32);
ALTER TABLE operators ADD mng_rad_attributes_new VARCHAR(32);
ALTER TABLE operators ADD mng_rad_attributes_edit VARCHAR(32);
ALTER TABLE operators ADD mng_rad_attributes_search VARCHAR(32);
ALTER TABLE operators ADD mng_rad_attributes_del VARCHAR(32);
UPDATE operators SET mng_rad_attributes_list='yes' WHERE username='administrator';
UPDATE operators SET mng_rad_attributes_new='yes' WHERE username='administrator';
UPDATE operators SET mng_rad_attributes_edit='yes' WHERE username='administrator';
UPDATE operators SET mng_rad_attributes_search='yes' WHERE username='administrator';
UPDATE operators SET mng_rad_attributes_del='yes' WHERE username='administrator';

ALTER TABLE operators ADD mng_rad_realms_list VARCHAR(32);
ALTER TABLE operators ADD mng_rad_realms_new VARCHAR(32);
ALTER TABLE operators ADD mng_rad_realms_edit VARCHAR(32);
ALTER TABLE operators ADD mng_rad_realms_del VARCHAR(32);
UPDATE operators SET mng_rad_realms_list='yes' WHERE username='administrator';
UPDATE operators SET mng_rad_realms_new='yes' WHERE username='administrator';
UPDATE operators SET mng_rad_realms_edit='yes' WHERE username='administrator';
UPDATE operators SET mng_rad_realms_del='yes' WHERE username='administrator';

ALTER TABLE operators ADD mng_rad_proxys_list VARCHAR(32);
ALTER TABLE operators ADD mng_rad_proxys_new VARCHAR(32);
ALTER TABLE operators ADD mng_rad_proxys_edit VARCHAR(32);
ALTER TABLE operators ADD mng_rad_proxys_del VARCHAR(32);
UPDATE operators SET mng_rad_proxys_list='yes' WHERE username='administrator';
UPDATE operators SET mng_rad_proxys_new='yes' WHERE username='administrator';
UPDATE operators SET mng_rad_proxys_edit='yes' WHERE username='administrator';
UPDATE operators SET mng_rad_proxys_del='yes' WHERE username='administrator';

ALTER TABLE operators ADD acct_maintenance_cleanup VARCHAR(32);
UPDATE operators SET acct_maintenance_cleanup='yes' WHERE username='administrator';
ALTER TABLE operators ADD acct_maintenance_delete VARCHAR(32);
UPDATE operators SET acct_maintenance_delete='yes' WHERE username='administrator';

ALTER TABLE hotspots ADD creationdate DATETIME;
ALTER TABLE hotspots ADD creationby VARCHAR(128);
ALTER TABLE hotspots ADD updatedate DATETIME;
ALTER TABLE hotspots ADD updateby VARCHAR(128);

ALTER TABLE userinfo ADD creationby VARCHAR(128);
ALTER TABLE userinfo ADD updatedate DATETIME;
ALTER TABLE userinfo ADD updateby VARCHAR(128);

ALTER TABLE realms ADD creationdate DATETIME;
ALTER TABLE realms ADD creationby VARCHAR(128);
ALTER TABLE realms ADD updatedate DATETIME;
ALTER TABLE realms ADD updateby VARCHAR(128);

ALTER TABLE proxys ADD creationdate DATETIME;
ALTER TABLE proxys ADD creationby VARCHAR(128);
ALTER TABLE proxys ADD updatedate DATETIME;
ALTER TABLE proxys ADD updateby VARCHAR(128);

