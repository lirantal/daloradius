#!/bin/bash
# Executable process script for daloRADIUS freeradius docker image:
# GitHub: git@github.com:lirantal/daloradius.git
RADIUS_PATH=/etc/freeradius

function init_freeradius {
	# Enable SQL in freeradius
	sed -i 's|driver = "rlm_sql_null"|driver = "rlm_sql_mysql"|' $RADIUS_PATH/mods-available/sql
	sed -i 's|dialect = "sqlite"|dialect = "mysql"|' $RADIUS_PATH/mods-available/sql
	sed -i 's|dialect = ${modules.sql.dialect}|dialect = "mysql"|' $RADIUS_PATH/mods-available/sqlcounter # avoid instantiation error
	sed -i 's|ca_file = "/etc/ssl/certs/my_ca.crt"|#ca_file = "/etc/ssl/certs/my_ca.crt"|' $RADIUS_PATH/mods-available/sql #disable sql encryption
        sed -i 's|ca_path = "/etc/ssl/certs/"|#ca_path = "/etc/ssl/certs/"|' $RADIUS_PATH/mods-available/sql #disable sql encryption
	sed -i 's|certificate_file = "/etc/ssl/certs/private/client.crt"|#certificate_file = "/etc/ssl/certs/private/client.crt"|' $RADIUS_PATH/mods-available/sql #disable sql encryption
	sed -i 's|private_key_file = "/etc/ssl/certs/private/client.key"|#private_key_file = "/etc/ssl/certs/private/client.key"|' $RADIUS_PATH/mods-available/sql #disable sql encryption
	sed -i 's|tls_required = yes|tls_required = no|' $RADIUS_PATH/mods-available/sql #disable sql encryption
	sed -i 's|#\s*read_clients = yes|read_clients = yes|' $RADIUS_PATH/mods-available/sql
	ln -s $RADIUS_PATH/mods-available/sql $RADIUS_PATH/mods-enabled/sql
	ln -s $RADIUS_PATH/mods-available/sqlcounter $RADIUS_PATH/mods-enabled/sqlcounter
	ln -s $RADIUS_PATH/mods-available/sqlippool $RADIUS_PATH/mods-enabled/sqlippool
	enable_noresetcounter
	sed -i 's|instantiate {|instantiate {\nsql|' $RADIUS_PATH/radiusd.conf # mods-enabled does not ensure the right order

	# Enable used tunnel for unifi
	sed -i 's|use_tunneled_reply = no|use_tunneled_reply = yes|' $RADIUS_PATH/mods-available/eap

        # Log authentication request in radius-log file
        sed -i 's|auth = no|auth = yes|' $RADIUS_PATH/radiusd.conf
        # Sane default log setings for authentication
        sed -i 's|#\s*msg_goodpass =.*|msg_goodpass = "authenticationtype:\\\"%{control:Auth-Type}\\\";nasipv4address:\\\"%{request:NAS-IP-Address}\\\";nasipv6address:\\\"%{request:NAS-IPv6-Address}\\\";nasid:\\\"%{request:NAS-Identifier}\\\";srcipaddress:\\\"%{request:Packet-Src-IP-Address}\\\";nasport:\\\"%{request:NAS-Port-Id}\\\";nasporttype:\\\"%{request:NAS-Port-Type}\\\";vlan:\\\"%{reply:Tunnel-Private-Group-Id}\\\";calledstationid:\\\"%{request:Called-Station-Id}\\\";callingstationid:\\\"%{request:Calling-Station-Id}\\\";session_timeout:\\\"%{reply:Session-Timeout}\\\""|' \
        $RADIUS_PATH/radiusd.conf
        sed -i 's|#\s*msg_badpass =.*|msg_badpass = "authenticationtype:\\\"%{control:Auth-Type}\\\";nasipv4address:\\\"%{request:NAS-IP-Address}\\\";nasipv6address:\\\"%{request:NAS-IPv6-Address}\\\";nasid:\\\"%{request:NAS-Identifier}\\\";srcipaddress:\\\"%{request:Packet-Src-IP-Address}\\\";nasport:\\\"%{request:NAS-Port-Id}\\\";nasporttype:\\\"%{request:NAS-Port-Type}\\\";calledstationid:\\\"%{request:Called-Station-Id}\\\";callingstationid:\\\"%{request:Calling-Station-Id}\\\""|' \
        $RADIUS_PATH/radiusd.conf

	# Enable status in freeadius
	ln -s $RADIUS_PATH/sites-available/status $RADIUS_PATH/sites-enabled/status

	# Set Database connection
	sed -i 's|^#\s*server = .*|server = "'$MYSQL_HOST'"|' $RADIUS_PATH/mods-available/sql
	sed -i 's|^#\s*port = .*|port = "'$MYSQL_PORT'"|' $RADIUS_PATH/mods-available/sql
	sed -i '1,$s/radius_db.*/radius_db="'$MYSQL_DATABASE'"/g' $RADIUS_PATH/mods-available/sql
	sed -i 's|^#\s*password = .*|password = "'$MYSQL_PASSWORD'"|' $RADIUS_PATH/mods-available/sql
	sed -i 's|^#\s*login = .*|login = "'$MYSQL_USER'"|' $RADIUS_PATH/mods-available/sql

	if [ -n "$DEFAULT_CLIENT_SECRET" ]; then
		sed -i 's|testing123|'$DEFAULT_CLIENT_SECRET'|' $RADIUS_PATH/mods-available/sql
	fi
	echo "freeradius initialization completed."
}

