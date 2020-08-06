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

use pocketmine\form\Form;
use pocketmine\Player;
use function is_int;

class HowToPlayForm implements Form{

	public function jsonSerialize() : array{
		$rule = "Rule\n\n§lHow to win?§r\nTo win the game, you have to break the opponent's core using your skills and the skills of your teammates.\n\n§lWhy diamond blocks?§r\nWhen playing with blocks other than diamond blocks, the game may end quickly or take a long time.\n\n§lOkay, but do I have to break it with my hands?§r\nIf we use tools, the game could end too easily :(\n\n§lOkay, how do you use the skill?§r\nBefore using the skill, we need to collect stones using \"Stone Generator\" and then gamble using \"!betting\". Stone generators can be created using lava and water. In gambling, you can get various items such as stones, iron, diamonds, and skill items. If you're lucky, you can get diamonds too! The skill item is \"Blaze Stick\" and the skill is used when touching the ground.\n\n§lIf you have more questions, please create a new issue at \nhttps://github.com/alvin0319/GodWar!";
		return ["type" => "form", "title" => "GodWar", "content" => $rule, "buttons" => [["text" => "exit"], ["text" => "back to main menu"]]];
	}

	public function handleResponse(Player $player, $data) : void{
		if(is_int($data)){
			switch($data){
				case 1:
					$player->sendForm(new GodWarMainForm());
					break;
			}
		}
	}
}