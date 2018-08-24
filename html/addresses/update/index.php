<?php
require __DIR__ . '/init.php';

try {
	$address = Input::get('address');

	// Form Submission
	$inbound = Input::post('inbound', 'string[]');
	if ($inbound) {
		$unlockedUntil = $info['wallet']['encrypted'] ? $info['wallet']['unlocked_until'] : null;

		if ($info['wallet']['encrypted']) {
			if (!$inbound['passphrase']) {
				throw new Exception('Please enter a Passphrase');
			}

			// "Wallet is already unlocked, use walletlock first if need to change unlock settings."
			pink2d('walletlock');

			// Unlock the wallet to send the transaction
			$until = strtotime('+1 minute');
			pink2d('walletpassphrase', $inbound['passphrase'], $until);
		}

		// Assign "Label" (account) to the Pinkcoin Address
		pink2d('setaccount', $inbound['address'], $inbound['label']);

		if ($info['wallet']['encrypted']) {
			// Re-lock wallet
			pink2d('walletlock');

			// Restore original unlocked state
			if ($info['wallet']['unlocked']) {
				pink2d('walletpassphrase', $inbound['passphrase'], $unlockedUntil, $info['wallet']['unlocked_staking_only']);
			}
		}

		$success[] = sprintf('Address <strong>%s</strong> (%s) has been successfully updated.', $inbound['label'], $inbound['address']);
		unset($_POST['address']);
	} else {
		// All addresses
		$addresses = getAddresses();
		$inbounds = array_column($addresses, null, 'address');

		if (!array_key_exists($address, $inbounds)) {
			throw new Exception('Address was not found.');
		}

		$inbound = $inbounds[$address];
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
	$title[] = $inbound['label'] ?: 'Update';
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
			<h5><small>Update Address</small></h5>
			<form method="post">
				<div class="form-group">
					<label for="inbound-label" class="h6">Address Label</label>
					<input id="inbound-label" name="inbound[label]" value="<?php echo $inbound['label']; ?>" class="form-control" placeholder="Address Label">
				</div>
				<div class="form-group">
					<label for="inbound-address" class="h6">Address</label>
					<input type="text" id="inbound-address" name="inbound[address]" value="<?php echo $inbound['address']; ?>" readonly class="form-control" placeholder="Pinkcoin Address">
					<p class="form-text">Address cannot be changed.</p>
				</div>
				<div class="form-group <?php if (!$info['wallet']['encrypted']) echo ' d-none'; ?>">
					<label for="inbound-passphrase" class="h6">Passphrase</label>
					<input type="password" id="inbound-passphrase" name="inbound[passphrase]" class="form-control" placeholder="Passphrase">
					<p class="form-text text-warning">
						Providing an incorrect passphrase will lock your wallet.
					</p>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col">
							<a href="addresses/" role="button" class="btn btn-secondary">
								<i class="fas fa-sm fa-chevron-left"></i>
								Back<span class="d-none d-sm-inline"> to Addresses</span>
							</a>
						</div>
						<div class="col text-right">
							<button type="submit" class="btn btn-success">
								<i class="fas fa-sm fa-key"></i>
								Save Address
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
