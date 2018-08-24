<?php
require __DIR__ . '/init.php';

try {
	$sideStake = Input::post('side-stake', 'string[]');
	if ($sideStake) {
		if (!$sideStake['address']) {
			throw new Exception('Please enter a valid Pinkcoin address.');
		}

		$sideStakeOld = null;
		try {
			$sideStakeOld = getSideStake($sideStake['address']);
		} catch (Exception $ex) {
			// Ignore failed not found
		}
		if ($sideStakeOld) {
			throw new Exception('Side Stake already exists. Please use a different Pinkcoin address.');
		}

		pink2d('addstakeout', $sideStake['name'], $sideStake['address'], $sideStake['percentage']);

		$success[] = sprintf('Side-Stake <strong>%s</strong> has been successfully created.', $sideStake['name']);
		unset($_POST['side-stake']);
	}
} catch (Exception $ex) {
	$errors[$ex->getCode()] = $ex->getMessage();
}
?>
<!doctype html>
<html lang="en">
<head>
	<?php
	$title[] = 'Side Stakes';
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
			<h5><small>Create Side Stake</small></h5>
			<form method="post">
				<div class="form-group">
					<label for="side-stake-name" class="h6">Label</label>
					<input type="text" id="side-stake-name" name="side-stake[name]" value="<?php echo Form::value('side-stake','name'); ?>" class="form-control" placeholder="Label">
				</div>
				<div class="form-group">
					<label for="side-stake-address" class="h6">Address</label>
					<input type="text" id="side-stake-address" name="side-stake[address]" value="<?php echo Form::value('side-stake','address'); ?>" class="form-control" placeholder="Pinkcoin Address">
				</div>
				<div class="form-group">
					<label for="side-stake-percentage" class="h6">Percent</label>
					<input type="number" id="side-stake-percentage" name="side-stake[percentage]" value="<?php echo Form::value('side-stake','percentage') ?: 0; ?>" min="0" max="100" step="0.01" class="form-control">
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
								<i class="fas fa-sm fa-code-branch"></i>
								Create Side Stake
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
