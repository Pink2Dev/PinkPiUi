<?php
require __DIR__ . '/init.php';

try {
	$network = Input::post('network', 'string[]');
	$wallet = Input::post('wallet', 'string[]');

	// Join provided network
	if ($network) {
		$network['ssid'] = $network['ssid_other'] ?: $network['ssid'];

		if (!$network['ssid']) {
			throw new Exception('Invalid Network Identifier provided.');
		}
		if (!$network['password']) {
			throw new Exception('Invalid Network Password provided.');
		}

		$success[] = 'Your PinkPi will now connect to: ' . $network['ssid'];

		exec(sprintf('sudo /home/pi/scripts/network_assign.sh %s %s > /dev/null 2>&1 &',
			escapeshellarg($network['ssid']),
			escapeshellarg($network['password'])
		));
	}

	// Wallet
	if ($wallet) {
		$action = key($wallet);
		$wallet = $wallet[$action];

		switch ($action) {
			case 'download':
				if (!file_exists($datTarget)) {
					throw new Exception('No Wallet File found.');
				}

				$filename = 'wallet.dat';
				$filesize = filesize($datTarget);

				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header(sprintf('Content-Disposition: attachment; filename="%s"', $filename));
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header(sprintf('Content-Length: %d', $filesize));
				readfile($datTarget);
				exit;
			case 'encrypt':
				throw new Exception('Wallet Encryption is currently not supported.');

				$success[] = 'Pinkcoin will now close to finish the encryption process. Remember that encrypting your wallet cannot fully protect your coins from being stolen by malware infecting your computer.';
				$success[] = 'IMPORTANT: Any previous backups you have made of your wallet file should be replaced with the newly generated, encrypted wallet file. For security reasons, previous backups of the unencrypted wallet file will become useless as soon as you start using the new, encrypted wallet.';
			break;
			case 'transactions_clear':
				// Output cannot be recorded if we wish to release the console (i.e. use &)
				exec('sudo /home/pi/scripts/transactions_clear.sh > /dev/null 2>&1 &');

				$success[] = 'Wallet Transactions have been successfully cleared.';
			break;
			case 'transactions_scan':
				// Output cannot be recorded if we wish to release the console (i.e. use &)
				exec('sudo /home/pi/scripts/transactions_scan.sh > /dev/null 2>&1 &');

				$success[] = 'Starting Wallet Transaction rescanning has been successful.';
			break;
			case 'upgrade':
				// Output cannot be recorded if we wish to release the console (i.e. use &)
				exec('sudo /home/pi/scripts/wallet_upgrade.sh > /dev/null 2>&1 &');

				$success[] = 'Wallet Upgrade started... Pinkcoin is being restarted...';
			break;
			case 'upload':
				$datNew = __DIR__ . '/cache/wallet.new.dat';
				$upload = $_FILES['wallet']['upload'];

				if ($upload['error']) {
					throw new Exception($upload['error_string'] ?: 'Error code returned: ' . $upload['error']);
				}

				$finfo = new finfo(FILEINFO_MIME_TYPE);
				$mimeType = $finfo->file($upload['tmp_name']);

				if ($mimeType !== 'application/octet-stream') {
					throw new Exception('Unknown file format: ' . $mimeType);
				}

				$extension = pathinfo($upload['name'], PATHINFO_EXTENSION);
				if ($extension !== 'dat') {
					throw new Exception('Unexpected file extension: ' . $extension);
				}
				if (!move_uploaded_file($upload['tmp_name'], $datNew)) {
					throw new Exception('Upload failed. Please try again.');
				}

				$success[] = 'Your new wallet has been successfully uploaded.';
			break;
		}
	}
} catch (Exception $ex) {
	$errors[$ex->getCode()] = $ex->getMessage();
}

$datFileExists = file_exists($datTarget);
$datFileSize = $datFileExists ? filesize($datTarget) : 0;
$datFileSizeOutput = Util::formatFileSize($datFileSize);
$networks = getNetworkList();

