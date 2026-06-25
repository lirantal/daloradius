#!/bin/bash

# daloRADIUS - RADIUS Web Platform
# Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
#
#
# Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
#


# Set default values for variables
ENABLE_COLORS=true
DB_HOST=localhost
DB_PORT=3306
DALORADIUS_USERS_PORT=80
DALORADIUS_OPERATORS_PORT=8000
DALORADIUS_ROOT_DIRECTORY=/var/www/daloradius
DALORADIUS_CONF_FILE="${DALORADIUS_ROOT_DIRECTORY}/app/common/includes/daloradius.conf.php"
DALORADIUS_SERVER_ADMIN=admin@daloradius.local
FREERADIUS_SQL_MOD_PATH="/etc/freeradius/3.0/mods-available/sql"
FREERADIUS_SQLCOUNTER_MOD_PATH="/etc/freeradius/3.0/mods-available/sqlcounter"
FREERADIUS_DEFAULT_SITE_PATH="/etc/freeradius/3.0/sites-available/default"

# Function to print an OK message in green
print_green() {
    echo -e "${GREEN}$1${NC}"
}

# Function to print a KO message in red
print_red() {
    echo -e "${RED}$1${NC}"
}

# Function to print a warning message in yellow
print_yellow() {
    echo -e "${YELLOW}$1${NC}"
}

# Function to print an info message in blue
print_blue() {
    echo -e "${BLUE}$1${NC}"
}

print_spinner() {
    PID=$1

    i=1
    sp="/-\|"
    echo -n ' '
    while [ -d /proc/$PID ]; do
        printf "\b${sp:i++%${#sp}:1}"
        sleep 0.1
    done
    printf "\b"
}

mariadb_init_conf() {
    echo -n "[+] Initializing MariaDB configuration... "
    MARIADB_CLIENT_FILENAME="$(mktemp -qu).conf"
    if ! cat << EOF > "${MARIADB_CLIENT_FILENAME}"
[client]
database=${DB_SCHEMA}
host=${DB_HOST}
port=${DB_PORT}
user=${DB_USER}
password=${DB_PASS}
EOF
    then
        print_red "KO"
        echo "[!] Failed to initialize MariaDB configuration. Aborting." >&2
        exit 1
    fi
    print_green "OK"
}


mariadb_clean_conf() {
    echo -n "[+] Cleaning up MariaDB configuration... "
    if [ -e "${MARIADB_CLIENT_FILENAME}" ]; then
        rm -rf "${MARIADB_CLIENT_FILENAME}"
    fi
    print_green "OK"
}

# Function to generate a random string of specified length
generate_random_string() {
    local length="$1"
    cat /dev/random | tr -dc 'A-Za-z0-9' | head -c"$length"
}

# Function to ensure the script is run as root
system_ensure_root() {
  if [ "$(id -u)" -ne 0 ]; then
    if command -v sudo >/dev/null 2>&1; then
      print_red "[!] This script needs to be run as root. Elevating script to root with sudo."
      interpreter="$(head -1 "$0" | cut -c 3-)"
      if [ -x "$interpreter" ]; then
        sudo "$interpreter" "$0" "$@"
      else
        sudo "$0" "$@"
      fi
      exit $?
    else
      print_red "[!] This script needs to be run as root."
      exit 1
    fi
  fi
}

# Function to ensure systemd can run services that protect kernel tunables.
# Some container environments, such as Proxmox LXC containers without the
# nesting feature enabled, cannot set up the mount namespace required by the
# Debian FreeRADIUS unit (ProtectKernelTunables=true). Detect this before the
# installer mutates the system and leaves a partial installation behind.
system_check_service_namespace_support() {
    echo -n "[+] Checking systemd service namespace support... "

    if ! command -v systemd-run >/dev/null 2>&1; then
        print_red "KO"
        echo "[!] systemd-run is required to validate service namespace support. Aborting." >&2
        exit 1
    fi

    if ! systemd-run --wait --collect --quiet -p ProtectKernelTunables=yes /bin/true >/dev/null 2>&1; then
        print_red "KO"
        echo "[!] This system cannot run services with protected kernel tunables." >&2
        echo "[!] In Proxmox LXC containers, enable the nesting feature or provide write access to /proc/sys before running this installer." >&2
        exit 1
    fi

    print_green "OK"
}

