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
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\item\BlazeRod;
use pocketmine\item\Item;
use pocketmine\Player;

class Ares extends Job{

	public const STRENGTH = "strength";

	public const TRIDENT = "trident";

	public function getName() : string{
		return "Ares";
	}

	public function getDescription() : string{
		return "Ares - God of War\n\nSkill 1: Level 1 power buff on teammates. Cooldown: 30 seconds\nSkill 2: Fire a trident. The opponent who hit the trident takes 5 damage and burns for 5 seconds. Cooldown: 40 seconds";
	}

	public function useSkillOn(Item $item) : ?string{
		if($item instanceof BlazeRod){
			if($item->getNamedTagEntry(Job::SKILL1_NAME) !== null){
				if(!$this->getRoom()->isSkillBlocked()){
					if(!$this->hasCool(self::STRENGTH)){
						$this->setCool(self::STRENGTH, 30);
						foreach($this->getRoom()->getTeamPlayers($this->getPlayer()) as $player){
							if(($target = $this->getRoom()->getServer()->getPlayerExact($player)) instanceof Player){
								$target->addEffect(new EffectInstance(Effect::getEffect(Effect::STRENGTH), 20 * 10, 1));
							}
						}
						return "Strength";
					}
				}else{
					$this->getPlayer()->sendMessage(GodWar::$prefix . "The skill cannot be used by Zeus.");
				}
			}
			if($item->getNamedTagEntry(Job::SKILL2_NAME) !== null){
				if(!$this->getRoom()->isSkillBlocked()){
					if(!$this->hasCool(self::TRIDENT)){
						$this->setCool(self::TRIDENT, 40);

						$nbt = Entity::createBaseNBT(
							$this->getPlayer()->add(0, $this->getPlayer()->getEyeHeight() + 1, 0),
							$this->getPlayer()->getDirectionVector(),
							($this->getPlayer()->yaw > 180 ? 360 : 0) - $this->getPlayer()->yaw,
							-$this->getPlayer()->pitch
						);

						$entity = Entity::createEntity("GodWarTrident", $this->getPlayer()->getLevel(), $nbt);
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