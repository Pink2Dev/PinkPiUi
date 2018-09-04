<?php
function alert_message($class, $message) {
	echo
	'<div class="alert alert-', $class, ' alert-dismissible fade show" role="alert">',
	$message,
	'<button type="button" class="close" data-dismiss="alert" aria-label="Close">',
	'<span aria-hidden="true">&times;</span>',
	'</button>',
	'</div>',
	PHP_EOL;
}

function alt($n) {
	return $n % 2 === 0;
}

function displayAddress(array $address, $index) {
	$actions = [];
	$actions['update'] = sprintf('<a href="addresses/update/?address=%s" class="ml-3 btn btn-secondary btn-sm"><i class="fas fa-sm fa-pen"></i> <span class="d-none d-sm-inline">Edit</span></a>', $address['address']);

	$alt = alt($index);

	echo
	'<div class="pt-2 pb-2 row', $alt ? ' row-alt' : '', '">',
		'<div class="col-5 col-md-4 text-truncate d-none d-md-block">',
			'<span class="h6">', $address['label'] ?: '<em class="text-muted">No label</em>', '</span>',
		'</div>',
		'<div class="col-10 col-md-6 text-truncate">',
			'<small>' . $address['address'] . '</small>',
		'</div>',
		'<div class="col-2 text-left">',
			implode(' ', $actions),
		'</div>',
	'</div>',
	PHP_EOL;
}

function displayPagination($total, $limit, $page=1, $base=null) {
	$pages = ceil($total / $limit);

	echo '<div class="mt-3 text-center">', PHP_EOL;
	for ($i = 1; $i <= $pages; $i++) {
		$active = $i == $page;
		$query = $_GET;
		$query['page'] = $i;
		$href = $base . '?' . http_build_query($query);

		echo '<a href="', $href, '" class="mb-1 mr-1 btn btn-secondary btn-sm', $active ? ' disabled' : '', '">', $i, '</a>', PHP_EOL;
	}
	echo '</div>', PHP_EOL;
}

function displaySideStake(array $sideStake, $index) {
	$actions = [];
	$actions['update'] = sprintf('<a href="side-stakes/update/?address=%s" class="ml-3 btn btn-secondary btn-sm"><i class="fas fa-sm fa-pen"></i> <span class="d-none d-sm-inline">Edit</span></a>', $sideStake['address']);
	$actions['remove'] = sprintf('<a href="side-stakes/remove/?address=%s" class="ml-3 btn btn-danger btn-sm" onclick="return confirm(\'' . $sideStake['name'] . '\nAre you sure you want to delete this Side Stake?\');"><i class="fas fa-sm fa-trash"></i> <span class="d-none d-sm-inline">Delete</span></a>', $sideStake['address']);

	$alt = alt($index);

	echo
	'<div class="pt-2 pb-2 row', $alt ? ' row-alt' : '', '">',
		'<div class="col-5 col-sm-6 col-md-3">',
			'<span class="h6">', $sideStake['name'] ?: '<em class="text-muted">No label</em>', '</span>',
		'</div>',
		'<div class="col-md-4 col-lg-5 text-truncate d-none d-md-block">',
			'<small>', $sideStake['address'], '</small>',
		'</div>',
		'<div class="col-2 col-sm-1">',
			$sideStake['percentage'],
		'</div>',
		'<div class="col-5 col-sm-5 col-md-4 col-lg-3 text-right">',
			implode(' ', $actions),
		'</div>',
	'</div>',
	PHP_EOL;
}

function displayTransaction(array $transaction, $index) {
	$alt = alt($index);
	$date = date('n-d / g:i A', $transaction['time']);
	$negative = $transaction['amount'] < 0;
	$outputAmount = outputAmount($transaction['amount']);
	$unconfirmed = $transaction['confirmations'] === 0;
	$year = (int)date('Y', $transaction['time']);

	$icon = '';
	switch ($transaction['category']) {
		case 'generate':
			$icon = 'fa-coins';
		break;
		case 'receive':
			$icon = 'fa-arrow-circle-left';
		break;
		case 'send':
			$icon = 'fa-arrow-circle-right negative';
		break;
	}

	echo
	'<div class="pt-2 pb-2 row', $alt ? ' row-alt' : '', $unconfirmed ? ' text-muted' : '', '">',
		'<div class="col-7 col-md-4">',
			'<i class="pr-2 fas fa-lg ', $icon, '"></i>',
			'<span class="d-none d-md-inline">' . $year . '-</span>', $date,
		'</div>',
		'<div class="col-5 col-md-5 text-truncate d-none d-md-block">',
			$transaction['account'] ?: '<small>' . $transaction['address'] . '</small>',
		'</div>',
		'<div class="col-5 col-md-3 text-right">',
			$negative ? '<strong class="negative">' . $outputAmount . '</strong>' : $outputAmount,
		'</div>',
	'</div>',
	PHP_EOL;
}

