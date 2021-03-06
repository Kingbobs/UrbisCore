<?php

declare(strict_types = 1);

namespace core\command\types;

use core\command\utils\Command;
use core\CorePlayer;
use core\translation\Translation;
use core\translation\TranslationException;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

class GiveKeysCommand extends Command {

    /**
     * GiveKeysCommand constructor.
     */
    public function __construct() {
        parent::__construct("givekeys", "Give crate keys to a player.", "/givekeys <player> <crate> [amount = 1]");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslationException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if($sender instanceof ConsoleCommandSender or $sender->isOp()) {
            if(!isset($args[2])) {
                $sender->sendMessage(Translation::getMessage("usageMessage", [
                    "usage" => $this->getUsage()
                ]));
                return;
            }
            $player = $this->getCore()->getServer()->getPlayer($args[0]);
            if(!$player instanceof CorePlayer) {
                $sender->sendMessage(Translation::getMessage("invalidPlayer"));
                return;
            }
            $crate = $this->getCore()->getCrateManager()->getCrate($args[1]);
            if($crate === null) {
                $sender->sendMessage(Translation::getMessage("invalidCrate"));
                return;
            }
            $amount = max(1, is_numeric($args[2]) ? (int)$args[2] : 1);
            $player->addKeys($crate, $amount);
            if ($player === $sender) {
                $sender->sendMessage("§l§a»§r §7You received a $args[1] key");
            } else {
                $sender->sendMessage("§l§a»§r §7You gave ".$player->getName()." a $args[1] key");
                $player->sendMessage("§l§6»§r §7You received a $args[1] key");
            }
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
        return;
    }
}