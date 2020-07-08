<?php
declare(strict_types=1);
namespace alvin0319\GodWar\task;

use pocketmine\Player;
use pocketmine\scheduler\Task;

class FlyTask extends Task{

	/** @var Player */
	protected $player;

	public function __construct(Player $player){
		$this->player = $player;
	}

	public function onRun(int $unused) : void{
		if($this->player instanceof Player){
			if($this->player->isOnline()){
				$this->player->setFlying(false);
				$this->player->setAllowFlight(false);

			}
		}
	}
}