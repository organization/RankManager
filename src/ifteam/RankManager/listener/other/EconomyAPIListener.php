<?php

namespace ifteam\RankManager\listener\other;

use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\plugin\Plugin;
use onebone\economyapi\event\money\AddMoneyEvent;
use onebone\economyapi\event\money\ReduceMoneyEvent;
use onebone\economyapi\event\money\SetMoneyEvent;
use onebone\economyapi\event\bank\MoneyChangedEvent;

class EconomyAPIListener implements Listener {
	private $economyAPI = null;
	public function __construct(Plugin $plugin) {
		$server = Server::getInstance ();
		if ($server->getPluginManager ()->getPlugin ( "EconomyAPI" ) != null) {
			$this->economyAPI = \onebone\economyapi\EconomyAPI::getInstance ();
			$server->getPluginManager ()->registerEvents ( $this, $plugin );
		}
	}
	/**
	 * Get EconomyAPI plug-in instance
	 *
	 * @return \onebone\economyapi\EconomyAPI | NULL
	 */
	public function getEconomyAPI() {
		return $this->economyAPI;
	}
}

?>