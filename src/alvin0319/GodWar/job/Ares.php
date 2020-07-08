<?php
declare(strict_types=1);
namespace alvin0319\GodWar\job;

use pocketmine\item\Item;

class Ares extends Job{

	public const STRENGTH = "strength";

	public const TRIDENT = "trident";

	public function getName() : string{
		return "Ares";
	}

	public function getDescription() : string{
		return "Ares - God of War\nSkill 1: Level 1 power buff on teammates. Cooldown: 30 seconds\nSkill 2: Fire a trident. The opponent who hit the trident takes 5 damage and burns for 5 seconds.";
	}

	public function useSkillOn(Item $item) : ?string{

	}
}