<?php

namespace MultiWorld\Event;

use MultiWorld\MultiWorld;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\Player;

class EventListener implements Listener {

    /** @var  MultiWorld */
    public $plugin;

    public function __construct(MultiWorld $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @param EntityLevelChangeEvent $event
     */
    public function onLevelChange(EntityLevelChangeEvent $event) {
        $entity = $event->getEntity();
        if($entity instanceof Player) {
            // ... For GAMEMODE PER WORLD SUPPORT
        }
    }
}
