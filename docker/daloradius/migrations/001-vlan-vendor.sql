-- =============================================================================
-- VLAN Configuration vendor for daloRADIUS dictionary
--
-- This file adds a custom vendor "VLAN Configuration" to the daloRADIUS
-- dictionary, grouping all attributes needed for VLAN-based network access
-- profiles. Makes it easy to create new groups without remembering attribute
-- names manually.
--
-- The default Session-Timeout (3600s) is applied by FreeRADIUS at runtime
-- to all users without a group-level override.
-- Simultaneous-Use is set per user (not per group).
--
-- Installed by: docker/daloradius/init.sh (init_database function)
-- =============================================================================

-- =============================================================================
-- Attribute definitions (base type entries)
-- =============================================================================
INSERT INTO dictionary (Type, Attribute, Vendor, RecommendedOP, RecommendedTable, RecommendedHelper, RecommendedTooltip)
VALUES
  ('integer', 'Tunnel-Type',             'VLAN Configuration', ':=', 'reply', NULL,
   'Tunnel protocol to use. Set to "VLAN" (13) for 802.1q VLAN assignment.'),
  ('integer', 'Tunnel-Medium-Type',      'VLAN Configuration', ':=', 'reply', NULL,
   'Transport medium. Set to "IEEE-802" (6) for Ethernet VLANs.'),
  ('string',  'Tunnel-Private-Group-Id', 'VLAN Configuration', ':=', 'reply', NULL,
   'VLAN ID number assigned to this group (e.g. 10, 20, 50). Must match the VLAN configured on the switch/AP.'),
  ('integer', 'Session-Timeout',         'VLAN Configuration', ':=', 'reply', NULL,
   'Maximum session duration in seconds. 3600 = 1 hour. Users are disconnected after this time and must re-authenticate.'),
  ('string',  'Cleartext-Password',      'VLAN Configuration', ':=', 'check', NULL,
   'User password in plain text. Used for PAP authentication. WARNING: stored as-is in the database.'),
  ('integer', 'Simultaneous-Use',        'VLAN Configuration', ':=', 'check', NULL,
   'Maximum number of simultaneous logins for this user. Set to 0 for unlimited, 1 for single-session, 2+ for multi-device.');

-- =============================================================================
-- Enumerated values for Tunnel-Type
-- =============================================================================
INSERT IGNORE INTO dictionary (Type, Attribute, Value, Vendor)
VALUES
  ('Tunnel-Type', 'Tunnel-Type', 'VLAN',    'VLAN Configuration'),
  ('Tunnel-Type', 'Tunnel-Type', 'PPTP',    'VLAN Configuration'),
  ('Tunnel-Type', 'Tunnel-Type', 'L2TP',    'VLAN Configuration'),
  ('Tunnel-Type', 'Tunnel-Type', 'IPsec',   'VLAN Configuration');

-- =============================================================================
-- Enumerated values for Tunnel-Medium-Type
-- =============================================================================
INSERT IGNORE INTO dictionary (Type, Attribute, Value, Vendor)
VALUES
  ('Tunnel-Medium-Type', 'Tunnel-Medium-Type', 'IEEE-802',  'VLAN Configuration'),
  ('Tunnel-Medium-Type', 'Tunnel-Medium-Type', 'IP',        'VLAN Configuration'),
  ('Tunnel-Medium-Type', 'Tunnel-Medium-Type', 'PPPoE',     'VLAN Configuration');

-- =============================================================================
-- Enumerated values for Simultaneous-Use (helper: dropdown with common values)
-- =============================================================================
INSERT IGNORE INTO dictionary (Type, Attribute, Value, Vendor)
VALUES
  ('Simultaneous-Use', 'Simultaneous-Use', '1',  'VLAN Configuration'),
  ('Simultaneous-Use', 'Simultaneous-Use', '2',  'VLAN Configuration'),
  ('Simultaneous-Use', 'Simultaneous-Use', '3',  'VLAN Configuration'),
  ('Simultaneous-Use', 'Simultaneous-Use', '4',  'VLAN Configuration'),
  ('Simultaneous-Use', 'Simultaneous-Use', '5',  'VLAN Configuration'),
  ('Simultaneous-Use', 'Simultaneous-Use', '10', 'VLAN Configuration');
