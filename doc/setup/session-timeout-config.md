# Configuring the Web Session Timeout for daloRADIUS

This guide explains how the web session timeout works in current versions of daloRADIUS and how to adjust it. It addresses a common question from administrators upgrading from older releases where `$configValues['session_timeout']` was available in `daloradius.conf.php`.

> **Note:** The `$configValues['session_timeout']` setting has been removed. Session management is now handled entirely in dedicated PHP files, as described below.

## Background

In previous releases of daloRADIUS, the session timeout could be configured through the `$configValues['session_timeout']` variable in `daloradius.conf.php`. This setting no longer exists. Session lifetime is now managed by a dedicated session management layer that was introduced to improve security (CSRF protection, strict session mode, custom session naming).

## How It Works

Session management is implemented in two separate files — one for each interface:

| Interface | File |
|-----------|------|
| Operators | `app/operators/library/sessions.php` |
| Users | `app/users/library/sessions.php` |

Each file defines a `dalo_session_start()` function that:

1. Sets the maximum session lifetime via `session.gc_maxlifetime`.
2. Enables PHP strict session mode (`session.use_strict_mode`).
3. Assigns a custom session cookie name (`daloradius_operator_sid` or `daloradius_user_sid`).
4. Records a `time` timestamp in the session data when the session is first created and destroys the session once the elapsed time since that timestamp exceeds the configured lifetime. This is an **absolute timeout** — user activity does not reset the timer.

The default value is **3600 seconds** (1 hour).

## Changing the Session Timeout

To change the session timeout, edit the `$session_max_lifetime` variable at the top of the `dalo_session_start()` function in both files.

### 1. Operators Interface

Open `app/operators/library/sessions.php` and locate the following line inside `dalo_session_start()`:

```php
$session_max_lifetime = 3600;
```

Change `3600` to the desired value in seconds. For example, to set a 2-hour timeout:

```php
$session_max_lifetime = 7200;
```

### 2. Users Interface

Open `app/users/library/sessions.php` and make the same change:

```php
$session_max_lifetime = 7200;
```

> **Note:** Both files must be updated independently. The operators and users interfaces use separate session configurations.

## Common Values

| Timeout | Value (seconds) |
|---------|-----------------|
| 30 minutes | `1800` |
| 1 hour (default) | `3600` |
| 2 hours | `7200` |
| 4 hours | `14400` |
| 8 hours | `28800` |

## Verifying the Configuration

After editing the files, restart Apache (or your web server) to ensure PHP picks up the changes:

```bash
sudo systemctl restart apache2
```

Then log in to the operators or users interface. The session should expire after the configured duration has elapsed since login, regardless of activity.

## Troubleshooting

| Symptom | Possible Cause | Resolution |
|---------|---------------|------------|
| Session expires before the configured time | PHP `session.gc_maxlifetime` is set to a lower value in `php.ini` | Ensure the `php.ini` value for `session.gc_maxlifetime` is equal to or greater than `$session_max_lifetime`. The `ini_set()` call in `sessions.php` overrides it at runtime, but shared hosting environments may restrict this. |
| Session never expires | Browser cookie is set to persist | The session cookie lifetime is set to `0` (browser session) by default. Clear the browser cookie and retest. |
| Changes have no effect | Wrong file edited | Verify you edited the correct `sessions.php` file under the matching interface directory (`operators` or `users`). |

## Additional Notes

- The session timeout is an **absolute timeout**, not an idle/inactivity timeout. The timer starts when the session is created (typically at login) and is not extended by subsequent page loads or API calls. Once the configured duration has elapsed, the next request will destroy the session and require a new login.
- The session timeout applies to the daloRADIUS **web interface** only. It does not affect RADIUS authentication sessions or the `Session-Timeout` RADIUS attribute.
- If you are using a reverse proxy or load balancer, ensure that its own session or connection timeout is not shorter than the value configured in daloRADIUS.

## References

- [GitHub Issue #601 — Web page session time](https://github.com/lirantal/daloradius/issues/601)
- [PHP session.gc_maxlifetime documentation](https://www.php.net/manual/en/session.configuration.php#ini.session.gc-maxlifetime)
