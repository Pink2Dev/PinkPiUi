<?php
namespace JSONRPC;

/**
 * Class Server
 * @package JSONRPC
 */
class Server {
	/**
	 * @var string
	 */
	private $certificate;
	/**
	 * @var string
	 */
	private $hostname;
	/**
	 * @var string
	 */
	private $password;
	/**
	 * @var int
	 */
	private $port;
	/**
	 * @var string
	 */
	private $username;

	/**
	 * Server constructor.
	 * @param string $hostname
	 * @param int $port
	 * @param string $username
	 * @param string $password
	 * @throws Exception
	 */
	public function __construct($hostname=null, $port=null, $username=null, $password=null) {
		if (!function_exists('curl_init')) {
			throw new Exception('PHP extension not found: curl');
		}

		if ($hostname) {
			$this->setHostname($hostname);
		}
		if ($port) {
			$this->setPort($port);
		}
		if ($username) {
			$this->setUsername($username);
		}
		if ($password) {
			$this->setPassword($password);
		}
	}

	/**
	 * @return array
	 */
	private function getOptions() {
		$options = [];
		$options[CURLOPT_CONNECTTIMEOUT] = 10;
		$options[CURLOPT_FOLLOWLOCATION] = true;
		$options[CURLOPT_FORBID_REUSE] = true;
		$options[CURLOPT_FRESH_CONNECT] = true;
		$options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
		$options[CURLOPT_HTTPHEADER] = [
			'Content-type: application/json',
		];
		$options[CURLOPT_MAXREDIRS] = 5;
		if ($this->port) {
			$options[CURLOPT_PORT] = $this->port;
		}
		$options[CURLOPT_POST] = true;
		$options[CURLOPT_PROTOCOLS] = CURLPROTO_HTTP;
		$options[CURLOPT_RETURNTRANSFER] = true;
		$options[CURLOPT_SSL_VERIFYHOST] = 2;
		$options[CURLOPT_SSL_VERIFYPEER] = false;
		$options[CURLOPT_TIMEOUT] = 20;
		$options[CURLOPT_USERPWD] = $this->username . ':' . $this->password;
		$options[CURLOPT_URL] = 'http://' . $this->hostname;

		if (ini_get('open_basedir')) {
			unset($options[CURLOPT_FOLLOWLOCATION]);
		}

		if ($this->certificate) {
			$options[CURLOPT_CAINFO] = $this->certificate;
			$options[CURLOPT_CAPATH] = dirname($this->certificate);
			$options[CURLOPT_PROTOCOLS] = CURLPROTO_HTTPS;
			$options[CURLOPT_SSL_VERIFYPEER] = true;
			$options[CURLOPT_URL] = 'https://' . $this->hostname;
		}

		return $options;
	}

	/**
	 * @param array|string $data
	 * @return mixed
	 * @throws Error
	 */
	public function post($data) {
		$options = $this->getOptions();
		$options[CURLOPT_POSTFIELDS] = $data;

		$handle = curl_init();
		curl_setopt_array($handle, $options);

		$raw = curl_exec($handle);

		$errno = curl_errno($handle);
		$error = trim(curl_error($handle)) ?: null;

		if ($errno || $error) {
			throw new Error($error, $errno);
		}

		$response = json_decode($raw, true);
		if (!$response) {
			$errno = json_last_error() ?: 0;
			$error = json_last_error_msg() ?: 'Invalid JSON response';
			throw new Error($error, $errno);
		}

		return $response;
	}

	/**
	 * Filepath to the SSL .cert cerificate
	 * @param string $certificate
	 * @throws Exception
	 */
	public function setCertificate($certificate) {
		if (!$certificate || !is_string($certificate)) {
			throw Exception::typeError('certificate', $certificate);
		}
		if (!file_exists($certificate)) {
			throw new Exception('Certificate filename was not found: ' . $certificate);
		}

		$this->certificate = $certificate;
	}

	/**
	 * @param string $value
	 * @throws Exception
	 */
	public function setHostname($value) {
		if (!is_string($value)) {
			throw Exception::typeError('hostname', $value);
		}

		// TODO Parse port?

		$this->hostname = $value;
	}

	/**
	 * @param string $value
	 * @throws Exception
	 */
	public function setPassword($value) {
		if (!is_string($value)) {
			throw Exception::typeError('password', $value);
		}

		$this->password = $value;
	}

	/**
	 * @param int $value
	 * @throws Exception
	 */
	public function setPort($value) {
		if (!is_numeric($value)) {
			throw Exception::typeError('port', $value);
		}

		$this->port = (int)$value;
	}

	/**
	 * @param string $value
	 * @throws Exception
	 */
	public function setUsername($value) {
		if (!is_string($value)) {
			throw Exception::typeError('username', $value);
		}

		$this->username = $value;
	}
}
