<?php
declare(strict_types=1);
namespace alvin0319\GodWar;

use InvalidStateException;
use pocketmine\level\Level;
use pocketmine\level\Position;
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
		}

		[$redX, $redY, $redZ, $worldName] = explode(":", $this->getConfig()->getNested("red-spawn"));
		[$blueX, $blueY, $blueZ, $_] = explode(":", $this->getConfig()->getNested("blue-spawn"));

		$world = $this->getServer()->getLevelByName($worldName);

		for($i = 0; $i < intval($this->getConfig()->getNested("room", 2)); $i++){
			$this->rooms[$i] = new Room($i, $this->getConfig()->getNested("time", 2000), new Position(intval($redX), intval($redY), intval($redZ), $world), new Position(intval($blueX), intval($blueY), intval($blueZ), $world), $worldName);
		}

		$this->invConfig = new Config($this->getDataFolder() . "Inventories.yml", Config::YAML);
		$this->db = $this->invConfig->getAll();
	}

	public function loadMap(string $file, int $mapId) : bool{
		$zip = new ZipArchive();
		if($zip->open($this->getDataFolder() . $this->getConfig()->getNested("world-zip")) === true){
			$zip->extractTo("godwar_{$mapId}");
			return $zip->close();
		}
		return false;
	}

	public function recursiveRmdir(string $dir) : void{
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
					$this->recursiveRmdir($realPath);
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
}