#!/bin/bash

# Clear dynamic network
> /etc/wpa_supplicant/wpa_supplicant.conf

# Setup Wireless Network
cat > /etc/network/interfaces.d/wlan0 << EOL
auto wlan0
iface wlan0 inet static
	address 172.24.1.2
	netmask 255.255.255.0
	network 172.24.1.0
	broadcast 172.24.1.255
	gateway 172.24.1.1
	dns-nameservers 172.24.1.1, 8.8.8.8
EOL

# Setup IP Range
cat > /etc/dnsmasq.conf << EOL
bogus-priv
server=/local/172.24.1.2
local=/local/
address=/#/172.24.1.2
interface=wlan0
domain=local
dhcp-range=172.24.1.4,172.24.1.254,255.255.255.0,12h
dhcp-option=3,172.24.1.2
dhcp-option=6,172.24.1.2
dhcp-authoritative
EOL

# Setup Wireless Network
cat > /etc/hostapd/hostapd.conf << EOL
interface=wlan0
driver=nl80211
ssid=PinkPi
hw_mode=g
channel=11
ieee80211n=1
wmm_enabled=0
ht_capab=[HT40][SHORT-GI-20][DSSS_CCK-40]
macaddr_acl=0
auth_algs=1
ignore_broadcast_ssid=0
wpa=2
wpa_key_mgmt=WPA-PSK
wpa_passphrase=my-pinkpi
wpa_pairwise=TKIP
rsn_pairwise=CCMP
EOL

# Setup configuration file
FILE="/etc/default/hostapd"
LINE="DAEMON_CONF=\"/etc/hostapd/hostapd.conf\""
if ! grep -qxFe "$LINE" "$FILE"
then
	echo "$LINE" >> "$FILE"
fi

# Initialize Wireless Network
FILE="/etc/dhcpcd.conf"
LINE="denyinterfaces wlan0"
if ! grep -qxFe "$LINE" "$FILE"
then
	echo "$LINE" >> "$FILE"
fi

# Pause for a moment
sleep 5s

# Reboot to reload network configurations
reboot
