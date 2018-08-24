<?php
require __DIR__ . '/init.php';

try {
	// Form Submission
	$inbound = Input::post('inbound', 'string[]');
	if ($inbound) {
		$walletInfo = $info['wallet'];

		// Unlock wallet to create a new address
		$stakingOnly = false;
		$timeout = 1; // TODO Can this timeout subsequent calls (increase)?
		walletUnlock($info['wallet'], $inbound['passphrase'], $timeout, $stakingOnly);

		// Obtain most recent, unused address under "Label" (account)
		// Note: Account and a new address is created upon Account not found
		$inbound['address'] = pink2d('getaccountaddress', $inbound['label']);

		// Restore original unlocked state
		walletUnlockState($walletInfo, $inbound['passphrase']);

		if ($inbound['label']) {
			$success[] = sprintf('Address <strong>%s</strong> (<small>%s</small>) has been successfully created.', $inbound['label'], $inbound['address']);
		} else {
			$success[] = sprintf('Address <strong>%s</strong> has been successfully created.', $inbound['address']);
		}
		unset($_POST['inbound']);
	}
} catch (Exception $ex) {
	$errors[$ex->getCode()] = $ex->getMessage();
}
?>
<!doctype html>
<html lang="en">
<head>
	<?php
	$title[] = 'Addresses';
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
			<h5><small>New Address</small></h5>
			<form method="post">
				<div class="form-group">
					<label for="inbound-label" class="h6">Address Label <small>Optional</small></label>
					<input id="inbound-label" name="inbound[label]" value="<?php echo Form::value('inbound','label'); ?>" class="form-control" placeholder="Address Label">
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
								Get New Address
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
