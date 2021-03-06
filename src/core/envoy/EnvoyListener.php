<?php

declare(strict_types = 1);

namespace core\envoy;

use core\Urbis;
use core\CorePlayer;
use libs\utils\UtilsException;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

class EnvoyListener implements Listener {

    /** @var Urbis */
    private $core;

    /**
     * EnvoyListener constructor.
     *
     * @param Urbis $core
     */
    public function __construct(Urbis $core) {
        $this->core = $core;
    }

    /**
     * @priority LOWEST
     * @param PlayerInteractEvent $event
     *
     * @throws UtilsException
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        if(!$player instanceof CorePlayer) {
            return;
        }
        $block = $event->getBlock();
        if($block->getId() !== Block::CHEST) {
            return;
        }
        $envoy = $this->core->getEnvoyManager()->getEnvoy($block->asPosition());
        if($envoy === null) {
            return;
        }
        $envoy->claim($player);
        $envoy->despawn();
        $event->setCancelled();
    }

    /**
     * @priority HIGH
     * @param BlockBreakEvent $event
     */
    public function onBlockBreak(BlockBreakEvent $event): void {
        if($event->isCancelled()) {
            return;
        }
        $block = $event->getBlock();
        if($block->getId() !== Block::CHEST) {
            return;
        }
        $envoy = $this->core->getEnvoyManager()->getEnvoy($block->asPosition());
        if($envoy === null) {
            return;
        }
        $event->setCancelled();
    }
}