function getAddresses() {
	$accounts = pink2d('listaccounts');
	$addresses = [];

	foreach ($accounts as $label => $address) {
		$addresses[] = [
			'address' => $address,
			'label' => $label,
		];
	}

	return $addresses;
}

function getChance($weight, $networkWeight) {
	$chance = 0;
	$time = time();

	if ($networkWeight > 0) {
		$chance = ($weight * 10000) / $networkWeight;
		$isFlashPeriod = isFlashStake($time);
		$stakesPerHour = $isFlashPeriod ? 60 : 10;

		for ($i = 0; $i < $stakesPerHour; $i++) {
			$diff = 10000 - $chance;
			$chance += ($weight * $diff) / $networkWeight;
		}

		$chance /= 100;
		$chance = min(100, $chance);
	}

	return $chance;
}

/**
 * @param string $filename
 * @return \JSONRPC\Client
 * @throws Exception
 */
function getClient(array $config) {
	if (empty($config['hostname'])) {
		throw new Exception('Client configuration property required: hostname');
	}
	if (empty($config['port'])) {
		throw new Exception('Client configuration property required: port');
	}
	if (empty($config['username'])) {
		throw new Exception('Client configuration property required: username');
	}
	if (empty($config['password'])) {
		throw new Exception('Client configuration property required: password');
	}

	// JSON RPC Server
	$server = new \JSONRPC\Server($config['hostname'], $config['port'], $config['username'], $config['password']);
	if (array_key_exists('certificate', $config)) {
		$server->setCertificate($config['certificate']);
	}

	// JSON RPC Client
	$client = new \JSONRPC\Client();
	$client->setServer($server);

	return $client;
}

function getConversionRates() {
	$filename = __ROOT__ . '/cache/conversions.json';
	$conversions = Util::getFileContent($filename);

	return $conversions;
}

function getInterfaceRepository() {
	return 'https://github.com/Pink2Dev/PinkPiUi';
}

function getInterfaceStatus() {
	$filepath = '/home/pi/pinkpiui';
	$status = [];

	$status['version_installed'] = getVersionInstalled($filepath);
	$status['version_repository'] = getVersionLatest($filepath);

	$cmp = version_compare($status['version_installed'], $status['version_repository']);
	if ($cmp === -1) {
		$status['content'] = '<p class="h4 text-default">Updating...</p>';
	} else {
		$status['content'] = '<p class="h4 text-success">Up To Date</p>';
	}

	return $status;
}

function getLatestFolder($filepath) {
	$folders = glob($filepath . '/*', GLOB_ONLYDIR);
	$folder = end($folders); // Most recent

	return $folder;
}

function getNetworkList() {
	$command = 'sudo /home/pi/scripts/network_scan.sh';
	$networks = [];
	exec($command, $networks);

	$networks = array_map('parse_ssid', $networks);
	$networks = array_filter($networks);
	sort($networks);

	return $networks;
}

function getPeerInfo($peerInfo) {
	$heights = array_column($peerInfo, 'startingheight');
	$height = count($heights) ? max($heights) : 0;

	return [
		'blocks' => $height,
		'total' => count($peerInfo),
	];
}

function getPinkcoinWallet() {
	$filepath = '/home/pi/.pink2';
	$filename = $filepath . '/pinkconf.txt';
	$ini = Util::getFileContent($filename, 'ini');
	if (!is_array($ini)) {
		throw new Exception('Client configuration could not be processed.');
	}

	// $filename is adjusted to read like an INI file within PHP
	// however, Pinkcoin will read/use the quotes
	$config = [];
	$config['hostname'] = 'localhost';
	$config['port'] = $ini['rpcport'];
	$config['username'] = '"' . $ini['rpcuser'] . '"';
	$config['password'] = '"' . $ini['rpcpassword'] . '"';
	if (array_key_exists('rpcssl', $ini) && $ini['rpcssl']) {
		$config['certificate'] = $filepath . '/server.cert';
	}

	$client = getClient($config);

	return $client;
}

function getSideStake($address) {
	$sideStakes = getSideStakes();
	$sideStakes = array_column($sideStakes, null, 'address');
	if (!array_key_exists($address, $sideStakes)) {
		throw new Exception('Side Stake was not found.');
	}

	return $sideStakes[$address];
}

