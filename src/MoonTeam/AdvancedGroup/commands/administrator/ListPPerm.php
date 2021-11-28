<?php

namespace MoonTeam\AdvancedGroup\commands\administrator;

use MoonTeam\AdvancedGroup\Lang;
use MoonTeam\AdvancedGroup\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class ListPPerm extends Command {

    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @throws \MoonTeam\AdvancedGroup\provider\ProviderErrorException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender->hasPermission("ag.listgperm")){
            if (!isset($args[0])){
                $sender->sendMessage("§cPlease do /listgperm [group].");
                return;
            }else{
                $provider = Main::getInstance()->getProvider();
                if (!$provider->hasAccount($args[0])){
                    $sender->sendMessage(Lang::get("no-in-data"));
                    return;
                }
                $sender->sendMessage(str_replace(["{permissions}", "{player}"], [implode("\n-> ", $provider->getPlayerPermissions($args[0])), $args[0]], Lang::get("successfully-list-player-perm")));
                return;
            }
        }else{
            $sender->sendMessage(Lang::get("no-permission"));
            return;
        }
    }

}