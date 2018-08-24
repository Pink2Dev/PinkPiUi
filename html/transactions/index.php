<?php
require __DIR__ . '/init.php';

$total = 1;
$transactions = [];
try {
	// Pagination
	if ($info['wallet']) {
		$total = $info['wallet']['txcount'];
		$total = min($total, $pages * $limit);
	}

	// Transactions
	$transactions = getTransactionHistory(null, $limit, $offset);
} catch (Exception $ex) {
	$errors[$ex->getCode()] = $ex->getMessage();
}
?>
<!doctype html>
<html lang="en">
<head>
	<?php
	$title[] = 'Transactions';
	require INCLUDES . '/head.php';
	?>
</head>
<body>
<div class="container-fluid">
	<?php
	require INCLUDES . '/container.php';
	?>

	<div class="mb-2 row">
		<div class="col-5">
			<h5><small>Transactions</small></h5>
		</div>
		<div class="col-7 text-right">
			<a href="addresses/" class="btn btn-secondary btn-sm">
				<i class="fas fa-sm fa-arrow-alt-circle-left"></i>
				Receive<span class="d-none d-sm-inline"> Pinkcoin</span>
			</a>
			<a href="transactions/create/" class="btn btn-success btn-sm">
				<i class="fas fa-sm fa-arrow-alt-circle-right"></i>
				Send<span class="d-none d-sm-inline"> Pinkcoin</span>
			</a>
		</div>
	</div>
	<?php
	array_walk($transactions, 'displayTransaction');

	displayPagination($total, $limit, $page, $location);
	?>

	<?php
	require INCLUDES . '/footer.php';
	?>
</div>
</body>
</html>
