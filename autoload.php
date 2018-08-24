<?php
spl_autoload_register(function($class) {
	static $types = [
		'class',
		'abstract',
		'trait',
		'interface'
	];

	$space = explode('\\', $class);

	$class = array_pop($space);

	array_unshift($space, __DIR__ . DIRECTORY_SEPARATOR . 'classes');
	$space = implode(DIRECTORY_SEPARATOR, $space);
	$space = strtolower($space);

	foreach ($types as $type) {
		$filename = $space . DIRECTORY_SEPARATOR . $class . '.' . $type . '.php';

		if (file_exists($filename)) {
			require $filename;
			break;
		}
	}
});
