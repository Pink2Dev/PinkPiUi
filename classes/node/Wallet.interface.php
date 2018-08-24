<?php
namespace Node;

/**
 * Interface Node
 * @package RPC
 */
interface Wallet {
	/**
	 * @param int $minConfirmations
	 * @param int $maxConfirmations
	 * @return array
	 */
	public function listUnspent($minConfirmations=1, $maxConfirmations=9999999);
}
