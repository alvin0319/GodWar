<?php
declare(strict_types=1);
namespace alvin0319\GodWar\event;

use alvin0319\GodWar\result\GameResult;
use pocketmine\event\Event;

class GameEndEvent extends Event{

	/** @var GameResult */
	protected $result;

	public function __construct(GameResult $result){
		$this->result = $result;
	}

	public function getResult() : GameResult{
		return $this->result;
	}
}