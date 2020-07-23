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

use alvin0319\GodWar\command\GodWarCommand;
use alvin0319\GodWar\entity\Fireball;
use alvin0319\GodWar\entity\TridentEntity;
use alvin0319\GodWar\task\GameTickTask;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use ZipArchive;
use function array_diff;
use function array_values;
use function count;
use function explode;
use function file_exists;
use function floatval;
use function implode;
use function intval;
use function is_dir;
use function is_file;
use function is_null;
use function rmdir;
use function scandir;
use function substr;
use function unlink;

class GodWar extends PluginBase{

	public static $prefix = "§b§l[GodWar] §r§7";

	/** @var GodWar|null */
	private static $instance = null;

	/** @var Config */
	protected $invConfig;

	protected $db = [];

	/** @var Room[] */
	protected $rooms = [];

	protected $roomIds = 0;

	public function onLoad() : void{
		self::$instance = $this;
	}

	public static function getInstance() : GodWar{
		return self::$instance;
	}

	public function onEnable() : void{
		$this->saveDefaultConfig();
		$this->saveResource("world.zip");

		if(is_null($this->getConfig()->getNested("world-zip", null))){
			$this->getLogger()->critical("You need to set up config.yml!");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}

		if(!(file_exists($file = $this->getDataFolder() . $this->getConfig()->getNested("world-zip", "")))){
			$this->getLogger()->critical("Failed to find world zip!");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}

		if(($this->getConfig()->getNested("red-spawn", "0:0:0:world") === "0:0:0:world") or ($this->getConfig()->getNested("blue-spawn", "0:0:0:world") === "0:0:0:world")){
			$this->getLogger()->critical("You need to set up red-spawn and blue-spawn in config.yml, or use /god [setred|setblue]");
		}else{
			[$redX, $redY, $redZ, $worldName] = explode(":", $this->getConfig()->getNested("red-spawn"));
			[$blueX, $blueY, $blueZ, $_] = explode(":", $this->getConfig()->getNested("blue-spawn"));

			$minCount = $this->getConfig()->get("min-count", 2);
			$maxCount = $this->getConfig()->get("max-count", 8);

			if($maxCount < $minCount){
				$this->getLogger()->critical("Max count MUST be larger than Min count");
				$this->getServer()->getPluginManager()->disablePlugin($this);
				return;
			}

			for($i = 0; $i < $this->roomIds = intval($this->getConfig()->getNested("room", 2)); $i++){
				$this->loadMap($i);
				$world = $this->getServer()->getLevelByName("godwar_{$i}");
				$this->rooms[$i] = new Room($i, $this->getConfig()->getNested("time", 2000), new Position(floatval($redX), floatval($redY), floatval($redZ), $world), new Position(intval($blueX), intval($blueY), intval($blueZ), $world), "godwar_{$i}", $minCount, $maxCount);
			}
			$this->getScheduler()->scheduleRepeatingTask(new GameTickTask(), 20);
		}


		$this->invConfig = new Config($this->getDataFolder() . "Inventories.yml", Config::YAML);
		$this->db = $this->invConfig->getAll();

		Entity::registerEntity(TridentEntity::class, true, ["GodWarTrident"]);
		Entity::registerEntity(Fireball::class, true, ["GodWarFireball"]);

		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

		$this->getServer()->getCommandMap()->register("godwar", new GodWarCommand());
	}

	public function onDisable() : void{
		foreach($this->getRooms() as $room) {
			if($room->isRunning()){
				$room->end(null);
			}
		}
		$this->invConfig->setAll($this->db);
		$this->invConfig->save();

		$this->getConfig()->save();
	}

	public function loadMap(int $mapId) : bool{
		$zip = new ZipArchive();
		if($zip->open($this->getDataFolder() . $this->getConfig()->getNested("world-zip")) === true){
			$zip->extractTo($this->getServer()->getDataPath() . "worlds/godwar_{$mapId}");
			$this->getServer()->loadLevel("godwar_{$mapId}");
			return $zip->close();
		}
		return false;
	}

	public function setRedSpawn(Position $pos) : void{
		$this->getConfig()->set("red-spawn", implode(":", [$pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $pos->getLevel()->getFolderName()]));
		$this->check();
	}

	public function setBlueSpawn(Position $pos) : void{
		$this->getConfig()->set("blue-spawn", implode(":", [$pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $pos->getLevel()->getFolderName()]));
		$this->check();
	}

