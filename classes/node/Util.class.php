<?php
namespace Node;

/**
 * Class Util
 */
class Util {
	/**
	 * @param $config
	 * @return \JSONRPC\Client
	 */
	//public static function getClient($config) {
	//	$config = file_get_contents('/home/json/' . $config . '.json');
	//	$config = json_decode($config, true);
	//
	//	$server = new \JSONRPC\Server(
	//		$config['hostname'],
	//		$config['port'],
	//		$config['username'],
	//		$config['password']
	//	);
	//	if ($config['certificate']) {
	//		$server->setCertificate($config['certificate']);
	//	}
	//
	//	$client = new \JSONRPC\Client();
	//	$client->setServer($server);
	//
	//	return $client;
	//}

	/**
	 * @param array $array
	 * @param string $column
	 * @return array
	 */
	public static function modeTree(array $array, $column = 'address') {
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
