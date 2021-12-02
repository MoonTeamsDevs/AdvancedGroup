<?php

namespace MoonTeam\AdvancedGroup\extensions;

use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

class RedSkyBlock {

    /**
     * @return Plugin
     */
    public static function getPlugin(): Plugin {
        return Server::getInstance()->getPluginManager()->getPlugin("RedSkyBlock");
    }

    /**
     * @param Player $player
     * @return bool
     */
    public static function hasIsland(Player $player): bool {
        return file_exists(self::getPlugin()->getDataFolder() . "Players/" . strtolower($player->getName()) . ".json");
    }

    /**
     * @param Player $player
     * @return string
     */
    public static function getIslandMembers(Player $player): string {
        if (self::hasIsland($player)) {
            return count(self::getPlugin()->getIslandMembers($player));
        } else return "...";
    }

    /**
     * @param Player $player
     * @return string
     */
    public static function getIslandRank(Player $player): string {
        if (self::hasIsland($player)) {
            return self::getPlugin()->getIslandRank($player);
        } else return "...";
    }

    /**
     * @param Player $player
     * @return string
     */
    public static function getIslandSize(Player $player): string {
        if (self::hasIsland($player)) {
            return self::getPlugin()->getIslandSize($player);
        } else return "...";
    }

    /**
     * @param Player $player
     * @return string
     */
    public static function getIslandValue(Player $player): string {
        if (self::hasIsland($player)) {
            return self::getPlugin()->getIslandValue($player);
        } else return "...";
    }

    /**
     * @param Player $player
     * @return string
     */
    public static function getIslandLocked(Player $player): string {
        if (self::hasIsland($player)) {
            if (self::getPlugin()->isIslandLocked($player)) {
                return "§aYes";
            } else return "§cNo";
        } else return "...";
    }

}