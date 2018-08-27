#!/bin/bash
iw dev wlan0 scan | grep SSID | sort --unique
