<?php
define('__ROOT__', __DIR__);
define('__HOME__', __DIR__ . '/html');
define('INCLUDES', __ROOT__ . '/inc');

require __DIR__ . '/autoload.php';
require __DIR__ . '/classes/bcmath.php';
require INCLUDES . '/functions.php';

Form::organizeFilesArray($_FILES);

// Globals
$base = '/';
$datTarget = '/home/pi/.pink2/wallet.dat';
$info = [
	'mining' => [],
	'peer' => [],
	'staking' => [],
	'wallet' => [],
];

$location = ltrim(str_replace('index.php', '', $_SERVER['PHP_SELF']), '/');

// Global messages
$errors = [];
$success = [];

// Template
$title = [
	'PinkPi',
];

// Pagination
$limit = Input::get('limit', 'int', 0, 100) ?: 25;
$pages = Input::get('pages', 'int', 1) ?: 100;
$page = Input::get('page', 'int', 1) ?: 1;
$page = min($page, $pages);
$offset = ($page - 1) * $limit;

$footerStatuses = [];
try {
	// Global Information
	$pinkcoin = getPinkcoinWallet();

	$pinkcoin->getmininginfo(); // [blocks,netstakeweight]
	$pinkcoin->getpeerinfo(); // count(), [startingheight]
	$pinkcoin->getstakinginfo(); // [staking,weight]
	$pinkcoin->getwalletinfo(); // [balance,txcount,unlocked_until]

	list($info['mining'], $peers, $info['staking'], $info['wallet']) = $pinkcoin->send(false);
	$info['peer'] = getPeerInfo($peers);

	$info['wallet']['encrypted'] = array_key_exists('unlocked_until', $info['wallet']);
	$info['wallet']['unlocked'] = true;
	$info['wallet']['unlocked_staking_only'] = false;
	if ($info['wallet']['encrypted']) {
		$info['wallet']['unlocked'] = $info['wallet']['unlocked_until'] > time() || $info['staking']['staking'];
		$info['wallet']['unlocked_staking_only'] = !$info['wallet']['unlocked_until'] && $info['staking']['staking'];
	}

	// Footer
	$footerStatuses = footer_statuses($info);
} catch (Exception $ex) {
	$errors[$ex->getCode()] = $ex->getMessage();
}
