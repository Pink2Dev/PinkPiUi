<?php
foreach ($errors as $message) {
	alert_message('danger', $message);
}

foreach ($success as $message) {
	alert_message('success', $message);
}
