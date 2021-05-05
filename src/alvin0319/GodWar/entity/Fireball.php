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
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\level\Explosion;
use pocketmine\level\particle\HugeExplodeParticle;
use pocketmine\Player;

class Fireball extends Projectile{

	public const NETWORK_ID = EntityIds::FIREBALL;

	public $width = 0.1;
	public $height = 0.1;

	protected int $aliveTicks = 20 * 10;

	public function onUpdate(int $currentTick) : bool{
		$hasUpdate = parent::onUpdate($currentTick);
		if(--$this->aliveTicks < 0 or $this->isCollided){
			$ev = new EntityExplodeEvent($this, $this, [], 0.0);
			$ev->call();

			$explode = new Explosion($this, 5);
			$explode->explodeA();

			$this->getLevel()->addParticle(new HugeExplodeParticle($this));
			$this->flagForDespawn();
		}
		return $hasUpdate;
	}
}