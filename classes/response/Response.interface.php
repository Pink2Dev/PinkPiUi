<?php
namespace Response;

/**
 * Interface Response
 * @package Response
 */
interface Response {
	/**
	 *
	 */
	public function getContent();

	/**
	 *
	 */
	public function outputContent();

	/**
	 *
	 */
	public function outputHeaders();
}
