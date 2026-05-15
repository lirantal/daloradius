# Disabled users group priority

The reserved `daloRADIUS-Disabled-Users` group disables a user by applying
`Auth-Type := Reject` from `radgroupcheck`.

FreeRADIUS evaluates a user's groups by `radusergroup.priority` in ascending
order. When a user belongs to multiple groups, the disabled-users group must be
evaluated before the normal service/profile groups. Otherwise another group can
be processed first and the disabled-users reject rule may not be reached.

For this reason, daloRADIUS now locks `daloRADIUS-Disabled-Users` user mappings
to priority `-1`. Normal user-group mappings continue to use priority `0` or
higher.

## Existing installations

This change does not modify existing `radusergroup` rows automatically. If users
were manually associated with `daloRADIUS-Disabled-Users` before this change,
verify that those mappings have top priority when the user belongs to multiple
groups. In practice, the disabled-users mapping should have a lower priority
number than the user's other groups, such as `-1`.
