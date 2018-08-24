<?php
require __DIR__ . '/init.php';

$sideStakes = [];
try {
	$sideStakes = getSideStakes();
} catch (Exception $ex) {
	$errors[$ex->getCode()] = $ex->getMessage();
}
?>
<!doctype html>
<html lang="en">
<head>
	<?php
	$title[] = 'Side Stakes';
	require INCLUDES . '/head.php';
	?>
</head>
<body>
<div class="container-fluid">
	<?php
	require INCLUDES . '/container.php';
	?>

	<div class="mb-2 row">
		<div class="col">
			<h5><small>Side Stakes</small></h5>
		</div>
		<div class="col text-right">
			<a href="side-stakes/create/" class="btn btn-success btn-sm">
				<i class="fas fa-sm fa-plus"></i>
				Create Side Stake
			</a>
		</div>
	</div>
	<?php
	array_walk($sideStakes, 'displaySideStake');
	?>

	<?php
	require INCLUDES . '/footer.php';
	?>
</div>
</body>
</html>