$interface = getInterfaceStatus();
$wallet = getWalletStatus($info);
?>
<!doctype html>
<html lang="en">
<head>
	<?php
	$title[] = 'Settings';
	require INCLUDES . '/head.php';
	?>
</head>
<body>
<div class="container-fluid">
	<?php
	require INCLUDES . '/container.php';
	?>

	<h5><small>Wireless Network Settings</small></h5>
	<form method="post">
		<div class="form-row">
			<div class="form-group col-sm-12 col-md-6 col-xl-8">
				<label for="network-ssid" class="h6">Network</label>
				<div class="row align-items-start">
					<div class="col-8 col-xl-5">
						<select id="network-ssid" name="network[ssid]" class="form-control">
							<?php
							if ($networks) {
								foreach ($networks as $network) {
									echo '<option value="', $network, '">', $network, '</option>', PHP_EOL;
								}
							} else {
								echo '<option value="">No networks found</option>', PHP_EOL;
							}
							?>
						</select>
					</div>
					<div class="col-4 col-xl-2 order-xl-3">
						<button type="button" class="btn btn-secondary" onclick="window.location.reload();">
							<i class="fas fa-sm fa-sync-alt"></i>
							Refresh
						</button>
					</div>
					<div class="col-8 col-xl-5">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text">or</span>
							</div>
							<input type="text" name="network[ssid_other]" class="form-control" placeholder="Network SSID">
						</div>
					</div>
				</div>
			</div>
			<div class="form-group col-sm-12 col-md-6 col-xl-4">
				<label for="network-password" class="h6">Password</label>
				<div class="row">
					<div class="col-8">
						<input type="password" id="network-password" name="network[password]" class="form-control" placeholder="Password">
					</div>
					<div class="col-4">
						<button type="submit" class="btn btn-success">
							<i class="fas fa-sm fa-wifi"></i>
							Connect
						</button>
					</div>
				</div>
			</div>
		</div>
	</form>

	<hr class="mt-3 mb-3">

	<h5><small>PinkPi Interface</small></h5>
	<div class="row">
		<div class="col-12 col-sm-6 col-md-4">
			<h6>Installed Version</h6>
			<p class="h4"><?php echo $interface['version_installed']; ?></p>
		</div>
		<div class="col-12 col-sm-6 col-md-4">
			<h6>Current Version</h6>
			<p class="h4"><?php echo $interface['version_repository']; ?></p>
		</div>
		<div class="col-12 col-sm-6 col-md-4">
			<h6>Status</h6>
			<?php echo $interface['content']; ?>
		</div>
	</div>

	<hr class="mt-3 mb-3">

	<h5><small>Pinkcoin Wallet</small></h5>
	<form method="post">
		<div class="row">
			<div class="col-12 col-sm-6 col-md-4">
				<h6>Installed Version</h6>
				<p class="h4"><?php echo $wallet['version_installed']; ?></p>
			</div>
			<div class="col-12 col-sm-6 col-md-4">
				<h6>Current Version</h6>
				<p class="h4"><?php echo $wallet['version_repository']; ?></p>
			</div>
			<div class="col-12 col-sm-6 col-md-4">
				<h6>Status</h6>
				<?php echo $wallet['content']; ?>
			</div>
		</div>
	</form>

	<hr class="mt-3 mb-3">

	<h5><small>Encrypt Wallet File</small></h5>
	<form method="post">
		<div class="form-row">
			<div class="form-group col-12 col-md-6 col-xl-3">
				<label for="encrypt-passphrase" class="h6">Current Passphrase</label>
				<input type="password" id="encrypt-passphrase" name="wallet[passphrase][curent]" class="form-control" placeholder="Current Passphrase">
			</div>
			<div class="form-group col-12 col-md-6 col-xl-3">
				<label for="encrypt-passphrase-new" class="h6">New Passphrase</label>
				<input type="password" id="encrypt-passphrase-new" name="wallet[passphrase][new]" class="form-control" placeholder="New Passphrase">
				<p class="mb-0 form-text">Please use a passphrase of <strong>10 or more random characters</strong> or <strong>8 or more words</strong>.</p>
			</div>
			<div class="form-group col-12 col-md-12 col-xl-6">
				<label for="encrypt-passphrase-confirm" class="h6">Confirm Passphrase</label>
				<div class="row">
					<div class="mb-2 col-12 col-md-8">
						<input type="password" id="encrypt-passphrase-confirm" name="wallet[passphrase][confirm]" class="form-control" placeholder="Confirm Passphrase">
						<p class="mb-0 form-text">Warning: If you encrypt your wallet and lose your passphrase, you will <strong>LOSE ALL OF YOUR COINS</strong>!</p>
					</div>
					<div class="col-12 col-md-4">
						<button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you wish to encrypt your wallet?');">
							<i class="fas fa-sm fa-eye-slash"></i>
							Change Passphrase
						</button>
					</div>
				</div>
			</div>
		</div>
	</form>

	<hr class="mt-3 mb-3">

	<h5><small>Wallet Operations</small></h5>
	<div class="row">
		<div class="mb-4 col-12 col-sm-6 col-md-6 col-lg-6 col-xl-3">
			<form method="post">
				<h6>Download Wallet</h6>
				<p class="text-warning">
					Always encrypt your wallet with a strong passphrase to secure your wallet file. Without a passphrase, your private keys can become easily exposed.
				</p>
				<button type="submit" name="wallet[download]" class="btn btn-secondary">
					<i class="fas fa-sm fa-download"></i>
					Download Wallet (<?php echo $datFileSizeOutput; ?>)
				</button>
			</form>
		</div>
		<div class="mb-4 col-12 col-sm-6 col-md-6 col-xl-5">
			<form method="post" enctype="multipart/form-data">
				<h6>Upload Wallet</h6>
				<p class="mb-0 text-warning">
					<strong class="text-danger">THIS IS IRREVERSIBLE!</strong><br/>
					Please download your wallet file if it contains addresses you wish to use in the future.
				</p>
				<div class="row">
					<div class="col-12 col-lg-8">
						<div class="mb-2 custom-file">
							<label for="wallet-upload" class="custom-file-label">Choose file</label>
							<input type="file" id="wallet-upload" name="wallet[upload]" accept=".dat" class="custom-file-input">
						</div>
					</div>
					<div class="col-12 col-lg-4">
						<button type="submit" name="wallet[upload]" class="btn btn-secondary">
							<i class="fas fa-sm fa-upload"></i>
							Upload Wallet
						</button>
					</div>
				</div>
			</form>
		</div>
		<div class="mb-4 col-12 col-sm-6 col-md-4 col-xl-2">
			<form method="post">
				<h6>Clear Transactions</h6>
				<p class="mb-0 text-warning">
					Clears the transactions within your wallet file.
				</p>
				<p class="form-text">
					<small>Wallet will temporarily show no balances or transaction history</small>
				</p>
				<button type="submit" name="wallet[transactions_clear]" class="btn btn-secondary">
					<i class="fas fa-sm fa-trash"></i>
					Clear Transactions
				</button>
			</form>
		</div>
		<div class="mb-4 col-12 col-sm-6 col-md-4 col-xl-2">
			<form method="post">
				<h6>Rescan Transactions</h6>
				<p class="mb-0 text-warning">
					Rebuild the transaction history within the wallet file.
				</p>
				<p>
					<small>This may take up to several minutes to complete.</small>
				</p>
				<button type="submit" name="wallet[transactions_scan]" class="btn btn-secondary">
					<i class="fas fa-sm fa-history"></i>
					Rescan Transactions
				</button>
			</form>
		</div>
	</div>

	<?php
	require INCLUDES . '/footer.php';
	?>
</div>
</body>
</html>
