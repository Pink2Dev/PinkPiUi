<?php
namespace Response\API;

/**
 * Class Common
 * @package Response\API
 *
 * @property string created
 * @property int error_code
 * @property string error_message
 * @property string response
 * @property bool success
 */
abstract class Common extends \Response\Common {
	/**
	 * Common constructor.
	 */
	public function __construct() {
		$this->created = new \DateTime();
		$this->error_code = 0;
		$this->error_message = null;
		$this->response = null;
		$this->success = false;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value) {
		parent::__set($name, $value);
		parent::__set('success', !($this->error_code || $this->error_message));
	}

	/**
	 * @param \Exception $ex
	 */
	public function setException(\Exception $ex) {
		$this->error_code = $ex->getCode() ?: -1 * $ex->getLine();
		$this->error_message = $ex->getMessage() ?: 'No error message.';
	}
}
