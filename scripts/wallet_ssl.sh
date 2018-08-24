#!/bin/bash

HOME="/home/pi"
DAYS=120
DIR="$HOME/.pink2"
INVALID=1
PEM="$DIR/server.pem"
SECONDS=$(($DAYS * 86400))
SUBJECT="/C=US/ST=FL/L=Boca Raton/O=Pinkcoin Foundation Inc/OU=PinkPi/CN=localhost"

if [ -f $PEM ]
then
	INVALID=$(openssl x509 -checkend "$SECONDS" -noout -in "$PEM")
fi

if [ $INVALID -ne 0 ]
then
	# Ensure directory exists
	mkdir -p "$DIR"

	# Generate (sef-signed) SSL Certificate
	openssl genrsa -out "$DIR/server.pem" 4096
	openssl req -new -x509 -nodes -sha512 -subj "$SUBJECT" -days "$DAYS" -key "$DIR/server.pem" > "$DIR/server.cert"
fi
