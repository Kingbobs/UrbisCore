<?php

declare(strict_types = 1);

namespace core\kit\types;

use core\item\CustomItem;
use core\kit\Kit;
use core\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

class MinerGKit extends Kit {

    /**
     * Miner constructor.
     */
    public function __construct() {
        $name = "§r§l§aMiner§r ";
        $items =  [
            (new CustomItem(Item::DIAMOND_HELMET, $name . "§r§7Helmet§r", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 10),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5)
            ]))->getItemForm(),
            (new CustomItem(Item::DIAMOND_CHESTPLATE, $name . "§r§7Chestplate§r", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 10),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5)
            ]))->getItemForm(),
            (new CustomItem(Item::DIAMOND_LEGGINGS, $name . "§r§7§r§7Leggings§r", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 10),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5)
            ]))->getItemForm(),
            (new CustomItem(Item::DIAMOND_BOOTS, $name . "§r§7Boots§r", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 10),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5)
            ]))->getItemForm(),
            (new CustomItem(Item::DIAMOND_SWORD, $name . "§r§7Sword§r", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::SHARPNESS), 11),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5),
            ]))->getItemForm(),
            (new CustomItem(Item::DIAMOND_SHOVEL, $name . "§r§7Shovel§r", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EFFICIENCY), 11),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5)
            ]))->getItemForm(),
            (new CustomItem(Item::DIAMOND_PICKAXE, $name . "§r§7Pickaxe§r", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EFFICIENCY), 22),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5),
            ]))->getItemForm(),
            (new CustomItem(Item::DIAMOND_AXE, $name . "§r§7Axe§r", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::SHARPNESS), 12),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EFFICIENCY), 22),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5)
            ]))->getItemForm(),
            Item::get(Item::STEAK, 0, 64),
            Item::get(Item::TORCH, 0, 64),
            Item::get(Item::OBSIDIAN, 0, 128),
        ];
        parent::__construct("Miner", self::RARE, $items, 43200);
    }
}