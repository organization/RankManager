<?php

namespace ifteam\RankManager\listener\other;

use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\plugin\Plugin;

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