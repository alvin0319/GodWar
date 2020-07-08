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
namespace alvin0319\GodWar\task;

use alvin0319\GodWar\Room;
use pocketmine\entity\Attribute;
use pocketmine\scheduler\Task;

class SleepTask extends Task{

	protected $room;

	public function __construct(Room $room){
		$this->room = $room;
	}

	public function onRun(int $unused) : void{
		foreach($this->room->getPlayers() as $player){
			$player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setValue($player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->getDefaultValue());
		}
	}
}