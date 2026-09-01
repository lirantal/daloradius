--
-- daloRADIUS operator password hashing migration
--
-- Apply this script when upgrading an existing daloRADIUS installation
-- to a version that stores operator passwords as a password_hash()
-- digest instead of a 32-char (MD5) value.
--
-- This widens operators.password from VARCHAR(32) to VARCHAR(95) so it
-- can hold the hash produced by PHP's password_hash().
--
-- Fresh installations already include this schema change in
-- contrib/db/mariadb-daloradius.sql.
--
-- NOTE: this migration only resizes the column. Existing stored
-- passwords are not re-hashed by this script; that is handled by the
-- application (e.g. transparently re-hashed on the operator's next login).
--

ALTER TABLE operators
  MODIFY COLUMN IF EXISTS password VARCHAR(95) NOT NULL;