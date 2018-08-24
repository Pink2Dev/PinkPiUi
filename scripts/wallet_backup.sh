#!/bin/bash

HOME="/home/pi"
DAT="$HOME/.pink2/wallet.dat"
RUNNING=$(pgrep -x "pink2d" > /dev/null)

if [ $# -ne 1 ]; then
	echo "Usage: $0 <Destination>"
	exit 1
fi

# Check if pink2d is running
if $RUNNING
then
	pink2d backupwallet "$1"
else
   cp "$DAT" "$1"
fi
