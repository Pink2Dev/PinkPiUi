#!/bin/bash

HOME="/home/pi"
DAT="$HOME/.pink2/wallet.dat"
RUNNING=$(pgrep -x "pink2d" > /dev/null)
SERVICE="pinkcoin.service"
SOURCE="$HOME/cache/wallet.new.dat"

if [ ! -f "$SOURCE" ]
then
	# No new wallet file found
    exit 1
fi

# Check if Pinkcoin is running
if $RUNNING
then
	systemctl stop "$SERVICE"
fi

# Overwrite existing wallet file
mv -f "$SOURCE" "$DAT"

# Correct permissions
chown pi:pi "$DAT"
chmod 600 "$DAT"

# Restart Wallet
if $RUNNING
then
	systemctl start "$SERVICE"
fi
