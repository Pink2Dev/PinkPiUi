<?php

/**
 * Class Util
 */
final class Util {
	/**
	 * Util constructor.
	 */
	private function __construct() {}

	/**
	 * @param int $a
	 * @param int $b
	 * @return array
	 * @throws TypeError
	 */
	public static function betweenTimestamps($a, $b=null) {
		if (!is_numeric($a)) {
			throw new TypeError('Invalid Timestamp A provided');
		}

		if ($b === null) {
			$b = time();
		} else {
			if (!is_numeric($b)) {
				throw new TypeError('Invalid Timestamp B provided');
			}
		}

		$minute = 60; // second
		$hour = 60 * $minute;
		$day = 24 * $hour;
		$year = 365 * $day;

		$distance = abs($a - $b);

		$seconds = floor($distance % $minute);
		$minutes = floor(($distance % $hour) / $minute);
		$hours = floor(($distance % $day) / $hour);
		$days = floor(($distance % $year) / $day);
		$years = floor($distance / $year);

		$stubs = [];
		if ($years) {
			$stubs[] = $years . 'y';
		}
		if ($days) {
			$stubs[] = $days . 'd';
		}
		if ($hours) {
			$stubs[] = $hours . 'h';
		}
		if ($minutes) {
			$stubs[] = $minutes . 'm';
		}
		if ($seconds || !$stubs) {
			$stubs[] = $seconds . 's';
		}

		$total = [];
		$total['seconds'] = $distance;
		$total['minutes'] = floor($distance / $minute);
		$total['hours'] = floor($distance / $hour);
		$total['days'] = floor($distance / $day);
		$total['years'] = floor($distance / $year);

		$return = [];
		$return['seconds'] = $seconds;
		$return['minutes'] = $minutes;
		$return['hours'] = $hours;
		$return['days'] = $days;
		$return['years'] = $years;
		$return['total'] = $total;
		$return['stubs'] = $stubs;

		return $return;
	}

	/**
	 * @param float $bytes
	 * @param int $precision
	 * @return string
	 */
	public static function formatFileSize($bytes, $precision=2) {
		static $sizes = ['','k','m','g','t','p','e','z','y',];
		$precision = max(0, (int)$precision);
		$size = 0;

		while ($bytes > 1024) {
			$size++;
			$bytes = floor($bytes / 1024);
		}

		$number = number_format($bytes, $precision);
		$suffix = $sizes[$size] . 'b';

		return $number . ' ' . $suffix;
	}

	/**
	 * @param string $dir
	 * @param int $depth
	 * @internal array $list
	 * @return array
	 * @throws Exception
	 */
	public static function getDirectory($dir, $depth=1, array &$list=[]) {
		if (!$dir || !is_dir($dir)) {
			throw new Exception('Invalid directory provided: ' . $dir);
		}

		$depth--;
		$dir = rtrim($dir, '/\\');
		$items = glob($dir . DIRECTORY_SEPARATOR . '*');
		foreach ($items as $i => $item) {
			if (is_dir($item) && $depth > 0) {
				self::getDirectory($item, $depth, $list);
			} else {
				$list[] = $item;
			}
		}

		return $list;
	}

	/**
	 * @param string $filename
	 * @param string $format
	 * @return mixed
	 * @throws \Exception
	 */
	public static function getFileContent($filename, $format=null) {
		if (!$filename || !file_exists($filename)) {
			throw new \Exception('Filename not found: ' . $filename);
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);
		if (!$extension) {
			$format = 'raw';
		}

		if (!$format) {
			$format = $extension;
		}

		switch ($format) {
			case 'csv':
				$content = self::getFileContentCSV($filename);
			break;
			case 'ini':
				$content = parse_ini_file($filename, true);
			break;
			case 'json':
				$content = file_get_contents($filename);
				$content = json_decode($content, true);
			break;
			default:
				$content = file_get_contents($filename);
		}

		return $content;
	}

	/**
	 * @param string $filename
	 * @return array
	 * @throws Exception
	 */
	public static function getFileContentCSV($filename) {
		if (!$filename || !file_exists($filename)) {
			throw new \Exception('Filename not found: ' . $filename);
		}

		$fh = fopen($filename, 'rb');
		if (!$fh) {
			throw new Exception('Unable to open file: ' . $filename);
		}

		$content = [];
		while (($buffer = fgetcsv($fh, 4096)) !== false) {
			$buffer = array_map('trim', $buffer);
			// Remove empty lines
			$buffer = array_filter($buffer);

			$content[] = $buffer;
		}

		fclose($fh);

		return $content;
	}

	/**
	 * @param string $namespace
	 * @return string
	 */
	public static function namespace_class($namespace) {
		$className = $namespace;
		$lastPosition = strrpos($namespace, '\\');
		if ($lastPosition !== false) {
			$className = substr($className, $lastPosition + 1);
		}
		return $className;
	}
}
