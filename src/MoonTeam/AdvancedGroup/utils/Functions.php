<?php

namespace MoonTeam\AdvancedGroup\utils;

use MoonTeam\AdvancedGroup\extensions\FactionsPro;
use MoonTeam\AdvancedGroup\extensions\PiggyFactions;
use MoonTeam\AdvancedGroup\extensions\SimpleFaction;
use MoonTeam\AdvancedGroup\Main;
use pocketmine\Player;

class Functions {

    public static string $PUBLIC_DATA = "";
    public static array $cachedGroup = [];
    public static array $cachedPlayers = [];
    public static array $mysqli = [];
    public static string $defaultGroup = "";
    public static array $attachments = [];

    public static function replace(Player $player, string $string): string{
        if (Main::$extensions["SimpleFaction"] === true) $string = str_replace(["{faction_name}", "{faction_rank}", "{faction_power}", "{faction_money}"], [SimpleFaction::getPlayerFaction($player), SimpleFaction::getPlayerRank($player), SimpleFaction::getFactionPower($player), SimpleFaction::getFactionMoney($player)], $string);
        if (Main::$extensions["PiggyFactions"] === true) $string = str_replace(["{faction_name}", "{faction_power}", "{faction_rank}"], [PiggyFactions::getPlayerFaction($player), PiggyFactions::getFactionPower($player), PiggyFactions::getPlayerRank($player)], $string);
        if (Main::$extensions["FactionsPro"] === true) $string = str_replace(["{faction_name}", "{faction_power}"], [FactionsPro::getPlayerFaction($player), FactionsPro::getFactionPower($player)], $string);
        return $string;
    }

}