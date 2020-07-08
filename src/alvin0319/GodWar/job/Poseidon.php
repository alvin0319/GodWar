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

use alvin0319\GodWar\entity\TridentEntity;
use alvin0319\GodWar\GodWar;
use pocketmine\entity\Entity;
use pocketmine\item\BlazeRod;
use pocketmine\item\Item;

class Poseidon extends Job{

	public const DASH = "dash";

	public const TRIDENT = "trident";

	public function getName() : string{
		return "Poseidon";
	}

	public function getDescription() : string{
		return "Poseidon - God of the Sea\n\nSkill 1: Dash Forward. Cooldown: 10 seconds\nSkill 2: Fire a trident. The opponent who hit the trident will burn and take 10 damage. Cooldown: 50 seconds";
	}

	public function useSkillOn(Item $item) : ?string{
		if($item instanceof BlazeRod){
			if($item->getNamedTagEntry(Job::SKILL1_NAME) !== null){
				if(!$this->getRoom()->isSkillBlocked()){
					if(!$this->hasCool(self::DASH)){
						$this->setCool(self::DASH, 10);
						$this->getPlayer()->setMotion($this->getPlayer()->getDirectionVector());
						return "Dash";
					}
				}else{
					$this->getPlayer()->sendMessage(GodWar::$prefix . "The skill cannot be used by Zeus.");
				}
			}
			if($item->getNamedTagEntry(Job::SKILL2_NAME) !== null){
				if(!$this->getRoom()->isSkillBlocked()){
					if(!$this->hasCool(self::TRIDENT)){
						$this->setCool(self::TRIDENT, 50);

						$nbt = Entity::createBaseNBT(
							$this->getPlayer()->add(0, $this->getPlayer()->getEyeHeight() + 1, 0),
							$this->getPlayer()->getDirectionVector(),
							($this->getPlayer()->yaw > 180 ? 360 : 0) - $this->getPlayer()->yaw,
							-$this->getPlayer()->pitch
						);

						/** @var TridentEntity $entity */
						$entity = Entity::createEntity("GodWarTrident", $this->getPlayer()->getLevel(), $nbt);
						$entity->setCritical(true);
						$entity->spawnToAll();
						return "Trident";
					}
				}else{
					$this->getPlayer()->sendMessage(GodWar::$prefix . "The skill cannot be used by Zeus.");
				}
			}
		}
		return null;
	}
}