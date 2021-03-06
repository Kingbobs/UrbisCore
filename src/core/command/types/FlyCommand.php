<?php

namespace core\command\types;

use core\command\utils\Command;
use core\faction\Faction;
use core\CorePlayer;
use core\rank\Rank;
use core\translation\Translation;
use core\translation\TranslationException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class FlyCommand extends Command {

    /**
     * FlyCommand constructor.
     */
    public function __construct() {
        parent::__construct("fly", "Toggle flight mode.");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslationException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if((!$sender instanceof CorePlayer) or ((!$sender->isOp()) and (!$sender->hasPermission("permission.tier2")))) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if($sender->getAllowFlight() === true) {
            $sender->setAllowFlight(false);
            $sender->setFlying(false);
        }
        else {
            $sender->setAllowFlight(true);
            $sender->setFlying(true);
        }
        $sender->sendMessage(Translation::getMessage("flightToggle"));
    }
}