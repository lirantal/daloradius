# Configuring FreeRADIUS proxy.conf Permissions

When managing Realms and Proxies via the daloRADIUS web interface, you may encounter an error stating that the file `/etc/freeradius/3.0/proxy.conf` is not writable. 

While the proxy/realm entry is saved correctly to the database, daloRADIUS requires permission to write the configuration to the actual FreeRADIUS configuration file so the RADIUS server can use it.

## The Secure Solution (ACLs)

It is highly discouraged to use `chmod 777` or to add the web server user (`www-data`) to the `freerad` group. Doing so grants excessive privileges to the web server and creates significant security risks.

The recommended, secure approach is to use POSIX Access Control Lists (ACLs). This grants the web server precise read and write permissions to **only** the specific file it needs, without changing the file's primary owner.

### Step-by-Step Configuration

Run the following commands as `root` (or using `sudo`). *Note: Adjust the path if your FreeRADIUS version is different (e.g., `/etc/freeradius/3.0/`).*

1. **Install the ACL package** (if not already installed):
   ```bash
   apt update
   apt install acl
   ```

2. **Grant the web server traversal access to the directories**:
   The web server needs to be able to traverse the directories to reach the file. We grant execute (`x`) permission to others, which allows the web server to enter the directory without allowing it to read/list the directory contents.
   ```bash
   chmod o+x /etc/freeradius
   chmod o+x /etc/freeradius/3.0
   ```

3. **Grant the web server Read/Write access to proxy.conf**:
   Use `setfacl` to give the `www-data` user read and write permissions on the file itself.
   ```bash
   setfacl -m u:www-data:rw /etc/freeradius/3.0/proxy.conf
   ```

4. **Verify the permissions**:
   You can verify that the ACLs were applied correctly using `getfacl`:
   ```bash
   getfacl /etc/freeradius/3.0/proxy.conf
   ```
   You should see `user:www-data:rw-` in the output.

---
**Note on Package Upgrades:** If the `freeradius` package is updated via your OS package manager, the file permissions might be reset. If the "not writable" error returns after a system update, simply re-run step 3.
