<div class="no-gutters row nav mb-4" role="navigation">
	<div class="active nav-item text-center col">
		<a class="nav-link <?php if (!$location) echo 'active'; ?>" href="">
			<i class="p-2 fas fa-3x fa-chart-pie"></i><br>
			Dashboard
		</a>
	</div>
	<div class="nav-item text-center col">
		<a class="nav-link <?php if ($location === 'transactions/') echo 'active'; ?>" href="transactions/">
			<i class="p-2 fas fa-3x fa-exchange-alt"></i><br>
			Transactions
		</a>
	</div>
	<div class="nav-item text-center col">
		<a class="nav-link <?php if ($location === 'side-stakes/') echo 'active'; ?>" href="side-stakes/">
			<i class="p-2 fas fa-3x fa-code-branch"></i><br>
			Side Stakes
		</a>
	</div>
	<div class="nav-item text-center col">
		<a class="nav-link <?php if ($location === 'settings/') echo 'active'; ?>" href="settings/">
			<i class="p-2 fas fa-3x fa-cog"></i><br>
			Settings
		</a>
	</div>
</div>
