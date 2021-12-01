<?php

namespace MoonTeam\AdvancedGroup;

use pocketmine\utils\Config;

class Lang {

    public static function getLang(): Config{
        $lang = Main::getInstance()->getConfig()->get("lang");
        if (!in_array($lang, ["fr", "en", "es"])){
            Main::getInstance()->getLogger()->error("The language \"{$lang}\" is not supported by the plugin. Restore the default language.");
            $config = Main::getInstance()->getConfig();
            $config->set("lang", "en");
            $config->save();
            if (!file_exists(Main::getInstance()->getDataFolder() . "lang-en.yml")){
                Main::getInstance()->saveResource("lang-en.yml");
            }
            return new Config(Main::getInstance()->getDataFolder() . "lang-en.yml", Config::YAML);
        }
        if (!file_exists(Main::getInstance()->getDataFolder() . "lang-{$lang}.yml")){
            Main::getInstance()->saveResource("lang-{$lang}.yml");
        }
        return new Config(Main::getInstance()->getDataFolder() . "lang-" . $lang . ".yml", Config::YAML);
    }

    public static function get(string $key){
        return self::getLang()->get($key);
    }

}