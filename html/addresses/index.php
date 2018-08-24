<?php
require __DIR__ . '/init.php';

$addresses = [];
$total = 1;
try {
	// Addresses
	$addresses = getAddresses();
	// Sort by Label
	$labels = array_column($addresses, 'label');
	$address = array_column($addresses, 'address');
	array_multisort(
		$labels, SORT_STRING, SORT_ASC,
		$address, SORT_STRING, SORT_ASC,
		$addresses
	);

	// Pagination
	$total = count($addresses);
	$total = min($total, $pages * $limit);
} catch (Exception $ex) {
	$errors[$ex->getCode()] = $ex->getMessage();
}
?>
<!doctype html>
<html lang="en">
<head>
	<?php
	$title[] = 'Addresses';
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
			<h5><small>Addresses</small></h5>
		</div>
		<div class="col text-right">
			<a href="addresses/create/" class="btn btn-success btn-sm">
				<i class="fas fa-sm fa-key"></i>
				New Address
			</a>
		</div>
	</div>
	<?php
	array_walk($addresses, 'displayAddress');

	displayPagination($total, $limit, $page, $location);
	?>

	<?php
	require INCLUDES . '/footer.php';
	?>
</div>
</body>
</html>