# Function to install necessary system packages and perform system update
system_update() {
    echo -n "[+] Updating system package lists... "

    apt update >/dev/null 2>&1 &
    print_spinner $!
    wait $!
    if [ $? -ne 0 ]; then
        echo "KO"
            echo "[!] Failed to update package lists. Aborting." >&2
            exit 1
    fi
    print_green "OK"

    echo -n "[+] Upgrading system packages... "
    apt dist-upgrade -y >/dev/null 2>&1 &
    print_spinner $!
    wait $!
    if [ $? -ne 0 ]; then
        print_red "KO"
        echo "[!] Failed to upgrade system packages. Aborting." >&2
        exit 1
    fi
    print_green "OK"
}

# Function to install MariaDB
mariadb_install() {
    echo -n "[+] Installing MariaDB... "
    apt --no-install-recommends install mariadb-server mariadb-client -y >/dev/null 2>&1 &
    print_spinner $!
    wait $!
    if [ $? -ne 0 ]; then
        print_red "KO"
        echo "[!] Failed to install MariaDB. Aborting." >&2
        exit 1
    fi
    print_green "OK"
}

# Function to secure MariaDB installation
mariadb_secure() {
    echo -n "[+] Securing MariaDB... "
    if ! mariadb -u root <<SQL >/dev/null 2>&1
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
ALTER USER root@'localhost' IDENTIFIED BY '';
DELETE FROM mysql.user WHERE User='';
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
SQL
    then
        print_red "KO"
        echo "[!] Failed to secure MariaDB. Aborting." >&2
        exit 1
    fi
    print_green "OK"
}


# Function to initialize MariaDB database and user
mariadb_db_init() {
    echo -n "[+] Initializing MariaDB database and user... "
    if ! mariadb -u root <<SQL >/dev/null 2>&1
CREATE DATABASE ${DB_SCHEMA};
GRANT ALL ON ${DB_SCHEMA}.* TO '${DB_USER}'@'${DB_HOST}' IDENTIFIED BY '${DB_PASS}';
FLUSH PRIVILEGES;
SQL
    then
        print_red "KO"
        echo "[!] Failed to init MariaDB. Aborting." >&2
        exit 1
    fi
    print_green "OK"
}

# Function to install freeRADIUS
freeradius_install() {
    echo -n "[+] Installing freeRADIUS... "
    apt --no-install-recommends install freeradius freeradius-common freeradius-mysql -y >/dev/null 2>&1 &
    print_spinner $!
    wait $!
    if [ $? -ne 0 ]; then
        print_red "KO"
        echo "[!] Failed to install freeRADIUS. Aborting." >&2
        exit 1
    fi
    print_green "OK"
}

# Function to set up freeRADIUS SQL module
freeradius_setup_sql_mod() {
    echo -n "[+] Setting up freeRADIUS SQL module... "
    if ! sed -Ei '/^[\t\s#]*tls\s+\{/, /[\t\s#]*\}/ s/^/#/' "${FREERADIUS_SQL_MOD_PATH}" >/dev/null 2>&1 || \
       ! sed -Ei 's/^[\t\s#]*dialect\s+=\s+.*$/\tdialect = "mysql"/g' "${FREERADIUS_SQL_MOD_PATH}" >/dev/null 2>&1 || \
       ! sed -Ei 's/^[\t\s#]*driver\s+=\s+"rlm_sql_null"/\tdriver = "rlm_sql_\${dialect}"/g' "${FREERADIUS_SQL_MOD_PATH}" >/dev/null 2>&1 || \
       ! sed -Ei "s/^[\t\s#]*server\s+=\s+\"localhost\"/\tserver = \"${DB_HOST}\"/g" "${FREERADIUS_SQL_MOD_PATH}" >/dev/null 2>&1 || \
       ! sed -Ei "s/^[\t\s#]*port\s+=\s+[0-9]+/\tport = ${DB_PORT}/g" "${FREERADIUS_SQL_MOD_PATH}" >/dev/null 2>&1 || \
       ! sed -Ei "s/^[\t\s#]*login\s+=\s+\"radius\"/\tlogin = \"${DB_USER}\"/g" "${FREERADIUS_SQL_MOD_PATH}" >/dev/null 2>&1 || \
       ! sed -Ei "s/^[\t\s#]*password\s+=\s+\"radpass\"/\tpassword = \"${DB_PASS}\"/g" "${FREERADIUS_SQL_MOD_PATH}" >/dev/null 2>&1 || \
       ! sed -Ei "s/^[\t\s#]*radius_db\s+=\s+\"radius\"/\tradius_db = \"${DB_SCHEMA}\"/g" "${FREERADIUS_SQL_MOD_PATH}" >/dev/null 2>&1 || \
       ! sed -Ei 's/^[\t\s#]*read_clients\s+=\s+.*$/\tread_clients = yes/g' "${FREERADIUS_SQL_MOD_PATH}" >/dev/null 2>&1 || \
       ! sed -Ei 's/^[\t\s#]*client_table\s+=\s+.*$/\tclient_table = "nas"/g' "${FREERADIUS_SQL_MOD_PATH}" >/dev/null 2>&1 || \
       ! ln -s "${FREERADIUS_SQL_MOD_PATH}" /etc/freeradius/3.0/mods-enabled/ >/dev/null 2>&1; then
        print_red "KO"
        echo "[!] Failed to set up freeRADIUS SQL module. Aborting." >&2
        exit 1
    fi
    print_green "OK"
}


