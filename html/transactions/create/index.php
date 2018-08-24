<?php
require __DIR__ . '/init.php';

try {
	// Form Submission
	$outbound = Input::post('outbound', 'string[]');
	if ($outbound) {
		$outbound['amount'] = Input::validate($outbound['amount'], 'float', 0);

		if (!$outbound['address']) {
			throw new Exception('Please enter a valid Pinkcoin address.');
		}
		if ($outbound['amount'] <= 0.0) {
			throw new Exception('Please enter a valid Amount.');
		}

		$unlockedUntil = $info['wallet']['encrypted'] ? $info['wallet']['unlocked_until'] : null;

		if ($info['wallet']['encrypted']) {
			if (!$outbound['passphrase']) {
				throw new Exception('Please enter a Passphrase');
			}

			// "Wallet is already unlocked, use walletlock first if need to change unlock settings."
			if ($info['wallet']['unlocked']) {
				pink2d('walletlock');
			}

			// Unlock the wallet to send the transaction
			$until = strtotime('+5 minutes');
			pink2d('walletpassphrase', $outbound['passphrase'], $until);
		}

		// Send the transaction
		$txid = pink2d('sendtoaddress', $outbound['address'], $outbound['amount']);

		if ($info['wallet']['encrypted']) {
			// Re-lock wallet
			pink2d('walletlock');

			// Restore original unlocked state
			if ($info['wallet']['unlocked']) {
				pink2d('walletpassphrase', $outbound['passphrase'], $unlockedUntil, $info['wallet']['unlocked_staking_only']);
			}
		}

		$success[] = sprintf('Transaction <strong>%f PINK</strong> to <strong>%s</strong> has been successfully sent.', $outbound['amount'], $outbound['address']);
		unset($_POST['outgoing']);
	}
} catch (Exception $ex) {
	$errors[$ex->getCode()] = $ex->getMessage();
}
?>
<!doctype html>
<html lang="en">
<head>
	<?php
	$title[] = 'Transactions';
	$title[] = 'Create';
	require INCLUDES . '/head.php';
	?>
</head>
<body>
<div class="container-fluid">
	<?php
	require INCLUDES . '/container.php';
	?>

	<div class="row justify-content-center">
		<div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-4">
			<h5><small>Create Transaction</small></h5>
			<form method="post">
				<div class="form-group">
					<label for="outbound-address" class="h6">Pay To</label>
					<input id="outbound-address" name="outbound[address]" value="<?php echo Form::value('outbound','address'); ?>" class="form-control" placeholder="Pinkcoin Address">
				</div>
				<div class="form-group">
					<label for="outbound-amount" class="h6">Amount</label>
					<div class="input-group">
						<input type="number" id="outbound-amount" name="outbound[amount]" value="<?php echo Form::value('outbound','amount'); ?>" min="0" step="0.000001" class="form-control" placeholder="Amount">
						<div class="input-group-append">
							<span class="input-group-text">PINK</span>
						</div>
					</div>
				</div>
				<div class="form-group <?php if (!$info['wallet']['encrypted']) echo ' d-none'; ?>">
					<label for="outbound-passphrase" class="h6">Passphrase</label>
					<input type="password" id="outbound-passphrase" name="outbound[passphrase]" class="form-control" placeholder="Passphrase">
					<p class="form-text text-warning">
						Providing an incorrect passphrase will lock your wallet.
					</p>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col">
							<a href="transactions/" role="button" class="btn btn-secondary">
								<i class="fas fa-sm fa-chevron-left"></i>
								Back<span class="d-none d-sm-inline"> to Transactions</span>
							</a>
						</div>
						<div class="col text-right">
							<button type="submit" class="btn btn-success">
								<i class="fas fa-sm fa-arrow-alt-circle-right"></i>
								Send Pinkcoin
							</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

	<?php
	require INCLUDES . '/footer.php';
	?>
</div>
</body>
</html>
