<?php
declare(strict_types=1);
namespace alvin0319\GodWar\job;

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
		return "Helios - God of the Sun\nSkill 1: Blocks the opposing player's vision within 8 spaces around you. Cooldown: 60 seconds\nSkill 2: Ignite opponents within 10 squares. Cooldown: 50 seconds";
	}

	public function useSkillOn(Item $item) : ?string{
		if($item instanceof BlazeRod){
			if($item->getNamedTagEntry(Job::SKILL1_NAME) !== null){
				if(!$this->hasCool(self::BLOCK_VISION, 60)){
					$this->setCool(self::BLOCK_VISION);
					foreach($this->getPlayer()->getLevel()->getPlayers() as $player){
						if($this->getPlayer()->distance($player) <= 8){
							if(!$this->getRoom()->isSameTeam($this->getPlayer(), $player)){
								$player->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 20 * 5, 0));
							}
						}
					}
					return "Block vision";
				}
			}
			if($item->getNamedTagEntry(Job::SKILL2_NAME) !== null){
				if(!$this->hasCool(self::FIRE, 50)){
					$this->setCool(self::FIRE);
					foreach($this->getPlayer()->getLevel()->getPlayers() as $player){
						if($this->getPlayer()->distance($player) <= 10){
							if(!$this->getRoom()->isSameTeam($this->getPlayer(), $player)){
								$player->setOnFire(5);
							}
						}
					}
					return "Fire";
				}
			}
		}
		return null;
	}
}