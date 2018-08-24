<?php
namespace Node;

/**
 * Class Base
 * @package Node
 */
abstract class Base {
	/**
	 * @var \JSONRPC\Client
	 */
	private $client;
	/**
	 * @var \JSONRPC\Server
	 */
	private $server;

	/**
	 * @return \JSONRPC\Client
	 * @throws \JSONRPC\Exception
	 */
	protected function getClient() {
		if ($this->client === null) {
			$server = $this->getServer();

			$client = new \JSONRPC\Client();
			$client->setServer($server);

			$this->client = $client;
		}

		return $this->client;
	}

	/**
	 * @return \JSONRPC\Server
	 * @throws \JSONRPC\Exception
	 */
	protected function getServer() {
		if ($this->server === null) {
			$server = new \JSONRPC\Server();

			$this->server = $server;
		}

		return $this->server;
	}

	//public function loadServer($filename) {
	//	$config = \Util::getFileContent($filename);
	//
	//	$server = $this->getServer();
	//	$server->setHostname($config['hostname']);
	//	$server->setPort($config['port']);
	//	$server->setUsername($config['username']);
	//	$server->setPassword($config['password']);
	//}

	public static function modeTree(array $array, $column='address') {
		$return = [];
		foreach ($array as $element) {
			$value = $element[$column] ?? '';

			if (!array_key_exists($value, $return)) {
				$return[$value] = [
					'total' => 0.0,
					'transactions' => [],
				];
			}

			$return[$value]['total'] += $element['amount'];
			$return[$value]['transactions'][] = $element;
		}

		return $return;
	}
}
