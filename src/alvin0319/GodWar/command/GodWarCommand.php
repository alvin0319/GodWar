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
namespace alvin0319\GodWar\command;

use alvin0319\GodWar\form\GodWarMainForm;
use alvin0319\GodWar\GodWar;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\entity\Entity;
use pocketmine\Player;
use function trim;

class GodWarCommand extends PluginCommand{

	public function __construct(){
		parent::__construct("god", GodWar::getInstance());
		$this->setDescription("GodWar command");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if($sender instanceof Player){
			if(!$sender->isOp()){
				$sender->sendForm(new GodWarMainForm());
			}else{
				if(trim($args[0] ?? "") !== ""){
					switch($args[0]){
						case "fireball":
							$nbt = Entity::createBaseNBT(
								$sender->add(0, $sender->getEyeHeight() + 1, 0),
								$sender->getDirectionVector(),
								($sender->yaw > 180 ? 360 : 0) - $sender->yaw,
								-$sender->pitch
							);

							$entity = Entity::createEntity("GodWarFireball", $sender->getLevel(), $nbt);
							$entity->spawnToAll();
							// fireball
							break;
						case "trident":
							$nbt = Entity::createBaseNBT(
								$sender->add(0, $sender->getEyeHeight() + 1, 0),
								$sender->getDirectionVector(),
								($sender->yaw > 180 ? 360 : 0) - $sender->yaw,
								-$sender->pitch
							);

							$entity = Entity::createEntity("GodWarTrident", $sender->getLevel(), $nbt);
							$entity->spawnToAll();
							// trident
							break;
						default:
							$sender->sendForm(new GodWarMainForm());
					}
				}else{
					$sender->sendForm(new GodWarMainForm());
				}
			}
		}
		return true;
	}
}