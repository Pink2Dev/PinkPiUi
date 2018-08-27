#!/bin/bash

HOME="/home/pi"
DAY=86400
DAYS=120
DIR="$HOME/.pink2"
CERT="$DIR/server.cert"
INVALID=1
PEM="$DIR/server.pem"
SECONDS=$(($DAYS * $DAY))
SECONDS_EXPIRE=$((30 * $DAY))
SUBJECT="/C=US/ST=FL/L=Boca Raton/O=Pinkcoin Foundation Inc/OU=PinkPi/CN=localhost"

if [ -f $CERT ]
then
	openssl x509 -checkend "$SECONDS_EXPIRE" -noout -in "$CERT"
	INVALID=$?
fi

if [ $INVALID -ne 0 ]
then
	# Ensure directory exists
	mkdir -p "$DIR"

	# Generate (sef-signed) SSL Certificate
	openssl genrsa -out "$PEM" 4096
	openssl req -new -x509 -nodes -sha512 -subj "$SUBJECT" -days "$DAYS" -key "$PEM" > "$CERT"

	# Correct permissions
	chown pi:pi "$CERT"
	chown pi:pi "$PEM"
fi
