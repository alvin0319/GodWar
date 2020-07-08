<?php
declare(strict_types=1);
namespace alvin0319\GodWar\command;

use alvin0319\GodWar\GodWar;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;

class GodWarCommand extends PluginCommand{

	public function __construct(){
		parent::__construct("god", GodWar::getInstance());
		$this->setDescription("GodWar command");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if($sender instanceof Player){

		}
	}
}