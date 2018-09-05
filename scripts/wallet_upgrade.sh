#!/bin/bash

HOME="/home/pi"
DIR="$HOME/pinkcoin"
SERVICE="pinkcoin.service"
SOURCE=$(ls -dt "$DIR/"*"/" | head -1)
TARGET="/usr/bin"
VERSION_CURRENT="0.0.0.0"
VERSION_FILE="$DIR/VERSION"
VERSION_LATEST=$(cat "${SOURCE}VERSION" | tr -d '[:space:]')
BIN="${SOURCE}src/pink2d"

if [ -f "$VERSION_FILE" ]
then
	VERSION_CURRENT=$(cat "$VERSION_FILE" | tr -d '[:space:]')
fi

# Check version
dpkg --compare-versions "$VERSION_LATEST" "gt" "$VERSION_CURRENT"
if [ $? -ne 0 ]
then
	# Nothing to do
	exit 0
fi

# Check for an existing process
if pgrep -x "make" > /dev/null
then
	exit 1
fi

# Compile program when not found
if [ ! -f "$BIN" ]
then
	cd "${SOURCE}src/leveldb"

	# Clear any rubble
	make clean

	# Compile database first
	TARGET_OS=Linux make --quiet libleveldb.a libmemenv.a > /dev/null

	cd "${SOURCE}src"

	# Clear any rubble
	make -f makefile.pi clean

	# Compile new wallet
	make --quiet -f makefile.pi > /dev/null
fi

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
