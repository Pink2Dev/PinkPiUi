<?php
function bcabs($number) {
	$number = (string)$number;

	return bcmul($number, bccomp($number, '0'));
}

function bcceil($number) {
	$number = (string)$number;

	return bcadd($number, (string)(bccomp($number, '0') !== -1), 0);
}

function bccomb($n, $k) {
	$n = (string)$n;
	$k = (string)$k;
	if (bccomp($n, '0') === -1) {
		throw new Exception('Argument 1 (' . $n . ') to be greater than or equal to 0');
	}
	if (bccomp($n, $k) === -1) {
		throw new Exception('Argument 1 (' . $n . ') to be greater than Argument 2 (' . $k . ')');
	}
	if (bccomp($k, '0') === -1) {
		throw new Exception('Argument 2 to be greater than or equal to 0');
	}

	return bcdiv(bcfact($n), bcmul(bcfact($k), bcfact(bcsub($n, $k, 0)), 0), 0);
}

function bcconvert($n, $frombase, $tobase) {
	static $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ+/=';
	$frombase = (string)$frombase;
	$tobase = (string)$tobase;

	if (bccomp('1', $frombase, 0) === 1) {
		throw new Exception('Argument 1 to be greater than 0');
	}
	if (bccomp($frombase, '65', 0) === 1) { // $chars length
		throw new Exception('Argument 1 to be less than ' . $l_chars);
	}
	if (bccomp('1', $tobase, 0) === 1) {
		throw new Exception('Argument 2 to be greater than 0');
	}
	if (bccomp($tobase, '65', 0) === 1) { // $chars length
		throw new Exception('Argument 2 to be less than ' . $l_chars);
	}

	if (bccomp($frombase, $tobase, 0) === 0) {
		return $n; // Nothing to convert
	}

	if (bccomp($tobase, '10', 0) === 0) {
		$l = strlen($n);
		$return = '0';
		for ($i = 1; $i <= $l; $i++) {
			$k = strpos($chars, $n[$i - 1]);
			if ($k >= $frombase) {
				throw new Exception('Invalid base ' . $frombase . ' character (' . $n[$i - 1] . ') found.');
			}
			$j = bcpow($frombase, (string)($l - $i), 0);
			$return = bcadd($return, bcmul($k, $j, 0), 0);
		}

		return $return;
	}

	if (bccomp($frombase, '10', 0) !== 0) {
		$n = bcconvert($n, $frombase, 10);
	}

	$return = '';
	while (bccomp($n, '0', 0) === 1) {
		$k = (int)bcmod($n, $tobase);
		$return = $chars[$k] . $return;
		$n = bcdiv($n, $tobase, 0);
	}

	return $return;
}

function bcfact($number) {
	$number = (string)$number;
	if (strpos($number, '.') !== false) {
		throw new Exception('Fraction factorials (if possible) are currently not supported.');
	}

	if (bccomp($number, '0', 0) === -1) {
		throw new Exception('Negative factorials are undefined');
	}
	if (bccomp($number, '1', 0) < 1) {
		return $number;
	}

	return bcmul($number, bcfact(bcsub($number, '1', 0)), 0);
}

function bcfloor($number) {
	$number = (string)$number;
	if (bccomp($number, bcadd($number, '0', 0)) === 0) {
		return $number;
	}

	return bcsub($number, (string)(bccomp($number, '0') === -1), 0);
}

/**
 * @param int $precision
 * @return int
 */
function bcgetscale($precision=null) {
	if ($precision === null) {
		$precision = bcsqrt('2', 8);
	}
	$precision = rtrim((string)$precision, '.0');

	$pos = strpos($precision, '.');
	if ($pos === false) {
		return 0;
	}

	return (strlen($precision) - $pos - 1);
}

function bcmadd(/* variable */) {
	$return = '0';
	foreach (func_get_args() as $number) {
		$return = bcadd($return, (string)$number);
	}

	return $return;
}

