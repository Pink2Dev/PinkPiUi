#!/bin/bash

HOME="/home/pi"
RUNNING=$(pgrep -x "pink2d" > /dev/null)
SERVICE="pinkcoin.service"

# Stop Pinkcoin Wallet
if $RUNNING
then
	sudo systemctl stop $SERVICE
fi

# (Re)Generate SSL Certificate
cd "$HOME/scripts" && wallet_ssl.sh >> wallet_ssl.log 2>&1

# (Re)Start Pinkcoin Wallet
if $RUNNING
then
	sudo systemctl start $SERVICE
fi