# Function to set up freeRADIUS SQL counter module for Max-All-Session enforcement
freeradius_setup_sqlcounter_mod() {
    echo -n "[+] Setting up freeRADIUS SQL counter module... "

    local freeradius_default_tmp
    freeradius_default_tmp=$(mktemp) || {
        print_red "KO"
        echo "[!] Failed to create temporary file. Aborting." >&2
        exit 1
    }

    if ! sed -Ei 's/^[[:space:]#]*dialect[[:space:]]+=[[:space:]]+.*$/	dialect = "mysql"/g' "${FREERADIUS_SQLCOUNTER_MOD_PATH}" >/dev/null 2>&1; then
        print_red "KO"
        echo "[!] Failed to configure freeRADIUS SQL counter module. Aborting." >&2
        exit 1
    fi

    if [ ! -e /etc/freeradius/3.0/mods-enabled/sqlcounter ]; then
        if ! ln -s "${FREERADIUS_SQLCOUNTER_MOD_PATH}" /etc/freeradius/3.0/mods-enabled/ >/dev/null 2>&1; then
            print_red "KO"
            echo "[!] Failed to enable freeRADIUS SQL counter module. Aborting." >&2
            exit 1
        fi
    fi

    if ! grep -q "^[[:space:]]*noresetcounter[[:space:]]*$" "${FREERADIUS_DEFAULT_SITE_PATH}"; then
        if ! awk '
            BEGIN { in_authorize = 0; added = 0 }
            /^authorize[[:space:]]*[{]/ { in_authorize = 1 }
            in_authorize && !added && /^[[:space:]]*-sql$/ {
                print
                print "	noresetcounter"
                added = 1
                next
            }
            /^authenticate[[:space:]]*[{]/ { in_authorize = 0 }
            { print }
            END { exit added ? 0 : 1 }
        ' "${FREERADIUS_DEFAULT_SITE_PATH}" > "${freeradius_default_tmp}"; then
            rm -f "${freeradius_default_tmp}"
            print_red "KO"
            echo "[!] Failed to add noresetcounter to freeRADIUS authorize section. Aborting." >&2
            exit 1
        fi
        mv "${freeradius_default_tmp}" "${FREERADIUS_DEFAULT_SITE_PATH}"
    else
        rm -f "${freeradius_default_tmp}"
    fi

    print_green "OK"
}


# Function to set up SQL-backed session tracking for Simultaneous-Use
freeradius_setup_sql_session_tracking() {
    echo -n "[+] Setting up freeRADIUS SQL session tracking... "

    local freeradius_default_tmp
    freeradius_default_tmp=$(mktemp) || {
        print_red "KO"
        echo "[!] Failed to create temporary file. Aborting." >&2
        exit 1
    }

    if ! awk '
        BEGIN { in_session = 0; in_post_auth = 0; session_sql = 0; sql_session_start = 0 }
        /^session[[:space:]]*[{]/ { in_session = 1 }
        in_session && /^[[:space:]]*#[[:space:]]*sql[[:space:]]*$/ {
            print "	sql"
            session_sql = 1
            next
        }
        in_session && /^[[:space:]]*sql[[:space:]]*$/ { session_sql = 1 }
        in_session && /^}/ { in_session = 0 }

        /^post-auth[[:space:]]*[{]/ { in_post_auth = 1 }
        in_post_auth && /^[[:space:]]*#[[:space:]]*sql_session_start[[:space:]]*$/ {
            print "	sql_session_start"
            sql_session_start = 1
            next
        }
        in_post_auth && /^[[:space:]]*sql_session_start[[:space:]]*$/ { sql_session_start = 1 }
        in_post_auth && /^}/ { in_post_auth = 0 }

        { print }
        END { exit (session_sql && sql_session_start) ? 0 : 1 }
    ' "${FREERADIUS_DEFAULT_SITE_PATH}" > "${freeradius_default_tmp}"; then
        rm -f "${freeradius_default_tmp}"
        print_red "KO"
        echo "[!] Failed to enable SQL session tracking in freeRADIUS. Aborting." >&2
        exit 1
    fi

    mv "${freeradius_default_tmp}" "${FREERADIUS_DEFAULT_SITE_PATH}"
    print_green "OK"
}


