#!/bin/sh
PATH=/bin:/usr/bin:/usr/sbin:/usr/etc:/sbin:/etc:/usr/contrib/bin:/usr/gnu/bin:/usr/ucb:/usr/bsd
SHELL=/bin/sh
#
# daloRADIUS Heartbeat agent
# @version 1.0
# @author Liran Tal <liran.tal@gmail.com>

#debbug oprions
#set -x

# ----------------------------------------------------------------------------
# Configuration --------------------------------------------------------------
# ----------------------------------------------------------------------------
# edit the settings below to apply configuration for your own deployment
# do not edit past the Configuration stanza unless you know what you're doing!


# Set to the URL of daloradius's heartbeat script location
DALO_HEARTBEAT_ADDR="http://daloradius.com/heartbeat.php"


# Set NAS MAC to the MAC address of LAN connected Linux node
# MAC address format, according to how the NAS sends this information. For example: 00-aa-bb or 00:aa:bb
#NAS_MAC="00:1D:73:11:22:33"

# Instead NAS MAC address use IP of Internet connected Linux node, external IP will be fetched form the script
#NAS_MAC="`wget -q -O - http://www.whatismyip.com/automation/n09230945.asp`"

# You can use hostname 
NAS_MAC=`hostname`


# Set to a unique, hard-to-figure-out key across all of your NASes.
# This key is saved in daloRADIUS's configuration and so should also
# be configured in daloRADIUS as well.
SECRET_KEY="sillykey"


# You need to change this according your server interfaces, otherwise it will not work!
# Change eth0, eth1, tun1 with your lo, real or virtual inrefaces
wan_iface="eth0"
lan_iface="eth1"
wifi_iface="tun0"



# Do not edit past this point
# ----------------------------------------------------------------------------
# Configuration --------------------------------------------------------------
# ----------------------------------------------------------------------------

KERNEL_NAME="$(uname -s)"



## get wan information --------------------------------------------------------

	wan_ip=`wget -q -O - http://www.whatismyip.com/automation/n09230945.asp`

	wan_mac=`ifconfig $wan_iface | awk '/HWaddr/{print substr($5,0)}'`

	wan_proto=`ifconfig $wan_iface | awk '/inet addr/{print substr($3,1)}'`
	
	wan_gateway=`ip route show default | awk '/default/{print substr($3,0)}'`


## get wifi information -------------------------------------------------------

	wifi_ip=`ifconfig $wifi_iface | awk '/inet addr/{print substr($2,6)}'`

	wifi_mac=`ifconfig $wifi_iface | awk '/HWaddr/{print substr($5,0)}'`

	wifi_ssid=`ifconfig $wifi_iface | awk '/inet addr/{print substr($3,1)}'`

	wifi_channel=`ip route show default | awk '/$wifi_iface/{print substr($6,0)}'`

## get lan information -------------------------------------------------------

	lan_ip=`ifconfig $lan_iface | awk '/inet addr/{print substr($2,6)}'`

	lan_mac=`ifconfig $lan_iface | awk '/HWaddr/{print substr($5,0)}'`

	lan_proto=`ifconfig $lan_iface | awk '/inet addr/{print substr($3,1)}'`

#gets wan ip address via wan_ipaddr name or via interface name
ip=$wan_ip

#gets the mac address of the WAN / external interface connected to Internet
mac=$wan_mac

uptime=`cat /proc/uptime | awk '{print $1}'`
memfree=`awk '/MemFree/{print $2}' /proc/meminfo`

# some options for more detail output 
# ps aux | awk '{sum +=$4}; END {print sum}'
# ps -A --sort -rss -o pid,comm,pmem,rss

# gets RX/TX data

wan_bdown=`ifconfig $wan_iface | awk '/RX bytes/{print substr($2, index($2, ":")+1)}'`
wan_bup=`ifconfig $wan_iface | awk '/TX bytes/{print substr($6, index($6, ":")+1)}'`

lan_bdown=`ifconfig $lan_iface | awk '/RX bytes/{print substr($2, index($2, ":")+1)}'`
lan_bup=`ifconfig $lan_iface | awk '/TX bytes/{print substr($6, index($6, ":")+1)}'`

wifi_bdown=`ifconfig $wifi_iface | awk '/RX bytes/{print substr($2, index($2, ":")+1)}'`
wifi_bup=`ifconfig $wifi_iface | awk '/TX bytes/{print substr($6, index($6, ":")+1)}'`


