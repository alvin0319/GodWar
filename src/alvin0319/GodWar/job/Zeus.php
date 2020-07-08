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

use alvin0319\GodWar\GodWar;
use alvin0319\GodWar\task\FlyTask;
use pocketmine\item\BlazeRod;
use pocketmine\item\Item;

class Zeus extends Job{

	public const BLOCK_ALL_SKILLS = "blockSkills";

	public const FLY = "fly";

	public function getName() : string{
		return "Zeus";
	}

	public function getDescription() : string{
		return "Zeus - King of the Gods\n\nSkill 1: Block all players' skill use. Cooldown: 120 seconds\nSkill 2: Can fly for 10 seconds. Cooldown: 50 seconds";
	}

	public function useSkillOn(Item $item) : ?string{
		if($item instanceof BlazeRod){
			if($item->getNamedTagEntry(Job::SKILL1_NAME) !== null){
				if(!$this->hasCool(self::BLOCK_ALL_SKILLS)){
					$this->setCool(self::BLOCK_ALL_SKILLS, 120);
					$this->getRoom()->blockAllSkills();
					return "Block all skills";
				}
			}
			if($item->getNamedTagEntry(Job::SKILL2_NAME) !== null){
				if(!$this->hasCool(self::FLY)){
					$this->setCool(self::FLY, 50);
					$this->getPlayer()->setAllowFlight(true);
					GodWar::getInstance()->getScheduler()->scheduleDelayedTask(new FlyTask($this->getPlayer()), 200);
					return "Fly";
				}
			}
		}
		return null;
	}
}