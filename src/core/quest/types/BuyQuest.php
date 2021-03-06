<?php

declare(strict_types = 1);

namespace core\quest\types;

use core\Urbis;
use core\CorePlayer;
use core\price\event\ItemBuyEvent;
use core\quest\Quest;
use core\translation\Translation;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

class BuyQuest extends Quest {

    /** @var Item */
    private $item = null;

    /**
     * SellQuest constructor.
     *
     * @param string $name
     * @param string $description
     * @param int $targetValue
     * @param int $difficulty
     * @param Item|null $item
     */
    public function __construct(string $name, string $description, int $targetValue, int $difficulty, ?Item $item = null) {
        $this->item = $item;
        $callable = function(ItemBuyEvent $event) {
            $player = $event->getPlayer();
            if(!$player instanceof CorePlayer) {
                return;
            }
            if($this->item !== null) {
                $item = $event->getItem();
                if(!$item->equals($this->item)) {
                    return;
                }
            }
            $session = Urbis::getInstance()->getQuestManager()->getSession($player);
            if($session->getQuestProgress($this) === -1) {
                return;
            }
            $session->updateQuestProgress($this, $event->getSpent());
            if($session->getQuestProgress($this) >= $this->targetValue) {
                $player->addQuestPoints($this->getDifficulty());
                $player->sendMessage(Translation::getMessage("questComplete", [
                    "name" => TextFormat::YELLOW . $this->name,
                    "amount" => TextFormat::LIGHT_PURPLE . $this->difficulty
                ]));
            }
        };
        parent::__construct($name, $description, self::BUY, $targetValue, $difficulty, $callable);
    }
}