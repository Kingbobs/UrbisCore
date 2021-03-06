<?php

declare(strict_types = 1);

namespace core\command\types;

use core\command\utils\Command;
use core\Urbis;
use core\translation\Translation;
use core\translation\TranslationException;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class GiveNoteCommand extends Command {

    /**
     * GiveNoteCommand constructor.
     */
    public function __construct() {
        parent::__construct("givetag", "Give tag note command.");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslationException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!$sender->isOp()){
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if(count($args) < 1){
            $sender->sendMessage("§l§6»§r §7Usage: /givetag (player) (tag)§r");
            return;
        }
        if(!isset($args[1])){
            $sender->sendMessage("§l§a»§r §7Usage: /givetag (player) (tag)§r");
            return;
        }
        if(!$p = Server::getInstance()->getPlayer($args[0])){
            $sender->sendMessage("§l§6»§r §7This player cannot be found.§r");
            return;
        }
        $tag = Urbis::getInstance()->getTagManager();
        if(!isset($tag->tags[$args[1]])){
            $sender->sendMessage("§l§c»§r §7This tag does not exist.§r");
            return;
        }
        $item = $tag->getTagNote(strval($args[1]));
        $p->getInventory()->addItem($item);
        $sender->sendMessage("§l§a»§r §7You've successfully given the tag, §a" . $args[1] . "§7.§r");
        $sender->sendMessage("§l§a»§r §7You've been given the tag, §a" . $args[1] . "§7.§r");
        return;
    }
}