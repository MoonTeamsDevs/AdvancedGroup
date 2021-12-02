<?php

namespace MoonTeam\AdvancedGroup\utils;

use MoonTeam\AdvancedGroup\extensions\EconomyAPI;
use MoonTeam\AdvancedGroup\extensions\FactionsPro;
use MoonTeam\AdvancedGroup\extensions\PiggyFactions;
use MoonTeam\AdvancedGroup\extensions\RedSkyBlock;
use MoonTeam\AdvancedGroup\extensions\SimpleFaction;
use MoonTeam\AdvancedGroup\extensions\SkyBlock;
use MoonTeam\AdvancedGroup\Main;
use pocketmine\player\Player;

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
        if (Main::$extensions["EconomyAPI"] === true) $string = str_replace(["{money}"], [EconomyAPI::getPlayerMoney($player)], $string);
        if (Main::$extensions["SkyBlock"] === true) $string = $string = str_replace(["{island_blocks}", "{island_members}", "{island_rank}", "{island_size}"], [SkyBlock::getIslandBlocks($player), SkyBlock::getIslandMembers($player), SkyBlock::getIslandRank($player), SkyBlock::getIslandSize($player)], $string);
        if (Main::$extensions["RedSkyBlock"] === true) $string = str_replace(["{island_members}", "{island_rank}", "{island_size}", "{island_value}", "{island_locked_status}"], [RedSkyBlock::getIslandMembers($player), RedSkyBlock::getIslandRank($player), RedSkyBlock::getIslandSize($player), RedSkyBlock::getIslandValue($player), RedSkyBlock::getIslandLocked($player)], $string);
        return $string;
    }

}