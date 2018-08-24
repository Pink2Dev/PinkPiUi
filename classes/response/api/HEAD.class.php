<?php
namespace Response\API;

/**
 * Class HEAD
 * @package Response\API
 */
class HEAD extends Common {
	/**
	 * @return null
	 */
	public function getContent() {
		return null;
	}

	/**
	 *
	 */
	public function outputContent() {}

	/**
	 *
	 */
	public function outputHeaders() {
		if ($this->error_code) {
			http_response_code($this->error_code);
		}
	}
}
