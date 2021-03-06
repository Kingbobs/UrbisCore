<?php

declare(strict_types = 1);

namespace core\command\types;

use core\command\utils\Command;
use core\Urbis;
use core\translation\Translation;
use core\translation\TranslationException;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class SetTagCommand extends Command {

    /**
     * SetTagCommand constructor.
     */
    public function __construct() {
        parent::__construct("settag", "Set a player tag.");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslationException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!$sender->isOp()) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if(count($args) < 2){
            $sender->sendMessage("§l§6»§r §7Usage: /settag <player> <tag>§r");
            return;
        }
        if(!isset($args[1])){
            $sender->sendMessage("§l§a6§r §7Usage: /settag <player> <tag>§r");
            return;
        }
        if(!$player = Server::getInstance()->getPlayer($args[0])){
            $sender->sendMessage("§l§c»§r §7That player cannot be found.§r");
            return;
        }
        if(Urbis::getInstance()->getTagManager()->setTag($player, $args[1]))
            $sender->sendMessage("§l§a»§r §7You've set the tag of §b" . $args[0] ." §r§7to §a" . $args[1] . "§7.§r");
    }
}