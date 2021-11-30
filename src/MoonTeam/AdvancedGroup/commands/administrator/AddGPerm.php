<?php

namespace MoonTeam\AdvancedGroup\commands\administrator;

use MoonTeam\AdvancedGroup\Lang;
use MoonTeam\AdvancedGroup\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class AddGPerm extends Command {

    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @throws \MoonTeam\AdvancedGroup\provider\ProviderErrorException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender->hasPermission("ag.addgperm")){
            if (!isset($args[1])){
                $sender->sendMessage("§cPlease do /addgperm [group] [permission].");
                return;
            }else{
                $provider = Main::getInstance()->getProvider();
                if (!$provider->existGroup($args[0])){
                    $sender->sendMessage(Lang::get("no-group-exist"));
                    return;
                }
                if ($provider->existGroupPermission($args[0], $args[1])){
                    $sender->sendMessage(str_replace(["{group}"], [$args[0]], Lang::get("have-this-permission-group")));
                }
                $provider->addGroupPermission($args[0], $args[1]);
                $sender->sendMessage(str_replace(["{group}", "{permission}"], [$args[0], $args[1]], Lang::get("successfully-add-group-permission")));
                return;
            }
        }else{
            $sender->sendMessage(Lang::get("no-permission"));
            return;
        }
    }

}