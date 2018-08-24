<?php
require __DIR__ . '/init.php';

$balance = 0.0;
$cad = 0.0;
$networkWeight = 0;
$staked = 0.0;
$transactions = [];
$weight = 0;

try {
	// Wallet Information
	if ($info['wallet']) {
		$balance = $info['wallet']['balance'];
	}

	// Staking Information
	if ($info['staking']) {
		$networkWeight = $info['staking']['netstakeweight'];
		$weight = $info['staking']['weight'];
	}

	// Most recent 10 transactions
	$transactions = getTransactionHistory(null, 10);

	// Unconfirmed Transactions (calculate stake value)
	$unconfirmed = pink2d('listunspent', 0, 21);
	if (count($unconfirmed) > 0) {
		//print_r($unconfirmed);
		foreach ($unconfirmed as $tx) {
			if (array_key_exists('generated', $tx) && $tx['generated']) {
				$staked += $tx['amount'];
			}
		}
	}

	// Conversion rates
	$conversions = getConversionRates();
	$cad = bcmul($conversions['PINK_CAD'], $balance, 8);
} catch (Exception $ex) {
	$errors[$ex->getCode()] = $ex->getMessage();
}


$chance = getChance($weight, $networkWeight);
?>
<!doctype html>
<html lang="en">
<head>
	<?php
	$title[] = 'Dashboard';
	require INCLUDES . '/head.php';
	?>
</head>
<body>
<div class="container-fluid">
	<?php
	require INCLUDES . '/container.php';
	?>

	<h5><small>Wallet</small></h5>
	<div class="row">
		<div class="col-12 col-sm-6 col-md-4 col-lg-3">
			<h6>Balance</h6>
			<p class="h4"><?php echo outputAmount($balance); ?> PINK</small></p>
		</div>
		<div class="col-12 col-sm-6 col-md-4 col-lg-3">
			<h6>Canadian Dollar</h6>
			<p class="h4">$ <?php echo outputAmount($cad, 2); ?> CAD</small></p>
		</div>
	</div>

	<hr class="mt-3 mb-3">

	<h5 class="mb-3"><small>Staking</small></h5>
	<div class="row">
		<div class="col-12 col-sm-6 col-md-4 col-lg-3">
			<h6>Staked</h6>
			<p class="h4"><?php echo outputAmount($staked); ?></p>
		</div>
		<div class="col-12 col-sm-6 col-md-2 col-lg-3">
			<h6>Chance</h6>
			<p class="h4<?php if (!$info['staking'] || !$info['staking']['staking']) echo ' text-muted'; ?>"><?php echo round($chance, 2); ?>% <i class="fas fa-question-circle fa-sm p-1" data-toggle="tooltip" title="Your estimated chance to receive a stake within the next hour."></i></p>
		</div>
		<div class="col-12 col-sm-6 col-md-3">
			<h6>Weight</h6>
			<p class="h4"><?php echo number_format($weight); ?></p>
		</div>
		<div class="col-12 col-sm-6 col-md-3">
			<h6>Network Weight</h6>
			<p class="h4"><?php echo number_format($networkWeight); ?></small></p>
		</div>
	</div>

	<hr class="mt-3 mb-3">

	<h5><small>Recent Transactions</small></h5>
	<?php
	array_walk($transactions, 'displayTransaction');
	?>

	<?php
	require INCLUDES . '/footer.php';
	?>
</div>
</body>
</html>
