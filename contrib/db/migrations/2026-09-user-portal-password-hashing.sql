--
-- daloRADIUS user portal password hashing migration
--
-- Fresh installations already use this column size in
-- contrib/db/mariadb-daloradius.sql.
--
-- This migration only widens the column. Use the application login path or
-- contrib/scripts/maintenance/hash-user-portal-passwords.php to replace
-- existing plaintext values with password_hash() digests.
--

ALTER TABLE userinfo
  MODIFY COLUMN IF EXISTS portalloginpassword VARCHAR(255) DEFAULT '';
