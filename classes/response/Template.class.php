<?php
namespace Response;

/**
 * Class Template
 * @package Response
 */
class Template extends Common {
	/**
	 * @var array
	 */
	private $data = [];
	/**
	 * @var string
	 */
	private $template;

	/**
	 * Template constructor.
	 * @param string $template
	 */
	public function __construct($template=null) {
		if ($template) {
			$this->setTemplate($template);
		}
	}

	/**
	 * @return string
	 */
	public function getContent() {
		$filename = self::getTemplatePath($this->template);
		$content = file_get_contents($filename);
		// TODO Content parser (Smarty ?)
		$content = strtr($content, $this->data);

		return $content;
	}

	/**
	 * @param string $filename
	 * @return string
	 * @throws Exception
	 */
	public static function getTemplatePath($filename) {
		$filepath = __ROOT__ . '/templates/';
		$filename = ltrim($filename, '/');

		if (!file_exists($filepath . $filename)) {
			throw new Exception('Template not found: ' . $filename);
		}

		return $filepath . $filename;
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

	}

	/**
	 * TODO Is this how it should be handled / passed around ???
	 * @param string $pattern
	 * @param mixed $value
	 * @throws Exception
	 */
	public function setData($pattern, $value) {
		if (!is_string($pattern)) {
			throw new Exception('Unexpected reference type provided: ' . gettype($pattern));
		}

		$this->data['{' . $pattern . '}'] = $value;
	}

	/**
	 * @param \Exception $exception
	 * @throws Exception
	 */
	public function setException(\Exception $exception) {
		$this->setData('EXCEPTION_CODE', $exception->getCode());
		$this->setData('EXCEPTION_FILE', $exception->getFile());
		$this->setData('EXCEPTION_LINE', $exception->getLine());
		$this->setData('EXCEPTION_MESSAGE', $exception->getMessage());
		$this->setData('EXCEPTION_TRACE', $exception->getTrace());
		$this->setData('EXCEPTION_TRACE_STRING', $exception->getTraceAsString());
	}

	/**
	 * @param string $template
	 */
	public function setTemplate($template) {
		$this->template = $template;
	}
}
