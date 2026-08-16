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

# This automatically sets the NAS MAC to the value configured in chilli.
# MAC address format, according to how the NAS sends this information. For example: 00-aa-bb or 00:aa:bb
NAS_MAC="$(uci -q get 'chilli.@chilli[0].radiusnasid')"
[ -n "$NAS_MAC" ] || NAS_MAC="$(uci -q get chilli.chilli1.radiusnasid)"

# Set to a unique, hard-to-figure-out key across all of your NASes.
# This key is saved in daloRADIUS's configuration and so should also
# be configured in daloRADIUS as well.
SECRET_KEY="sillykey"

# Optional interface overrides. Leave empty to discover the active OpenWrt
# interfaces through ubus/UCI.
WAN_DEV=""
LAN_DEV=""
WLAN_DEV=""

# Do not edit past this point
# ----------------------------------------------------------------------------
# Configuration --------------------------------------------------------------
# ----------------------------------------------------------------------------

get_l3_device() {
    ubus call "network.interface.$1" status 2>/dev/null | jsonfilter -e '@.l3_device' 2>/dev/null
}

get_wifi_section() {
    wifi_section_fallback=""

    for wifi_section_candidate in $(
        uci -q show wireless 2>/dev/null |
            sed -n 's/^wireless\.\([^.=]*\)=wifi-iface$/\1/p'
    )
    do
        wifi_section_disabled="$(uci -q get "wireless.$wifi_section_candidate.disabled")"
        [ "$wifi_section_disabled" = "1" ] && continue

        wifi_section_mode="$(uci -q get "wireless.$wifi_section_candidate.mode")"
        [ -n "$wifi_section_mode" ] && [ "$wifi_section_mode" != "ap" ] && continue

        wifi_radio="$(uci -q get "wireless.$wifi_section_candidate.device")"
        [ "$(uci -q get "wireless.$wifi_radio.disabled")" = "1" ] && continue

        [ -n "$wifi_section_fallback" ] || wifi_section_fallback="$wifi_section_candidate"
        wifi_section_network="$(uci -q get "wireless.$wifi_section_candidate.network")"
        case " $wifi_section_network " in
            *" lan "*)
                printf '%s' "$wifi_section_candidate"
                return
                ;;
        esac
    done

    printf '%s' "$wifi_section_fallback"
}

get_wifi_device() {
    [ -n "$1" ] || return
    ubus call network.wireless status 2>/dev/null |
        jsonfilter -l 1 \
            -e "@[@.up=true].interfaces[@.section=\"$1\"].ifname" \
            2>/dev/null
}

get_ipv4() {
    ip -4 addr show dev "$1" 2>/dev/null |
        awk '/inet / { split($2, address, "/"); print address[1]; exit }'
}

get_mac() {
    [ -r "/sys/class/net/$1/address" ] && tr -d '\n' < "/sys/class/net/$1/address"
}

get_rx_bytes() {
    awk -v iface="$1:" '$1 == iface { print $2; exit }' /proc/net/dev
}

get_tx_bytes() {
    awk -v iface="$1:" '$1 == iface { print $10; exit }' /proc/net/dev
}

urlencode() {
    printf '%s' "$1" | LC_ALL=C od -An -tu1 |
        awk '{
            for (i = 1; i <= NF; i++) {
                byte = $i
                if ((byte >= 48 && byte <= 57) ||
                    (byte >= 65 && byte <= 90) ||
                    (byte >= 97 && byte <= 122) ||
                    byte == 45 || byte == 46 || byte == 95 || byte == 126) {
                    printf "%c", byte
                } else {
                    printf "%%%02X", byte
                }
            }
        }'
}

wan_iface="${WAN_DEV:-$(get_l3_device wan)}"
[ -n "$wan_iface" ] || wan_iface="$(uci -q get network.wan.device)"
[ -n "$wan_iface" ] || wan_iface="$(uci -q get network.wan.ifname)"

lan_iface="${LAN_DEV:-$(get_l3_device lan)}"
[ -n "$lan_iface" ] || lan_iface="$(uci -q get network.lan.device)"
[ -n "$lan_iface" ] || lan_iface="$(uci -q get network.lan.ifname)"

wifi_section="$(get_wifi_section)"
wifi_iface="${WLAN_DEV:-$(get_wifi_device "$wifi_section")}"
[ -n "$wifi_iface" ] || wifi_iface="$(uci -q get "wireless.$wifi_section.ifname")"

