#!/bin/sh
#
# daloRADIUS Heartbeat agent
# @version 1.0
# @author Liran Tal <liran.tal@gmail.com>
#
# NOTE: this agent hasn't been fully tested and requires more scripting work to get
#       working.

# ----------------------------------------------------------------------------
# Configuration --------------------------------------------------------------
# ----------------------------------------------------------------------------
# edit the settings below to apply configuration for your own deployment
# do not edit past the Configuration stanza unless you know what you're doing!


# Set to the URL of daloradius's heartbeat script location
DALO_HEARTBEAT_ADDR="http://daloradius.com/heartbeat.php"

# Set NAS MAC to the MAC address of the chilli interface
# MAC address format, according to how the NAS sends this information. For example: 00-aa-bb or 00:aa:bb
NAS_MAC="00-1D-7E-11-22-33"


# Set to a unique, hard-to-figure-out key across all of your NASes.
# This key is saved in daloRADIUS's configuration and so should also
# be configured in daloRADIUS as well.
SECRET_KEY="sillykey"


# Set to 1 if debug mode should be enabled for the agent
# Debug mode prints the collected variable values from the NAS and the returned response form the
# daloradius server
DEBUG_MODE=0


# do not edit past this point
# ----------------------------------------------------------------------------
# Configuration --------------------------------------------------------------
# ----------------------------------------------------------------------------









# ----------------------------------------------------------------------------
# Grab Statistics --------------------------------------------------------------
# ----------------------------------------------------------------------------

wan_iface=`nvram get wan_ifname`
wan_ip=`nvram get wan_ipaddr`
if [ -z $wan_ip ]
then
	wan_ip=`ifconfig $wan_iface | awk '/inet addr/{print substr($2,6)}'`
fi

wl0_mac=`nvram get wl0_hwaddr`
wan_mac=`nvram get wan_hwaddr`
lan_mac=`nvram get lan_hwaddr`

#gets wan ip address via wan_ipaddr name or via interface name
ip=$wan_ip

#gets the mac address of the wireless interface on which the hotspot
#runs on
mac=$wl0_mac

uptime=`uptime | awk '{print $1}'`
memfree=`awk '/MemFree/{print $2}' /proc/meminfo`
bdown=`awk '/'"$wan_iface"'/{print substr($1,6)}'  /proc/net/dev`	#in bytes, need to turn to kilobytes
bup=`awk '/'"$wan_iface"'/{print $9}'  /proc/net/dev`				#in bytes, need to turn to kilobytes

kbdown=$((bdown/1024))
kbup=$((bup/1024))

ssid=`nvram get wl0_ssid`

# Snippet to get CPU % --------------------------------------------------------------
# adopted from Paul Colby (http://colby.id.au)
PREV_TOTAL=0
PREV_IDLE=0
#repeat period
x=5
#counter
i=1
while [ $i -le $x ]; do
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
	echo $wan_iface
	echo $wan_ip
	echo $wl0_mac
	echo $wan_mac
	echo $lan_mac
	echo $ip
	echo $mac
	echo $uptime
	echo $memfree
	echo $bdown
	echo $bup
	echo $kbdown
	echo $kbup
	echo $ssid
	echo $cpu
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

