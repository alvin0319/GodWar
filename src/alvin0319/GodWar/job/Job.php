<?php

/*
 *    ___          _ __    __
 *   / _ \___   __| / / /\ \ \__ _ _ __
 *  / /_\/ _ \ / _` \ \/  \/ / _` | '__|
 * / /_\\ (_) | (_| |\  /\  / (_| | |
 * \____/\___/ \__,_| \/  \/ \__,_|_|
 *
 * Copyright (C) 2020 alvin0319
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

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
	protected Player $player;

	/** @var Room */
	protected Room $room;

	/** @var int[][] */
	protected array $coolTimes = [];

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

	public function hasCool(string $name) : bool{
		if(!isset($this->coolTimes[$name])){
			return false;
		}
		return time() - $this->coolTimes[$name]["time"] < $this->coolTimes[$name]["cool"];
	}

	public function setCool(string $name, int $cool) : void{
		$this->coolTimes[$name] = ["cool" => $cool, "time" => time()];
	}

	public function getCoolTimes() : array{
		return $this->coolTimes;
	}

	abstract public function getName() : string;

	abstract public function useSkillOn(Item $item) : ?string;

	abstract public function getDescription() : string;
}