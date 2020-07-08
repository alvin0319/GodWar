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
namespace alvin0319\GodWar\form;

use alvin0319\GodWar\GodWar;
use alvin0319\GodWar\Room;
use pocketmine\form\Form;
use pocketmine\Player;
use function is_int;

class GodWarMainForm implements Form{

	public function jsonSerialize() : array{
		return [
			"type" => "form",
			"title" => "§aGodWar",
			"content" => "",
			"buttons" => [
				["text" => "Exit"],
				["text" => "Join the game"],
				["text" => "Show job list"],
				["text" => "Cancel the join"]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(is_int($data)){
			switch($data){
				case 1:
					if(!GodWar::getInstance()->getRoomForPlayer($player) instanceof Room){
						if(($room = GodWar::getInstance()->getAvailableRoom($player)) instanceof Room){
							$room->addPlayer($player);
						}else{
							$player->sendMessage(GodWar::$prefix . "There are no available rooms.");
						}
					}else{
						$player->sendMessage(GodWar::$prefix . "You can't join the room while in other room");
					}
					break;
				case 2:
					// TODO
					break;
				case 3:
					if(($room = GodWar::getInstance()->getRoomForPlayer($player)) instanceof Room){
						$room->removePlayer($player);
						$player->sendMessage(GodWar::$prefix . "You have left the room.");
					}else{
						$player->sendMessage(GodWar::$prefix . "You have not entered any room.");
					}
					break;
			}
		}
	}
}