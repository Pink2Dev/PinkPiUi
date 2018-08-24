<?php

/**
 * Class Form
 */
final class Form {
	/**
	 * @var array
	 */
	private static $uploadErrors = [
		0 => null,
		1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
		2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
		3 => 'The uploaded file was only partially uploaded.',
		4 => 'No file was uploaded.',
		5 => 'This error is not currently documented. Please contact webmaster.',
		6 => 'Missing a temporary folder.',
		7 => 'Failed to write file to disk.',
		8 => 'A PHP extension stopped the file upload.'
	];

	/**
	 * Form constructor.
	 * @throws InputException
	 */
	public function __construct() {
		throw new InputException('Class cannot be instantiated.');
	}

	/**
	 * @param mixed $array The input array (e.g. $_FILES)
	 * @return array Properly organized array based on normal parent[child] relationship in HTML forms
	 */
	public static function organizeFilesArray(array &$array) {
		if (isset($array['error'], $array['name'], $array['size'], $array['tmp_name'], $array['type'])) {
			if (is_array($array['name'])) {
				$temp = [];
				foreach ($array as $key => $value) {
					foreach ($value as $key2 => $value2) {
						if (!array_key_exists($key2, $temp)) {
							$temp[$key2] = [];
						}
						$temp[$key2][$key] = $value2;
					}
				}

				$array = self::organizeFilesArray($temp);
				unset($temp);
			} else {
				$array['error_string'] = self::$uploadErrors[$array['error']];
			}
		} else if (is_array($array)) {
			foreach ($array as $key => &$value) {
				self::organizeFilesArray($array[$key]); // Recursive
			}
		}

		return $array;
	}

	/**
	 * Returns the value for the input, provided it is set
	 * @param string ...$keys
	 * @return mixed An input field safe string of the entered form data (before submit)
	 */
	public static function value(...$keys) {
		$value = $_POST;
		while ($key = array_shift($keys)) {
			if (!$value || !array_key_exists($key, $value)) {
				return null;
			}

			$value = $value[$key];
		}

		return $value;
	}
}
