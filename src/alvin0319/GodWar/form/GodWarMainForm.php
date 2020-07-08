<?php
declare(strict_types=1);
namespace alvin0319\GodWar\form;

use pocketmine\form\Form;
use pocketmine\Player;

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
	}
}