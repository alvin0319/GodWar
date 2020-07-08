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

use alvin0319\GodWar\entity\Fireball;
use alvin0319\GodWar\entity\TridentEntity;
use alvin0319\GodWar\task\GameTickTask;
use InvalidStateException;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use ZipArchive;
use function array_diff;
use function array_values;
use function explode;
use function file_exists;
use function intval;
use function is_dir;
use function is_file;
use function is_null;
use function rmdir;
use function scandir;
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

	public function onLoad() : void{
		if(self::$instance instanceof GodWar){
			throw new InvalidStateException("");
		}
		self::$instance = $this;
	}

	public static function getInstance() : GodWar{
		return self::$instance;
	}

	public function onEnable() : void{
		$this->saveDefaultConfig();

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

		if(($this->getConfig()->getNested("red-spawn", "0:0:0:world") === "0:0:0:world") or ($this->getConfig()->getNested("blue-spawn", "0:0:0:world"))){
			$this->getLogger()->critical("You need to set up red-spawn and blue-spawn in config.yml.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}

		[$redX, $redY, $redZ, $worldName] = explode(":", $this->getConfig()->getNested("red-spawn"));
		[$blueX, $blueY, $blueZ, $_] = explode(":", $this->getConfig()->getNested("blue-spawn"));

		$world = $this->getServer()->getLevelByName($worldName);

		for($i = 0; $i < intval($this->getConfig()->getNested("room", 2)); $i++){
			$this->rooms[$i] = new Room($i, $this->getConfig()->getNested("time", 2000), new Position(intval($redX), intval($redY), intval($redZ), $world), new Position(intval($blueX), intval($blueY), intval($blueZ), $world), $worldName);
		}

		$this->invConfig = new Config($this->getDataFolder() . "Inventories.yml", Config::YAML);
		$this->db = $this->invConfig->getAll();

		Entity::registerEntity(TridentEntity::class, true, ["GodWarTrident"]);
		Entity::registerEntity(Fireball::class, true, ["GodWarFireball"]);

		$this->getScheduler()->scheduleRepeatingTask(new GameTickTask(), 20);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
	}

	public function loadMap(int $mapId) : bool{
		$zip = new ZipArchive();
		if($zip->open($this->getDataFolder() . $this->getConfig()->getNested("world-zip")) === true){
			$zip->extractTo("godwar_{$mapId}");
			return $zip->close();
		}
		return false;
	}

	public function recursiveRmdirWorld(string $dir) : void{
		if(($world = $this->getServer()->getLevelByName($dir)) instanceof Level){
			$this->getServer()->unloadLevel($world);
		}
		if(file_exists($path = $this->getServer()->getDataPath() . "worlds/" . $dir)){
			$scanned = array_diff(scandir($this->getDataFolder()), [".", ".."]);
			foreach($scanned as $file){
				$realPath = $path . $file;
				if(is_file($realPath)){
					unlink($realPath);
				}elseif(is_dir($realPath)){
					$this->recursiveRmdirWorld($realPath);
					rmdir($realPath);
				}
			}
			rmdir($path);
		}
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
		$player->getInventory()->clearAll();
		$player->getArmorInventory()->clearAll();

		foreach($this->db[$player->getName()]["inv"] as $slot => $itemData){
			$player->getInventory()->setItem($slot, Item::jsonDeserialize($itemData));
		}
		foreach($this->db[$player->getName()]["armorinv"] as $slot => $itemData){
			$player->getArmorInventory()->setItem($slot, Item::jsonDeserialize($itemData));
		}
		unset($this->db[$player->getName()]);
	}
}