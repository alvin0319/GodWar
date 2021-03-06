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
namespace alvin0319\GodWar\entity;

use pocketmine\entity\EntityIds;
use pocketmine\entity\Living;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\Player;

class TridentEntity extends Projectile{

	public const NETWORK_ID = EntityIds::TRIDENT;

	public $width = 0.5;
	public $height = 0.5;

	protected bool $critical = false;

	public function onCollideWithPlayer(Player $player) : void{
		$this->flagForDespawn();
	}

	public function onHit(ProjectileHitEvent $event) : void{
		$this->flagForDespawn();
		if($event instanceof ProjectileHitEntityEvent){
			$player = $event->getEntityHit();
			$player->setOnFire(5);
			$player->attack(new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_PROJECTILE, ($this->critical ? 10 : 5)));
			if($this->critical){
				if($player instanceof Living){
					$player->knockBack($this, 0, $this->x - $player->x, $this->z - $player->z);
				}
			}
		}
	}

	public function setCritical(bool $v) : void{
		$this->critical = $v;
	}
}