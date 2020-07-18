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

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\BlazeRod;
use pocketmine\item\Item;
use pocketmine\math\Vector3;

class Gaia extends Job{

	public const MOVE = "move";

	public const PROTECTION = "protection";

	public function getName() : string{
		return "Gaia";
	}

	public function getDescription() : string{
		return "Gaia - Goddess of Earth\n\nSkill 1: Bind opponents within 6 squares of me to the floor. Cooldown: 30s\nSkill 2: Gain 2 protection for 5 seconds. Cooldown: 50 seconds";
	}

	public function useSkillOn(Item $item) : ?string{
		if($item instanceof BlazeRod){
			if($item->getNamedTagEntry(Job::SKILL1_NAME) !== null){
				if(!$this->hasCool(self::MOVE)){
					$this->setCool(self::MOVE, 30);
					foreach($this->getPlayer()->getLevel()->getPlayers() as $player){
						if($this->getRoom()->getTeamFor($this->getPlayer()) !== $this->getRoom()->getTeamFor($player)){
							if($this->getPlayer()->distance($player) <= 6){
								$player->addEffect(new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 20 * 3, 3));
								if($player->getLevel()->getBlock($player->add(0, -1))->getId() !== 0){
									$player->teleport(new Vector3($player->getX(), $player->getLevel()->getHighestBlockAt($player->getFloorX() >> 4, $player->getFloorZ() >> 4), $player->getZ()));
								}
							}
						}
					}
					return "Slowness";
				}
			}
			if($item->getNamedTagEntry(Job::SKILL2_NAME) !== null){
				if(!$this->hasCool(self::PROTECTION)){
					$this->setCool(self::PROTECTION, 50);
					$this->getPlayer()->addEffect(new EffectInstance(Effect::getEffect(Effect::RESISTANCE), 20 * 5, 1));
					return "Protection";
				}
			}
		}
		return null;
	}
}