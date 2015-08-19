<?php

namespace ifteam\RankManager\rank;

use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\Server;
use ifteam\RankManager\RankManager;

class RankLoader {
	private static $instance = null;
	private $users;
	private $plugin;
	private $server;
	public function __construct(RankManager $plugin) {
		if (self::$instance == null)
			self::$instance = $this;
		$this->server = Server::getInstance ();
		$this->plugin = $plugin;
	}
	/**
	 * Create a default setting
	 *
	 * @param string $userName        	
	 * @param string $nowPrefix        	
	 * @param array $havePrefixList        	
	 */
	public function loadRank($userName = null) {
		$userName = strtolower ( $player->getName () );
		$alpha = substr ( $userName, 0, 1 );
		
		if (isset ( $this->users [$userName] ))
			return $this->users [$userName];
		
		return $this->users [$userName] = new RankData ( $userName, $this->plugin->getDataFolder () . "player/" );
	}
	public function unloadRank($userName = null) {
		$userName = strtolower ( $player->getName () );
		if (! isset ( $this->users [$userName] ))
			return false;
		if ($this->users [$userName] instanceof RankData)
			$this->users [$userName]->save ();
		unset ( $this->users [$userName] );
		return true;
	}
	/**
	 * Get Rank Data
	 *
	 * @param Player $player        	
	 * @return RankData
	 */
	public function getRank(Player $player) {
		$userName = strtolower ( $player->getName () );
		if (! isset ( $this->users [$userName] ))
			$this->loadRank ( $userName );
		return $this->users [$userName];
	}
	/**
	 * Get Rank Data
	 *
	 * @param string $player        	
	 * @return RankData
	 */
	public function getRankToName($name) {
		$userName = strtolower ( $name );
		if (! isset ( $this->users [$userName] ))
			$this->loadRank ( $userName );
		return $this->users [$userName];
	}
	public function save($async = false) {
		foreach ( $this->users as $userName => $rankData )
			if ($rankData instanceof RankData)
				$rankData->save ( $async );
	}
	/**
	 *
	 * @return AreaLoader
	 */
	public static function getInstance() {
		return static::$instance;
	}
}

?>