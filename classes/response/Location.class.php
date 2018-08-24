<?php
namespace Response;

/**
 * Class Location
 * @package Response
 */

class Location extends Common {
	/**
	 * @var string
	 */
	private $uri;

	/**
	 * Location constructor.
	 * @param string $uri
	 */
	public function __construct($uri) {
		$this->setUri($uri);
	}

	/**
	 * @return string
	 */
	public function getContent() {
		$content = null;
		if (headers_sent()) {
			$template = new Template('/response/location.tpl');
			$template->setData('RESOURCE', $this->getUri());
			$content = $template->getContent();
		}

		return $content;
	}

	/**
	 * @return string
	 */
	public function getUri() {
		return $this->uri;
	}

	/**
	 *
	 */
	public function outputContent() {
		echo $this->getContent();
	}

	/**
	 *
	 */
	public function outputHeaders() {
		if (!headers_sent()) {
			header('Location: ' . $this->getUri(), true, 302);
			exit;
		}
	}

	/**
	 * @param string $uri
	 */
	public function setUri($uri) {
		$uri = filter_var($uri, FILTER_SANITIZE_URL);
		$this->uri = $uri;
	}
}
