#!/bin/bash

HOME="/home/pi"
DIR="$HOME/pinkcoin"
SERVICE="pinkcoin.service"
SOURCE=$(ls -dt "$DIR/"*"/" | head -1)
TARGET="/usr/bin"
BIN="${SOURCE}src/pink2d"

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

if [ ! -f "$BIN" ]
then
	# e.g. not finished compiling
	exit 1
fi

# Stop Pinkcoin Wallet
# "cp: cannot create regular file '/usr/bin/pink2d': Text file busy"
systemctl stop "$SERVICE"

# Copy new Pinkcoin Wallet
cp "$BIN" "$TARGET"

# Copy version file (i.e. mark as installed)
cp "${SOURCE}VERSION" "$DIR"

# Restart Pinkcoin Wallet
systemctl restart "$SERVICE"