#wan_bdown=`ifconfig $eth_iface | awk '/RX bytes/{print substr($3" "$4, index($1, ":")+1)}'` # RX in KiB,MiB - (42.0 MiB) 
#wan_bup=`ifconfig $eth_iface | awk '/TX bytes/{print substr($7" "$8, index($1, ":")+1)}'` # TX in KiB,MiB - (24.0 MiB) 

#bdown=`awk '/'"$wan_iface"'/{print substr($1,6)}'  /proc/net/dev`	#in bytes, need to turn to kilobytes
#bup=`awk '/'"$wan_iface"'/{print $9}'  /proc/net/dev`				#in bytes, need to turn to kilobytes

kbdown=$((bdown/1024))
kbup=$((bup/1024))

#---------------------------------------------------------------------------------------

# Adopted form iRedMail Zhang Huangbin (zhb(at)iredmail.org)
# Get OS name version and architecture, device firmware :) extended
 
arch="$(uname -m)"
case $arch in
    i[3456]86) export ARCH='i386' ;;
    x86_64|amd64) export ARCH='x86_64' ;;
    *)
        echo "Your architecture is not supported yet: ${arch}."
        echo "Both i386 and x86_64 are supported by ${PROG_NAME}."
        exit 255
        ;;
esac

# Check version distribution 
if [ X"${KERNEL_NAME}" == X"Linux" ]; then
    # Directory of RC scripts.
    export DIR_RC_SCRIPTS='/etc/init.d'

    if [ -f /etc/redhat-release ]; then
        # RHEL/CentOS
        export DISTRO='RHEL'

        # RHEL/CentOS 5.x.
        grep '\ 5' /etc/redhat-release &>/dev/null
        if [ X"$?" == X"0" ]; then
            export DISTRO_VERSION='5'
        fi

        # RHEL/CentOS 6.x.
        grep '\ 6' /etc/redhat-release &>/dev/null
        if [ X"$?" == X"0" ]; then
            export DISTRO_VERSION='6'
        fi

    elif [ -f /etc/SuSE-release ]; then
        # SLES/OpenSuSE
        export DISTRO='SUSE'

        # SuSE version number. e.g. 11.3, 11.4.
        export DISTRO_VERSION="$(grep 'VERSION' /etc/SuSE-release | awk '{print $3}')"

        # SuSE code name.
        #   - 11.3:
        #   - 11.4: celadon
        export DISTRO_CODENAME="$(grep 'CODENAME' /etc/SuSE-release | awk '{print $3}' |tr [A-Z] [a-z])"

    elif [ -f /etc/lsb-release ]; then
        # Ubuntu
        export DISTRO='UBUNTU'

        # Code name:
        #   - 8.04: hardy
        #   - 8.10: intrepid
        #   - 9.04: jaunty
        #   - 9.10: karmic
        #   - 10.04: lucid
        #   - 10.10: maverick
        export DISTRO_CODENAME="$( grep 'DISTRIB_CODENAME' /etc/lsb-release | awk -F'=' '{print $2}' )"
        # Mark 10.10 (maverick) as 10.04 (lucid).
        if [ X"${DISTRO_CODENAME}" == X"maverick" ]; then
            export DISTRO_CODENAME='lucid'
        fi

    elif [ -f /etc/debian_version ]; then
        # Debian
        export DISTRO='DEBIAN'

        # Detect release version: 5.x, 6.x.
        # Debian 5.
        grep '^5\.' /etc/debian_version &>/dev/null
        if [ X"$?" == X"0" ]; then
            export DISTRO_VERSION='5'
            export DISTRO_CODENAME='lenny'
        fi

        # Debian 6.
        grep '^6\.' /etc/debian_version &>/dev/null
        if [ X"$?" == X"0" ]; then
            export DISTRO_VERSION='6'
            export DISTRO_CODENAME='squeeze'
        fi
    else
        # Not support yet.
        echo "Your distrobution is not supported yet."
        exit 255
    fi
elif [ X"${KERNEL_NAME}" == X"FreeBSD" ]; then
    # Directory of RC scripts.
    export DIR_RC_SCRIPTS='/usr/local/etc/rc.d'

    export DISTRO='FREEBSD'
else
    # Not support *BSD and other distrobutions yet.
    echo "Error: Your OS is not supported yet."
    exit 255
fi

firmware="$DISTRO $DISTRO_VERSION"
firmware_revision="$DISTRO_CODENAME $ARCH"

