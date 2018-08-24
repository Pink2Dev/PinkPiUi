<?php
namespace Node;

/**
 * Class Pinkcoin
 * @package RPC
 */
class Pinkcoin extends Base implements Wallet {
	/**
	 * @param int $minConfirmations
	 * @return string
	 * @throws \JSONRPC\Exception
	 */
	public function getBalance($minConfirmations=1) {
		$transactions = $this->listUnspent($minConfirmations);

		$amounts = array_column($transactions, 'amount');
		$balance = bcsum($amounts, 8);

		return $balance;
	}

	/**
	 * @param int $minConfirmations
	 * @param int $maxConfirmations
	 * @return array
	 * @throws \JSONRPC\Exception
	 */
	public function listUnspent($minConfirmations=1, $maxConfirmations=9999999) {
		$client = $this->getClient();

		$client->listunspent($minConfirmations, $maxConfirmations);
		$unspent_transactions = $client->send();

		// Group by account
		$accounts = self::modeTree($unspent_transactions);

		return $accounts;
	}
}
