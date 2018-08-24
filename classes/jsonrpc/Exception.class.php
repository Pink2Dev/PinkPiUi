<?php
namespace JSONRPC;

/**
 * Class Exception
 * @package JSONRPC
 */
class Exception extends \Exception {
	const ERROR_TYPE_ERROR = 101;

	const ERROR_PROPERTY_UNDEFINED = 201;
	const ERROR_QUEUE_UNDEFINED = 202;
	const ERROR_SERVER_UNDEFINED = 203;

	/**
	 * @param string $name
	 * @param string $value
	 * @return Exception
	 */
	public static function typeError($name, $value) {
		$type = gettype($value);

		return new self('Invalid "' . $name . '" type provided: ' . $type, self::ERROR_TYPE_ERROR);
	}

	/**
	 * @param string $property
	 * @return Exception
	 */
	public static function propertyUndefined($property) {
		return new self('Undefined property: ' . $name, self::ERROR_PROPERTY_UNDEFINED);
	}

	/**
	 * @return Exception
	 */
	public static function queueUndefined() {
		return new self('Undefined queue.', self::ERROR_QUEUE_UNDEFINED);
	}

	/**
	 * @return Exception
	 */
	public static function serverUndefined() {
		return new self('Undefined server.', self::ERROR_SERVER_UNDEFINED);
	}
}
