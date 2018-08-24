<?php
require __DIR__ . '/init.php';

try {
	$address = Input::get('address');
	$sideStake = getSideStake($address);

	pink2d('delstakeout', $sideStake['address']);

	$success[] = sprintf('Side Stake <strong>%s</strong> has been successfully deleted.', $sideStake['name']);
} catch (Exception $ex) {
	$errors[$ex->getCode()] = $ex->getMessage();
}
?>
<!doctype html>
<html lang="en">
<head>
	<?php
	$title[] = 'Side Stakes';
	$title[] = 'Remove';
	require INCLUDES . '/head.php';
	?>
</head>
<body>
<div class="container-fluid">
	<?php
	require INCLUDES . '/container.php';
	?>

	<a href="side-stakes/" role="button" class="btn btn-secondary">
		<i class="fas fa-sm fa-chevron-left"></i>
		Back to Side Stakes
	</a>

	<?php
	require INCLUDES . '/footer.php';
	?>
</div>
</body>
</html>