function getSideStakes() {
	$raw = pink2d('liststakeout');
	$sideStakes = [];

	// Clean up wallet response
	foreach ($raw as $index => $item) {
		$array = [];
		foreach ($item as $key => $value) {
			$key = trim($key);
			$key = trim($key, ':');
			$key = strtolower($key);
			$array[$key] = $value;
		}

		$sideStakes[] = $array;
	}

	// Sort by Name
	$names = array_column($sideStakes, 'name');
	array_multisort(
		$names, SORT_ASC, SORT_STRING,
		$sideStakes
	);

	return $sideStakes;
}

function getTransactionHistory($account=null, $count=10, $offset=0) {
	$account = $account ?: '*';
	$history = pink2d('listtransactions', $account, $count, $offset);
	$history = array_reverse($history);

	return $history;
}

function getTransactions(array $transactionIds) {
	if (count($transactionIds) === 0) {
		return [];
	}

	$pinkcoin = getPinkcoinWallet();
	foreach ($transactionIds as $transactionId) {
		$pinkcoin->gettransaction($transactionId);
	}
	$transactions = $pinkcoin->send(false);

	$details = [];
	foreach ($transactions as $tx) {
		$inputs = [];
		foreach ($tx['vin'] as $i => $input) {
			$inputs[$input['txid']] = null;
		}

		$outputs = [];
		foreach ($tx['vout'] as $j => $output) {
			if (isset($output['scriptPubKey']['addresses'])) {
				foreach ($output['scriptPubKey']['addresses'] as $address) {
					$outputs[$address] = $output['value'];
				}
			}
		}

		$tx['vin'] = array_keys($inputs);
		$tx['vout'] = $outputs;

		$details[$tx['txid']] = $tx;
	}

	return $details;
}

function getVersionInstalled($filepath) {
	$filename = $filepath . '/VERSION';
	$version = '0.0.0';
	try {
		$version = Util::getFileContent($filename);
		$version = trim($version);
	} catch (Exception $ex) {
		// e.g. File Not Found
	}

	return $version;
}

function getVersionLatest($filepath) {
	$folder = getLatestFolder($filepath);
	$version = '0.0.0';
	if ($folder) {
		$filename = $folder . '/VERSION';
		try {
			$version = Util::getFileContent($filename);
			$version = trim($version);
		} catch (Exception $ex) {
			// e.g. File not found
		}
	}

	return $version;
}

function getWalletRepository() {
	return 'https://github.com/Pink2Dev/Pink2';
}

function getWalletStatus(array $info) {
	$filepath = '/home/pi/pinkcoin';
	$status = [];

	// Network Status
	$height = $info['mining'] ? $info['mining']['blocks'] : 0;
	$heightNetwork = $info['peer'] ? $info['peer']['blocks'] : 0;
	$heightSyncing = $heightNetwork - $height;

	$status['version_installed'] = getVersionInstalled($filepath);
	$status['version_repository'] = getVersionLatest($filepath);

	$cmp = version_compare($status['version_installed'], $status['version_repository']);
	if ($cmp === -1) {
		$folder = getLatestFolder($filepath);
		$filename = $folder . '/src/pink2d';
		$exists = file_exists($filename);

		if ($folder && $exists) {
			$status['content'] =
				'<p class="h4 text-primary">
					<button type="submit" name="wallet[upgrade]" class="btn btn-primary">
						<i class="fas fa-sm fa-arrow-up"></i>
						Upgrade to ' . $status['version_repository'] . '
					</button>
				</p>';
		} else {
			$status['content'] = '<p class="h4 text-default">Updating...</p>';
		}
	} else {
		// Pinkcoin Wallet running
		$command = 'pgrep pink2d';
		$pid = (int)exec($command);
		$running = $pid > 0;
		if ($running) {
			if ($heightSyncing > 0) {
				$percentage = $heightNetwork ? floor(($height / $heightNetwork) * 100) . '%' : '';
				$status['content'] = '<p class="h4 text-warning">Syncing... ' . $percentage . '</p>';
			} else {
				$status['content'] = '<p class="h4 text-success">Running</p>';
			}
		} else {
			$status['content'] = '<p class="h4 text-danger">Not Running</p>';
		}
	}

	return $status;
}

function isFlashStake($time) {
	static $hours = [
		1, // 5PM PST
		6, // 10PM PST
		15, // 7AM PST
		20, // 12PM PST
	];
	$hour = (int)date('G', $time);

	return in_array($hour, $hours);
}

function footer_status($status) {
	echo
	'<div class="col">',
	'<i class="fas fa-2x ', $status['icon'], '" title="', $status['title'], '" data-toggle="tooltip"></i><br>',
	'<small>', $status['content'], '</small>',
	'</div>',
	PHP_EOL;
}

