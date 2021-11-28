<?php

namespace MoonTeam\AdvancedGroup;

use pocketmine\utils\Config;

class Lang {

    public static function getLang(): Config{
        if (!file_exists(Main::getInstance()->getDataFolder() . "lang.yml")){
            Main::getInstance()->saveResource("lang.yml");
        }
        return new Config(Main::getInstance()->getDataFolder() . "lang.yml", Config::YAML);
    }

    public static function get(string $key){
        return self::getLang()->get($key);
    }

}