#!/bin/bash

HOME="/home/pi"
DIR="$HOME/pinkpiui"
SOURCE=$(ls -dt "$DIR/"*"/" | head -1)
URL_REPO="https://github.com/Pink2Dev/PinkPiUi"


install_pinkpiui() {
	DATE=`date '+%Y%m%d%H%M%S'`
	TARGET="$DIR/$DATE"

	# Download latest version
	git clone "$URL_REPO" "$TARGET" > /dev/null 2>&1

	# Install latest version
	"$TARGET/scripts/ui_upgrade.sh"
}

version_check() {
	URL_VERSION="$URL_REPO/raw/master/VERSION"
	VERSION_FILE="${SOURCE}VERSION"
	VERSION_CURRENT="0.0.0"
	VERSION_LATEST=$(wget "$URL_VERSION" -q -O - | tr -d '[:space:]')

	if [ -f "$VERSION_FILE" ]
	then
		VERSION_CURRENT=$(< "$VERSION_FILE" | tr -d '[:space:]')
	fi

	# Check version
	dpkg --compare-versions "$VERSION_LATEST" "gt" "$VERSION_CURRENT"
	if [ $? -ne "0" ]
	then
		# Nothing to do
		exit 0
	fi
}


# Check the version
version_check

# Install PinkPi Interface
install_pinkpiui
