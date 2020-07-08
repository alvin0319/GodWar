<?php
declare(strict_types=1);
namespace alvin0319\GodWar\result;

use alvin0319\GodWar\Room;

class GameResult{

	/** @var Room */
	protected $room;

	/** @var string|null */
	protected $winner = null;

	public function __construct(Room $room, ?string $winner){
		$this->room = $room;
		$this->winner = $winner;
	}

	public function getRoom() : Room{
		return $this->room;
	}

	public function getWinner() : ?string{
		return $this->winner;
	}
}