function enable_noresetcounter {
	# Enforce Max-All-Session limits through FreeRADIUS sqlcounter.
	# The sqlcounter module must run in authorize; enabling mods-enabled/sqlcounter alone is not enough.
	if grep -q "^[[:space:]]*noresetcounter[[:space:]]*$" $RADIUS_PATH/sites-available/default; then
		return
	fi

	if ! awk '
		BEGIN { in_authorize = 0; added = 0 }
		/^authorize[[:space:]]*[{]/ { in_authorize = 1 }
		in_authorize && !added && /^[[:space:]]*-sql$/ {
			print
			print "\tnoresetcounter"
			added = 1
			next
		}
		/^authenticate[[:space:]]*[{]/ { in_authorize = 0 }
		{ print }
		END { exit added ? 0 : 1 }
	' $RADIUS_PATH/sites-available/default > /tmp/freeradius-default; then
		rm -f /tmp/freeradius-default
		echo "Failed to add noresetcounter to FreeRADIUS authorize section."
		exit 1
	fi
	mv /tmp/freeradius-default $RADIUS_PATH/sites-available/default
}


function enable_group_nas_restrictions {
	# Enforce daloRADIUS group/profile NAS restrictions stored in radgroupcheck.
	# FreeRADIUS uses radgroupcheck to decide whether group reply items apply; it
	# does not automatically reject a user who already authenticated via radcheck.
	# This policy rejects users when their SQL group has NAS-IP-Address == rows
	# and the request NAS-IP-Address does not match any of those allowed values.
	if grep -q "daloRADIUS group NAS restriction policy" $RADIUS_PATH/sites-available/default; then
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
	' $RADIUS_PATH/sites-available/default > /tmp/freeradius-default; then
		rm -f /tmp/freeradius-default
		echo "Failed to add daloRADIUS group NAS restriction policy to FreeRADIUS authorize section."
		exit 1
	fi
	mv /tmp/freeradius-default $RADIUS_PATH/sites-available/default
}

function ensure_daloradius_schema {
	mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" <<'EOSQL'
CREATE TABLE IF NOT EXISTS `radhuntgroup` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `nasipaddress` varchar(15) NOT NULL DEFAULT '',
  `nasportid` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nasipaddress` (`nasipaddress`)
);
EOSQL
}

function init_database {
	mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" < $RADIUS_PATH/mods-config/sql/main/mysql/schema.sql
	mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" < $RADIUS_PATH/mods-config/sql/ippool/mysql/schema.sql
	ensure_daloradius_schema

	# Insert a client for the current subnet (to allow daloradius to perform checks)
	IP=`ifconfig eth0 | awk '/inet/{ print $2;} '` # does also work: $IP=`hostname -I | awk '{print $1}'`
	NM=`ifconfig eth0 | awk '/netmask/{ print $4;} '`
	CIDR=`ipcalc $IP $NM | awk '/Network/{ print $2;} '`
	SECRET=testing123
	if [ -n "$DEFAULT_CLIENT_SECRET" ]; then
		SECRET=$DEFAULT_CLIENT_SECRET
	fi
	echo "Adding client for $CIDR with default secret $SECRET"
	mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" -e "INSERT INTO nas (nasname,shortname,type,ports,secret,server,community,description) VALUES ('$CIDR','DOCKER NET','other',0,'$SECRET',NULL,'','')"

	echo "Database initialization for freeradius completed."
}

echo "Starting freeradius..."

# wait for MySQL-Server to be ready
while ! mysqladmin ping -h"$MYSQL_HOST" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --silent; do
	echo "Waiting for mysql ($MYSQL_HOST)..."
	sleep 20
done

INIT_LOCK=/data/.freeradius_init_done
if test -f "$INIT_LOCK"; then
	# Lock file exists, but verify that FreeRADIUS is actually configured
	# This handles the case where containers are recreated but volumes persist
	if test -L "$RADIUS_PATH/mods-enabled/sql" && grep -q "rlm_sql_mysql" "$RADIUS_PATH/mods-available/sql" 2>/dev/null; then
		enable_noresetcounter
		echo "Init lock file exists and FreeRADIUS is properly configured, skipping initial setup."
	else
		echo "Init lock file exists but FreeRADIUS configuration is missing, reinitializing..."
		rm -f "$INIT_LOCK"
		init_freeradius
		date > $INIT_LOCK
	fi
else
	init_freeradius
	date > $INIT_LOCK
fi

# Ensure Max-All-Session is enforced before FreeRADIUS starts, including after
# lock-file and reinitialization paths.
if test -L "$RADIUS_PATH/mods-enabled/sqlcounter"; then
	enable_noresetcounter
fi

if test -L "$RADIUS_PATH/mods-enabled/sql"; then
	enable_group_nas_restrictions
fi

DB_LOCK=/data/.db_init_done
if test -f "$DB_LOCK"; then
	echo "Database lock file exists, skipping initial setup of mysql database."
	ensure_daloradius_schema
else
	init_database
	date > $DB_LOCK
fi

# make logs directory world readable
chmod -R a+rX /var/log/freeradius

# start freeradius in foreground mode
freeradius -f "$@" &
RADIUS_PID=$!

tail -F /var/log/freeradius/radius.log &
TAIL_PID=$!

# trap SIGINT/SIGTERM and forward to both processes
_term() {
  echo "Caught signal—shutting down..."
  kill -TERM "$RADIUS_PID" 2>/dev/null
  kill -TERM "$TAIL_PID"   2>/dev/null
  wait "$RADIUS_PID"
  wait "$TAIL_PID"
  exit 0
}
trap _term INT TERM

wait "$RADIUS_PID"
# if freeradius dies, kill the tail and exit with its code
kill -TERM "$TAIL_PID" 2>/dev/null
wait "$TAIL_PID"
exit $?