# Function to enforce daloRADIUS group/profile NAS restrictions in freeRADIUS
freeradius_setup_group_nas_restrictions() {
    echo -n "[+] Setting up freeRADIUS group NAS restriction policy... "

    local freeradius_default_tmp
    freeradius_default_tmp=$(mktemp) || {
        print_red "KO"
        echo "[!] Failed to create temporary file. Aborting." >&2
        exit 1
    }

    if grep -q "daloRADIUS group NAS restriction policy" "${FREERADIUS_DEFAULT_SITE_PATH}"; then
        rm -f "${freeradius_default_tmp}"
        print_green "OK"
        return
    fi

    if ! awk '
        BEGIN { added = 0 }
        {
            print
            if (!added && /^[[:space:]]*noresetcounter[[:space:]]*$/) {
                print ""
                print "\t\t# daloRADIUS group NAS restriction policy"
                print "\t\t# Enforce radgroupcheck NAS-IP-Address == restrictions as an"
                print "\t\t# authentication deny rule for users assigned to SQL groups."
                print "\t\tif (&request:NAS-IP-Address) {"
                print "\t\t\tif (\"%{sql:SELECT COUNT(*) FROM radusergroup ug JOIN radgroupcheck gc ON gc.groupname = ug.groupname WHERE ug.username = '\''%{User-Name}'\'' AND gc.attribute = '\''NAS-IP-Address'\'' AND gc.op = '\''=='\''}\" != \"0\") {"
                print "\t\t\t\tif (\"%{sql:SELECT COUNT(*) FROM radusergroup ug JOIN radgroupcheck gc ON gc.groupname = ug.groupname WHERE ug.username = '\''%{User-Name}'\'' AND gc.attribute = '\''NAS-IP-Address'\'' AND gc.op = '\''=='\'' AND gc.value = '\''%{NAS-IP-Address}'\''}\" == \"0\") {"
                print "\t\t\t\t\treject"
                print "\t\t\t\t}"
                print "\t\t\t}"
                print "\t\t}"
                added = 1
            }
        }
        END { exit added ? 0 : 1 }
    ' "${FREERADIUS_DEFAULT_SITE_PATH}" > "${freeradius_default_tmp}"; then
        rm -f "${freeradius_default_tmp}"
        print_red "KO"
        echo "[!] Failed to add daloRADIUS group NAS restriction policy to freeRADIUS authorize section. Aborting." >&2
        exit 1
    fi

    mv "${freeradius_default_tmp}" "${FREERADIUS_DEFAULT_SITE_PATH}"
    print_green "OK"
}

# Function to restart freeRADIUS service
freeradius_enable_restart() {
    echo -n "[+] Enabling and restarting freeRADIUS... "
    if ! systemctl enable freeradius.service  >/dev/null 2>&1 || ! systemctl restart freeradius.service >/dev/null 2>&1; then
        print_red "KO"
        echo "[!] Failed to enable and restart freeRADIUS. Aborting." >&2
        exit 1
    fi
    print_green "OK"
}

