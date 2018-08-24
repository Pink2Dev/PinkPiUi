<?php
namespace Response\API;

/**
 * Class JSON
 * @package Response\API
 */
class JSON extends Common {
	/**
	 * JSON constructor.
	 */
	public function __construct() {
		parent::__construct();

		// Human readable JSON output
		if (\Input::get('pretty') || \Input::post('pretty')) {
			$this->addOutputFlags(JSON_PRETTY_PRINT);
		}
	}

	/**
	 * @return string
	 */
	public function getContent() {
		$content = $this->getData();
		$flags = $this->getOutputFlags();
		ksort($content);
		$content = json_encode($content, $flags);
		return $content;
	}

	/**
	 *
	 */
	public function outputHeaders() {
		// TODO Change to be configurable; change default to absent
		header('Access-Control-Allow-Origin: *'); // e.g. Autoview
		header('Content-Type: application/json');
	}

	/**
	 *
	 */
	public function outputContent() {
		$content = $this->getContent();

		echo $content, PHP_EOL;
	}
}
