<?php

namespace MoonTeam\AdvancedGroup\listeners;

use MoonTeam\AdvancedGroup\Main;
use MoonTeam\AdvancedGroup\utils\Functions;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;

class PlayerListener implements Listener {

    /**
     * @throws \MoonTeam\AdvancedGroup\provider\ProviderErrorException
     */
    public function onPreLogin(PlayerPreLoginEvent $event){
        $player = $event->getPlayer();
        $provider = Main::getInstance()->getProvider();
        if (!$provider->hasAccount($player)){
            $provider->createAccount($player);
        }
        if (!Main::getInstance()->isRegistered($player)){
            Main::getInstance()->registerPlayer($player);
        }
    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        if (Main::caching()){
            $provider = Main::getInstance()->getProvider();
            $provider->savePlayerData($player);
        }
        Main::getInstance()->unregisterPlayer($player);
    }

    /**
     * @throws \MoonTeam\AdvancedGroup\provider\ProviderErrorException
     */
    public function onChat(PlayerChatEvent $event){
        $player = $event->getPlayer();
        $provider = Main::getInstance()->getProvider();
        $event->setFormat(str_replace(["{group}", "{playerName}", "{msg}"], [$provider->getPlayerGroup($player), $player->getName(), $event->getMessage()], Functions::replace($player, $provider->getFormatGroup($provider->getPlayerGroup($player)))));
    }

}