<?php

namespace MoonTeam\AdvancedGroup\commands\administrator;

use MoonTeam\AdvancedGroup\Lang;
use MoonTeam\AdvancedGroup\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class RemoveGroup extends Command {

    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @throws \MoonTeam\AdvancedGroup\provider\ProviderErrorException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender->hasPermission("ag.removegroup")){
            if (!isset($args[0])){
                $sender->sendMessage("§cPlease do /removegroup [name].");
                return;
            }else{
                $provider = Main::getInstance()->getProvider();
                if (!$provider->existGroup($args[0])){
                    $sender->sendMessage(Lang::get("group-no-exist"));
                    return;
                }
                $provider->removeGroup($args[0]);
                $sender->sendMessage(str_replace(["{group}"], [$args[0]], Lang::get("successfully-group-remove")));
                return;
            }
        }else{
            $sender->sendMessage(Lang::get("no-permission"));
            return;
        }
    }

}