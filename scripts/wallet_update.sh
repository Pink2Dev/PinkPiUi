#!/bin/bash

HOME="/home/pi"
DIR="$HOME/pinkcoin"
SOURCE=$(ls -dt "$DIR/"*"/" | head -1)
URL_REPO="https://github.com/Pink2Dev/Pink2"
VERSION_LATEST=$(git -C "$SOURCE" tag -l "*.*.*" --sort=-v:refname | head -1)

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

# Install Pinkcoin Wallet
install_pinkcoin
