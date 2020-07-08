<?php
declare(strict_types=1);
namespace alvin0319\GodWar\event;

use alvin0319\GodWar\Room;
use pocketmine\event\Event;

class GameStartEvent extends Event{

	/** @var Room */
	protected $room;

	public function __construct(Room $room){
		$this->room = $room;
	}

	public function getRoom() : Room{
		return $this->room;
	}
}