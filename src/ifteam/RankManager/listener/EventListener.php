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
		$this->loader->loadRank ( $event->getPlayer ()->getName () );
	}
	public function onPlayerQuitEvent(PlayerQuitEvent $event) {
		$this->loader->unloadRank ( $event->getPlayer ()->getName () );
	}
	public function onPlayerKickEvent(PlayerKickEvent $event) {
		$this->loader->unloadRank ( $event->getPlayer ()->getName () );
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
		
		if ($rankShop === null)
			return;
		
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
			// TODO 이미구매한 칭호입니다 ! 구매불가능 !
			// TODO /칭호 설정 칭호명 으로 변경 가능합니다 !
			return;
		}
		
		$economyAPI->reduceMoney ( $event->getPlayer (), $rankShop ["price"] );
		$rankData->addPrefixs ( [ 
				$rankShop ["prefix"] 
		] );
		$rankData->setPrefix ( $rankShop ["prefix"] );
		$this->plugin->message ( $player, $this->plugin->get ( "prefix-buy-success" ) );
	}
	public function onCommand(CommandSender $player, Command $command, $label, Array $args) {
		if (! $player->hasPermission ( "rankmanager.rank.manage" ))
			return false;
		if (! is_array ( $args ) or ! isset ( $args [0] )) {
		}
		// TODO 칭호 목록 - 본인이 보유하고 있는 칭호목록을 표시합니다.
		// TODO 칭호 설정 <칭호명> - 해당칭호로 칭호를 변경합니다.
		// TODO 칭호 삭제 <칭호명> - 해당 칭호를 삭제합니다.
		// ----------------------------------------------
		// TODO 칭호 추가 <유저명> <칭호명> - 해당유저에게 해당칭호를 줍니다.
		// TODO 칭호 삭제 <유저명> <칭호명> - 해당유저에게서 해당 칭호를 삭제합니다.
		// TODO 칭호 확인 <유저명> - 해당유저의 칭호를 확인합니다.
	}
}
?>