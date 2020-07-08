<?php
declare(strict_types=1);
namespace alvin0319\GodWar\job;

use alvin0319\GodWar\GodWar;
use alvin0319\GodWar\task\FlyTask;
use pocketmine\item\BlazeRod;
use pocketmine\item\Item;

class Zeus extends Job{

	public const BLOCK_ALL_SKILLS = "blockSkills";

	public const FLY = "fly";

	public function getName() : string{
		return "Zeus";
	}

	public function getDescription() : string{
		return "Zeus - King of the Gods\nSkill 1: Block all players' skill use. Cooldown: 120 seconds\nSkill 2: Can fly for 10 seconds. Cooldown: 50 seconds";
	}

	public function useSkillOn(Item $item) : ?string{
		if($item instanceof BlazeRod){
			if($item->getNamedTagEntry(Job::SKILL1_NAME) !== null){
				if(!$this->hasCool(self::BLOCK_ALL_SKILLS, 120)){
					$this->setCool(self::BLOCK_ALL_SKILLS);
					return "Block all skills";
				}
			}
			if($item->getNamedTagEntry(Job::SKILL2_NAME) !== null){
				if(!$this->hasCool(self::FLY, 50)){
					$this->setCool(self::FLY);
					$this->getPlayer()->setAllowFlight(true);
					GodWar::getInstance()->getScheduler()->scheduleDelayedTask(new FlyTask($this->getPlayer()), 200);
				}
			}
		}
		return null;
	}
}