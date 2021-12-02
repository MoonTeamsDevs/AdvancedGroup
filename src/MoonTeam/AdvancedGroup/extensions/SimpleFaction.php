<?php

namespace MoonTeam\AdvancedGroup\extensions;

use Ayzrix\SimpleFaction\API\FactionsAPI;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

class SimpleFaction {

    /**
     * @param Player $player
     * @return string
     */
    public static function getPlayerFaction(Player $player): string {
        if (FactionsAPI::isInFaction($player->getName())) {
            return FactionsAPI::getFaction($player->getName());
        } else return "...";
    }

    /**
     * @param Player $player
     * @return string
     */
    public static function getPlayerRank(Player $player): string {
        if (FactionsAPI::isInFaction($player->getName())) {
            return FactionsAPI::getRank($player->getName());
        } else return "...";
    }

    /**
     * @param Player $player
     * @return string|int
     */
    public static function getFactionPower(Player $player) {
        if (FactionsAPI::isInFaction($player->getName())) {
            return FactionsAPI::getPower(FactionsAPI::getFaction($player->getName()));
        } else return "...";
    }

    /**
     * @param Player $player
     * @return string|int
     */
    public static function getFactionMoney(Player $player) {
        if (FactionsAPI::isInFaction($player->getName())) {
            return FactionsAPI::getMoney(FactionsAPI::getFaction($player->getName()));
        } else return "...";
    }

}