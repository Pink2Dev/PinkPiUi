#!/bin/bash

HOME="/home/pi"
DIR="$HOME/pinkcoin"
SERVICE="pinkcoin.service"
SOURCE=$(ls -dt "$DIR/"*"/" | head -1)
TARGET="/usr/bin"
VERSION_LATEST=$(cat "${SOURCE}VERSION" | tr -d '[:space:]')
BIN="${SOURCE}src/pink2d"

install_dependencies() {
	sudo DEBIAN_FRONTEND=noninteractive apt-get -qqy install checkinstall libboost-all-dev libminiupnpc-dev libssl1.0-dev > /dev/null

	# Install DB 4.8
	install_db4
}

install_db4() {
	PACKAGE="libdb-dev"
	# .* added to ignore extra characters (e.g. libdb-dev:armhf)
	INSTALLED=$(dpkg --get-selections | grep -c "^$PACKAGE.*[[:space:]]*install$")

	if [ $INSTALLED -eq 0 ]
	then
		cd /tmp

		# Fetch the source and verify that it is not tampered with
		wget -q 'https://download.oracle.com/berkeley-db/db-4.8.30.NC.tar.gz' -O db-4.8.30.NC.tar.gz

		echo "12edc0df75bf9abd7f82f821795bcee50f42cb2e5f76a6a281b85732798364ef db-4.8.30.NC.tar.gz" | sha256sum -c > /dev/null
		if [ $? -ne 0 ]
		then
			# Checksum failed
			exit 1
		fi

		# Extract
		tar -xzf db-4.8.30.NC.tar.gz

		# Build the library and install to our prefix
		cd db-4.8.30.NC/build_unix/
		../dist/configure --quiet --enable-cxx --with-pic --prefix=/usr > /dev/null
		make --quiet > /dev/null

		# Install and verify
		sudo checkinstall --fstrans=no --pkgversion=4.8.30 --pkgname="$PACKAGE" --nodoc -y > /dev/null

		# Clean up
		cd /tmp
		rm -fR db-4.8.30.NC/
		rm db-4.8.30.NC.tar.gz
	fi
}

install_wallet() {
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
}

version_check() {
	VERSION_FILE="$DIR/VERSION"
	VERSION_CURRENT="0.0.0.0"

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
}

# Check the version
version_check

# Check for an existing process
if pgrep -x "make" > /dev/null
then
	exit 1
fi

if pgrep -x "checkinstall" > /dev/null
then
	exit 3
fi

# Install Dependencies
install_dependencies

# Install Wallet
install_wallet

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
