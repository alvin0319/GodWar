<?php
declare(strict_types=1);
namespace alvin0319\GodWar\job;

use alvin0319\GodWar\Room;
use pocketmine\item\Item;
use pocketmine\Player;
use function time;

abstract class Job{

	public const SKILL1_NAME = "skill1";

	public const SKILL2_NAME = "skill2";

	/** @var Player */
	protected $player;

	/** @var Room */
	protected $room;

	/** @var int[] */
	protected $coolTimes = [];

	public function __construct(Player $player, Room $room){
		$this->player = $player;
		$this->room = $room;
	}

	final public function getPlayer() : Player{
		return $this->player;
	}

	final public function getRoom() : Room{
		return $this->room;
	}

	public function hasCool(string $name, int $cool) : bool{
		if(!isset($this->coolTimes[$name])){
			return false;
		}
		return time() - $this->coolTimes[$name] >= $cool;
	}

	public function setCool(string $name) : void{
		$this->coolTimes[$name] = time();
	}

	abstract public function getName() : string;

	abstract public function useSkillOn(Item $item) : ?string;

	abstract public function getDescription() : string;
}