<?php

namespace MoonTeam\AdvancedGroup\commands\administrator;

use MoonTeam\AdvancedGroup\Lang;
use MoonTeam\AdvancedGroup\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;

class RemovePPerm extends Command{

    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @throws \MoonTeam\AdvancedGroup\provider\ProviderErrorException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender->hasPermission("ag.removepperm")){
            if (!isset($args[1])){
                $sender->sendMessage("§cPlease do /removepperm [player] [permission].");
                return;
            }else{
                $provider = Main::getInstance()->getProvider();
                if ($provider->hasAccount($args[0])){
                    if ($provider->existPlayerPermission($args[0], $args[1])){
                        $provider->removePlayerPermission($args[0], $args[1]);
                        $player = Server::getInstance()->getPlayer($args[0]);
                        if ($player instanceof Player){
                            Main::getInstance()->updatePermissions($player);
                        }
                        $sender->sendMessage(str_replace(["{player}", "{permission}"], [$args[0], $args[1]], Lang::get("successfully-remove-player-permission")));
                        return;
                    }else{
                        $sender->sendMessage(str_replace(["{player}", "{permission}"], [$args[0], $args[1]], Lang::get("no-have-this-permission-player")));
                        return;
                    }
                }else{
                    $sender->sendMessage(Lang::get("no-in-data"));
                    return;
                }
            }
        }else{
            $sender->sendMessage(Lang::get("no-permission"));
            return;
        }
    }

}