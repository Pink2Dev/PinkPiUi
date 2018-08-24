<?php
namespace Request;

/**
 * Class Response
 * @package Request
 */
class Response {
	/**
	 * @var resource
	 */
	private $handle;
	/**
	 * @var array
	 */
	private $headers = [];
	/**
	 * @var array
	 */
	private $info = [];
	/**
	 * @var string
	 */
	private $method;
	/**
	 * @var string
	 */
	private $raw;
	/**
	 * @var \Request
	 */
	private $request;

	/**
	 *
	 */
	public function close() {
		if (is_resource($this->handle)) {
			curl_close($this->handle);
		}
	}

	/**
	 * @param bool $refresh
	 * @return resource
	 * @throws Exception
	 */
	protected function getHandle($refresh=false) {
		if ($this->handle === null || $refresh) {
			$this->handle = curl_init();
		}

		if (!is_resource($this->handle)) {
			throw new Exception('Invalid resource for handler');
		}

		return $this->handle;
	}

	/**
	 * @return string[]
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * @param int $option
	 * @return mixed
	 * @throws Exception
	 */
	public function getInfo($option=0) {
		if ($this->info === null) {
			$handle = $this->getHandle();
			$this->info = curl_getinfo($handle);
		}

		if ($option) {
			return $this->info[$option] ?? null;
		}

		return $this->info;
	}

	/**
	 * @return string
	 */
	public function getRaw() {
		return $this->raw;
	}

	/**
	 * @param string $method
	 */
	public function setMethod($method) {
		$this->method = \Input::validate($method);
	}

	/**
	 * @param \Request $request
	 */
	public function setRequest(\Request $request) {
		$this->request = $request;
	}

	/**
	 * @throws Exception
	 */
	public function status() {
		$handle = $this->getHandle();

		$errNo = curl_errno($handle);
		$error = curl_error($handle) ?: 'An unknown error has occurred.';
		if ($errNo || $error) {
			throw new Exception($error, $errNo);
		}
	}
}
