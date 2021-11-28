<?php

namespace MoonTeam\AdvancedGroup\commands\administrator;

use MoonTeam\AdvancedGroup\Lang;
use MoonTeam\AdvancedGroup\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Groups extends Command {

    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @throws \MoonTeam\AdvancedGroup\provider\ProviderErrorException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender->hasPermission("ag.groups")){
            $provider = Main::getInstance()->getProvider();
            if (empty($provider->getGroups())){
                $sender->sendMessage("§cThere are no groups currently created.");
                return;
            }else{
                $groups = $provider->getGroups();
                $sender->sendMessage(str_replace(["{count}", "{groups}"], [count($groups), (empty($groups) ? "None" : implode(", ", $groups))], Lang::get("show-groups")));
                return;
            }
        }else{
            $sender->sendMessage(Lang::get("no-permission"));
            return;
        }
    }

}