<?php

namespace ifteam\RankManager\rank;

use ifteam\RankManager\RankManager;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class RankProvider {
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
	 * @var RankProvider DB
	 */
	private $db;
	public function __construct(RankManager $plugin) {
		$this->plugin = $plugin;
		$this->loader = $plugin->getRankLoader ();
		
		$this->db = (new Config ( $this->plugin->getDataFolder () . "pluginDB.yml", Config::YAML, [ 
				"defaultPrefix" => $this->plugin->get ( "default-player-prefix" ),
				"defaultPrefixFormat" => TextFormat::GOLD . "[ %prefix% ]",
				"chatFormat" => "%special% %prefix% %name% > %message%",
				"nameTagFormat" => "%prefix% %name%",
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
		
		$string = str_replace ( "%special%", $this->applyPrefixFormat ( $special ), $string );
		$string = str_replace ( "%prefix%", $this->applyPrefixFormat ( $prefix ), $string );
		$string = str_replace ( "%name%", $name, $string );
		$string = str_replace ( "%message%", $message, $string );
		return $string;
	}
	public function applyNameTagFormat($name) {
		$string = $this->db ["nameTagFormat"];
		
		$string = str_replace ( "%prefix%", $prefix, $this->db ["defaultPrefixFormat"] );
		$string = str_replace ( "%name%", $name, $this->db ["defaultPrefixFormat"] );
		return $string;
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
}

?>