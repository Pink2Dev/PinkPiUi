<?php

/**
 * Class Pinkcoin
 */
class Pinkcoin {
	/**
	 * @var \JSONRPC\Client
	 */
	private $client;
	/**
	 * @var Pinkcoin[]
	 */
	private static $instances = [];
	/**
	 * @var JSONRPC\Server
	 */
	private $server;

	/**
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments) {
		$client = $this->getClient();
		$response = call_user_func_array([$client,$name], $arguments);
		return $response;
	}

	/**
	 * @param bool $reload
	 * @return \JSONRPC\Client
	 */
	public function getClient($reload=false) {
		if ($reload || !$this->client) {
			$server = $this->getServer();

			$client = new JSONRPC\Client();
			$client->setServer($server);

			$this->client = $client;
		}

		return $this->client;
	}

	/**
	 * @param string $instance
	 * @return Pinkcoin
	 * @throws Exception
	 */
	public static function getInstance($instance='__DEFAULT__') {
		if (!is_string($instance)) {
			throw new InvalidArgumentException('Invalid instance identifier: ' . gettype($instance));
		}
		if (empty(self::$instances[$instance])) {
			self::$instances[$instance] = new self();
		}
		return self::$instances[$instance];
	}

	/**
	 * @param bool $reload
	 * @return \JSONRPC\Server
	 */
	protected function getServer($reload=false) {
		if ($reload || !$this->server) {
			$filepath = '~' . DIRECTORY_SEPARATOR . '.pink2';
			$filename = 'pinkconf.txt';
			$config = Util::getFileContent($filepath . DIRECTORY_SEPARATOR . $filename, 'ini');
			var_dump(['config', $config]);

			$server = new JSONRPC\Server('127.0.0.1', $config['rpcport'], $config['rpcusername'], $config['rpcpassword']);
			if ($config['rpcssl']) {
				$server->setCertificate($filepath . DIRECTORY_SEPARATOR . 'server.crt');
			}

			$this->server = $server;
		}

		return $this->server;
	}
}
