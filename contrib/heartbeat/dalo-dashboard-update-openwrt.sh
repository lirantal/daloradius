#!/bin/sh
#
# daloRADIUS Heartbeat agent
# @version 1.1
# @author Muhammed Al-Qadhy <witradius@gmail.com>

#debbug oprions
#set -x

# ----------------------------------------------------------------------------
# Configuration --------------------------------------------------------------
# ----------------------------------------------------------------------------
# edit the settings below to apply configuration for your own deployment

# Set to the URL of daloradius's heartbeat script location
DALO_HEARTBEAT_ADDR="http://daloradius.com/heartbeat.php"

# This is Auto Set NAS MAC to the MAC address of LAN connected openwrt node
# MAC address format, according to how the NAS sends this information. For example: 00-aa-bb or 00:aa:bb
# Extracting NAS MAC from uci chilli config.
NAS_MAC=`uci get chilli.chilli1.radiusnasid |awk '/''/{print substr ($1,1)}'`

# Set to a unique, hard-to-figure-out key across all of your NASes.
# This key is saved in daloRADIUS's configuration and so should also
# be configured in daloRADIUS as well.
SECRET_KEY="sillykey"

# Do not edit past this point
# ----------------------------------------------------------------------------
# Configuration --------------------------------------------------------------
# ----------------------------------------------------------------------------

wan_iface=`uci get network.wan.device`
wan_ip=`ifconfig eth0.2 | awk '/inet addr/{print substr ($2,6)}'`
wan_mac=`ifconfig eth0.2 | awk '/HWaddr/{print substr ($5,0)}'`
wan_gateway=`ifconfig eth0.2 | awk '/inet addr/{print substr ($2,6)}'`
wan_proto=`uci get network.wan.proto`
wifi_iface=`uci get wireless.default_radio0.device  | awk '{ gsub(/ /,""); print }'`
wifi_ip=`uci get chilli.chilli1.uamlisten`
wifi_mac=`ifconfig wlan0 | awk '/HWaddr/{print substr ($5,0)}'`
wifi_ssid=`uci get wireless.default_radio0.ssid | awk '{ gsub(/ /,""); print }'`
#wifi_key=`nvram get wl_wep_gen`
wifi_channel=`uci get wireless.radio0.channel`
lan_iface=`uci get network.device1.ports`
lan_ip=`ifconfig tun0 | awk '/inet addr/{print substr ($2,6)}'`
lan_mac=`ifconfig br-lan | awk '/HWaddr/{print substr ($5,1)}'`
lan_proto=`uci get network.lan.proto`
ip=$wan_ip
mac=$lan_mac
uptime=`cat /proc/uptime | awk '{print ($1)}'`
memfree=`cat /proc/meminfo | awk '/MemFree/{print substr($2,$3)}'`
wan_bdown=`ifconfig eth0.2 | awk '/RX bytes/{print substr($2,7)}'`
wan_bup=`ifconfig eth0.2 | awk '/TX bytes/{print substr($6,7)}'`
#bdown=`awk '/'"$wan_iface"'/{print substr($1,6)}'  /proc/net/dev` #in byte
#bup=`awk '/'"$wan_iface"'/{print $9}'  /proc/net/dev`	#in bytes, need to turn to kilobytes
#kbdown=$((bdown/1024))
#kbup=$((bup/1024))
firmware=`cat /proc/cpuinfo | awk '/machine/{print substr($5,1)}'`
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


wget -O /tmp/heartbeat.txt "$DALO_HEARTBEAT_ADDR?secret_key=$SECRET_KEY&nas_mac=$NAS_MAC&firmware=$firmware&firmware_revision=$firmware_revision&wan_iface=$wan_iface&wan_ip=$wan_ip&wan_mac=$wan_mac&wifi_mac=$wifi_mac&wan_gateway=$wan_gateway&wifi_iface=$wifi_iface&wifi_ip=$wifi_ip&wifi_mac=$wifi_mac&wifi_ssid=$wifi_ssid&wifi_key=$wifi_key&wifi_channel=$wifi_channel&lan_iface=$lan_iface&lan_ip=$lan_ip&lan_mac=$lan_mac&uptime=$uptime&memfree=$memfree&wan_bup=$wan_bup&wan_bdown=$wan_bdown&cpu=$cpu"


if [ "$DEBUG_MODE" = "1" ]
then
	echo "-------------------------------------------------------"
	echo "daloRADIUS server returned: \n"
	echo "-------------------------------------------------------"
	cat /tmp/heartbeat.txt
	echo "-------------------------------------------------------"
fi
