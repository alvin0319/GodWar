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
use alvin0319\GodWar\task\SleepTask;
use pocketmine\entity\Attribute;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\item\BlazeRod;
use pocketmine\item\Item;

class Hypnos extends Job{

	public const SLEEP = "sleep";

	public const HEAL = "heal";

	public function getName() : string{
		return "Hypnos";
	}

	public function getDescription() : string{
		return "Hypnos - God of Sleep\n\nSkill 1: Puts all players to sleep. (Excluding yourself) Cooldown: 70 seconds\nSkill 2: Recovers your health by 8. Cooldown: 20 seconds";
	}

	public function useSkillOn(Item $item) : ?string{
		if($item instanceof BlazeRod){
			if($item->getNamedTagEntry(Job::SKILL1_NAME) !== null){
				if(!$this->getRoom()->isSkillBlocked()){
					if(!$this->hasCool(self::SLEEP)){
						$this->setCool(self::SLEEP, 70);
						foreach($this->getRoom()->getPlayers() as $player){
							$player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setValue(0);
							GodWar::getInstance()->getScheduler()->scheduleDelayedTask(new SleepTask($this->getRoom()), 20 * 3);
							return "Sleep";
						}
					}
				}else{
					$this->getPlayer()->sendMessage(GodWar::$prefix . "The skill cannot be used by Zeus.");
				}
			}
			if($item->getNamedTagEntry(Job::SKILL2_NAME) !== null){
				if(!$this->getRoom()->isSkillBlocked()){
					if(!$this->hasCool(self::HEAL)){
						$this->setCool(self::HEAL, 20);
						$this->getPlayer()->heal(new EntityRegainHealthEvent($this->getPlayer(), EntityRegainHealthEvent::CAUSE_CUSTOM, 8));
						return "Heal";
					}
				}else{
					$this->getPlayer()->sendMessage(GodWar::$prefix . "The skill cannot be used by Zeus.");
				}
			}
		}
		return null;
	}
}