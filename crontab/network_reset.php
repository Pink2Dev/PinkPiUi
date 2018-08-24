<?php
require __DIR__ . '/init.php';

function isStaticNetwork() {
	$ip = exec('sudo ifconfig | grep -Eo "inet (addr:)?([0-9]*\.){3}[0-9]*" | grep -Eo "([0-9]*\.){3}[0-9]*" | grep -v "127.0.0.1"');

	return ($ip === '172.24.1.2');
}

function lastCredentialUpdate() {
	// Network Credentials
	$filename = '/etc/wpa_supplicant/wpa_supplicant.conf';
	$exists = file_exists($filename);
	$updated = $exists ? filemtime($filename) : 0;

	return $updated;
}

function lastOnlineStatus() {
	$lastOnline = 0;
	try {
		$filename = '/home/pi/cache/last_network_internet_success';
		$lastOnline = (int)Util::getFileContent($filename);
	} catch (Exception $ex) {
		// File Not Found
	}

	return $lastOnline;
}


// Do not proceed if we are on our static network (i.e. PinkPi)
$isStaticNetwork = isStaticNetwork();
if ($isStaticNetwork) {
	exit(1);
}

// Do not proceed if network credentials have changed recently
// (allow for connectivity and online checks)
$lastCredentialUpdate = lastCredentialUpdate();
$gracePeriod = strtotime('-5 minutes');
if ($lastCredentialUpdate > $gracePeriod) {
	exit(5);
}

// Do not proceed if there has been recent internet activity
$lastOnlineStatus = lastOnlineStatus();
$timeout = strtotime('-15 minutes');
if ($lastOnlineStatus > $timeout) {
	exit(15);
}

// if it has been <duration> since out last "online" status, revert to static network
exec('sudo /home/pi/scripts/network_reset.sh');
