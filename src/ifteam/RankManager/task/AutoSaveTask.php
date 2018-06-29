<?php
//
namespace ifteam\RankManager\task;

use pocketmine\scheduler\Task;
use pocketmine\plugin\Plugin;

class AutoSaveTask extends Task {
	protected $owner;
	
	public function __construct(Plugin $owner) {
		$this->owner = $owner;
	}
	public function onRun(int $currentTick) {
		$this->owner->save ( true );
	}
}
?>