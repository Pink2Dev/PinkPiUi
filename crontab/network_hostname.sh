#!/bin/bash

FILE="/etc/hosts"
HOST="pinkpi.local"
DATA=$(ip addr | grep -Eo "inet6? [0-9a-f\.:]*" | grep -Ev "::1|127.0.0.1" | sed -e 's/inet6* //')
IPS=(${DATA//\n/ })
if [ ${#IPS[@]} -lt 1 ]
then
	# IP is not yet resolved
	exit 1;
fi

# Remove any currently assigned hostname
sed -i "/.*$HOST$/d" "$FILE"

for IP in "${IPS[@]}"
do
	# Assign hostname
	echo "$IP	$HOST" >> "$FILE"
done
