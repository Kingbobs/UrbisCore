<?php

declare(strict_types = 1);

namespace core\command\types;

use core\command\forms\WithdrawForm;
use core\command\utils\Command;
use core\CorePlayer;
use core\translation\Translation;
use core\translation\TranslationException;
use pocketmine\command\CommandSender;

class WithdrawCommand extends Command {

    /**
     * WithdrawCommand constructor.
     */
    public function __construct() {
        parent::__construct("withdraw", "Withdraw xp, money, or keys.", "/withdraw");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslationException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if($sender instanceof CorePlayer) {
            $sender->sendForm(new WithdrawForm($sender));
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
    }
}