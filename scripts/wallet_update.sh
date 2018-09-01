#!/bin/bash

HOME="/home/pi"
DIR="$HOME/pinkcoin"
SOURCE=$(ls -dt "$DIR/"*"/" | head -1)
URL_REPO="https://github.com/Pink2Dev/Pink2"
VERSION_LATEST=$(git -C "$SOURCE" tag -l "*.*.*" --sort=-refname | head -1)


install_dependencies() {
	sudo DEBIAN_FRONTEND=noninteractive apt-get clean
	sudo DEBIAN_FRONTEND=noninteractive apt-get -qqy update > /dev/null
	sudo DEBIAN_FRONTEND=noninteractive apt-get -qqy upgrade > /dev/null
	sudo DEBIAN_FRONTEND=noninteractive apt-get -qqy install checkinstall git libboost-all-dev libminiupnpc-dev libssl1.0-dev > /dev/null
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
		make --quiet > /dev/null 2>&1

		# Install and verify
		sudo checkinstall --fstrans=no --pkgversion=4.8.30 --pkgname="$PACKAGE" --nodoc -y > /dev/null

		# Clean up
		cd /tmp
		rm -fR db-4.8.30.NC/
		rm db-4.8.30.NC.tar.gz
	fi
}

install_pinkcoin() {
	DATE=`date '+%Y%m%d%H%M%S'`
	TARGET="$DIR/$DATE"

	# Download latest version by tag
	git clone --branch "$VERSION_LATEST" "$URL_REPO" "$TARGET"
	if [ $? -ne 0 ]
	then
		exit 0
	fi

	# Mark current version
	echo "$VERSION_LATEST" > "$TARGET/VERSION"

	cd "$TARGET/src/leveldb"

	# Compile database first
	TARGET_OS=Linux make --quiet libleveldb.a libmemenv.a > /dev/null

	cd "$TARGET/src"

	# Compile new wallet
	make --quiet -f makefile.pi > /dev/null
}

version_check() {
	VERSION_FILE="${SOURCE}VERSION"
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

# Install Dependencies
install_dependencies

# Install DB 4.8
install_db4

# Install Pinkcoin Wallet
install_pinkcoin
