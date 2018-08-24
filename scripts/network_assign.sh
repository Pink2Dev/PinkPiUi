#!/bin/bash

if [ $# -ne 2 ]; then
	echo "Usage: $0 <Network SSID> <Network Password>"
	exit 1
fi

# Clear static network
> /etc/dnsmasq.conf
> /etc/hostapd/hostapd.conf
> /etc/network/interfaces.d/wlan0

sed -i "/denyinterfaces wlan0/d" /etc/dhcpcd.conf

# Assign dynamic network
cat > /etc/wpa_supplicant/wpa_supplicant.conf << EOL
ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev
update_config=1

network={
	scan_ssid=1
	ssid="$1"
	psk="$2"
}
EOL

#  Give the PinkPiUi a moment to render
sleep 5s

# Reboot
reboot
