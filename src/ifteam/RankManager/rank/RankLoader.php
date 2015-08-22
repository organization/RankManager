<?php

namespace ifteam\RankManager\rank;

use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\Server;
use ifteam\RankManager\RankManager;
use ifteam\RankManager\task\AutoUnloadTask;

class RankLoader {
	private static $instance = null;
	/**
	 *
	 * @var Users prefix data
	 */
	private $users = [ ];
	/**
	 *
	 * @var RankManager
	 */
	private $plugin;
	/**
	 *
	 * @var Server
	 */
	private $server;
	public function __construct(RankManager $plugin) {
		if (self::$instance == null)
			self::$instance = $this;
		
		$this->server = Server::getInstance ();
		$this->plugin = $plugin;
		
		$this->server->getScheduler ()->scheduleRepeatingTask ( new AutoUnloadTask ( $this ), 12000 );
	}
	/**
	 * Create a default setting
	 *
	 * @param string $userName        	
	 * @param string $nowPrefix        	
	 * @param array $havePrefixList        	
	 */
	public function loadRank($userName) {
		$userName = strtolower ( $userName );
		$alpha = substr ( $userName, 0, 1 );
		
		if (isset ( $this->users [$userName] ))
			return $this->users [$userName];
		
		if (! file_exists ( $this->plugin->getDataFolder () . "player/" ))
			@mkdir ( $this->plugin->getDataFolder () . "player/" );
		
		return $this->users [$userName] = new RankData ( $userName, $this->plugin->getDataFolder () . "player/" );
	}
	public function unloadRank($userName = null) {
		if ($userName === null) {
			foreach ( $this->users as $userName => $rankData ) {
				if ($this->users [$userName] instanceof RankData)
					$this->users [$userName]->save ( true );
				unset ( $this->users [$userName] );
			}
			return true;
		}
		
		$userName = strtolower ( $userName );
		if (! isset ( $this->users [$userName] ))
			return false;
		if ($this->users [$userName] instanceof RankData){
			$this->users [$userName]->save ( true );
		}
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