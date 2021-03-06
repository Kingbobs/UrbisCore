<?php

declare(strict_types = 1);

namespace core\item\enchantment\types;

use core\item\enchantment\Enchantment;
use pocketmine\utils\TextFormat;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\player\PlayerMoveEvent;

class GearsEnchantment extends Enchantment {

    /**
     * GearsEnchantment constructor.
     */
    public function __construct() {
        parent::__construct(self::GEARS, "Gears", self::RARITY_MYTHIC, "Obtain speed boost.", self::MOVE, self::SLOT_FEET, 3);
        $this->callable = function(PlayerMoveEvent $event, int $level) {
            $player = $event->getPlayer();
            if((!$player->hasEffect(Effect::SPEED)) or $player->getEffect(Effect::SPEED)->getDuration() <= 20) {
                $player->addEffect(new EffectInstance(Effect::getEffect(Effect::SPEED), 120, $level));
            }
            return;
        };
    }
}
