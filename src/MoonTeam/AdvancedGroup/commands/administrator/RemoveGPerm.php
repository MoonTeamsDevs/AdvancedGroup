<?php

namespace MoonTeam\AdvancedGroup\commands\administrator;

use MoonTeam\AdvancedGroup\Lang;
use MoonTeam\AdvancedGroup\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class RemoveGPerm extends Command {

    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @throws \MoonTeam\AdvancedGroup\provider\ProviderErrorException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender->hasPermission("ag.removegperm")){
            if (!isset($args[1])){
                $sender->sendMessage("§cPlease do /removegperm [group] [permission].");
                return;
            }else{
                $provider = Main::getInstance()->getProvider();
                if (!$provider->existGroup($args[0])){
                    $sender->sendMessage(Lang::get("group-no-exist"));
                    return;
                }
                if (!$provider->existGroupPermission($args[0], $args[1])){
                    $sender->sendMessage(str_replace(["{group}", "{permission}"], [$args[0], $args[1]], Lang::get("no-have-this-permission-group")));
                    return;
                }
                $provider->removeGroupPermission($args[0], $args[1]);
                $sender->sendMessage(str_replace(["{group}", "{permission}"], [$args[0], $args[1]], Lang::get("successfully-remove-group-permission")));
                return;
            }
        }else{
            $sender->sendMessage(Lang::get("no-permission"));
            return;
        }
    }

}