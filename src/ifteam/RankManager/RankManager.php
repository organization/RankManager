<?php

namespace ifteam\RankManager;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\IPlayer;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class RankManager extends PluginBase implements Listener {
	/**
	 *
	 * @var RankManager default config
	 */
	public $db;
	/**
	 *
	 * @var Users prefix data
	 */
	public $users = [ ];
	/**
	 *
	 * @var Users special prefix data
	 */
	public $specialPrefix = [ ];
	/**
	 *
	 * @var Message file version
	 */
	private $m_version = 1;
	/**
	 *
	 * @var Plug-in Instance
	 */
	private static $instance = null;
	/**
	 *
	 * @var EventListener
	 */
	public $eventListener;
	public function onEnable() {
		if (! file_exists ( $this->getDataFolder () ))
			@mkdir ( $this->getDataFolder () );
		
		$this->initMessage ();
		// chat example
		// [ 광산 ] [ 일반 ] hmhmmhm > 채팅메시지 견본
		
		// nametag example
		// [ 24레벨 ] [ 일반 ]
		// hmhmmhm
		$this->db = (new Config ( $this->getDataFolder () . "pluginDB.yml", Config::YAML, [ 
				"defaultPrefix" => $this->get ( "default-player-prefix" ),
				"defaultPrefixFormat" => TextFormat::GOLD . "[ %prefix% ]",
				"chatPrefix" => "%special_prefix% %prefixs% %user_name% > %message%",
				"nameTagPrefix" => "%prefixs% %user_name%",
				"rankShop" => [ ] 
		] ))->getAll ();
		
		if (self::$instance == null)
			self::$instance = $this;
		
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
		$this->eventListener = new EventListener ( $this );
	}
	private function messagesUpdate($targetYmlName) {
		$targetYml = (new Config ( $this->getDataFolder () . $targetYmlName, Config::YAML ))->getAll ();
		if (! isset ( $targetYml ["m_version"] )) {
			$this->saveResource ( $targetYmlName, true );
		} else if ($targetYml ["m_version"] < $this->m_version) {
			$this->saveResource ( $targetYmlName, true );
		}
	}
	private function initMessage() {
		$this->saveResource ( "messages.yml", false );
		$this->messagesUpdate ( "messages.yml" );
		$this->messages = (new Config ( $this->getDataFolder () . "messages.yml", Config::YAML ))->getAll ();
	}
	public function get($var) {
		if (isset ( $this->messages [$this->getServer ()->getLanguage ()->getLang ()] )) {
			$lang = $this->getServer ()->getLanguage ()->getLang ();
		} else {
			$lang = "eng";
		}
		return $this->messages [$lang . "-" . $var];
	}
	public function message($player, $text = "", $mark = null) {
		if ($mark == null)
			$mark = $this->get ( "default-prefix" );
		$player->sendMessage ( TextFormat::DARK_AQUA . $mark . " " . $text );
	}
	public function alert($player, $text = "", $mark = null) {
		if ($mark == null)
			$mark = $this->get ( "default-prefix" );
		$player->sendMessage ( TextFormat::RED . $mark . " " . $text );
	}
	public static function getInstance() {
		return static::$instance;
	}
	public function onCommand(CommandSender $player, Command $command, $label, Array $args) {
		$this->eventListener->onCommand ( $player, $command, $label, $args );
	}
}

?>