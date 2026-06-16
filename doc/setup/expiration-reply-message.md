# Expiration reply messages

When a user account expires, FreeRADIUS returns an `Access-Reject`. Some NAS or
hotspot devices show only a generic message such as `invalid username or
password` unless the RADIUS response includes a `Reply-Message` attribute.

daloRADIUS can store that message in the user's reply attributes. FreeRADIUS then
includes it in the reject packet, and the NAS can display it when it supports
reject reply messages.

## Use case

This is useful when an expired user should see a clearer message, for example:

```text
The account is expired
```

instead of a generic authentication failure.

## Configure a per-user expiration message

1. Open the operators interface.
2. Edit the user account.
3. Add or verify the check attribute that expires the account:

   | Attribute | Operator | Example value |
   |-----------|----------|---------------|
   | `Expiration` | `:=` | `01 Jan 2020 00:00:00` |

4. Add a reply attribute for the message:

   | Attribute | Operator | Example value |
   |-----------|----------|---------------|
   | `Reply-Message` | `:=` | `The account is expired` |

5. Save the user.

The `Expiration` check attribute decides whether the request is rejected. The
`Reply-Message` reply attribute supplies the text that FreeRADIUS includes in the
RADIUS response.

## SQL example

The same configuration can be applied directly in SQL:

```sql
INSERT INTO radcheck (username, attribute, op, value)
VALUES
  ('alice', 'Cleartext-Password', ':=', 'secret'),
  ('alice', 'Expiration', ':=', '01 Jan 2020 00:00:00');

INSERT INTO radreply (username, attribute, op, value)
VALUES
  ('alice', 'Reply-Message', ':=', 'The account is expired');
```

## Test with radtest

After configuring the expired user and reply message, test the RADIUS response
from a host that has `radtest` available and is allowed as a RADIUS client:

```bash
radtest alice secret 127.0.0.1 0 testing123
```

Replace `alice`, `secret`, `127.0.0.1`, and `testing123` with the username,
password, RADIUS server address, and shared secret for your environment.

The expected response is an `Access-Reject` that contains the configured
`Reply-Message`:

```text
Received Access-Reject ...
    Reply-Message = "The account is expired"
```

## NAS and captive portal behavior

FreeRADIUS can send the `Reply-Message`, but the final user-facing text depends
on the NAS, access point, hotspot, or captive portal. Some devices display the
message as-is. Others ignore reject reply messages and continue to show a generic
login error.

For MikroTik and similar NAS devices, verify the exact behavior with the device's
hotspot or PPP authentication flow after confirming with `radtest` that
FreeRADIUS sends the expected `Reply-Message`.

## Troubleshooting

| Symptom | Possible cause | Resolution |
|---------|----------------|------------|
| `Access-Reject` has no `Reply-Message` | No `radreply` row exists for the user | Add `Reply-Message := <message>` to the user's reply attributes. |
| `radtest` shows the message but the NAS does not | The NAS or captive portal ignores reject reply messages | Check the NAS documentation and test the NAS-specific login flow. |
| The user is accepted instead of rejected | The `Expiration` value is in the future, has an invalid format, or is not evaluated | Use a past date in FreeRADIUS' expected format and verify the user has an `Expiration` check attribute. |
| The message appears for successful logins too | `Reply-Message` is also sent on `Access-Accept` when the user is not rejected | Use wording that is safe for the intended state, or apply the reply message only to users that are expected to be expired. |

## References

- [GitHub Issue #562 — Expiration Reply from radius](https://github.com/lirantal/daloradius/issues/562)
- [FreeRADIUS users file documentation for `Reply-Message`](https://wiki.freeradius.org/config/Users)