# Function to install daloRADIUS and required packages
daloradius_install_dep() {
    echo -n "[+] Installing daloRADIUS dependencies... "
    apt --no-install-recommends install apache2 php libapache2-mod-php php-mysql php-zip php-mbstring php-common php-curl \
                                        php-gd php-db php-mail php-mail-mime freeradius-utils git rsyslog -y >/dev/null 2>&1 &
    print_spinner $!
    wait $!
    if [ $? -ne 0 ]; then
        print_red "KO"
        print_red "[!] Failed to install daloRADIUS dependencies. Aborting." >&2
        exit 1
    fi
    print_green "OK"
}

# Function to install daloRADIUS
daloradius_installation() {
    SCRIPT_PATH=$(realpath $0)
    SCRIPT_DIR=$(dirname ${SCRIPT_PATH})

    if [ "${SCRIPT_DIR}" = "${DALORADIUS_ROOT_DIRECTORY}/setup" ]; then
        # local installation
        echo -n "[+] Setting up daloRADIUS... "

        if [ ! -f "${DALORADIUS_CONF_FILE}.sample" ]; then
            print_red "KO"
            print_red "[!] daloRADIUS code seems to be corrupted. Aborting." >&2
            exit 1
        fi

    else
        # remote installation
        echo -n "[+] Downloading and setting up daloRADIUS... "
        if [ -d "${DALORADIUS_ROOT_DIRECTORY}" ]; then
            print_red "KO"
            print_red "[!] Directory ${DALORADIUS_ROOT_DIRECTORY} already exists. Aborting." >&2
            exit 1
        fi

        git clone https://github.com/lirantal/daloradius.git "${DALORADIUS_ROOT_DIRECTORY}" >/dev/null 2>&1 &
        print_spinner $!
        wait $!
        if [ $? -ne 0 ]; then
            print_red "KO"
            print_red "[!] Failed to clone daloRADIUS repository. Aborting." >&2
            exit 1
        fi

    fi

    print_green "OK"
}

# Function to create required directories for daloRADIUS
daloradius_setup_required_dirs() {
    echo -n "[+] Creating required directories for daloRADIUS... "

    if ! mkdir -p /var/log/apache2/daloradius/{operators,users} >/dev/null 2>&1; then
        print_red "KO"
        print_red "[!] Failed to create operators and users directories. Aborting." >&2
        exit 1
    fi

    if ! mkdir -p ${DALORADIUS_ROOT_DIRECTORY}/var/{log,backup} >/dev/null 2>&1; then
        print_red "KO"
        print_red "[!] Failed to create log and backup directories. Aborting." >&2
        exit 1
    fi

    if ! chown -R www-data:www-data ${DALORADIUS_ROOT_DIRECTORY}/var >/dev/null 2>&1; then
        print_red "KO"
        print_red "[!] Failed to change ownership of var directory. Aborting." >&2
        exit 1
    fi

    if ! chmod -R 775 ${DALORADIUS_ROOT_DIRECTORY}/var >/dev/null 2>&1; then
        print_red "KO"
        print_red "[!] Failed to change permissions of var directory. Aborting." >&2
        exit 1
    fi

    print_green "OK"
}