	public function recursiveRmdirWorld(string $dir) : void{
		if(is_dir($dir = $this->getServer()->getDataPath() . "worlds/{$dir}")){
			if(substr($dir, -1) !== "/"){
				$dir .= "/";
			}
			$dirs = scandir($dir);
			foreach($dirs as $file){
				if($file !== "." and $file !== ".."){
					$realPath = $dir . $file;

					if(file_exists($realPath)){
						if(is_file($realPath)){
							unlink($realPath);
						}elseif(is_dir($realPath)){
							$ssss = array_diff(scandir($dir . $file), [".", ".."]);

							if(count($ssss) === 0){
								rmdir($realPath);
							}else{
								$this->recursiveRmdirWorld($realPath);
							}
						}
					}
				}
			}
			$ssss = array_diff(scandir($dir), [".", ".."]);

			if(count($ssss) === 0){
				rmdir($dir);
			}else{
				$this->recursiveRmdirWorld($dir);
			}
		}
	}

	public function setUpRoom() : void{
		[$redX, $redY, $redZ, $_] = explode(":", $this->getConfig()->getNested("red-spawn"));
		[$blueX, $blueY, $blueZ, $_] = explode(":", $this->getConfig()->getNested("blue-spawn"));

		$minCount = $this->getConfig()->get("min-count", 2);
		$maxCount = $this->getConfig()->get("max-count", 8);

		if($maxCount < $minCount){
			return;
		}

		for($i = 0; $i < $this->roomIds = intval($this->getConfig()->getNested("room", 2)); $i++){
			$this->loadMap($i);
			$world = $this->getServer()->getLevelByName("godwar_{$i}");
			$this->rooms[$i] = new Room($i, $this->getConfig()->getNested("time", 2000), new Position(floatval($redX), floatval($redY), floatval($redZ), $world), new Position(intval($blueX), intval($blueY), intval($blueZ), $world), "godwar_{$i}", $minCount, $maxCount);
		}
		$this->getScheduler()->scheduleRepeatingTask(new GameTickTask(), 20);
	}

	public function canStart() : bool{
		return ($this->getConfig()->getNested("red-spawn", "0:0:0:world") !== "0:0:0:world") and ($this->getConfig()->getNested("blue-spawn", "0:0:0:world") !== "0:0:0:world") && count($this->rooms) === 0;
	}

	public function check() : void{
		if($this->canStart())
			$this->setUpRoom();
	}

	public function syncGameTick() : void{
		foreach(array_values($this->rooms) as $room){
			$room->syncTick();
		}
	}

	public function getRoomForPlayer(Player $player) : ?Room{
		foreach($this->getRooms() as $room){
			if($room->isPlayer($player))
				return $room;
		}
		return null;
	}

	public function getRoom(int $id) : ?Room{
		return $this->rooms[$id] ?? null;
	}

	/**
	 * @return Room[]
	 */
	public function getRooms() : array{
		return array_values($this->rooms);
	}

	public function saveInventory(Player $player) : void{
		$this->db[$player->getName()] = [
			"inv" => [],
			"armorinv" => []
		];
		foreach($player->getInventory()->getContents(false) as $slot => $item){
			$this->db[$player->getName()]["inv"][$slot] = $item->jsonSerialize();
		}
		foreach($player->getArmorInventory()->getContents(false) as $slot => $item){
			$this->db[$player->getName()]["armorinv"][$slot] = $item->jsonSerialize();
		}
		$player->getInventory()->clearAll();
		$player->getArmorInventory()->clearAll();
	}

	public function restoreInventory(Player $player) : void{
		if(isset($this->db[$player->getName()])){
			$player->getInventory()->clearAll();
			$player->getArmorInventory()->clearAll();

			foreach($this->db[$player->getName()]["inv"] as $slot => $itemData){
				$player->getInventory()->setItem($slot, Item::jsonDeserialize($itemData));
			}
			foreach($this->db[$player->getName()]["armorinv"] as $slot => $itemData){
				$player->getArmorInventory()->setItem($slot, Item::jsonDeserialize($itemData));
			}
			unset($this->db[$player->getName()]);

			$player->teleport(GodWar::getInstance()->getServer()->getDefaultLevel()->getSafeSpawn());
		}
	}

	public function getAvailableRoom(Player $player) : ?Room{
		foreach($this->getRooms() as $room){
			if($room->canJoin($player)){
				return $room;
			}
		}
		return null;
	}
}