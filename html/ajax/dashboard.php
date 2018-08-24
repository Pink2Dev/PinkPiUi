<?php
require __DIR__ . '/init.php';

$response = new \Response\API\JSON();

try {
	$wallet = Pinkcoin::getInstance();

	$info = $wallet->getinfo();
	$staking = $wallet->getstakinginfo();
	print_r($info);
	print_r($staking);

	$response->response = [
		'wallet' => [
			'balance' => 70500072395721,
			'minted' => 188000,
			'chance' => 4, // Percent
			'weight' => 96021787,
		],
		'network' => [
			'balance' => 39752354949070001,
			'minted' => 0,
			'chance' => 0,
			'weight' => 11709972213,
		],
	];
} catch (Exception $ex) {
	$response->setException($ex);
}

$response->render();
