<?php
//
namespace ifteam\RankManager\task;

use pocketmine\scheduler\Task;
use ifteam\RankManager\rank\RankLoader;

class AutoUnloadTask extends Task {
	protected $owner;
	public function __construct(RankLoader $owner) {
		$this->owner = $owner;
	}
	public function onRun($currentTick) {
		$this->owner->unloadRank ();
	}
}
?>