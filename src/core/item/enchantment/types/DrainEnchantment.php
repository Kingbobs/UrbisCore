<?php

declare(strict_types = 1);

namespace core\item\enchantment\types;

use core\item\enchantment\Enchantment;
use core\CorePlayer;
use core\translation\Translation;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;

class DrainEnchantment extends Enchantment {

    /**
     * DrainEnchantment constructor.
     */
    public function __construct() {
        parent::__construct(self::DRAIN, "Drain", self::RARITY_RARE, "Have a chance to steal health from your opponent.", self::DAMAGE, self::SLOT_SWORD, 10);
        $this->callable = function(EntityDamageByEntityEvent $event, int $level) {
            $damager = $event->getDamager();
            $maxHealth = $damager->getMaxHealth();
            if(!$damager instanceof CorePlayer) {
                return;
            }
            if($damager->getHealth() === $maxHealth) {
                return;
            }
            $random = mt_rand(1, 60);
            $chance = $level * 3;
            if($chance >= $random) {
                $amount = $damager->getHealth() + $event->getFinalDamage();
                if($amount > $maxHealth) {
                    $damager->setHealth($maxHealth);
                    return;
                }
                $damager->setHealth($amount);
                $entity = $event->getEntity();
                if(!$entity instanceof Player) {
                    $entity->sendMessage("§§6»»§r §7You are now drained!");
                }
                $damager->sendMessage("§6»»§r §7Your opponent has been drained.");
                return;
            }
        };
    }
}