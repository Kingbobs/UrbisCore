<?php

namespace core\combat\boss\types;

use core\combat\boss\Boss;
use core\item\ItemManager;
use core\item\types\EnchantmentBook;
use core\item\types\EnchantmentRemover;
use core\item\types\HolyBox;
use core\item\types\LuckyBlock;
use core\item\types\MoneyNote;
use core\item\types\SacredStone;
use core\item\types\SellWand;
use core\item\types\XPNote;
use core\Urbis;
use core\utils\Utils;
use pocketmine\item\GoldenAppleEnchanted;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Alien extends Boss {

    const BOSS_ID = 2;

    /**
     * Alien constructor.
     *
     * @param Level $level
     * @param CompoundTag $nbt
     */
    public function __construct(Level $level, CompoundTag $nbt) {
        $path = Urbis::getInstance()->getDataFolder() . "skins" . DIRECTORY_SEPARATOR . "alien.png";
        $this->setSkin(Utils::createSkin(Utils::getSkinDataFromPNG($path)));
        parent::__construct($level, $nbt);
        $this->setMaxHealth(3000);
        $this->setHealth(3000);
        $this->setNametag(TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Aliran " . TextFormat::RESET . TextFormat::RED . $this->getHealth() . TextFormat::RESET . "/" . TextFormat::RED . $this->getMaxHealth() . TextFormat::RESET);
        $this->setScale(2);
        $this->attackDamage = 120;
        $this->speed = 1;
        $this->attackWait = 5;
        $this->regenerationRate = 20;
    }

    /**
     * @param int $tickDiff
     *
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool {
        $this->setNametag(TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Aliran " . TextFormat::RESET . TextFormat::RED . $this->getHealth() . TextFormat::RESET . "/" . TextFormat::RED . $this->getMaxHealth() . TextFormat::RESET);
        return parent::entityBaseTick($tickDiff);
    }

    public function onDeath(): void {
        $kits = Urbis::getInstance()->getKitManager()->getSacredKits();
        $kit = $kits[array_rand($kits)];
        $rewards = [
            (new EnchantmentBook(ItemManager::getRandomEnchantment()))->getItemForm(),
            (new HolyBox($kit))->getItemForm(),
            (new MoneyNote(mt_rand(50000, 5000000)))->getItemForm(),
            (new XPNote(mt_rand(25000, 100000)))->getItemForm(),
            (new EnchantmentRemover(100))->getItemForm(),
            (new SellWand(100))->getItemForm()
        ];
        $drops = [];
        for($i = 0; $i <= 3; ++$i) {
            $drops[] = $rewards[array_rand($rewards)];
        }
        $d = null;
        $p = null;
        foreach($this->damages as $player => $damage) {
            if(Server::getInstance()->getPlayer($player) === null) {
                continue;
            }
            $online = Server::getInstance()->getPlayer($player);
            if($damage > $d) {
                $d = $damage;
                $p = $online;
            }
        }
        if($p === null) {
            return;
        }
        Server::getInstance()->broadcastMessage($p->getDisplayName() . TextFormat::GRAY . " has dealt the most damage " . TextFormat::DARK_GRAY . "(" . TextFormat::WHITE . $d . TextFormat::RED . TextFormat::BOLD . " DMG" . TextFormat::RESET . TextFormat::DARK_GRAY . ")" . TextFormat::GRAY . " to " . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Aliran " . TextFormat::RESET . TextFormat::GRAY . "and received:");
        foreach($drops as $item) {
            $name = TextFormat::RESET . TextFormat::WHITE . $item->getName();
            if($item->hasCustomName()) {
                $name = $item->getCustomName();
            }
            Server::getInstance()->broadcastMessage($name . TextFormat::RESET . TextFormat::GRAY . " * " . TextFormat::WHITE . $item->getCount());
            if($p->getInventory()->canAddItem($item)) {
                $p->getInventory()->addItem($item);
                continue;
            }
            $p->getLevel()->dropItem($p, $item);
        }
        foreach($this->damages as $player => $damage) {
            if($player === $p->getName()) {
                continue;
            }
            if(Server::getInstance()->getPlayer($player) === null) {
                continue;
            }
            $online = Server::getInstance()->getPlayer($player);
            $item = $rewards[array_rand($rewards)];
            $name = TextFormat::RESET . TextFormat::WHITE . $item->getName();
            if($item->hasCustomName()) {
                $name = $item->getCustomName();
            }
            $online->sendMessage(TextFormat::GRAY . "You dealt " . TextFormat::WHITE . $damage . TextFormat::RED . TextFormat::BOLD . " DMG" . TextFormat::GRAY . " to " . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Aliran " . TextFormat::RESET . TextFormat::GRAY . "and received:");
            $online->sendMessage($name . TextFormat::RESET . TextFormat::GRAY . " * " . TextFormat::WHITE . $item->getCount());
            if($online->getInventory()->canAddItem($item)) {
                $online->getInventory()->addItem($item);
                continue;
            }
            $online->getLevel()->dropItem($p, $item);
        }
    }
}
