<?php
namespace JSONRPC;

/**
 * Class Request
 * @package JSONRPC
 */
class Request implements \JsonSerializable {
	/**
	 * @var int
	 */
	protected static $identifier = 0;
	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var string
	 */
	protected $method;
	/**
	 * @var bool
	 */
	protected $notification = false;
	/**
	 * @var array
	 */
	protected $params;

	/**
	 * @return int
	 */
	public function getID() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * @return bool
	 */
	public function getNotification() {
		return $this->notification;
	}

	/**
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * @param int|null|string $value
	 * @throws Exception
	 */
	public function setID($value) {
		if (!($value === null || is_int($value) || is_string($value))) {
			throw Exception::typeError($name, $value);
		}

		$this->id = $value;
	}

	/**
	 * @param string $value
	 * @throws Exception
	 */
	public function setMethod($value) {
		if (!is_string($value)) {
			throw Exception::typeError($name, $value);
		}

		$this->method = $value;
	}

	/**
	 * @param bool $value
	 * @throws Exception
	 */
	public function setNotification($value) {
		if (!is_bool($value)) {
			throw Exception::typeError($name, $value);
		}

		$this->notification = $value;
	}

	/**
	 * @param array|mixed $value
	 * @throws Exception
	 */
	public function setParams($value) {
		if (!is_array($value)) {
			throw Exception::typeError($name, $value);
		}

		// Remove any associations
		$this->params = array_values($value);
	}

	/**
	 * @return int
	 */
	protected static function identifier() {
		self::$identifier++;

		return self::$identifier;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize() {
		$json = [];
		$json['jsonrpc'] = '2.0';
		$json['method'] = $this->method;
		if ($this->params) {
			$json['params'] = $this->params;
		}
		if (!$this->notification) {
			$json['id'] = $this->id ?: self::identifier();
		}

		return $json;
	}
}