function footer_statuses($info) {
	$statuses = [];

	// Network Status
	$height = $info['mining']['blocks'];
	$heightNetwork = $info['peer']['blocks'];
	$heightSyncing = $heightNetwork - $height;

	$block = pink2d('getblockbynumber', $height);
	$lastSeen = $block['time'];

	$status = [];
	if ($heightNetwork > 0 && $heightSyncing <= 0) {
		$between = Util::betweenTimestamps($lastSeen);
		$stubs = array_slice($between['stubs'], 0, 2);

		$status['content'] = 'Up to date';
		$status['icon'] = 'fa-check';
		$status['title'] = sprintf('Highest block %s was seen %s ago', number_format($heightNetwork), implode(' ', $stubs));
	} else {
		$status['content'] = sprintf('Syncing %s blocks', number_format($heightSyncing));
		$status['icon'] = 'fa-sync negative';
		$status['title'] = sprintf('Downloading block %s / %s of transaction history', number_format($height), number_format($heightNetwork));
	}
	$statuses[] = $status;

	// Wallet Encryption
	$status = [];
	if ($info['wallet']['encrypted']) {
		$status['content'] = 'Encrypted';
		$status['icon'] = 'fa-eye-slash';
		$status['title'] = 'Wallet file is safely stored';
	} else {
		$status['content'] = 'Decrypted';
		$status['icon'] = 'fa-eye negative';
		$status['title'] = 'Wallet file is insecurely stored';
	}
	$statuses[] = $status;

	// Wallet Lock Status
	$status = [];
	if ($info['wallet']['unlocked']) {
		$status['content'] = 'Unlocked';
		$status['icon'] = 'fa-lock-open';
		$status['title'] = 'Wallet is currently unlocked';
	} else {
		$status['content'] = 'Locked';
		$status['icon'] = 'fa-lock negative';
		$status['title'] = 'Wallet is currently locked';
	}
	$statuses[] = $status;

	// Wallet Connections
	$walletConnections = $info['peer']['total'];
	$status = [];
	$status['content'] = sprintf('%s connections', number_format($walletConnections));
	$status['icon'] = 'fa-signal';
	if (!$walletConnections) {
		$status['icon'] .= ' negative';
	}
	$status['title'] = 'Number of wallets connected to on the network';
	$statuses[] = $status;

	return $statuses;
}

function outputAmount($amount, $precision=8) {
	$number = number_format($amount, $precision, '.', '');
	$array = explode('.', $number);
	$suffix = isset($array[1]) ? '<small>.' . $array[1] . '</small>' : '';

	return number_format($array[0], 0) . $suffix;
}

function outputTitle(array $title) {
	$title = array_reverse($title);
	$title = implode(' - ', $title);

	return $title;
}

function parse_ssid($ssid) {
	$ssid = str_replace('SSID:', '', $ssid);
	$ssid = trim($ssid);
	return $ssid;
}

function pink2d(/* varadiac */) {
	$arguments = func_get_args();
	$command = array_shift($arguments);

	$client = getPinkcoinWallet();
	call_user_func_array([$client,$command], $arguments);
	$response = $client->send();

	if ($response instanceof \JSONRPC\Error) {
		$message = 'Pinkcoin Wallet: ' . nl2br($response->getMessage());
		throw new Exception($message, $response->getCode());
	}

	if (is_array($response) && array_key_exists('error', $response)) {
		$error = $response['error'];
		$message = 'Pinkcoin Wallet: ' . nl2br($error['message']);
		throw new Exception($message, $error['code']);
	}

	return $response;
}

function walletLock(array $walletInfo) {
	// "Error: running with an unencrypted wallet, but walletlock was called. (code -15)"
	if ($walletInfo['encrypted']) {
		pink2d('walletlock');
	}

	return true;
}

function walletUnlock(array $walletInfo, $passphrase, $timeout=1, $stakingOnly=true) {
	if (!$passphrase) {
		throw new Exception('Please provide a Passphrase');
	}

	// "Wallet is already unlocked, use walletlock first if need to change unlock settings."
	walletLock($walletInfo);

	// "Error: running with an unencrypted wallet, but walletpassphrase was called. (code -15)"
	if ($walletInfo['encrypted']) {
		pink2d('walletpassphrase', $passphrase, $timeout, $stakingOnly);
	}

	return true;
}

function walletUnlockState(array $walletInfo, $passphrase) {
	if ($walletInfo['unlocked']) {
		$stakingOnly = $walletInfo['unlocked_staking_only'];
		$timeout = 1;

		if (!$stakingOnly) {
			$now = time();
			$timeout = max($timeout, $walletInfo['unlocked_until'] - $now);
		}

		walletUnlock($walletInfo, $passphrase, $timeout, $stakingOnly);
	}
}
