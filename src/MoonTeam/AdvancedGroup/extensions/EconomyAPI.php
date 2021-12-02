<?php

namespace MoonTeam\AdvancedGroup\extensions;

use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

class EconomyAPI {

    /**
     * @return Plugin
     */
    public static function getPlugin(): Plugin {
        return Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI");
    }

    public static function getPlayerMoney(Player $player){
        return self::getPlugin()->myMoney($player);
    }


}