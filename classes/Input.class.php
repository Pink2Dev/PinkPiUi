<?php

/**
 * Class Input
 */
final class Input {
	/**
	 * Input constructor.
	 * @throws InputException
	 */
	public function __construct() {
		throw new InputException('Class cannot be instantiated.');
	}

	/**
	 * @param $realm
	 * @throws ApiAccessException
	 * @throws \Database\Exception
	 * @throws \Database\QueryException
	 * @throws EndpointException
	 */
	public static function authenticate($realm) {
		$publicKey = self::server('PHP_AUTH_USER');
		$signature = self::server('PHP_AUTH_PW');

		if (!$publicKey) {
			//header('HTTP/1.1 401 Unauthorized');
			http_response_code(401);
			header(sprintf('WWW-Authenticate: Basic realm="%s"', $realm));
			exit;
		}

		// Credentials / Account verification
		$api_access = \api_access::lookupApiAccess([
			'public_key' => $publicKey,
		]);

		$knownSignature = self::getRequestSignature($api_access->private_key);
		if (!hash_equals($knownSignature, $signature)) {
			throw \EndpointException::parameterInvalid('signature (password)');
		}
	}

	/**
	 * @param array ...$allowedMethods
	 * @return bool
	 */
	public static function checkRequestMethod(...$allowedMethods) {
		$result = true;
		if ($allowedMethods) {
			$allowedMethods = array_map('strtoupper', $allowedMethods);
			$requestMethod = self::server('REQUEST_METHOD');
			$result = in_array($requestMethod, $allowedMethods, true);
		}
		return $result;
	}

	/**
	 * @param string $key
	 * @param string $type
	 * @param mixed $test1
	 * @param mixed $test2
	 * @return mixed
	 */
	public static function get($key, $type='string', $test1=null, $test2=null) {
		try {
			$value = self::validateArrayKey($key, $_GET, $type, $test1, $test2);
		} catch (InputException $ex) {
			$value = null;
		}
		
		return $value;
	}

	/**
	 * @return string
	 */
	public static function getRawInput() {
		$source = 'php://input';
		$data = file_get_contents($source);
		return $data;
	}

	/**
	 * @param string $privateKey
	 * @param string $algorithm
	 * @return string
	 */
	public static function getRequestSignature($privateKey, $algorithm='sha512') {
		$data = [
			Input::server('REQUEST_METHOD'),
			Input::getRawInput(),
		];
		$data = implode('|', $data);
		$signature = hash_hmac($algorithm, $data, $privateKey);

		return $signature;
	}

	/**
	 * @param string $key
	 * @param string $type
	 * @param mixed $test1
	 * @param mixed $test2
	 * @return mixed
	 */
	public static function cookie($key, $type='string', $test1=null, $test2=null) {
		try {
			$value = self::validateArrayKey($key, $_COOKIE, $type, $test1, $test2);
		} catch (InputException $ex) {
			$value = null;
		}

		return $value;
	}

	/**
	 * @param string $key
	 * @param string $type
	 * @param mixed $test1
	 * @param mixed $test2
	 * @return mixed
	 */
	public static function post($key, $type='string', $test1=null, $test2=null) {
		try {
			$value = self::validateArrayKey($key, $_POST, $type, $test1, $test2);
		} catch (InputException $ex) {
			$value = null;
		}

		return $value;
	}

	/**
	 * @return false|int
	 */
	public static function save(/** variadic */) {
		list($milliseconds, $seconds) = explode(' ', microtime());
		$filename = $seconds . substr($milliseconds, 1) . '.txt';
		$input = file_get_contents('php://input');

		$_VARS = [
			'arguments' => func_get_args(),
			'cookie' => $_COOKIE,
			'get' => $_GET,
			'input' => $input,
			'post' => $_POST,
			'server' => $_SERVER,
		];
		$data = var_export($_VARS, true);
		$bytes = file_put_contents($filename, $data); // false on failure

		return $bytes;
	}

