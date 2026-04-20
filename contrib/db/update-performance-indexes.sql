-- 
-- Comprehensive Performance Indexes for daloRADIUS
-- This script safely attempts to create required performance indexes.
-- It is perfectly idempotent and can be re-run indefinitely without error.
-- 

DELIMITER $$

-- Helper procedure to ensure idempotent index creation across older MySQL versions
DROP PROCEDURE IF EXISTS _safe_create_index$$
CREATE PROCEDURE _safe_create_index(
    IN table_name_in VARCHAR(128),
    IN index_name_in VARCHAR(128),
    IN create_statement TEXT
)
BEGIN
    DECLARE index_count INT DEFAULT 0;
    
    SELECT COUNT(1) INTO index_count
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = table_name_in
      AND INDEX_NAME = index_name_in;
      
    IF index_count = 0 THEN
        SET @sql_stmt = create_statement;
        PREPARE dynamic_stmt FROM @sql_stmt;
        EXECUTE dynamic_stmt;
        DEALLOCATE PREPARE dynamic_stmt;
    END IF;
END$$

DELIMITER ;

-- ==========================================
-- 1. User Listing Performance (mng-list-all)
-- ==========================================
CALL _safe_create_index('radacct', 'idx_radacct_username_time', 'CREATE INDEX idx_radacct_username_time ON radacct (username, acctstarttime)');
CALL _safe_create_index('userinfo', 'idx_userinfo_username', 'CREATE INDEX idx_userinfo_username ON userinfo (username)');

-- ==========================================
-- 2. Dashboard Performance (home-main)
-- ==========================================
CALL _safe_create_index('radpostauth', 'idx_radpostauth_authdate', 'CREATE INDEX idx_radpostauth_authdate ON radpostauth (authdate)');
CALL _safe_create_index('radacct', 'idx_radacct_status_start', 'CREATE INDEX idx_radacct_status_start ON radacct (acctstoptime, acctstarttime)');
CALL _safe_create_index('radacct', 'idx_radacct_top_users', 'CREATE INDEX idx_radacct_top_users ON radacct (acctstarttime, username, acctsessiontime, acctinputoctets, acctoutputoctets)');
CALL _safe_create_index('radcheck', 'idx_radcheck_username_attr', 'CREATE INDEX idx_radcheck_username_attr ON radcheck (username, attribute)');

-- Clean up
DROP PROCEDURE IF EXISTS _safe_create_index;
