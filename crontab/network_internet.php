<?php
require __DIR__ . '/init.php';

function recordSuccess() {
	$filename = __DIR__ . '/../cache/last_network_internet_success';
	$now = time();

	$bytes = file_put_contents($filename, $now);

	return $bytes !== false;
}


$hostnames = [
	// Cloudflare
	'1.0.0.1',
	'1.1.1.1',
	// Google
	'8.8.4.4',
	'8.8.8.8',
	// Quad9
	'9.9.9.9',
	'149.112.112.112',
	// Verisign
	'64.6.64.6',
	'64.6.65.6',
	// OpenDNS Home
	'208.67.220.220',
	'208.67.222.222',
	// Level3
	'209.244.0.3',
	'209.244.0.4',
	// Dyn
	'216.146.35.35',
	'216.146.36.36',
];
shuffle($hostnames);

foreach ($hostnames as $hostname) {
	$success = (int) exec(sprintf('ping -q -w 1 -c 1 %s > /dev/null 2>&1 && echo 1 || echo 0', $hostname));
	if ($success) {
		recordSuccess();
		exit(0);
	}
}

exit(1);