	/**
	 * @param string $key
	 * @param string $type
	 * @param mixed $test1
	 * @param mixed $test2
	 * @return mixed
	 */
	public static function server($key, $type='string', $test1=null, $test2=null) {
		try {
			$value = self::validateArrayKey($key, $_SERVER, $type, $test1, $test2);
		} catch (InputException $ex) {
			$value = null;
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 * @param string $type
	 * @param mixed $test1
	 * @param mixed $test2
	 * @return mixed
	 * @throws InputException
	 */
	public static function validate($value, $type='string', $test1=null, $test2=null) {
		if (($p = strpos($type, '[]')) > 0) {
			$type = substr($type, 0, $p);
			if (!is_array($value)) {
				throw new InputException('Array: value is not an array.');
			}
			foreach ($value as $key => $item) {
				$value[$key] = self::validate($item, $type, $test1, $test2);
			}

			return $value;
		}

		switch ($type) {
			case 'array':
				if (!is_array($value)) {
					throw new InputException('Array: value could is not of valid type.');
				}

				return $value;

			case 'bool':
			case 'boolean':
				if (is_bool($value)) {
					return $value;
				}

				if (is_array($value) || is_object($value) || is_resource($value)) {
					throw new InputException('Boolean: value could not be converted to boolean.');
				}

				$value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
				if ($value === null) {
					throw new InputException('Boolean: value could not be converted to a boolean.');
				}

				return (bool)$value;

			case 'double':
			case 'float':
			case 'int':
			case 'integer':
				if (!is_numeric($value)) {
					throw new InputException('Float: value is not numeric.');
				}

				// preg_match('/^[\s]*[+|-]?[0-9\,\.\s]*$/', $value) === 1
				$value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
				if ((float)$value != $value) {
					throw new InputException('Float: value is not a float.');
				}

				$value = (float)$value;

				if (is_array($test1)) {
					$test1 = array_map('floatval', $test1);
					if (!in_array($value, $test1, null)) {
						throw new InputException('Float: value is not in the provided list.');
					}
				} else if ($test1 !== null && $value < (float)$test1) {
					throw new InputException('Float: value is less than the minimum.');
				}

				if ($test2 !== null && $value > (float)$test2) {
					throw new InputException('Float: value is greater than the maximum.');
				}

				return $value;

			case 'email':
				$value = self::validate($value, 'string', $test1, $test2);
				$value = filter_var($value, FILTER_VALIDATE_EMAIL);
				if ($value === false) {
					throw new InputException('Boolean: value is not a valid e-mail address.');
				}
				$value = filter_var($value, FILTER_SANITIZE_EMAIL);

				return $value;

			case 'string':
				if (is_array($value) || (is_object($value) && !method_exists($value, '__toString')) || is_resource($value)) {
					throw new InputException('String: value cannot be converted to a string.');
				}
				if (is_bool($value)) {
					$value = $value ? 'true' : 'false';
				}
				$value = (string)$value;

				if (is_array($test1)) {
					if (!in_array($value, $test1, null)) {
						throw new InputException('String: value is not in the provided list.');
					}
				} else if ($test1 !== null && strlen($value) < (int)$test1) {
					throw new InputException('String: value is less than the minimum length.');
				}

				if ($test2 !== null && strlen($value) > (int)$test2) {
					throw new InputException('String: value is greater than the maximum length.');
				}

				return $value;

			default:
				throw new InputException('Validation type is not supported: ' . $type);
		}
	}

	/**
	 * @param string $key
	 * @param array $array
	 * @param string $type
	 * @param mixed $test1
	 * @param mixed $test2
	 * @return mixed
	 */
	public static function validateArrayKey($key, $array, $type='string', $test1=null, $test2=null) {
		if (!is_string($key) || !is_array($array) || !array_key_exists($key, $array)) {
			return null;
		}

		try {
			$result = self::validate($array[$key], $type, $test1, $test2);
		} catch (Exception $ex) {
			$result = null;
		}

		return $result;
	}
}
