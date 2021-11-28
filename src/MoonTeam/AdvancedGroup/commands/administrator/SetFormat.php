<?php

namespace MoonTeam\AdvancedGroup\commands\administrator;

use MoonTeam\AdvancedGroup\Lang;
use MoonTeam\AdvancedGroup\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class SetFormat extends Command {

    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @throws \MoonTeam\AdvancedGroup\provider\ProviderErrorException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender->hasPermission("ag.setformat")){
            if (!isset($args[1])){
                $sender->sendMessage("§cPlease do /setformat [group] [format].");
                return;
            }else{
                $provider = Main::getInstance()->getProvider();
                if (!$provider->existGroup($args[0])){
                    $sender->sendMessage(Lang::get("group-no-exist"));
                    return;
                }
                $format = [];
                for ($i = 1;$i < count($args);$i++){
                    $format[] = $args[$i];
                }
                $provider->setFormat($args[0], implode(" ", $format));
                $sender->sendMessage(str_replace(["{group}", "{format}"], [$args[0], implode(" ", $format)], Lang::get("successfully-set-format")));
                return;
            }
        }else{
            $sender->sendMessage(Lang::get("no-permission"));
            return;
        }
    }

}