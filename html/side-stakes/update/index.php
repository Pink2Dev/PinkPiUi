<?php
require __DIR__ . '/init.php';

try {
	$address = Input::get('address');

	// Use submission data
	$sideStake = Input::post('side-stake', 'string[]');
	if ($sideStake) {
		pink2d('addstakeout', $sideStake['name'], $sideStake['address'], $sideStake['percentage']);

		$success[] = sprintf('Side Stake <strong>%s</strong> has been successfully updated.', $sideStake['name']);
		unset($_POST['side-stake']);
	} else {
		$sideStake = getSideStake($address);
	}
} catch (Exception $ex) {
	$errors[$ex->getCode()] = $ex->getMessage();
}

$percentage = trim($sideStake['percentage'], '%');
?>
<!doctype html>
<html lang="en">
<head>
	<?php
	$title[] = 'Side Stakes';
	$title[] = $sideStake['name'] ?: 'Update';
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
			<h5><small>Update Side Stake</small></h5>
			<form method="post">
				<div class="form-group">
					<label for="side-stake-name" class="h6">Label</label>
					<input type="text" id="side-stake-name" name="side-stake[name]" value="<?php echo $sideStake['name']; ?>" class="form-control" placeholder="Label">
				</div>
				<div class="form-group">
					<label for="side-stake-address" class="h6">Address</label>
					<input type="text" id="side-stake-address" name="side-stake[address]" value="<?php echo $sideStake['address']; ?>" readonly class="form-control" placeholder="Pinkcoin Address">
					<p class="form-text">To change the address, please delete and re-create the side-stake with the new address.</p>
				</div>
				<div class="form-group">
					<label for="side-stake-percentage" class="h6">Percent</label>
					<input type="number" id="side-stake-percentage" name="side-stake[percentage]" value="<?php echo $percentage; ?>" min="0" max="100" step="0.01" class="form-control">
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col">
							<a href="side-stakes/" role="button" class="btn btn-secondary">
								<i class="fas fa-sm fa-chevron-left"></i>
								Back<span class="d-none d-sm-inline"> to Side Stakes</span>
							</a>
						</div>
						<div class="col text-right">
							<button type="submit" class="btn btn-success">
								<i class="fas fa-sm fa-save"></i>
								Update Side Stake
							</button>
						</div>
					</div>
				</div>
			</form>
			<p class="text-warning">
				WARNING: Most services (such as Exchanges) do not currently support receiving side stakes.
				If you set your stakes to go to an exchange or other service, they will not credit to your account.
				Please do not set your stakes to go to a service unless you are sure they support the feature.
			</p>
		</div>
	</div>

	<?php
	require INCLUDES . '/footer.php';
	?>
</div>
</body>
</html>
