<?php
namespace Response;

/**
 * Class Common
 * @package Response
 */
abstract class Common implements Response {
	/**
	 * @var array
	 */
	private $data = [];
	/**
	 * @var int
	 */
	private $outputFlags = 0;

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name) {
		if (array_key_exists($name, $this->data)) {
			return $this->data[$name];
		}

		return null;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name) {
		return isset($this->data[$name]);
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value) {
		$this->data[$name] = $value;
	}

	/**
	 * @param string $name
	 */
	public function __unset($name) {
		unset($this->data[$name]);
	}

	/**
	 * @param int $flags
	 */
	public function addOutputFlags($flags) {
		$this->outputFlags |= $flags;
	}

	/**
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * @return int
	 */
	public function getOutputFlags() {
		return $this->outputFlags;
	}

	/**
	 *
	 */
	final public function render() {
		$this->outputHeaders();

		$this->outputContent();
	}
}
