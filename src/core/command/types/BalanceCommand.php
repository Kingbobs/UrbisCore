<?php

declare(strict_types = 1);

namespace core\command\types;

use core\command\utils\Command;
use core\CorePlayer;
use core\translation\Translation;
use core\translation\TranslationException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class BalanceCommand extends Command {

    /**
     * BalanceCommand constructor.
     */
    public function __construct() {
        parent::__construct("balance", "Show your or another player's balance.", "/balance [player]", ["bal", "mymoney", "seemoney"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslationException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $name = "Your";
        $balance = $sender->getBalance();
        if(isset($args[0])) {
            $player = $this->getCore()->getServer()->getPlayer($args[0]);
            if(!$player instanceof CorePlayer) {
                $stmt = $this->getCore()->getMySQLProvider()->getDatabase()->prepare("SELECT balance FROM players WHERE username = ?");
                $stmt->bind_param("s", $args[0]);
                $stmt->execute();
                $stmt->bind_result($balance);
                $stmt->fetch();
                $stmt->close();
                $name = "$args[0]'s";
            }
        }
        $sender->sendMessage(Translation::getMessage("balance", [
            "name" => $name,
            "amount" => TextFormat::GREEN . "$$balance"
        ]));
    }
}