--
-- daloRADIUS operator LDAP account migration
--
-- Apply this script when upgrading an existing daloRADIUS installation to
-- operator accounts backed by either the local password store or LDAP.
--
-- Fresh installations already include these columns in
-- contrib/db/mariadb-daloradius.sql. Existing operators remain local
-- accounts, and existing password values are preserved.
--

ALTER TABLE operators
  ADD COLUMN IF NOT EXISTS auth_source VARCHAR(16) NOT NULL DEFAULT 'local',
  ADD COLUMN IF NOT EXISTS external_id VARCHAR(255) DEFAULT NULL;

ALTER TABLE operators
  MODIFY COLUMN password VARCHAR(95) NULL;

UPDATE operators
SET auth_source = 'local'
WHERE auth_source IS NULL OR auth_source = '';

ALTER TABLE operators
  ADD UNIQUE INDEX IF NOT EXISTS operators_external_id_uq (external_id);
