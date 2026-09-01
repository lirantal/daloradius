--
-- daloRADIUS operator TOTP MFA migration
--
-- Apply this script when upgrading an existing daloRADIUS installation
-- to a version that supports operator TOTP MFA.
--
-- Fresh installations already include these schema changes in
-- contrib/db/mariadb-daloradius.sql.
--

ALTER TABLE operators
  ADD COLUMN IF NOT EXISTS totp_enabled TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS totp_secret VARCHAR(64) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS totp_last_counter BIGINT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS totp_confirmed_at DATETIME DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS totp_recovery_codes TEXT DEFAULT NULL;

INSERT INTO operators_acl_files (file, category, section)
SELECT 'config_operator_2fa', 'Configuration', 'Operators'
WHERE NOT EXISTS (
    SELECT 1
    FROM operators_acl_files
    WHERE file = 'config_operator_2fa'
);

INSERT INTO operators_acl (operator_id, file, access)
SELECT id, 'config_operator_2fa', 1
FROM operators
WHERE username = 'administrator'
  AND NOT EXISTS (
      SELECT 1
      FROM operators_acl
      WHERE operators_acl.operator_id = operators.id
        AND operators_acl.file = 'config_operator_2fa'
  );
