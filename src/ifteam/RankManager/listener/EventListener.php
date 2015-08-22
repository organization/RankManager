<?php

namespace ifteam\RankManager\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\tile\Sign;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use ifteam\RankManager\rank\RankLoader;
use ifteam\RankManager\RankManager;
use ifteam\RankManager\rank\RankProvider;
use ifteam\RankManager\listener\other\ListenerLoader;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerChatEvent;

class EventListener implements Listener {
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
	 * @var RankProvider
	 */
	private $provider;
	/**
	 *
	 * @var ListenerLoader
	 */
	private $listenerLoader;
	public function __construct(RankManager $plugin) {
		$this->plugin = $plugin;
		$this->loader = $plugin->getRankLoader ();
		$this->provider = $plugin->getRankProvider ();
		$this->listenerLoader = $plugin->getListenerLoader ();
		
		$plugin->getServer ()->getPluginManager ()->registerEvents ( $this, $plugin );
	}
	public function onPlayerJoinEvent(PlayerJoinEvent $event) {
		if ($this->provider->getDefaultPrefix () != null) {
			$rankData = $this->loader->getRank ( $event->getPlayer () );
			
			$rankData->addPrefixs ( [ 
					$this->provider->getDefaultPrefix () 
			] );
			if ($rankData->getPrefix () == null)
				$rankData->setPrefix ( $this->provider->getDefaultPrefix () );
		}
	}
	public function onPlayerQuitEvent(PlayerQuitEvent $event) {
		$this->loader->unloadRank ( $event->getPlayer ()->getName () );
	}
	public function onPlayerKickEvent(PlayerKickEvent $event) {
		$this->loader->unloadRank ( $event->getPlayer ()->getName () );
	}
	public function onPlayerChatEvent(PlayerChatEvent $event) {
		$event->setFormat ( $this->provider->applyChatFormat ( $event->getPlayer ()->getName (), TextFormat::WHITE . $event->getMessage () ) );
	}
	public function onSignChangeEvent(SignChangeEvent $event) {
		if (! $event->getPlayer ()->hasPermission ( "rankmanager.rankshop.create" ))
			return;
		switch ($event->getLine ( 0 )) {
			case $this->plugin->get ( "rankshop" ) :
				if ($event->getLine ( 1 ) == null or $event->getLine ( 2 ) === null or ! is_numeric ( $event->getLine ( 2 ) )) {
					$this->plugin->message ( $event->getPlayer (), $this->plugin->get ( "rankshop-help" ) );
					return;
				}
				$requestedPrefix = $event->getLine ( 1 );
				$requestedPrice = $event->getLine ( 2 );
				$levelName = $event->getBlock ()->getLevel ()->getName ();
				$x = $event->getBlock ()->getX ();
				$y = $event->getBlock ()->getY ();
				$z = $event->getBlock ()->getZ ();
				$this->provider->setRankShop ( $levelName, $x, $y, $z, $requestedPrefix, $requestedPrice );
				
				$prefix = $this->provider->applyPrefixFormat ( $requestedPrefix );
				$event->setLine ( 0, $this->plugin->get ( "rankshop-format1" ) );
				$event->setLine ( 1, str_replace ( "%prefix%", $prefix, $this->plugin->get ( "rankshop-format2" ) ) );
				$event->setLine ( 2, str_replace ( "%price%", $requestedPrice, $this->plugin->get ( "rankshop-format3" ) ) );
				
				$this->plugin->message ( $event->getPlayer (), $this->plugin->get ( "rankshop-created" ) );
				break;
		}
	}
	public function onPlayerInteractEvent(PlayerInteractEvent $event) {
		if (! $event->getBlock () instanceof Sign)
			return;
		
		$levelName = $event->getBlock ()->getLevel ()->getName ();
		$x = $event->getBlock ()->getX ();
		$y = $event->getBlock ()->getY ();
		$z = $event->getBlock ()->getZ ();
		$rankShop = $this->provider->getRankShop ( $levelName, $x, $y, $z );
		
		if ($rankShop !== null) {
			$event->setCancelled ();
			
			if (! $event->getPlayer ()->hasPermission ( "rankmanager.rankshop.use" )) {
				$this->plugin->alert ( $event->getPlayer (), $this->plugin->get ( "rankshop-you-cant-buy-rank" ) );
				return;
			}
			
			$economyAPI = $this->listenerLoader->getEconomyAPI ();
			if ($economyAPI === null) {
				$this->plugin->alert ( $event->getPlayer (), $this->plugin->get ( "there-are-no-economyapi" ) );
				return;
			}
			
			$myMoney = $economyAPI->myMoney ( $event->getPlayer () );
			if ($rankShop ["price"] > $myMoney) {
				$this->plugin->message ( $event->getPlayer (), $this->plugin->get ( "rankshop-not-enough-money" ) );
				return;
			}
			
			$rankData = $this->loader->getRank ( $event->getPlayer () );
			if ($rankData->isExistPrefix ( $rankShop ["prefix"] )) {
				$this->plugin->alert ( $event->getPlayer (), $this->plugin->get ( "already-buy-that-prefix" ) );
				$this->plugin->alert ( $event->getPlayer (), $this->plugin->get ( "you-can-change-prefix" ) );
				return;
			}
			
			$economyAPI->reduceMoney ( $event->getPlayer (), $rankShop ["price"] );
			$rankData->addPrefixs ( [ 
					$rankShop ["prefix"] 
			] );
			$rankData->setPrefix ( $rankShop ["prefix"] );
			$this->plugin->message ( $player, $this->plugin->get ( "prefix-buy-success" ) );
		}
	}
	public function onCommand(CommandSender $player, Command $command, $label, Array $args) {
		if (! $player->hasPermission ( "rankmanager.rank.manage" ))
			return false;
		if (strtolower ( $command->getName () ) != $this->plugin->get ( "rank" ))
			return false;
		
		if (! isset ( $args [0] )) {
			$this->plugin->message ( $player, $this->plugin->get ( "rank-help1" ) );
			$this->plugin->message ( $player, $this->plugin->get ( "rank-help2" ) );
			$this->plugin->message ( $player, $this->plugin->get ( "rank-help3" ) );
			$this->plugin->message ( $player, $this->plugin->get ( "rank-help6" ) );
			if ($player->hasPermission ( "rankmanager.rank.control" )) {
				$this->plugin->message ( $player, $this->plugin->get ( "rank-help4" ) );
				$this->plugin->message ( $player, $this->plugin->get ( "rank-help5" ) );
			}
			return true;
		}
		
		switch ($args [0]) {
			case $this->plugin->get ( "list" ) :
				$rankData = $this->loader->getRank ( $player );
				
				$string = TextFormat::DARK_AQUA;
				foreach ( $rankData->getPrefixList () as $prefix => $bool )
					$string .= "<{$prefix}> ";
				$this->plugin->message ( $player, $this->plugin->get ( "show-the-self-prefix-list" ) );
				$this->plugin->message ( $player, $string );
				break;
			case $this->plugin->get ( "set" ) :
				if (! isset ( $args [1] )) {
					$this->plugin->message ( $player, $this->plugin->get ( "rank-help2" ) );
					return true;
				}
				$rankData = $this->loader->getRank ( $player );
				if (! $rankData->isExistPrefix ( $args [1] )) {
					$this->plugin->alert ( $player, $this->plugin->get ( "not-exist-that-prefix" ) );
					return true;
				}
				$rankData->setPrefix ( $args [1] );
				$this->provider->applyNameTag ( $player->getName () );
				$this->plugin->message ( $player, $this->plugin->get ( "prefix-changed" ) );
				break;
			case $this->plugin->get ( "add" ) :
				if (! $player->hasPermission ( "rankmanager.rank.control" ))
					return false;
				if (! isset ( $args [1] )) {
					$this->plugin->message ( $player, $this->plugin->get ( "rank-help4" ) );
					return true;
				}
				if (! isset ( $args [2] )) {
					$rankData = $this->loader->getRank ( $player );
					if ($rankData->isExistPrefix ( $args [1] )) {
						$this->plugin->alert ( $player, $this->plugin->get ( "already-exist-that-prefix" ) );
						return true;
					}
					$rankData->addPrefixs ( [ 
							$args [1] 
					] );
					$this->plugin->message ( $player, $this->plugin->get ( "prefix-added" ) );
				} else {
					$rankData = $this->loader->getRankToName ( $args [1] );
					if ($rankData->isExistPrefix ( $args [2] )) {
						$this->plugin->alert ( $player, $this->plugin->get ( "already-exist-that-prefix" ) );
						return true;
					}
					$rankData->addPrefixs ( [ 
							$args [2] 
					] );
					$this->plugin->message ( $player, $this->plugin->get ( "prefix-added" ) );
				}
				break;
			case $this->plugin->get ( "del" ) :
				if (! isset ( $args [1] )) {
					$this->plugin->message ( $player, $this->plugin->get ( "rank-help3" ) );
					return true;
				}
				if (! isset ( $args [2] )) {
					$rankData = $this->loader->getRank ( $player );
					if (! $rankData->isExistPrefix ( $args [1] )) {
						$this->plugin->alert ( $player, $this->plugin->get ( "not-exist-that-prefix" ) );
						return true;
					}
					$rankData->deletePrefixs ( [ 
							$args [1] 
					] );
					$this->plugin->message ( $player, $this->plugin->get ( "prefix-deleted" ) );
				} else {
					if (! $player->hasPermission ( "rankmanager.rank.control" ))
						return false;
					$rankData = $this->loader->getRankToName ( $args [1] );
					if (! $rankData->isExistPrefix ( $args [2] )) {
						$this->plugin->alert ( $player, $this->plugin->get ( "not-exist-that-prefix" ) );
						return true;
					}
					$rankData->deletePrefixs ( [ 
							$args [2] 
					] );
					$this->plugin->message ( $player, $this->plugin->get ( "prefix-deleted" ) );
				}
				break;
			case $this->plugin->get ( "check" ) :
				if (! isset ( $args [1] )) {
					$this->plugin->message ( $player, $this->plugin->get ( "rank-help6" ) );
					return true;
				}
				$rankData = $this->loader->getRankToName ( $args [1] );
				
				$string = TextFormat::DARK_AQUA;
				foreach ( $rankData->getPrefixList () as $prefix => $bool )
					$string .= "<{$prefix}> ";
				$this->plugin->message ( $player, $this->plugin->get ( "show-the-user-prefix-list" ) );
				$this->plugin->message ( $player, $string );
				break;
		}
		return true;
	}
}
?>