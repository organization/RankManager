<?php

namespace ifteam\RankManager\rank;

use ifteam\RankManager\RankManager;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\Server;
use pocketmine\Player;

class RankProvider {
	/**
	 *
	 * @var RankProvider
	 */
	private static $instance = null;
	/**
	 *
	 * @var RankManager
	 */
	private $plugin;
	/**
	 *
	 * @var RankLoader
	 */
	private $loader;
	/**
	 *
	 * @var Server
	 */
	private $server;
	/**
	 *
	 * @var RankProvider DB
	 */
	private $db;
	public function __construct(RankManager $plugin) {
		if (self::$instance == null)
			self::$instance = $this;
		
		$this->plugin = $plugin;
		$this->loader = $plugin->getRankLoader ();
		$this->server = Server::getInstance ();
		
		$this->db = (new Config ( $this->plugin->getDataFolder () . "pluginDB.yml", Config::YAML, [ 
				"defaultPrefix" => $this->plugin->get ( "default-player-prefix" ),
				"defaultPrefixFormat" => TextFormat::GOLD . "[ %prefix% ]",
				"chatFormat" => "%special%%prefix%%name%>%message%",
				"nameTagFormat" => "%prefix%%name%",
				"rankShop" => [ ] 
		] ))->getAll ();
	}
	public function save($async = false) {
		(new Config ( $this->plugin->getDataFolder () . "pluginDB.yml", Config::YAML, $this->db ))->save ( $async );
	}
	public function setDefaultPrefix($prefix) {
		$this->db ["defaultPrefix"] = $prefix;
	}
	public function setDefaultPrefixFormat($format) {
		$this->db ["defaultPrefixFormat"] = $format;
	}
	public function setChatFormat($format) {
		$this->db ["chatFormat"] = $format;
	}
	public function setNameTagFormat($format) {
		$this->db ["nameTagFormat"] = $format;
	}
	public function getDefaultPrefix() {
		return $this->db ["defaultPrefix"];
	}
	public function getDefaultPrefixFormat() {
		return $this->db ["defaultPrefixFormat"];
	}
	public function getChatFormat() {
		return $this->db ["chatFormat"];
	}
	public function getNameTagFormat() {
		return $this->db ["nameTagFormat"];
	}
	public function applyPrefixFormat($prefix) {
		return str_replace ( "%prefix%", $prefix, $this->db ["defaultPrefixFormat"] );
	}
	public function applyChatFormat($name, $message) {
		$special = $this->loader->getRankToName ( $name )->getSpecialPrefix ();
		$prefix = $this->loader->getRankToName ( $name )->getPrefix ();
		$string = $this->db ["chatFormat"];
		
		($special == null) ? $special = "" : $special = $this->applyPrefixFormat ( $special ) . " ";
		$string = str_replace ( "%special%", $special, $string );
		
		($prefix == null) ? $prefix = "" : $prefix = $this->applyPrefixFormat ( $prefix ) . " ";
		$string = str_replace ( "%prefix%", $prefix, $string );
		
		$string = str_replace ( "%name%", TextFormat::WHITE . $name . " ", $string );
		$string = str_replace ( "%message%", " " . $message, $string );
		return $string;
	}
	public function applyNameTagFormat($name) {
		$string = $this->db ["nameTagFormat"];
		
		$prefix = $this->loader->getRankToName ( $name )->getPrefix ();
		($prefix == null) ? $prefix = "" : $prefix = $this->applyPrefixFormat ( $prefix ) . " ";
		
		$string = str_replace ( "%prefix%", $prefix, $string );
		$string = str_replace ( "%name%", TextFormat::WHITE . $name, $string );
		return $string;
	}
	public function applyNameTag($name) {
		$player = $this->server->getPlayer ( $name );
		if ($player instanceof Player)
			$player->setNameTag ( $this->applyNameTagFormat ( $name ) );
	}
	public function setRankShop($levelName, $x, $y, $z, $prefix, $price) {
		$this->db ["rankShop"] ["{$levelName}:{$x}:{$y}:{$z}"] = [ 
				"prefix" => $prefix,
				"price" => $price 
		];
	}
	/**
	 *
	 * @param string $levelName        	
	 * @param int $x        	
	 * @param int $y        	
	 * @param int $z        	
	 * @return NULL|array
	 */
	public function getRankShop($levelName, $x, $y, $z) {
		if (! isset ( $this->db ["rankShop"] ["{$levelName}:{$x}:{$y}:{$z}"] ))
			return null;
		return $this->db ["rankShop"] ["{$levelName}:{$x}:{$y}:{$z}"];
	}
	public function deleteRankShop($levelName, $x, $y, $z) {
		if (! isset ( $this->db ["rankShop"] ["{$levelName}:{$x}:{$y}:{$z}"] ))
			return false;
		unset ( $this->db ["rankShop"] ["{$levelName}:{$x}:{$y}:{$z}"] );
		return true;
	}
	/**
	 * Create a default setting
	 *
	 * @param string $userName        	
	 */
	public function loadRank($userName) {
		return $this->loader->loadRank ( $userName );
	}
	public function unloadRank($userName = null) {
		return $this->loader->unloadRank ( $userName );
	}
	public function getRank(Player $player) {
		return $this->loader->getRank ( $player );
	}
	public function getRankToName($name) {
		return $this->loader->getRankToName ( $name );
	}
	public static function getInstance() {
		return static::$instance;
	}
}

?>