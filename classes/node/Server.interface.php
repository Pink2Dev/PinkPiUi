<?php
namespace Node;

/**
 * Interface Server
 * @package Node
 */
interface Server {
	/**
	 * Server constructor.
	 * @param string $hostname
	 * @param int $port
	 * @param string $username
	 * @param string $password
	 */
	public function __construct($hostname=null, $port=null, $username=null, $password=null);

	/**
	 * Filepath to the SSL .cert cerificate
	 * @param string $certificate
	 */
	public function setCertificate($certificate);

	/**
	 * @param string $value
	 */
	public function setHostname($value);

	/**
	 * @param string $value
	 */
	public function setPassword($value);

	/**
	 * @param int $value
	 */
	public function setPort($value);

	/**
	 * @param string $value
	 */
	public function setUsername($value);
}