#------------------------------------------------------------------------------------

# Snippet to get CPU % --------------------------------------------------------------
# adopted from Paul Colby (http://colby.id.au)
PREV_TOTAL=0
PREV_IDLE=0
#repeat period
x=5
#counter
i=1
while [ $i -le $x ]
do
  IDLE=`cat /proc/stat | grep '^cpu ' | awk '{print $5}'`       # get cpu idle time
  TOTAL=`cat /proc/stat | grep '^cpu ' | awk '{print $1+$2+$3+$4+$5+$6+$7+$8+$9+$10+$11}'` #get total cpu time

  # Calculate the CPU usage since we last checked.
  let "DIFF_IDLE=$IDLE-$PREV_IDLE"
  let "DIFF_TOTAL=$TOTAL-$PREV_TOTAL"
  let "DIFF_USAGE=1000*($DIFF_TOTAL-$DIFF_IDLE)/$DIFF_TOTAL"
  let "DIFF_USAGE_UNITS=$DIFF_USAGE/10"
  let "DIFF_USAGE_DECIMAL=$DIFF_USAGE%10"
#  echo -en "\rCPU: $DIFF_USAGE_UNITS.$DIFF_USAGE_DECIMAL%    \b\b\b\b"

# No decemical  
  #let "DIFF_IDLE=$IDLE-$PREV_IDLE"
  #let "DIFF_TOTAL=$TOTAL-$PREV_TOTAL"
  #let "DIFF_USAGE=1000*($DIFF_TOTAL-$DIFF_IDLE)/$DIFF_TOTAL"
  #let "DIFF_USAGE=(1000*($DIFF_TOTAL-$DIFF_IDLE)/$DIFF_TOTAL+5)/10"
  #echo -en "\rCPU: $DIFF_USAGE%  \b\b"

  # Remember the total and idle CPU times for the next check.
  PREV_TOTAL="$TOTAL"
  PREV_IDLE="$IDLE"

  # Wait before checking again.
  sleep 1
  i=$(( $i + 1 ))
done
cpu=$DIFF_USAGE_UNITS.$DIFF_USAGE_DECIMAL%
# --------------------------------------------------------------------------------------


if [ "$DEBUG_MODE" = "1" ]
then
	echo "Collected the following information..."
	echo "-------------------------------------------------------"
	echo "wan_iface $wan_iface"
	echo "wan_ip $wan_ip"
	echo "wan_mac $wan_mac"
	echo "wan_gateway $wan_gateway"
	echo "wifi_mac $wifi_mac"
	echo "wifi_ip $wifi_ip"
	echo "wifi_iface $wifi_iface"

	echo "lan_mac $lan_mac"
	echo "lan_ip $lan_ip"
	echo "lan_iface $lan_iface"

	echo "ip $ip"
	echo "mac $mac"
	echo "uptime $uptime"
	echo "memfree $memfree"
	echo "wan_bdown $wan_bdown"
	echo "wan_bup $wan_bup"
	echo "wifi_ssid $wifi_ssid"
	echo "wifi_key $wifi_key"
	echo "wifi_channel $wifi_channel"
	echo "firmware $firmware"
	echo "firmware_revision $firmware_revision"
	echo "$cpu"
	echo "-------------------------------------------------------"
fi


wget -O /tmp/heartbeat.txt "$DALO_HEARTBEAT_ADDR?secret_key=$SECRET_KEY&nas_mac=$NAS_MAC&firmware=$firmware&firmware_revision=$firmware_revision&wan_iface=$wan_iface&wan_ip=$wan_ip&wan_mac=$wan_mac&wifi_mac=$wifi_mac&wan_gateway=$wan_gateway&wifi_iface=$wifi_iface&wifi_ip=$wifi_ip&wifi_mac=$wifi_mac&wifi_ssid=$wifi_ssid&wifi_key=$wifi_key&wifi_channel=$wifi_channel&lan_iface=$lan_iface&lan_ip=$lan_ip&lan_mac=$lan_mac&uptime=$uptime&memfree=$memfree&wan_bup=$wan_bup&wan_bdown=$wan_bdown&cpu=$cpu" > /dev/null 2>&1

if [ "$DEBUG_MODE" = "1" ]
then
	echo "-------------------------------------------------------"
	echo "daloRADIUS server returned: \n"
	echo "-------------------------------------------------------"
	cat /tmp/heartbeat.txt
	echo "-------------------------------------------------------"
fi

exit 0
