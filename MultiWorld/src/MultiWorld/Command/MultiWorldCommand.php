<?php

namespace MultiWorld\Command;

use MultiWorld\MultiWorld;
use MultiWorld\Util\ConfigManager;
use MultiWorld\Util\LanguageManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class MultiWorldCommand {

    /** @var  MultiWorld $plugin */
    public $plugin;

    /**
     * MultiWorldCommand constructor.
     * @param MultiWorld $plugin
     */
    public function __construct(MultiWorld $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @param CommandSender $sender
     * @param Command $cmd
     * @param $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
        if (!($sender instanceof Player)) {
            return false;
        }
        if ($cmd->getName() == "multiworld") {
            if (isset($args[0])) {
                switch (strtolower($args[0])) {
                    case "help":
                    case "?":
                        if (!$sender->hasPermission("mw.cmd.help")) {
                            $sender->sendMessage(LanguageManager::translateMessage("not-perms"));
                            return false;
                        }
                        $sender->sendMessage(LanguageManager::translateMessage("help-0") . "\n" .
                            LanguageManager::translateMessage("help-1") . "\n" .
                            LanguageManager::translateMessage("help-2") . "\n" .
                            LanguageManager::translateMessage("help-3") . "\n" .
                            LanguageManager::translateMessage("help-4") . "\n");
                        return true;
                    case "create":
                    case "add":
                    case "generate":
                        if (!$sender->hasPermission("mw.cmd.create")) {
                            $sender->sendMessage(LanguageManager::translateMessage("not-perms"));
                            return false;
                        }
                        if (empty($args[1])) {
                            $sender->sendMessage(MultiWorld::getPrefix() . LanguageManager::translateMessage("create-usage"));
                            return false;
                        }
                        $this->plugin->bgenerator->generateLevel($args[1], $args[2], $args[3]);
                        $sender->sendMessage(MultiWorld::getPrefix() . str_replace("%1", $args[1], LanguageManager::translateMessage("create.generating")));
                        return false;
                    case "teleport":
                    case "tp":
                    case "move":
                        if (!$sender->hasPermission("mw.cmd.teleport")) {
                            $sender->sendMessage(LanguageManager::translateMessage("not-perms"));
                            return false;
                        }
                        if (empty($args[1])) {
                            $sender->sendMessage(MultiWorld::getPrefix() . LanguageManager::translateMessage("teleport-usage"));
                            return false;
                        }
                        if (!Server::getInstance()->isLevelGenerated($args[1])) {
                            $sender->sendMessage(MultiWorld::getPrefix() . LanguageManager::translateMessage("teleport-levelnotexists"));
                            return false;
                        }
                        if (!Server::getInstance()->isLevelLoaded($args[1])) {
                            Server::getInstance()->loadLevel($args[1]);
                            $this->plugin->getLogger()->debug(MultiWorld::getPrefix() . str_replace("%1", $args[1], LanguageManager::translateMessage("teleport-load")));
                        }
                        if (isset($args[2])) {
                            $player = Server::getInstance()->getPlayer($args[2]);
                            if ($player->isOnline()) {
                                $player->teleport(Server::getInstance()->getLevelByName($args[1])->getSafeSpawn(), 0, 0);
                                $player->sendMessage(MultiWorld::getPrefix() . str_replace("%1", $args[1], LanguageManager::translateMessage("teleport-done-1")));
                                $sender->sendMessage(MultiWorld::getPrefix() . str_replace("%1", $args[1], str_replace("%2", $player->getName(), LanguageManager::translateMessage("teleport-done-2"))));
                                return false;
                            } else {
                                $sender->sendMessage(MultiWorld::getPrefix() . LanguageManager::translateMessage("teleport-playernotexists"));
                                return false;
                            }
                        } else {
                            $sender->teleport(Server::getInstance()->getLevelByName($args[1])->getSafeSpawn(), 0, 0);
                            $sender->sendMessage(MultiWorld::getPrefix() . str_replace("%1", $args[1], LanguageManager::translateMessage("teleport-done-1")));
                        }
                        return false;
                    case "import":
                        if (!$sender->hasPermission("mw.cmd.import")) {
                            $sender->sendMessage(LanguageManager::translateMessage("not-perms"));
                            return false;
                        }
                        if (empty($args[1])) {
                            $sender->sendMessage(MultiWorld::getPrefix() . LanguageManager::translateMessage("import-usage"));
                            return false;
                        }
                        $zipPath = ConfigManager::getDataPath() . "levels/{$args[1]}.zip";
                        if (!file_exists($zipPath)) {
                            $sender->sendMessage(MultiWorld::getPrefix() . LanguageManager::translateMessage("import-zipnotexists"));
                            return false;
                        }
                        $zip = new \ZipArchive;
                        $zip->open($zipPath);
                        $zip->extractTo(ConfigManager::getDataPath() . "worlds/");
                        $zip->close();
                        unset($zip);
                        Server::getInstance()->loadLevel($args[1]);
                        $sender->sendMessage(MultiWorld::getPrefix() . LanguageManager::translateMessage("import-done"));
                        return false;
                    case "list":
                    case "ls":
                    case "levels":
                    case "worlds":
                        if (!$sender->hasPermission("mw.cmd.list")) {
                            $sender->sendMessage(LanguageManager::translateMessage("not-perms"));
                            return false;
                        }
                        $list = scandir(ConfigManager::getDataPath() . "worlds");
                        unset($list[0]);
                        unset($list[1]);
                        $list = implode(", ", $list);
                        $sender->sendMessage(MultiWorld::getPrefix() . str_replace("%1", $list, LanguageManager::translateMessage("list-done")));
                        return false;
                    case "load":
                        if (!$sender->hasPermission("mw.cmd.load")) {
                            $sender->sendMessage(LanguageManager::translateMessage("not-perms"));
                            return false;
                        }
                        if (empty($args[1])) {
                            $sender->sendMessage(MultiWorld::getPrefix() . LanguageManager::translateMessage("load-usage"));
                            return false;
                        }
                        if (!Server::getInstance()->isLevelGenerated($args[1])) {
                            $sender->sendMessage(MultiWorld::getPrefix() . str_replace("%1", $args[1], LanguageManager::translateMessage("load-levelnotexists")));
                            return false;
                        }
                        Server::getInstance()->loadLevel($args[1]);
                        $sender->sendMessage(MultiWorld::getPrefix() . str_replace("%1", $args[1], LanguageManager::translateMessage("load-done")));
                        break;
                    case "unload":
                        if (!$sender->hasPermission("mw.cmd.unload")) {
                            $sender->sendMessage(LanguageManager::translateMessage("not-perms"));
                            return false;
                        }
                        if (empty($args[1])) {
                            $sender->sendMessage(MultiWorld::getPrefix() . LanguageManager::translateMessage("unload-usage"));
                            return false;
                        }
                        if (!Server::getInstance()->isLevelGenerated($args[1])) {
                            $sender->sendMessage(MultiWorld::getPrefix() . str_replace("%1", $args[1], LanguageManager::translateMessage("unload-levelnotexists")));
                            return false;
                        }
                        if (!Server::getInstance()->isLevelLoaded($args[1])) {
                            $sender->sendMessage(MultiWorld::getPrefix() . str_replace("%1", $args[1], LanguageManager::translateMessage("unload-unloaded")));
                            return false;
                        }
                        Server::getInstance()->unloadLevel(Server::getInstance()->getLevelByName($args[1]));
                        $sender->sendMessage(MultiWorld::getPrefix() . str_replace("%1", $args[1], LanguageManager::translateMessage("unload-done")));
                        return false;
                    case "delete":
                    case "remove":
                        if (!$sender->hasPermission("mw.cmd.delete")) {
                            $sender->sendMessage(LanguageManager::translateMessage("not-perms"));
                            return false;
                        }
                        if (empty($args[1])) {
                            $sender->sendMessage(MultiWorld::getPrefix() . LanguageManager::translateMessage("delete-usage"));
                            return false;
                        }
                        if (!Server::getInstance()->isLevelGenerated($args[1])) {
                            $sender->sendMessage(MultiWorld::getPrefix() . str_replace("%1", $args[1], LanguageManager::translateMessage("delete-levelnotexists")));
                            return false;
                        }

                        $level = $this->plugin->getServer()->getLevelByName($args[1]);
                        $folderName = $level->getFolderName();

                        if (Server::getInstance()->isLevelLoaded($args[1])) {
                            Server::getInstance()->unloadLevel($this->plugin->getServer()->getLevelByName($args[1]));
                        }

                        $worldPath = ConfigManager::getDataPath() . "worlds/";
                        $levelPath = $worldPath . $folderName;

                        if (!is_dir($levelPath)) {
                            $sender->sendMessage("§cLevel path not found.");
                            return false;
                        }

                        foreach (scandir($levelPath) as $directory) {
                            files:
                            if (!is_dir($worldPath . $directory)) {
                                // scanning for level.dat, ...
                                if (is_file($worldPath . $directory)) {
                                    unlink($worldPath . $directory);
                                } else {
                                    $sender->sendMessage("§cLevel can not be deleted bug #1");
                                    return false;
                                }
                            }
                            dirs:
                            if (is_dir($worldPath . $directory)) {
                                // scanning for region
                                foreach (scandir($worldPath . $directory) as $file) {
                                    rmdir($file);
                                }
                            }
                            else {
                                goto files;
                            }
                        }

                        if (!(count(scandir($levelPath)) <= 0)) {
                            $sender->sendMessage("§cLevel can not be deleted bug #2");
                            return false;
                        }

                        rmdir($levelPath);

                        $sender->sendMessage(MultiWorld::getPrefix() . str_replace("%1", $args[1], LanguageManager::translateMessage("delete-done")));
                        return false;
                    case "setdefault":
                        $defaultLevel = Server::getInstance()->getDefaultLevel()->getName();
                        if(isset($args[1])) {
                            $senderLevel = Server::getInstance()->getLevelByName($args[1])->getName();
                            if($defaultLevel == $senderLevel) {
                                $sender->sendMessage(MultiWorld::getPrefix(). str_replace("%1", $args[1], LanguageManager::translateMessage("setdefault-isdefault")));
                                return false;
                            }
                            else {
                                Server::getInstance()->setDefaultLevel(Server::getInstance()->getLevelByName($senderLevel));
                                $properties = new Config(ConfigManager::getDataPath()."server.properties", Config::PROPERTIES);
                                $properties->set("level-name", $senderLevel);
                                $properties->save();
                                $sender->sendMessage(MultiWorld::getPrefix().str_replace("%1", $args[1], LanguageManager::translateMessage("setdefault-done")));

                            }
                        }
                        else {
                            $senderLevel = $sender->getLevel()->getName();
                            if($defaultLevel == $senderLevel) {
                                $sender->sendMessage(MultiWorld::getPrefix(). str_replace("%1", $args[1], LanguageManager::translateMessage("setdefault-isdefault")));
                                return false;
                            }
                            else {
                                LanguageManager::translateMessage(str_replace("%1", $senderLevel, LanguageManager::translateMessage("setdefault-done")));
                                Server::getInstance()->setDefaultLevel(Server::getInstance()->getLevelByName($senderLevel));
                                $properties = new Config(ConfigManager::getDataPath()."server.properties", Config::PROPERTIES);
                                $properties->set("level-name", $senderLevel);
                                $properties->save();
                            }
                        }
                        return false;
                    case "setlobby":
                        Server::getInstance()->setDefaultLevel($sender->getLevel());
                        $sender->getLevel()->setSpawnLocation($sender);
                        $sender->sendMessage("setlobby-done");
                        // save to properties
                        $properties = new Config(ConfigManager::getDataPath()."server.properties", Config::PROPERTIES);
                        $properties->set("level-name", $sender->getLevel()->getName());
                        $properties->save();
                        return false;
                    case "setspawn":
                    case "setspawnlocation":
                    case "setworldspawn":
                        $sender->getLevel()->setSpawnLocation($sender);
                        $sender->sendMessage(LanguageManager::translateMessage("setspawn-done"));
                        return false;
                }
            }
        }
        else {
            if (!$sender->hasPermission("mw.cmd.help")) {
                $sender->sendMessage(LanguageManager::translateMessage("not-perms"));
            }
            else {
                $sender->sendMessage(MultiWorld::getPrefix() . LanguageManager::translateMessage("default-usage"));
            }
        }

    }

}