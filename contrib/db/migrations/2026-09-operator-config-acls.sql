--
-- daloRADIUS operator configuration ACL repair
--
-- Apply this script when upgrading an existing daloRADIUS installation whose
-- operator ACL catalog predates the Mail and Message Settings permissions.
--
-- Fresh installations already include these ACLs in
-- contrib/db/mariadb-daloradius.sql.
--

INSERT INTO operators_acl_files (file, category, section)
SELECT 'config_mail_settings', 'Configuration', 'Mail'
WHERE NOT EXISTS (
    SELECT 1
    FROM operators_acl_files
    WHERE file = 'config_mail_settings'
);

INSERT INTO operators_acl_files (file, category, section)
SELECT 'config_mail_testing', 'Configuration', 'Mail'
WHERE NOT EXISTS (
    SELECT 1
    FROM operators_acl_files
    WHERE file = 'config_mail_testing'
);

INSERT INTO operators_acl_files (file, category, section)
SELECT 'config_messages', 'Configuration', 'Core'
WHERE NOT EXISTS (
    SELECT 1
    FROM operators_acl_files
    WHERE file = 'config_messages'
);

-- The ACL editor only displays definitions that already have an operator row.
-- Seed missing rows as denied, except for the built-in administrator account.
INSERT INTO operators_acl (operator_id, file, access)
SELECT operators.id, required_acls.file,
       CASE WHEN operators.username = 'administrator' THEN 1 ELSE 0 END
FROM operators
CROSS JOIN (
    SELECT 'config_mail_settings' AS file
    UNION ALL SELECT 'config_mail_testing'
    UNION ALL SELECT 'config_messages'
) AS required_acls
WHERE NOT EXISTS (
    SELECT 1
    FROM operators_acl
    WHERE operators_acl.operator_id = operators.id
      AND operators_acl.file = required_acls.file
);
