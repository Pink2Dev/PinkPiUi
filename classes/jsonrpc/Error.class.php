<?php
namespace JSONRPC;

/**
 * Class Error
 * @package JSONRPC
 */
class Error extends Exception {
	private $data;

	public function __construct($message='', $code=0, $data='', Throwable $previous=null) {
		parent::__construct($message, $code, $previous);

		$this->data = $data;
	}

	final public function getData() {
		return $this->data;
	}
}
