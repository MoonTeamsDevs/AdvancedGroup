<?php

namespace MoonTeam\AdvancedGroup\commands\administrator;

use MoonTeam\AdvancedGroup\Lang;
use MoonTeam\AdvancedGroup\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class ListGPerms extends Command {

    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @throws \MoonTeam\AdvancedGroup\provider\ProviderErrorException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender->hasPermission("ag.listgperms")){
            if (!isset($args[0])){
                $sender->sendMessage("§cPlease do /listgperms [group].");
                return;
            }else{
                $provider = Main::getInstance()->getProvider();
                if (!$provider->existGroup($args[0])){
                    $sender->sendMessage(Lang::get("group-no-exist"));
                    return;
                }
                $sender->sendMessage(str_replace(["{permissions}", "{group}", "{lines}"], [implode("\n-> ", $provider->getGroupPermissions($args[0])), $args[0], "\n"], Lang::get("successfully-list-group-perm")));
                return;
            }
        }else{
            $sender->sendMessage(Lang::get("no-permission"));
            return;
        }
    }

}