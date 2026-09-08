--
-- daloRADIUS: remove the orphaned rep-username.php report
--
-- Apply this script when upgrading an existing daloRADIUS installation to a
-- version that no longer ships app/operators/rep-username.php. The page was
-- unreachable from the UI (no menu, sidebar or link pointed to it) and its
-- functionality is already covered by mng-edit.php.
--
-- Fresh installations already omit these rows from
-- contrib/db/mariadb-daloradius.sql.
--
-- This drops the page's ACL definition and every per-operator grant for it.
--

DELETE FROM operators_acl       WHERE file = 'rep_username';
DELETE FROM operators_acl_files WHERE file = 'rep_username';
