#!/bin/bash

HOME="/home/pi"
DIR="$HOME/pinkcoin"
SERVICE="pinkcoin.service"
SOURCE=$(ls -dt "$DIR/"*"/" | head -1)
TARGET="/usr/bin"
BIN="${SOURCE}src/pink2d"

if [ ! -f "$BIN" ]
then
	# Do not install
	# Compiling must have failed
	exit 4
fi

# Stop Pinkcoin Wallet
# "cp: cannot create regular file '/usr/bin/pink2d': Text file busy"
sudo systemctl stop "$SERVICE"

# Copy new Pinkcoin Wallet
sudo cp "$BIN" "$TARGET"

# Copy version file (i.e. mark as installed)
cp "${SOURCE}VERSION" "$DIR"

# Restart Pinkcoin Wallet
sudo systemctl restart "$SERVICE"
