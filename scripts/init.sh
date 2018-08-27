#!/bin/bash

HOME="/home/pi"

# Auto-heal
sudo dpkg --configure -a

# Check certificates
cd "$HOME/scripts" && ./wallet_ssl.sh >> wallet_ssl.log 2>&1
