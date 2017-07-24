<?php

namespace MultiWorld\Generator;

use MultiWorld\MultiWorld;
use pocketmine\level\generator\Flat;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\nether\Nether;
use pocketmine\level\generator\normal\Normal;
use pocketmine\level\generator\normal\Normal2;
use pocketmine\level\generator\Void;
use pocketmine\Server;

class BasicGenerator {

    /** @var  MultiWorld */
    public $plugin;

    const NORMAL = 0;
    const FLAT = 1;
    const NETHER = 2;
    const VOID = 3;

    public function __construct(MultiWorld $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @param string $name
     * @param int|string $seed
     * @param string $generatorName
     */
    public function generateLevel(string $name, int $seed, string $generatorName) {
        $seed = intval($seed);
        $generator = $this->getBasicGeneratorByName($generatorName);
        // random seed
        if(!is_dir(MultiWorld::getInstance()->configmgr->getDataPath()."world/{$name}")) {
            Server::getInstance()->generateLevel($name, $seed, $generator);
            Server::getInstance()->broadcastMessage(MultiWorld::getPrefix()."Level {$name} is generated using seed: {$seed} & generator: {$generatorName}.");
        }
    }

    /**
     * @param string $generatorName
     * @return string
     */
    public function getBasicGeneratorByName(string $generatorName):string {
        switch (strtolower($generatorName)) {
            case "normal":
            case "default":
            case "classic":
            case "world":
            case self::NORMAL:
                if(MultiWorld::getInstance()->getServer()->getName()=="PocketMine-MP") {
                    return Normal::class;
                }
                else {
                    return Normal2::class;
                }
                break;
            case "flat":
            case "superflat":
            case self::FLAT:
                return Flat::class;
                break;
            case "nether":
            case "hell":
            case self::NETHER:
                return Nether::class;
                break;
            case self::VOID:
            case "void":
                // will be added
                if(MultiWorld::getInstance()->getServer()->getName()=="PocketMine-MP") {
                    MultiWorld::getInstance()->getLogger()->critical("§4Void generator not found!");
                    return Normal::class;
                }
                else {
                    return Void::class;
                }
        }
    }
}
