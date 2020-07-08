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
use alvin0319\GodWar\job\Ares;
use alvin0319\GodWar\job\Helios;
use alvin0319\GodWar\job\Hestia;
use alvin0319\GodWar\job\Hypnos;
use alvin0319\GodWar\job\Job;
use alvin0319\GodWar\job\Poseidon;
use alvin0319\GodWar\job\Zeus;
use alvin0319\GodWar\result\GameResult;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use function array_filter;
use function array_keys;
use function array_map;
use function array_rand;
use function array_values;
use function count;
use function shuffle;
use function time;

class Room{

	public const TEAM_RED = "red";
	public const TEAM_BLUE = "blue";

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

	protected $skillBlocked = false;

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

	public function getRedSpawn() : Position{
		return $this->redSpawn;
	}

	public function getBlueSpawn() : Position{
		return $this->blueSpawn;
	}

	public function setUp() : void{
		$this->progress = $this->timeLeft;
		$this->players = [];
		$this->redTeam = [];
		$this->blueTeam = [];
		$this->running = false;

		GodWar::getInstance()->recursiveRmdirWorld($this->worldName);
		GodWar::getInstance()->loadMap($this->id);
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
		if(count($this->players) >= 6)
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
			new Helios($player, $this),
			new Poseidon($player, $this),
			new Hypnos($player, $this),
			new Hestia($player, $this)
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
		(new GameStartEvent($this))->call();
	}

	public function end(?string $winner) : void{
		(new GameEndEvent(new GameResult($this, $winner)))->call();
		$this->setUp();
	}

	public function getTeam(string $team) : array{
		switch($team){
			case self::TEAM_RED:
				return $this->redTeam;
			case self::TEAM_BLUE:
				return $this->blueTeam;
			default:
				return [];
		}
	}

	public function syncTick() : void{
		$this->sendProgressBar();
		if(!$this->isRunning()){
			if(count($this->players) === 6){
				--$this->waitTime;
			}
		}else{
			--$this->progress;


			if($this->progress <= 0){
				$this->end(null);
			}
		}
	}

	public function sendProgressBar() : void{
		foreach(array_keys($this->players) as $name){
			if(($player = $this->getServer()->getPlayerExact($name)) instanceof Player){
				$text = "§a-=-=-=-=-= [ §fGodWar §a] =-=-=-=-=-\n";
				if($this->isRunning()){
					$job = $this->getJob($player);
					$text .= "Job: §a" . $job->getName() . "§r\n";
					$text .= "Time left: " . $this->convertTimeToString($this->progress);
					$cools = [];
					if(count($job->getCoolTimes()) > 0){
						foreach($job->getCoolTimes() as $name => $data){
							if($job->hasCool($name)){
								$cools[] = $name . ": " . $this->convertTimeToString($data["cool"] - (time() - $data["time"]));
							}
						}
					}
					if(count($cools) > 0){
						$text .= "§a---- §fCooldown list §a----§f\n";
						foreach($cools as $cool){
							$text .= $cool . "\n";
						}
					}
					$player->sendPopup($text);
				}else{
					$text .= "Waiting for more players...\n";
					$text .= "Time left: " . $this->convertTimeToString($this->waitTime);
					$player->sendPopup($text);
				}
			}
		}
	}

	public function getTeamPlayers(Player $player) : array{
		switch($this->players[$player->getName()]){
			case self::TEAM_RED:
				return array_values(
					array_filter(
						array_keys($this->redTeam),
						function(string $name) use ($player) : bool{
							return $player->getName() === $name;
						}
					)
				);
			case self::TEAM_BLUE:
				return array_values(
					array_filter(
						array_keys($this->blueTeam),
						function(string $name) use ($player) : bool{
							return $player->getName() === $name;
						}
					)
				);
			default:
				return [];
		}
	}

	public function blockAllSkills() : void{
		$this->skillBlocked = true;
	}

	public function isSkillBlocked() : bool{
		return $this->skillBlocked;
	}

	public function getJob(Player $player) : Job{
		switch($this->players[$player->getName()]){
			case self::TEAM_RED:
				return $this->redTeam[$player->getName()];
			case self::TEAM_BLUE:
				return $this->blueTeam[$player->getName()];
			default:
				return new Ares($player, $this);
		}
	}

	public function convertTimeToString(int $time) : string{
		$h = (int) ($time / 60 / 60);
		$m = ((int) ($time / 60)) - ($h * 60);
		$s = (int) $time - (($h * 60 * 60) + ($m * 60));

		$str = "";

		if($h > 0)
			$str .= "{$h} hours ";
		if($m > 0)
			$str .= "{$m} minutes ";
		$str .= "{$s} seconds";

		return $str;
	}

	/**
	 * @return Job[]
	 */
	public function getBlueTeam() : array{
		return $this->blueTeam;
	}

	/**
	 * @return Job[]
	 */
	public function getRedTeam() : array{
		return $this->redTeam;
	}

	/**
	 * @return Player[]
	 */
	public function getPlayers() : array{
		return array_values(
			array_filter(
				array_map(function(string $name) : ?Player{
					return $this->getServer()->getPlayerExact($name);
				}, array_keys($this->players)),
				function(?Player $player) : bool{
					return $player instanceof Player;
				}
			)
		);
	}

	public function broadcastMessage(string $message) : void{
		$this->getServer()->broadcastMessage(GodWar::$prefix . $message, $this->getPlayers());
	}
}