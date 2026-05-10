-- 
-- Comprehensive Performance Indexes for daloRADIUS
-- This script safely attempts to create required performance indexes.
-- It is idempotent: it checks both index name and column definition
-- before creating, avoiding duplicate functional indexes.
-- 

DELIMITER $$

-- Helper procedure to ensure idempotent index creation.
-- Checks two conditions before creating:
--   1. No index with the requested name already exists.
--   2. No existing index covers the exact same column(s), even under a different name.
DROP PROCEDURE IF EXISTS _safe_create_index$$
CREATE PROCEDURE _safe_create_index(
    IN table_name_in VARCHAR(128),
    IN index_name_in VARCHAR(128),
    IN index_columns_in VARCHAR(255),
    IN create_statement TEXT
)
BEGIN
    DECLARE index_count INT DEFAULT 0;
    DECLARE equiv_count INT DEFAULT 0;

    -- 1. Check if an index with this exact name already exists
    SELECT COUNT(1) INTO index_count
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = table_name_in
      AND INDEX_NAME = index_name_in;

    -- 2. Check if a functionally equivalent index already exists (same columns, same order)
    IF index_count = 0 THEN
        SELECT COUNT(1) INTO equiv_count
        FROM (
            SELECT
                INDEX_NAME,
                GROUP_CONCAT(
                    CASE
                        WHEN SUB_PART IS NULL THEN COLUMN_NAME
                        ELSE CONCAT(COLUMN_NAME, '(', SUB_PART, ')')
                    END
                    ORDER BY SEQ_IN_INDEX
                    SEPARATOR ','
                ) AS indexed_columns
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = table_name_in
            GROUP BY INDEX_NAME
        ) existing_indexes
        WHERE existing_indexes.indexed_columns = index_columns_in;
    END IF;

    IF index_count = 0 AND equiv_count = 0 THEN
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
-- Note: userinfo already has KEY `username` (`username`) in the base schema,
-- so we do NOT add idx_userinfo_username here — it would be a functional duplicate.
CALL _safe_create_index('radacct', 'idx_radacct_username_time', 'username,acctstarttime', 'CREATE INDEX idx_radacct_username_time ON radacct (username, acctstarttime)');

-- ==========================================
-- 2. Dashboard Performance (home-main)
-- ==========================================
CALL _safe_create_index('radpostauth', 'idx_radpostauth_authdate', 'authdate', 'CREATE INDEX idx_radpostauth_authdate ON radpostauth (authdate)');
CALL _safe_create_index('radacct', 'idx_radacct_status_start', 'acctstoptime,acctstarttime', 'CREATE INDEX idx_radacct_status_start ON radacct (acctstoptime, acctstarttime)');
CALL _safe_create_index('radacct', 'idx_radacct_top_users', 'acctstarttime,username,acctsessiontime,acctinputoctets,acctoutputoctets', 'CREATE INDEX idx_radacct_top_users ON radacct (acctstarttime, username, acctsessiontime, acctinputoctets, acctoutputoctets)');
CALL _safe_create_index('radcheck', 'idx_radcheck_username_attr', 'username,attribute', 'CREATE INDEX idx_radcheck_username_attr ON radcheck (username, attribute)');

-- Clean up
DROP PROCEDURE IF EXISTS _safe_create_index;
