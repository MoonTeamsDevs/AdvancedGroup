<?php

namespace MoonTeam\AdvancedGroup\commands\administrator;

use MoonTeam\AdvancedGroup\Lang;
use MoonTeam\AdvancedGroup\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;

class SetGroup extends Command {

    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @throws \MoonTeam\AdvancedGroup\provider\ProviderErrorException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender->hasPermission("ag.setgroup")){
            if (!isset($args[1])){
                $sender->sendMessage("§cPlease do /setgroup [player] [group].");
                return;
            }else{
                $provider = Main::getInstance()->getProvider();
                $player = Server::getInstance()->getPlayerByPrefix($args[0]);
                if ($player instanceof Player){
                    if ($provider->hasAccount($player)){
                        if ($provider->existGroup($args[1])){
                            $provider->setGroup($player, $args[1]);
                            Main::getInstance()->updatePermissions($player);
                            $player->sendMessage(str_replace(["{group}"], [$args[1]], Lang::get("changed-group-player")));
                            $sender->sendMessage(str_replace(["{group}", "{player}"], [$args[1], $player->getName()], Lang::get("changed-group")));
                            return;
                        }else{
                            $sender->sendMessage(Lang::get("group-no-exist"));
                            return;
                        }
                    }else{
                        $sender->sendMessage(Lang::get("no-in-data"));
                        return;
                    }
                }else{
                    if ($provider->hasAccount($player)){
                        if ($provider->existGroup($args[1])){
                            $provider->setGroup($player, $args[1]);
                            $player->sendMessage(str_replace(["{group}"], [$args[1]], Lang::get("changed-group-player")));
                            $sender->sendMessage(str_replace(["{group}", "{player}"], [$args[1], $player], Lang::get("changed-group")));
                            return;
                        }else{
                            $sender->sendMessage(Lang::get("group-no-exist"));
                            return;
                        }
                    }else{
                        $sender->sendMessage(Lang::get("no-in-data"));
                        return;
                    }
                }
            }
        }else{
            $sender->sendMessage(Lang::get("no-permission"));
            return;
        }
    }

}