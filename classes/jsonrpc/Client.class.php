<?php
namespace JSONRPC;

/**
 * Class Client
 * @package JSONRPC
 */
class Client {
	protected static $id = 0;

	/**
	 * @var Request[]
	 */
	protected $queue = [];
	/**
	 * @var array
	 */
	private $raw;
	/**
	 * @var Server
	 */
	protected $server;

	/**
	 * @param string $name
	 * @param array $arguments
	 * @throws Exception
	 */
	public function __call($name, $arguments) {
		$this->queue[] = self::request($name, $arguments);
	}

	/**
	 * @param Request $request
	 */
	public function addRequest(Request $request) {
		$this->queue[] = $request;
	}

	/**
	 * @return array
	 */
	public function getRaw() {
		return $this->raw;
	}

	/**
	 * @param string $method
	 * @param array $params
	 * @param bool $notification
	 * @return Request
	 */
	protected static function request($method, $params, $notification=false) {
		$request = new Request();
		$request->setMethod($method);
		$request->setNotification($notification);
		$request->setParams($params);

		return $request;
	}

	/**
	 * @param bool $minimize
	 * @return mixed
	 * @throws Exception
	 */
	public function send($minimize=true) {
		if (!$this->server) {
			throw Exception::serverUndefined();
		}
		if (count($this->queue) === 0) {
			throw Exception::queueUndefined();
		}

		$queue = $this->queue;
		$this->queue = [];

		$request = json_encode($queue);

		$results = $this->raw = $this->server->post($request) ?: [];

		foreach ($results as $i => $array) {
			if (!array_key_exists('result', $array)) {
				$array['result'] = null;
			}
			if (array_key_exists('error', $array) && $array['error']) {
				$error = $array['error'];
				$array['result'] = new Error($error['message'], $error['code'], $array['result']);
			}

			$results[$i] = $array['result'];
		}

		if ($minimize && count($queue) === 1) {
			$results = reset($results);
		}

		return $results;
	}

	/**
	 * @param Server $server
	 */
	public function setServer(Server $server) {
		$this->server = $server;
	}
}
