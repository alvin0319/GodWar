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
namespace alvin0319\GodWar;

use alvin0319\GodWar\event\GameEndEvent;
use alvin0319\GodWar\event\GameStartEvent;
use alvin0319\GodWar\job\Job;
use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\CommandEnum;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use function array_rand;
use function in_array;
use function is_string;
use function str_replace;
use function substr;
use function trim;

class EventListener implements Listener{

	public function onPlayerJoin(PlayerJoinEvent $event) : void{
		GodWar::getInstance()->restoreInventory($event->getPlayer());
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void{
		if(($room = GodWar::getInstance()->getRoomForPlayer($event->getPlayer())) instanceof Room){
			$room->removePlayer($event->getPlayer());
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) : void{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if(($room = GodWar::getInstance()->getRoomForPlayer($player)) instanceof Room){
			$team = $room->getTeamFor($player);
			if($block->getId() === BlockIds::DIAMOND_BLOCK){
				if($player->getInventory()->getItemInHand()->getId() === 0){
					if(in_array($team, [Room::TEAM_RED, Room::TEAM_BLUE])){
						switch($team){
							case Room::TEAM_RED:
								$redSpawn = $room->getRedSpawn();
								if($redSpawn->distance($block) <= 30){
									$event->setCancelled();
									$player->sendMessage(GodWar::$prefix . "Why are you trying to break your team's core :(");
								}
								break;
							case Room::TEAM_BLUE:
								$blueSpawn = $room->getBlueSpawn();
								if($blueSpawn->distance($block) <= 30){
									$event->setCancelled();
									$player->sendMessage(GodWar::$prefix . "Why are you trying to break your team's core :(");
								}
								break;
						}
						if(!$event->isCancelled()){
							$room->end($team);
						}
					}
				}else{
					$player->sendMessage(GodWar::$prefix . "The core block can be broken with your hands.");
				}
			}
		}
	}

	public function onDataPacketSend(DataPacketSendEvent $event) : void{
		$player = $event->getPlayer();
		$packet = $event->getPacket();
		if($packet instanceof ContainerOpenPacket){
			$room = GodWar::getInstance()->getRoomForPlayer($player);
			if($room instanceof Room){
				if(!$room->isRunning()){
					$event->setCancelled();
					$player->sendMessage(GodWar::$prefix . "The inventory cannot be opened while waiting GodWar.");
				}
			}
		}
		if($packet instanceof AvailableCommandsPacket){
			if(isset($packet->commandData["god"]) && $player->hasPermission("godwar.command")){
				$data = $packet->commandData["god"];

				$parameter = new CommandParameter();
				$parameter->paramName = "args";
				$parameter->paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_STRING;
				$parameter->isOptional = true;
				$parameter->enum = new CommandEnum();
				$parameter->enum->enumName = "value";
				$parameter->enum->enumValues = [
					"trident",
					"fireball",
					"setred",
					"setblue",
					"stopall"
				];
				$data->overloads = [[$parameter]];
				$packet->commandData["god"] = $data;
			}
		}
	}

	public function onPlayerChat(PlayerChatEvent $event) : void{
		$player = $event->getPlayer();
		$room = GodWar::getInstance()->getRoomForPlayer($player);
		if($room instanceof Room){
			if($room->isRunning()){
				if(trim(substr($event->getMessage(), 0, 1)) === "!"){
					if($event->getMessage() === "!help"){
						$player->sendMessage(GodWar::$prefix . "!help - Help of the GodWar.");
						$player->sendMessage(GodWar::$prefix . "!job - Info of your job.");
						$player->sendMessage(GodWar::$prefix . "!betting - Do a betting.");
						$player->sendMessage(GodWar::$prefix . "Team chat is possible with \"!(message)\".");
					}elseif($event->getMessage() === "!job"){
						$job = $room->getJob($player);
						$player->sendMessage(GodWar::$prefix . "You are " . $job->getName() . ".");
						$player->sendMessage("§7" . $job->getDescription());
					}elseif($event->getMessage() === "!betting"){
						if($player->getInventory()->contains($item = ItemFactory::get(ItemIds::COBBLESTONE, 0, 32))){
							$randItem = $this->getRandomItem();
							$player->getInventory()->removeItem($item);
							$player->getInventory()->addItem($randItem);
							$player->sendMessage(GodWar::$prefix . "You got {$randItem->getName()}(x{$randItem->getCount()})");
						}else{
							$player->sendMessage(GodWar::$prefix . "You don't have enough cobblestones to gamble. (32 required)");
						}
					}elseif($event->getMessage() === "!chjob"){
						$room->setJob($player, $room->chooseJobFor($player));
						$player->sendMessage("Job: " . $room->getJob($player)->getName());
					}else{
						foreach($room->getTeamPlayers($player) as $name){
							if(($m = $room->getServer()->getPlayerExact($name)) instanceof Player){
								$m->sendMessage("§b[{$room->getTeamFor($player)}] §7{$player->getName()} > " . str_replace("!", "", $event->getMessage()));
							}
						}
					}
					$event->setCancelled();
				}
			}
		}
	}

	public function getRandomItem() : Item{
		$skill1 = ItemFactory::get(ItemIds::BLAZE_ROD, 0, 1)->setCustomName("Skill 1");
		$skill1->setNamedTagEntry(new StringTag(Job::SKILL1_NAME));
		$skill2 = ItemFactory::get(ItemIds::BLAZE_ROD, 0, 1)->setCustomName("Skill 2");
		$skill2->setNamedTagEntry(new StringTag(Job::SKILL2_NAME));
		$items = [
			ItemFactory::get(ItemIds::IRON_INGOT, 0, 3),
			ItemFactory::get(ItemIds::COBBLESTONE, 0, 16),
			ItemFactory::get(ItemIds::DIAMOND, 0, 3),
			ItemFactory::get(ItemIds::BONE, 0, 1),
			$skill1,
			$skill2
		];
		return $items[array_rand($items)];
	}

	public function onGameStart(GameStartEvent $event) : void{
		$items = [
			ItemFactory::get(ItemIds::BUCKET, 8, 1), // Water bucket
			ItemFactory::get(ItemIds::BUCKET, 10, 1), // Lava Bucket
			ItemFactory::get(ItemIds::WOOD, 0, 4)
		];
		foreach($event->getRoom()->getRedTeam() as $name => $job){
			GodWar::getInstance()->saveInventory($job->getPlayer());
			$job->getPlayer()->sendMessage(GodWar::$prefix . "You are a red team.");
			$job->getPlayer()->sendMessage(GodWar::$prefix . "You are " . $job->getName() . ".");
			$job->getPlayer()->sendMessage(GodWar::$prefix . "If you need help, type !help.");
			$job->getPlayer()->setNameTag("§a[Red] §7" . $job->getPlayer()->getName());
			$job->getPlayer()->getInventory()->addItem(...$items);
		}
		foreach($event->getRoom()->getBlueTeam() as $name => $job){
			GodWar::getInstance()->saveInventory($job->getPlayer());
			$job->getPlayer()->sendMessage(GodWar::$prefix . "You are a blue team.");
			$job->getPlayer()->sendMessage(GodWar::$prefix . "You are " . $job->getName() . ".");
			$job->getPlayer()->sendMessage(GodWar::$prefix . "If you need help, type !help.");
			$job->getPlayer()->setNameTag("§b[Blue] §7" . $job->getPlayer()->getName());
			$job->getPlayer()->getInventory()->addItem(...$items);
		}
	}

	public function onGameEnd(GameEndEvent $event) : void{
		foreach($event->getResult()->getRoom()->getRedTeam() as $name => $job){
			GodWar::getInstance()->restoreInventory($job->getPlayer());
			$job->getPlayer()->teleport($event->getResult()->getRoom()->getServer()->getDefaultLevel()->getSafeSpawn());
		}
		foreach($event->getResult()->getRoom()->getBlueTeam() as $name => $job){
			GodWar::getInstance()->restoreInventory($job->getPlayer());
			$job->getPlayer()->teleport($event->getResult()->getRoom()->getServer()->getDefaultLevel()->getSafeSpawn());
		}
		GodWar::getInstance()->getServer()->broadcastMessage("§a===============");
		GodWar::getInstance()->getServer()->broadcastMessage("The game in room {$event->getResult()->getRoom()->getId()} is over!");
		GodWar::getInstance()->getServer()->broadcastMessage("Winner: " . (is_string($winner = $event->getResult()->getWinner()) ? $winner : "draw"));
		GodWar::getInstance()->getServer()->broadcastMessage("§a===============");
	}

	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		$player = $event->getPlayer();
		if(($room = GodWar::getInstance()->getRoomForPlayer($player)) instanceof Room){
			if($room->isRunning()){
				$job = $room->getJob($player);
				if(is_string($message = $job->useSkillOn($event->getItem()))){
					$room->broadcastMessage("{$player->getName()} used [ §a{$message} §7] skill!");
				}
			}
		}
	}

	public function onPlayerRespawn(PlayerRespawnEvent $event) : void{
		$player = $event->getPlayer();
		if(($room = GodWar::getInstance()->getRoomForPlayer($player)) instanceof Room){
			if($room->isRunning()){
				GodWar::getInstance()->getScheduler()->scheduleTask(new ClosureTask(function(int $unused) use ($player, $room) : void{
					switch($room->getTeamFor($player)){
						case Room::TEAM_RED:
							$player->teleport($room->getRedSpawn());
							break;
						case Room::TEAM_BLUE:
							$player->teleport($room->getBlueSpawn());
							break;
					}
				}));
			}
		}
	}

	public function onPlayerKick(PlayerKickEvent $event) : void{
		$player = $event->getPlayer();
		if(($room = GodWar::getInstance()->getRoomForPlayer($player)) instanceof Room){
			if($room->isRunning()){
				if($event->getReason() === $room->getServer()->getLanguage()->translateString("kick.reason.cheat", ["%ability.flight"])){
					$event->setCancelled();
				}
			}
		}
	}
}