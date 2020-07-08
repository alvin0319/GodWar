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
use pocketmine\item\BlazeRod;
use pocketmine\item\Item;

class Helios extends Job{

	public const BLOCK_VISION = "blockVision";

	public const FIRE = "fire";

	public function getName() : string{
		return "Helios";
	}

	public function getDescription() : string{
		return "Helios - God of the Sun\n\nSkill 1: Blocks the opposing player's vision within 8 spaces around you. Cooldown: 60 seconds\nSkill 2: Ignite opponents within 10 squares. Cooldown: 50 seconds";
	}

	public function useSkillOn(Item $item) : ?string{
		if($item instanceof BlazeRod){
			if($item->getNamedTagEntry(Job::SKILL1_NAME) !== null){
				if(!$this->getRoom()->isSkillBlocked()){
					if(!$this->hasCool(self::BLOCK_VISION)){
						$this->setCool(self::BLOCK_VISION, 60);
						foreach($this->getPlayer()->getLevel()->getPlayers() as $player){
							if($this->getPlayer()->distance($player) <= 8 && $player !== $this->getPlayer()){
								if(!$this->getRoom()->isSameTeam($this->getPlayer(), $player)){
									$player->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 20 * 5, 0));
								}
							}
						}
						return "Block vision";
					}
				}else{
					$this->getPlayer()->sendMessage(GodWar::$prefix . "The skill cannot be used by Zeus.");
				}
			}
			if($item->getNamedTagEntry(Job::SKILL2_NAME) !== null){
				if(!$this->getRoom()->isSkillBlocked()){
					if(!$this->hasCool(self::FIRE)){
						$this->setCool(self::FIRE, 50);
						foreach($this->getPlayer()->getLevel()->getPlayers() as $player){
							if($this->getPlayer()->distance($player) <= 10){
								if(!$this->getRoom()->isSameTeam($this->getPlayer(), $player)){
									$player->setOnFire(5);
								}
							}
						}
						return "Fire";
					}
				}else{
					$this->getPlayer()->sendMessage(GodWar::$prefix . "The skill cannot be used by Zeus.");
				}
			}
		}
		return null;
	}
}