wan_ip="$(get_ipv4 "$wan_iface")"
wan_mac="$(get_mac "$wan_iface")"
wan_gateway="$(ip route show default dev "$wan_iface" 2>/dev/null | awk '/default/ { print $3; exit }')"
wifi_ip="$(uci -q get 'chilli.@chilli[0].uamlisten')"
[ -n "$wifi_ip" ] || wifi_ip="$(uci -q get chilli.chilli1.uamlisten)"
wifi_mac="$(get_mac "$wifi_iface")"
wifi_ssid="$(uci -q get "wireless.$wifi_section.ssid")"
wifi_key=""
wifi_radio="$(uci -q get "wireless.$wifi_section.device")"
wifi_channel="$(uci -q get "wireless.$wifi_radio.channel")"
lan_ip="$(get_ipv4 "$lan_iface")"
lan_mac="$(get_mac "$lan_iface")"
ip=$wan_ip
mac=$lan_mac
uptime="$(awk '{ print $1 }' /proc/uptime)"
memfree="$(awk '/MemFree/ { print $2; exit }' /proc/meminfo)"
wan_bdown="$(get_rx_bytes "$wan_iface")"
wan_bup="$(get_tx_bytes "$wan_iface")"
firmware="$(awk -F= '/^ID=/ { gsub(/\"/, "", $2); print $2; exit }' /etc/os-release)"
firmware_revision="$(awk -F= '/^VERSION_ID=/ { gsub(/\"/, "", $2); print $2; exit }' /etc/os-release)"
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
  IDLE=$(awk '/^cpu / { print $5; exit }' /proc/stat)       # get cpu idle time
  TOTAL=$(awk '/^cpu / { print $1+$2+$3+$4+$5+$6+$7+$8+$9+$10+$11; exit }' /proc/stat) #get total cpu time

  # Calculate the CPU usage since we last checked.
  DIFF_IDLE=$((IDLE - PREV_IDLE))
  DIFF_TOTAL=$((TOTAL - PREV_TOTAL))
  if [ "$DIFF_TOTAL" -gt 0 ]
  then
    DIFF_USAGE=$((1000 * (DIFF_TOTAL - DIFF_IDLE) / DIFF_TOTAL))
  else
    DIFF_USAGE=0
  fi
  DIFF_USAGE_UNITS=$((DIFF_USAGE / 10))
  DIFF_USAGE_DECIMAL=$((DIFF_USAGE % 10))
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
  i=$((i + 1))
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


heartbeat_query=""
append_heartbeat_parameter() {
    parameter="$1=$(urlencode "$2")"
    if [ -n "$heartbeat_query" ]
    then
        heartbeat_query="$heartbeat_query&$parameter"
    else
        heartbeat_query="$parameter"
    fi
}

append_heartbeat_parameter secret_key "$SECRET_KEY"
append_heartbeat_parameter nas_mac "$NAS_MAC"
append_heartbeat_parameter firmware "$firmware"
append_heartbeat_parameter firmware_revision "$firmware_revision"
append_heartbeat_parameter wan_iface "$wan_iface"
append_heartbeat_parameter wan_ip "$wan_ip"
append_heartbeat_parameter wan_mac "$wan_mac"
append_heartbeat_parameter wan_gateway "$wan_gateway"
append_heartbeat_parameter wifi_iface "$wifi_iface"
append_heartbeat_parameter wifi_ip "$wifi_ip"
append_heartbeat_parameter wifi_mac "$wifi_mac"
append_heartbeat_parameter wifi_ssid "$wifi_ssid"
append_heartbeat_parameter wifi_key "$wifi_key"
append_heartbeat_parameter wifi_channel "$wifi_channel"
append_heartbeat_parameter lan_iface "$lan_iface"
append_heartbeat_parameter lan_ip "$lan_ip"
append_heartbeat_parameter lan_mac "$lan_mac"
append_heartbeat_parameter uptime "$uptime"
append_heartbeat_parameter memfree "$memfree"
append_heartbeat_parameter wan_bup "$wan_bup"
append_heartbeat_parameter wan_bdown "$wan_bdown"
append_heartbeat_parameter cpu "$cpu"

wget -O /tmp/heartbeat.txt "$DALO_HEARTBEAT_ADDR?$heartbeat_query"


if [ "$DEBUG_MODE" = "1" ]
then
	echo "-------------------------------------------------------"
	printf "daloRADIUS server returned:\n\n"
	echo "-------------------------------------------------------"
	cat /tmp/heartbeat.txt
	echo "-------------------------------------------------------"
fi
