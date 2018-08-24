<?php

namespace Request;

/**
 * Class Common
 * @package Request
 */
class Common {
	/**
	 * @var string
	 */
	private $format;
	/**
	 * @var resource
	 */
	private $handle;
	/**
	 * @var array
	 */
	private $info;
	/**
	 * @var string
	 */
	private $method = 'GET';
	/**
	 * @var array
	 */
	private $options = [];
	/**
	 * @var resource
	 */
	private $parent;
	/**
	 * @var string
	 */
	private $raw;

	/**
	 * Request constructor.
	 * @throws Exception
	 */
	public function __construct() {
		if (!function_exists('curl_init')) {
			throw new Exception('cURL library not found.');
		}

		$this->handle = curl_init();
		if (!is_resource($this->handle)) {
			$errNo = CURLE_FAILED_INIT;
			$error = curl_strerror($errNo);
			throw new Exception($error, $errNo);
		}

		$this->reset();
	}

	/**
	 * @throws Exception
	 */
	public function __destruct() {
		$this->close();
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @throws Exception
	 */
	public function addHeader($key, $value) {
		$key = trim($key);
		$value = trim($value);
		$header = $key . ': ' . $value;
		$this->setOption(CURLOPT_HTTPHEADER, $header);
	}

	/**
	 * @param array $headers
	 */
	public function setHeaders(array $headers) {
		foreach ($headers as $key => $value) {
			$this->addHeader($key, $value);
		}
	}

	/**
	 * @param resource $mh
	 * @throws Exception
	 */
	public function addMultiHandle($mh) {
		if (!is_resource($mh)) {
			throw new Exception('Bad multi handle', CURLM_BAD_HANDLE);
		}

		$errNo = curl_multi_add_handle($mh, $this->handle);
		$error = curl_strerror($errNo);
		if ($errNo || $error) {
			throw new Exception($error, $errNo);
		}

		$this->parent = $mh;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function close() {
		$this->multi_remove_handle();

		if (is_resource($this->handle)) {
			curl_close($this->handle);
		}

		return true;
	}

	/**
	 * @return mixed
	 */
	public function getData() {
		$return = $this->getOption(CURLOPT_POSTFIELDS); // String
		parse_str($return, $return);

		return $return;
	}

	/**
	 * @return string
	 */
	public function getFormat() {
		return $this->format;
	}

	/**
	 * @param int $option
	 * @return mixed
	 * @throws Exception
	 */
	public function getInfo($option = null) {
		if (!is_resource($this->handle)) {
			$errNo = CURLE_FAILED_INIT;
			$error = curl_strerror($errNo);
			throw new Exception($error, $errNo);
		}

		if ($this->info === null) {
			$this->info = curl_getinfo($this->handle);
		}

		if ($option !== null) {
			return $this->info[$option] ?? null;
		}

		return $this->info;
	}

	/**
	 * @return string (e.g. GET, HEAD, POST)
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * @param int $option (e.g. CURLOPT_*)
	 * @return mixed
	 */
	public function getOption($option) {
		return $this->options[$option] ?? null;
	}

	/**
	 * @return string
	 */
	public function getRaw() {
		return $this->raw;
	}

	/**
	 * @return string
	 */
	public function getURL() {
		return $this->getOption(CURLOPT_URL);
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @throws Exception
	 */
	public function login($username, $password) {
		$credentials = $username . ':' . $password;
		$this->setOption(CURLOPT_USERPWD, $credentials);
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function make() {
		if (!is_resource($this->handle)) {
			$errNo = CURLE_FAILED_INIT;
			$error = curl_strerror($errNo);
			throw new Exception($error, $errNo);
		}

		$this->info = null;

		if (is_resource($this->parent)) {
			$this->raw = curl_multi_getcontent($this->handle);
		} else {
			$this->raw = curl_exec($this->handle);
		}

		$this->status();

		$content = $this->process();

		return $content;
	}

	/**
	 * @throws Exception
	 */
	private function multi_remove_handle() {
		if (is_resource($this->handle) && is_resource($this->parent)) {
			$errNo = curl_multi_remove_handle($this->parent, $this->handle);
			$error = curl_strerror($errNo);
			throw new Exception($error, $errNo);
		}

		$this->parent = null;
	}

	/**
	 * @param string $header
	 * @return string[]
	 */
	public static function parseHeaders($header) {
		$header = explode("\r\n", $header);
		$return = [];

		foreach ($header as $value) {
			if (empty($value)) {
				continue;
			}

			if (stripos($value, ':') === false) {
				if (empty($key)) {
					$return[] = $value;
				} else {
					$return[$key] .= "\r\n" . $value;
				}
				continue;
			}

			list($key, $value) = explode(':', $value, 2);

			$key = explode('-', $key);
			$key = array_map('ucfirst', $key);
			$key = implode('-', $key);

			$value = trim($value);

			if (isset($return[$key])) {
				if (is_array($return[$key])) {
					$return[$key][] = $value;
				} else {
					$return[$key] = [$return[$key], $value];
				}
			} else{
				$return[$key] = $value;
			}
		}

		return $return;
	}

	/**
	 *
	 */
	public function process() {
		if ($this->raw === null) {
			return null;
		}

		$content = $this->raw;
		$format = $this->getFormat();
		switch ($format) {
			case 'json':
				$content = json_decode($content, true);

				$errNo = json_last_error();
				if ($errNo) {
					$error = json_last_error_msg();
					throw new Exception($error, $errNo);
				}
			break;
		}

		return $content;
	}

	/**
	 * @throws Exception
	 */
	public function reset() {
		if (!is_resource($this->handle)) {
			$errNo = CURLE_FAILED_INIT;
			$error = curl_strerror($errNo);
			throw new Exception($error, $errNo);
		}

		$this->format = null;
		$this->info = null;
		$this->method = 'GET';
		$this->options = [];
		$this->raw = null;

		curl_reset($this->handle);

		$this->setOption(CURLOPT_AUTOREFERER, false); // Referer:
		$this->setOption(CURLOPT_COOKIEFILE, '');
		$this->setOption(CURLOPT_CONNECTTIMEOUT, 5);
		$this->setOption(CURLOPT_ENCODING, ''); // empty string (all), identity, deflate, gzip
		$this->setOption(CURLOPT_FOLLOWLOCATION, false); // Location:
		//$this->setOption(CURLOPT_FORBID_REUSE, true);
		//$this->setOption(CURLOPT_FRESH_CONNECT, true);
		$this->setOption(CURLOPT_HEADER, false);
		// IPv6 is possibly not as available as it should be
		//$this->setOption(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		$this->setOption(CURLOPT_RETURNTRANSFER, true);
		$this->setOption(CURLOPT_SAFE_UPLOAD, true); // Disable @ in CURLOPT_POSTFIELDS
		//$this->setOption(CURLOPT_SSL_VERIFYHOST, 0); // 0, 1 (deprecated), 2
		//$this->setOption(CURLOPT_SSL_VERIFYPEER, false);
		//$this->setOption(CURLOPT_SSLVERSION, 3); // 0-6
		$this->setOption(CURLOPT_TIMEOUT, 10);
		$this->setOption(CURLOPT_USERAGENT, 'Foundation/3.0.0 (' . PHP_OS . '; U;) PHP/' . PHP_VERSION);

		$this->addHeader('Expect', '');
	}

	/**
	 * @param mixed $value
	 * @throws Exception
	 */
	public function setData($value) {
		if (is_array($value) || is_object($value)) {
			$value = http_build_query($value, '', '&');
		}

		$this->setOption(CURLOPT_POSTFIELDS, $value); // String
	}

	/**
	 * @param string $format
	 */
	public function setFormat($format) {
		$this->format = $format;
	}

	/**
	 * @param string $value (e.g. GET, HEAD, POST)
	 * @throws Exception
	 */
	public function setMethod($value) {
		if (!is_string($value)) {
			throw new Exception('Invalid Method provided: ' . gettype($value));
		}

		$value = strtoupper($value);
		switch ($value) {
			case 'DELETE':
				$this->setOption(CURLOPT_CUSTOMREQUEST, 'DELETE');
			break;

			case 'GET':
				$this->setOption(CURLOPT_HTTPGET, true);
			break;

			case 'HEAD':
				$this->setOption(CURLOPT_HEADER, true);
				$this->setOption(CURLOPT_NOBODY, true);
			break;

			case 'POST':
				$this->setOption(CURLOPT_POST, true);
			break;

			case 'PUT':
				$this->setOption(CURLOPT_CUSTOMREQUEST, 'PUT');
				//$this->set_option(CURLOPT_HTTPHEADER, 'Content-Type: application/x-www-form-urlencoded');
				//$this->set_option(CURLOPT_HTTPHEADER, 'X-HTTP-Method-Override: PUT');
				//$this->set_option(CURLOPT_PUT, true);
			break;

			default:
				throw new Exception('Unsupported method: ' . $value, 405);
		}

		$this->method = $value;
	}

	/**
	 * @param int $option
	 * @param mixed $value
	 * @throws Exception
	 */
	public function setOption($option, $value) {
		if (!is_resource($this->handle)) {
			$errNo = CURLE_FAILED_INIT;
			$error = curl_strerror($errNo);
			throw new Exception($error, $errNo);
		}

		switch ($option) {
			case CURLOPT_HTTPHEADER:
				if (!array_key_exists($option, $this->options)) {
					$this->options[$option] = [];
				}

				if (is_array($value)) {
					$this->options[$option] = array_merge($this->options[$option], $value);
				} else {
					$this->options[$option][] = $value;
				}
			break;

			default:
				$this->options[$option] = $value;
		}

		curl_setopt($this->handle, $option, $this->options[$option]);
	}

	/**
	 * @param string $url
	 * @throws Exception
	 */
	public function setURL($url) {
		$filter = FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED;
		$result = filter_var($url, FILTER_VALIDATE_URL, $filter);
		if ($result) {
			$this->setOption(CURLOPT_URL, $url);
		}
	}

	/**
	 * @param string $key Hexadecimal strings are converted to binary
	 * @param string $algorithm
	 * @return string
	 */
	public function signature($key, $algorithm = 'sha512') {
		if (ctype_xdigit($key)) {
			$key = pack('H*', $key); // Binary
		}
		$method = $this->getMethod();
		$params = [];
		$uri = $this->getURL();

		if ($method === 'GET') {
			list($uri, $params) = explode('?', $uri, 2);
		} else if ($postData = $this->getData()) {
			$params = $postData;
		}

		if (!is_array($params)) {
			parse_str($params, $params);
		}
		ksort($params, SORT_STRING);

		$data = [];
		$data[] = $method;
		$data[] = rawurlencode($uri);
		$data[] = rawurlencode(http_build_query($params, '', '&'));
		$data = implode('|', $data);

		return hash_hmac($algorithm, $data, $key);
	}

	/**
	 * @throws Exception
	 */
	private function status() {
		if (!is_resource($this->handle)) {
			$errNo = CURLE_FAILED_INIT;
			$error = curl_strerror($errNo);
			throw new Exception($error, $errNo);
		}

		$errNo = (int)curl_errno($this->handle) ?: 0;
		$error = trim(curl_error($this->handle)) ?: null;
		if ($errNo || $error) {
			throw new Exception($error, $errNo);
		}
	}
}