function bcmax(/* variable */) {
	$numbers = func_get_args();
	if (!$numbers) {
		throw new Exception('No arguments were provided.', E_USER_WARNING);
	}

	if (func_num_args() === 1 && is_array($numbers[0])) {
		$numbers = $numbers[0];
		trigger_error('Please utilize call_user_func_array(callback, array)', E_USER_DEPRECATED);
	}

	$return = (string)array_shift($numbers);
	$scale = bcgetscale();
	foreach ($numbers as $number) {
		if (bccomp((string)$number, $return, $scale) === 1) {
			$return = (string)$number;
		}
	}

	return $return;
}

function bcmin(/* variable */) {
	$numbers = func_get_args();
	if (!$numbers) {
		throw new Exception('No arguments provided.');
	}

	if (func_num_args() === 1 && is_array($numbers[0])) {
		$numbers = $numbers[0];
		trigger_error('Please utilize call_user_func_array(callback, array)', E_USER_DEPRECATED);
	}

	$return = (string)array_shift($numbers);
	foreach ($numbers as $number) {
		if (bccomp((string)$number, $return) === -1) {
			$return = (string)$number;
		}
	}

	return $return;
}

function bcproduct(array $array, $scale=null) {
	if (!$array) {
		return '0';
	}

	$return = '1';
	$scale = validateScale($scale);
	foreach ($array as $number) {
		$return = bcmul($return, (string)$number, $scale);
	}

	return $return;
}

function bcrand($min, $max, $scale=null) {
	$scale = validateScale($scale);
	$scale++; // Mathematical operation accuracy

	$diff = bcsub($max, $min, $scale);
	$rand = bcdiv((string)mt_rand(), (string)mt_getrandmax(), 20);

	return bcround(bcadd($min, bcmul($rand, $diff, $scale), $scale), $scale - 1);
}

function bcround($number, $scale=null) {
	$number = (string)$number;
	$scale = validateScale($scale);
	$scale++; // Mathematical operation accuracy

	$h = bcmul('5', bcpow('10', '-' . $scale, $scale), $scale);
	if (bccomp($number, '0', $scale) === -1) {
		$h = bcmul($h, '-1', $scale);
	}

	return bcadd($number, $h, $scale - 1);
}

function bcsum(array $array, $scale=null) {
	if (!$array) {
		return '0';
	}

	$return = '0';
	$scale = validateScale($scale);
	foreach ($array as $number) {
		$return = bcadd($return, (string)$number, $scale);
	}

	return $return;
}

function bcuuid(/* variable */) {
	$args = func_get_args();
	$l = 0;
	foreach ($args as $i => $arg) {
		$arg = bcabs((string)$arg);
		$l = max($l, strlen($arg));

		$args[$i] = $arg;
	}

	$return = array();
	foreach ($args as $n) {
		$n = str_pad($n, $l, '0', STR_PAD_LEFT);
		$n = strrev($n);

		array_unshift($return, $n);
	}

	$return = implode($return);
	$return = bcconvert($return, 10, 16);
	if (!$return) {
		throw new Exception('Base 10 to Base 16 conversion failed.');
	}

	$r = strlen($return);
	$return = str_pad($return, $r + ($r % 2), '0', STR_PAD_LEFT);

	return sprintf('%02x', $l) . $return;
}

function bcuuid_array($data) {
	if (!$data || !ctype_xdigit($data) || strlen($data) % 2 !== 0 || strlen($data) <= 2) {
		throw new Exception('Invalid hexidecimal data provided.');
	}

	$l = hexdec(substr($data, 0, 2));
	if (!$l) {
		throw new Exception('Invalid length checksum');
	}

	$data = bcconvert(substr($data, 2), 16, 10);
	if (!$data) {
		throw new Exception('Base 16 to Base 10 conversion failed.');
	}

	$p = (strlen($data) % $l);
	if ($p > 0) {
		$data = str_repeat('0', $l - $p) . $data;
	}
	$data = str_split($data, $l);

	$return = array();
	foreach ($data as $n) {
		$n = strrev($n);
		$n = bcadd($n, '0', 0);

		array_unshift($return, $n);
	}

	return $return;
}

/**
 * @param int $scale
 * @return int
 * @throws Exception
 */
function validateScale($scale) {
	if ($scale === null) {
		return bcgetscale();
	}

	$scale = (int)$scale;
	if ($scale < 0) {
		throw new Exception('Scale must be greater than or equal too 0, ' . $scale . ' given');
	}

	return $scale;
}