# Function to set up daloRADIUS
daloradius_setup_required_files() {
    echo -n "[+] Setting up daloRADIUS... "
    DALORADIUS_CONF_FILE="${DALORADIUS_ROOT_DIRECTORY}/app/common/includes/daloradius.conf.php"

    if ! cp "${DALORADIUS_CONF_FILE}.sample" "${DALORADIUS_CONF_FILE}" >/dev/null 2>&1; then
        print_red "KO"
        print_red "[!] Failed to copy sample configuration file. Aborting." >&2
        exit 1
    fi

    ( sed -Ei "s/^.*CONFIG_DB_HOST'\].*$/\$configValues['CONFIG_DB_HOST'] = '${DB_HOST}';/" "${DALORADIUS_CONF_FILE}" >/dev/null 2>&1 && \
      sed -Ei "s/^.*CONFIG_DB_PORT'\].*$/\$configValues['CONFIG_DB_PORT'] = '${DB_PORT}';/" "${DALORADIUS_CONF_FILE}" >/dev/null 2>&1 && \
      sed -Ei "s/^.*CONFIG_DB_USER'\].*$/\$configValues['CONFIG_DB_USER'] = '${DB_USER}';/" "${DALORADIUS_CONF_FILE}" >/dev/null 2>&1 && \
      sed -Ei "s/^.*CONFIG_DB_PASS'\].*$/\$configValues['CONFIG_DB_PASS'] = '${DB_PASS}';/" "${DALORADIUS_CONF_FILE}" >/dev/null 2>&1 && \
      sed -Ei "s/^.*CONFIG_DB_NAME'\].*$/\$configValues['CONFIG_DB_NAME'] = '${DB_SCHEMA}';/" "${DALORADIUS_CONF_FILE}" >/dev/null 2>&1 ) &
    print_spinner $!
    wait $!
    if [ $? -ne 0 ]; then
        print_red "KO"
        print_red "[!] Failed to setup daloRADIUS configuration file. Aborting." >&2
        exit 1
    fi

    if ! chown www-data:www-data "${DALORADIUS_CONF_FILE}" >/dev/null 2>&1; then
        print_red "KO"
        print_red "[!] Failed to change ownership of configuration file. Aborting." >&2
        exit 1
    fi

    if ! chmod 664 "${DALORADIUS_CONF_FILE}" >/dev/null 2>&1; then
        print_red "KO"
        print_red "[!] Failed to change permissions of configuration file. Aborting." >&2
        exit 1
    fi

    if ! chown www-data:www-data ${DALORADIUS_ROOT_DIRECTORY}/contrib/scripts/dalo-crontab >/dev/null 2>&1; then
        print_red "KO"
        print_red "[!] Failed to change ownership of dalo-crontab script. Aborting." >&2
        exit 1
    fi

    print_green "OK"
}

# Function to disable all Apache sites
apache_disable_all_sites() {
    echo -n "[+] Disabling all Apache sites... "
    find /etc/apache2/sites-enabled/ -type l -exec rm "{}" \; >/dev/null 2>&1 &
    print_spinner $!
    wait $!
    if [ $? -ne 0 ]; then
        print_red "KO"
        print_red "[!] Failed to disable all Apache sites. Aborting." >&2
        exit 1
    fi
    print_green "OK"
}

# Function to set up Apache environment variables for daloRADIUS
apache_setup_envvars() {
    echo -n "[+] Setting up Apache environment variables for daloRADIUS... "
    cat <<EOF >> /etc/apache2/envvars
# daloRADIUS users interface port
export DALORADIUS_USERS_PORT=${DALORADIUS_USERS_PORT}

# daloRADIUS operators interface port
export DALORADIUS_OPERATORS_PORT=${DALORADIUS_OPERATORS_PORT}

# daloRADIUS package root directory
export DALORADIUS_ROOT_DIRECTORY=${DALORADIUS_ROOT_DIRECTORY}

# daloRADIUS administrator's email
export DALORADIUS_SERVER_ADMIN=${DALORADIUS_SERVER_ADMIN}
EOF
    if [ $? -ne 0 ]; then
        print_red "KO"
        print_red "[!] Failed to set up Apache environment variables for daloRADIUS. Aborting." >&2
        exit 1
    fi
    print_green "OK"
}

# Function to set up Apache ports for daloRADIUS
apache_setup_ports() {
    echo -n "[+] Setting up Apache ports for daloRADIUS... "
    cat <<EOF > /etc/apache2/ports.conf
# daloRADIUS
Listen \${DALORADIUS_USERS_PORT}
Listen \${DALORADIUS_OPERATORS_PORT}
EOF
    if [ $? -ne 0 ]; then
        print_red "KO"
        print_red "[!] Failed to set up Apache ports for daloRADIUS. Aborting." >&2
        exit 1
    fi
    print_green "OK"
}

