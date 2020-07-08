<?php
declare(strict_types=1);
namespace alvin0319\GodWar;

use alvin0319\GodWar\event\GameEndEvent;
use alvin0319\GodWar\job\Ares;
use alvin0319\GodWar\job\Helios;
use alvin0319\GodWar\job\Job;
use alvin0319\GodWar\job\Zeus;
use alvin0319\GodWar\result\GameResult;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use function array_keys;
use function array_rand;
use function count;
use function shuffle;

class Room{

	public const TEAM_RED = "§cred";
	public const TEAM_BLUE = "§bblue";

	/** @var int */
	protected $id;

	/** @var string[] */
	protected $players = [];

	/** @var Position */
	protected $redSpawn;

	/** @var Position */
	protected $blueSpawn;

	/** @var Job[] */
	protected $redTeam = [];

	/** @var Job[] */
	protected $blueTeam = [];

	/** @var bool */
	protected $running = false;

	/** @var int */
	protected $timeLeft;

	/** @var int */
	protected $progress;

	/** @var string */
	protected $worldName;

	protected $waitTime = 60;

	/** @var Server */
	private $server;

	public function __construct(int $id, int $timeLeft, Position $redSpawn, Position $blueSpawn, string $worldName){
		$this->id = $id;
		$this->timeLeft = $timeLeft;
		$this->redSpawn = $redSpawn;
		$this->blueSpawn = $blueSpawn;
		$this->worldName = $worldName;
		$this->setUp();
		$this->server = Server::getInstance();
	}

	public function getServer() : Server{
		return $this->server;
	}

	public function getId() : int{
		return $this->id;
	}

	public function setUp() : void{
		$this->progress = $this->timeLeft;
		$this->players = [];
		$this->redTeam = [];
		$this->blueTeam = [];
		$this->running = false;
	}

	public function isRunning() : bool{
		return $this->running && $this->waitTime <= 0;
	}

	public function isPlayer(Player $player) : bool{
		return isset($this->players[$player->getName()]);
	}

	public function getTeamFor(Player $player) : string{
		return $this->players[$player->getName()];
	}

	public function canJoin(Player $player) : bool{
		if($this->isRunning())
			return false;
		if($this->isPlayer($player))
			return false;
		if(count($this->players) >= 8)
			return false;
		return true;
	}

	public function addPlayer(Player $player) : void{
		if($this->canJoin($player)){
			$this->players[$player->getName()] = "none";
		}
	}

	public function removePlayer(Player $player) : void{
		if(isset($this->players[$player->getName()])){
			$redOrBlue = $this->players[$player->getName()];
			switch($redOrBlue){
				case self::TEAM_RED:
					if(isset($this->redTeam[$player->getName()]))
						unset($this->redTeam[$player->getName()]);
					break;
				case self::TEAM_BLUE:
					if(isset($this->blueTeam[$player->getName()]))
						unset($this->blueTeam[$player->getName()]);
					break;
			}
		}
	}

	public function isSameTeam(Player $player, Player $target) : bool{
		return $this->players[$player->getName()] === $this->players[$target->getName()];
	}

	public function chooseJobFor(Player $player) : Job{
		$jobs = [
			new Zeus($player, $this),
			new Ares($player, $this),
			new Helios($player, $this)
		];
		return $jobs[array_rand($jobs)];
	}

	public function hasJobForTeam(Job $job, string $team) : bool{
		switch($team){
			case self::TEAM_RED:
				foreach($this->redTeam as $name => $j){
					if($j->getName() === $job->getName()){
						return true;
					}
				}
				return false;
			case self::TEAM_BLUE:
				foreach($this->blueTeam as $name => $j){
					if($j->getName() === $job->getName()){
						return true;
					}
				}
				return false;
			default:
				return false;
		}
	}

	public function start() : void{
		$this->running = true;
		shuffle($this->players);
		foreach(array_keys($this->players) as $name){
			if(($target = $this->getServer()->getPlayerExact($name)) instanceof Player){
				if(count($this->blueTeam) > count($this->redTeam)){
					$this->players[$name] = self::TEAM_RED;
					$job = $this->chooseJobFor($target);
					$this->redTeam[$target->getName()] = $job;
				}else{
					$this->players[$name] = self::TEAM_BLUE;
					$job = $this->chooseJobFor($target);
					$this->blueTeam[$target->getName()] = $job;
				}
			}
		}
	}

	public function end(?string $winner) : void{
		(new GameEndEvent(new GameResult($this, $winner)))->call();
	}

	public function syncTick() : void{

	}
}