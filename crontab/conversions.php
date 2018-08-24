<?php
require __DIR__ . '/init.php';

try {
	$request = new \Request\Common();
	$request->setURL('https://price.with.pink/api/');
	$request->setFormat('json');

	$response = $request->make();
	if (!$response || !$response['success']) {
		throw new Exception('Invalid response: ' . gettype($response));
	}

	$conversions = $response['conversions'];

	$content = json_encode($conversions);

	$filename = __ROOT__ . '/cache/conversions.json';
	$result = file_put_contents($filename, $content, LOCK_EX);
	if ($result === false) {
		throw new Exception('Failed to write content');
	}
} catch (Exception $ex) {
	$errNo = $ex->getCode();

	switch ($errNo) {
		case 6: // Could not resolve
		case 7: // ''
		case 28: // Resolving timed out after # milliseconds
		break;

		default:
			echo $errNo, ': ', $ex->getMessage(), PHP_EOL;
	}
	exit(1);
}