# Function to set up Apache site for operators
apache_setup_operators_site() {
    echo -n "[+] Setting up Apache site for operators... "

    cat <<EOF > /etc/apache2/sites-available/operators.conf
<VirtualHost *:\${DALORADIUS_OPERATORS_PORT}>
  ServerAdmin \${DALORADIUS_SERVER_ADMIN}
  DocumentRoot \${DALORADIUS_ROOT_DIRECTORY}/app/operators

  <Directory \${DALORADIUS_ROOT_DIRECTORY}/app/operators>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
  </Directory>

  <Directory \${DALORADIUS_ROOT_DIRECTORY}>
    Require all denied
  </Directory>

  ErrorLog \${APACHE_LOG_DIR}/daloradius/operators/error.log
  CustomLog \${APACHE_LOG_DIR}/daloradius/operators/access.log combined
</VirtualHost>
EOF
    if [ $? -ne 0 ]; then
        print_red "KO"
        print_red "[!] Failed to init operators site. Aborting." >&2
        exit 1
    fi

    if ! a2ensite operators.conf >/dev/null 2>&1; then
        print_red "KO"
        print_red "[!] Failed to enable operators site. Aborting." >&2
        exit 1
    fi

    print_green "OK"
}

# Function to set up Apache site for users
apache_setup_users_site() {
    echo -n "[+] Setting up Apache site for users... "

    cat <<EOF > /etc/apache2/sites-available/users.conf
<VirtualHost *:\${DALORADIUS_USERS_PORT}>
  ServerAdmin \${DALORADIUS_SERVER_ADMIN}
  DocumentRoot \${DALORADIUS_ROOT_DIRECTORY}/app/users

  <Directory \${DALORADIUS_ROOT_DIRECTORY}/app/users>
    Options -Indexes +FollowSymLinks
    AllowOverride None
    Require all granted
  </Directory>

  <Directory \${DALORADIUS_ROOT_DIRECTORY}>
    Require all denied
  </Directory>

  ErrorLog \${APACHE_LOG_DIR}/daloradius/users/error.log
  CustomLog \${APACHE_LOG_DIR}/daloradius/users/access.log combined
</VirtualHost>
EOF
    if [ $? -ne 0 ]; then
        print_red "KO"
        print_red "[!] Failed to init users site. Aborting." >&2
        exit 1
    fi

    if ! a2ensite users.conf >/dev/null 2>&1; then
        print_red "KO"
        print_red "[!] Failed to enable users site. Aborting." >&2
        exit 1
    fi

    print_green "OK"
}

# Function to enable and restart Apache
apache_enable_restart() {
    echo -n "[+] Enabling and restarting Apache... "
    if ! systemctl enable apache2.service  >/dev/null 2>&1 || ! systemctl restart apache2.service >/dev/null 2>&1; then
        print_red "KO"
        echo "[!] Failed to enable and restart Apache. Aborting." >&2
        exit 1
    fi
    print_green "OK"
}

