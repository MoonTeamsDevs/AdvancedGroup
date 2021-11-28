<?php

namespace MoonTeam\AdvancedGroup\commands\administrator;

use MoonTeam\AdvancedGroup\Lang;
use MoonTeam\AdvancedGroup\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class AddGroup extends Command {

    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @throws \MoonTeam\AdvancedGroup\provider\ProviderErrorException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender->hasPermission("ag.addgroup")){
            if (!isset($args[0])){
                $sender->sendMessage("§cPlease do /addgroup [name] (default = optional).");
                return;
            }
            $provider = Main::getInstance()->getProvider();
            if ($provider->existGroup($args[0])){
                $sender->sendMessage(Lang::get("group-exist"));
                return;
            }
            if (!isset($args[1])){
                $provider->addGroup($args[0], false);
                $sender->sendMessage(str_replace(["{group}"], [$args[0]], Lang::get("successfully-group-create")));
                return;
            }else{
                switch ($args[1]){
                    case "true":
                        $provider->addGroup($args[0], true);
                        $sender->sendMessage(str_replace(["{group}"], [$args[0]], Lang::get("successfully-group-create")));
                        break;
                    case "false":
                        $provider->addGroup($args[0], false);
                        $sender->sendMessage(str_replace(["{group}"], [$args[0]], Lang::get("successfully-group-create")));
                        break;
                    default:
                        $sender->sendMessage("§cYou must choose between true or false.");
                        break;
                }
            }
        }else{
            $sender->sendMessage(Lang::get("no-permission"));
            return;
        }
    }

}