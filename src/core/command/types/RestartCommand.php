<?php

declare(strict_types=1);

namespace core\command\types;

use core\command\utils\Command;
use core\CorePlayer;
use core\rank\Rank;
use core\translation\Translation;
use core\translation\TranslationException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class RestartCommand extends Command{

	/**
	 * StaffChatCommand constructor.
	 */
	public function __construct(){
		parent::__construct("restart", "Manage restart", "/restart <queue | reset>", ["restart"]);
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $commandLabel
	 * @param array         $args
	 *
	 * @throws TranslationException
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$sender->isOp()){
			$sender->sendMessage(Translation::getMessage("noPermission"));
			return;
		}
		if(!isset($args[0])){
			$sender->sendMessage("§l§6»§r §7Usage: /restart (queue/reset)");
			return;
		}
		switch($args[0]){
			case "queue":
			    $time = 30;
			    if(isset($args[1]) and is_numeric($args[1])){
			        $time = intval($args[1]);
                }
				$this->getCore()->getAnnouncementManager()->getRestarter()->setRestartProgress($time);
				$sender->sendMessage("§l§a»§r §7You have queued a restart for the server in $time seconds...");
				foreach($this->getCore()->getServer()->getOnlinePlayers() as $player){
					if($player instanceof CorePlayer){
						if(in_array($player->getRank()->getIdentifier(), [Rank::SENIOR_ADMIN, Rank::MANAGER, Rank::DEVELOPER, Rank::OWNER])){
							$player->sendMessage("§l§a»§r §e" . $sender->getName() . " §7has queued the server for a restart in $time seconds...");
						}
						$player->sendMessage("§l§6»§r §7The server will restart in $time seconds...");
						$player->teleport($player->getServer()->getDefaultLevel()->getSpawnLocation());
					}
				}
				break;
			case "reset":
				$this->getCore()->getAnnouncementManager()->getRestarter()->setRestartProgress(5400);
				$sender->sendMessage("§l§a»§r §7You have reset the restart timer...");
				break;
		}
	}
}
