<?php
namespace Node;

/**
 * Interface Client
 * @package Node
 */
interface Client {
	/**
	 * @return mixed
	 */
	public function send();

	/**
	 * @param Server $server
	 */
	public function setServer(Server $server);
}
