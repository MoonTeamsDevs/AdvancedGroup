<?php

namespace MoonTeam\AdvancedGroup\commands\administrator;

use MoonTeam\AdvancedGroup\Lang;
use MoonTeam\AdvancedGroup\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;

class AddPPerm extends Command {

    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @throws \MoonTeam\AdvancedGroup\provider\ProviderErrorException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender->hasPermission("ag.addpperm")){
            if (!isset($args[1])){
                $sender->sendMessage("§cPlease do /addpperm [joueur] [permission].");
                return;
            }else{
                $provider = Main::getInstance()->getProvider();
                $player = Server::getInstance()->getPlayer($args[0]);
                if ($player instanceof Player){
                    $provider->addPlayerPermission($player, $args[1]);
                    Main::getInstance()->updatePermissions($player);
                }
                $provider->addGroupPermission($args[0], $args[1]);
                $sender->sendMessage(str_replace(["{group}", "{permissions}"], [$args[0], $args[1]], Lang::get("successfully-add-group-permission")));
                return;
            }
        }else{
            $sender->sendMessage(Lang::get("no-permission"));
            return;
        }
    }

}