# Function to load daloRADIUS SQL schema into MariaDB
daloradius_load_sql_schema() {
    DB_DIR="${DALORADIUS_ROOT_DIRECTORY}/contrib/db"
    echo -n "[+] Loading daloRADIUS SQL schemas into MariaDB... "

    if ! mariadb --defaults-extra-file="${MARIADB_CLIENT_FILENAME}" < "${DB_DIR}/fr3-mariadb-freeradius.sql" >/dev/null 2>&1; then
        print_red "KO"
        print_red "[!] Failed to load FreeRADIUS base schema into MariaDB. Aborting." >&2
        exit 1
    fi

    if ! mariadb --defaults-extra-file="${MARIADB_CLIENT_FILENAME}" < "${DB_DIR}/mariadb-daloradius.sql" >/dev/null 2>&1; then
        print_red "KO"
        print_red "[!] Failed to load daloRADIUS dictionaries into MariaDB. Aborting." >&2
        exit 1
    fi

    if ! mariadb --defaults-extra-file="${MARIADB_CLIENT_FILENAME}" < "${DB_DIR}/mariadb-daloradius-dictionaries.sql" >/dev/null 2>&1; then
        print_red "KO"
        print_red "[!] Failed to load daloRADIUS base schema into MariaDB. Aborting." >&2
        exit 1
    fi

    for f in "${DB_DIR}"/migrations/*.sql; do
        [ -e "$f" ] || continue

        if ! mariadb --defaults-extra-file="${MARIADB_CLIENT_FILENAME}" < "$f" >/dev/null 2>&1; then
            print_red "KO"
            print_red "[!] Failed to load daloRADIUS migration schema ${f} into MariaDB. Aborting." >&2
            exit 1
        fi
    done

    if ! mariadb --defaults-extra-file="${MARIADB_CLIENT_FILENAME}" < "${DB_DIR}/update-performance-indexes.sql" >/dev/null 2>&1; then
        print_red "KO"
        print_red "[!] Failed to load daloRADIUS performance indexes into MariaDB. Aborting." >&2
        exit 1
    fi

    print_green "OK"
}

system_finalize() {
    INIT_USERNAME="administrator"
    INIT_PASSWORD=$(generate_random_string 12)
    SQL="UPDATE operators SET password='${INIT_PASSWORD}' WHERE username='${INIT_USERNAME}'"
    if ! mariadb --defaults-extra-file="${MARIADB_CLIENT_FILENAME}" --execute="${SQL}" >/dev/null 2>&1; then
        INIT_PASSWORD="radius"
        print_yellow "[!] Failed to update ${INIT_USERNAME}'s default password"
    fi

    echo -e "[+] ${GREEN}daloRADIUS${NC} has been installed."
    echo -e "    ${BLUE}Here are some installation details:${NC}"
    echo -e "      - DB hostname: ${BLUE}${DB_HOST}${NC}"
    echo -e "      - DB port: ${BLUE}${DB_PORT}${NC}"
    echo -e "      - DB username: ${BLUE}${DB_USER}${NC}"
    echo -e "      - DB password: ${BLUE}${DB_PASS}${NC}"
    echo -e "      - DB schema: ${BLUE}${DB_SCHEMA}${NC}"

    echo -e "    Users' dashboard can be reached via ${BLUE}HTTP${NC} on port ${BLUE}${DALORADIUS_USERS_PORT}${NC}."
    echo -e "    Operators' dashboard can be reached via ${BLUE}HTTP${NC} on port ${BLUE}${DALORADIUS_OPERATORS_PORT}${NC}."
    echo -e "    To log into the ${BLUE}operators' dashboard${NC}, use the following credentials:"
    echo -e "      - Username: ${BLUE}${INIT_USERNAME}${NC}"
    echo -e "      - Password: ${BLUE}${INIT_PASSWORD}${NC}"
}

# Main function calling other functions in the correct order
main() {
    system_ensure_root
    system_check_service_namespace_support
    system_update

    mariadb_install
    mariadb_secure
    mariadb_db_init
    mariadb_init_conf

    daloradius_install_dep
    daloradius_installation
    daloradius_setup_required_dirs
    daloradius_setup_required_files

    daloradius_load_sql_schema

    freeradius_install
    freeradius_setup_sql_mod
    freeradius_setup_sqlcounter_mod
    freeradius_setup_sql_session_tracking
    freeradius_setup_group_nas_restrictions
    freeradius_enable_restart

    apache_disable_all_sites
    apache_setup_envvars
    apache_setup_ports
    apache_setup_operators_site
    apache_setup_users_site
    apache_enable_restart

    system_finalize
    mariadb_clean_conf
}

# Parsing command line options
while getopts ":u:p:h:P:s:c" opt; do
  case $opt in
    u) DB_USER="$OPTARG" ;;
    p) DB_PASS="$OPTARG" ;;
    h) DB_HOST="$OPTARG" ;;
    P) DB_PORT="$OPTARG" ;;
    s) DB_SCHEMA="$OPTARG" ;;
    c) ENABLE_COLORS=false ;;
    \?) echo "Invalid option -$OPTARG" >&2; exit 1 ;;
  esac
done

# Generate a random username if not provided
if [ -z "$DB_USER" ]; then
    prefix="user_"
    random_string=$(generate_random_string 6)
    DB_USER="${prefix}${random_string}"
fi

# Generate a random password if not provided
if [ -z "$DB_PASS" ]; then
    DB_PASS=$(generate_random_string 12)
fi

# Generate a random scheme if not provided
if [ -z "$DB_SCHEMA" ]; then
    prefix="radius_"
    random_string=$(generate_random_string 6)
    DB_SCHEMA="${prefix}${random_string}"
fi

# Define color codes
if $ENABLE_COLORS; then
    GREEN='\033[0;32m'
    RED='\033[0;31m'
    YELLOW='\033[1;33m'
    BLUE='\033[0;34m'
    NC='\033[0m' # No Color
else
    GREEN=''
    RED=''
    YELLOW=''
    BLUE=''
    NC=''
fi

# Call the main function to start the installation process
main
