#!/bin/bash

HOME="/home/pi"
DIR="$HOME/pinkpiui"
SOURCE=$(ls -dt "$DIR/"*"/" | head -1)
URL_REPO="https://github.com/Pink2Dev/PinkPiUi"
URL_VERSION="$URL_REPO/raw/master/VERSION"
VERSION_LATEST=$(wget "$URL_VERSION" -q -O - | tr -d '[:space:]')


install_pinkpiui() {
	DATE=`date '+%Y%m%d%H%M%S'`
	TARGET="$DIR/$DATE"

	# Download latest version
	git clone --branch "$VERSION_LATEST" "$URL_REPO" "$TARGET" > /dev/null
	if [ $? -ne 0 ]
	then
		exit 0
	fi

	# Copy cache files
	cp -R "${SOURCE}cache" "$TARGET"

	# Install latest version
	"$TARGET/scripts/ui_upgrade.sh"
}

version_check() {
	VERSION_FILE="${SOURCE}VERSION"
	VERSION_CURRENT="0.0.0"

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

# Install PinkPi Interface
install_pinkpiui
