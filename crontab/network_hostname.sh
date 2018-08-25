#!/bin/bash

FILE="/etc/hosts"
HOST="pinkpi.local"
PATTERN="([0-9]*\.){3}[0-9]*"
IP=$(ifconfig | grep -Eo "inet (addr:)?$PATTERN" | grep -Eo "$PATTERN" | grep -v "127.0.0.1")
if [ -z "$IP" ]
then
	# IP is not yet resolved
	exit 1;
fi

# Only replace when the IP is different
if ! grep -q "$IP" "$FILE"
then
	# Remove any currently assigned hostname
	sed -i "/.*$HOST$/d" "$FILE"

	# Assign hostname
	echo "$IP	$HOST" >> "$FILE"
fi
