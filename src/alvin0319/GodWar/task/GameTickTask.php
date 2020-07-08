<?php
declare(strict_types=1);
namespace alvin0319\GodWar\task;

use alvin0319\GodWar\GodWar;
use pocketmine\scheduler\Task;

class GameTickTask extends Task{

	public function onRun(int $unused) : void{
		GodWar::getInstance()->syncGameTick();
	}
}