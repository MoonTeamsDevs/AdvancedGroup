<?php

namespace MoonTeam\AdvancedGroup\provider;

use http\Exception\RuntimeException;
use MoonTeam\AdvancedGroup\Main;
use MoonTeam\AdvancedGroup\tasks\async\MySQLAsyncTask;
use MoonTeam\AdvancedGroup\utils\Functions;
use pocketmine\permission\PermissionAttachment;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\UUID;

class JSONProvider implements Provider {

    public function getGroupData(): Config
    {
        if (!file_exists(Main::getInstance()->getDataFolder() . "groups.json",)){
            Main::getInstance()->saveResource("groups.json");
        }
        return new Config(Main::getInstance()->getDataFolder() . "groups.json");
    }

    public function getPlayersData(): Config
    {
        if (!file_exists(Main::getInstance()->getDataFolder() . "players.json",)){
            Main::getInstance()->saveResource("players.json");
        }
        return new Config(Main::getInstance()->getDataFolder() . "players.json");
    }

    public function getGroups(): array
    {
        if (Main::caching()){
            $result = [];
            foreach (Functions::$cachedGroup as $name => $value){
                $result[] = $name;
            }
            return $result;
        }else{
            $result = [];
            foreach ($this->getGroupData()->getAll() as $name => $value){
                $result[] = $name;
            }
            return $result;
        }
    }

    public function addGroup(string $name, bool $default = false)
    {
        if (Main::caching()){
            Functions::$cachedGroup[$name] = [
                "permissions" => [],
                "default" => $default,
                "format" => "§f[§7{group}§f] §7{playerName} §f-> §7{msg}"
            ];
        }else{
            $config = $this->getGroupData();
            $config->set($name, ["permissions" => [], "default" => $default, "format" => "§f[§7{group}§f] §7{playerName} §f-> §7{msg}"]);
            $config->save();
        }
    }

    public function removeGroup(string $name)
    {
        if (Main::caching()){
            unset(Functions::$cachedGroup[$name]);
        }else{
            $config = $this->getGroupData();
            $config->remove($name);
            $config->save();
        }
    }

    public function existGroup(string $name): bool
    {
        if (Main::caching()){
            return isset(Functions::$cachedGroup[$name]);
        }else{
            $config = $this->getGroupData();
            return $config->exists($name);
        }
    }

    public function hasAccount(Player|string $player): bool
    {
        if ($player instanceof Player) {
            $config = $this->getPlayersData();
            return $config->exists($player->getName());
        }else{
            $config = $this->getPlayersData();
            return $config->exists($player);
        }
    }

    public function createAccount(Player|string $player)
    {
        if ($player instanceof Player) {
            if (Main::caching()) {
                Functions::$cachedPlayers[$player->getName()] = [
                    "permissions" => [],
                    "group" => $this->getDefaultGroup()
                ];
            }else{
                $config = $this->getPlayersData();
                $config->set($player->getName(), ["permissions" => [], "group" => $this->getDefaultGroup()]);
                $config->save();
            }
        }else{
            if (Main::caching()) {
                Functions::$cachedPlayers[$player] = [
                    "permissions" => [],
                    "group" => $this->getDefaultGroup()
                ];
            }else{
                $config = $this->getPlayersData();
                $config->set($player, ["permissions" => [], "group" => $this->getDefaultGroup()]);
                $config->save();
            }
        }
    }

    public function getDefaultGroup(): string
    {
        if (Main::caching()){
            return Functions::$defaultGroup ?? "";
        }else{
            foreach ($this->getGroupData() as $name => $value){
                if ($this->getGroupData()->get($name)["default"] === true){
                    return $name;
                }
            }
        }
        return "";
    }

    public function savePlayerData(Player|string $player)
    {
        if ($player instanceof Player){
            $data = Functions::$cachedPlayers[$player->getName()];
            $config = $this->getPlayersData();
            $config->set($player->getName(), $data);
            $config->save();
        }else{
            $data = Functions::$cachedPlayers[$player];
            $config = $this->getPlayersData();
            $config->set($player, $data);
            $config->save();
        }
    }

    public function saveGroupData(array $groups)
    {
        foreach ($groups as $name => $value){
            $config = $this->getGroupData();
            $config->set($name, $value);
            $config->save();
        }
    }

    public function getPlayerGroup(Player|string $player)
    {
        if ($player instanceof Player){
            if (Main::caching()){
                return Functions::$cachedPlayers[$player->getName()]["group"];
            }else{
                $config = $this->getPlayersData();
                return $config->get($player->getName())["group"];
            }
        }else{
            if (Main::caching()){
                return Functions::$cachedPlayers[$player]["group"];
            }else{
                $config = $this->getPlayersData();
                return $config->get($player)["group"];
            }
        }
    }

    public function getFormatGroup(string $group)
    {
        if (Main::caching()){
            return Functions::$cachedGroup[$group]["format"];
        }else{
            $config = $this->getGroupData();
            return $config->exists($group) ? $config->get($group)["format"] : "<{playerName}> {msg}";
        }
    }

    public function setGroup(Player|string $player, string $group)
    {
        if ($player instanceof Player) {
            if (Main::caching()) {
                Functions::$cachedPlayers[$player->getName()]["group"] = $group;
                $this->updatePermissions($player);
            } else {
                $config = $this->getPlayersData();
                $players = $config->get($player->getName());
                $players["group"] = $group;
                $config->set($player->getName(), $players);
                $config->save();
                $this->updatePermissions($player);
            }
        }else{
            if (Main::caching()) {
                Functions::$cachedPlayers[$player]["group"] = $group;
            } else {
                $config = $this->getPlayersData();
                $players = $config->get($player);
                $players["group"] = $group;
                $config->set($player, $players);
                $config->save();
            }
        }
    }

    public function setFormat(string $group, string $format)
    {
        if (Main::caching()){
            Functions::$cachedGroup[$group]["format"] = $format;
        }else{
            $config = $this->getGroupData();
            $groups = $config->get($group);
            $groups["format"] = $format;
            $config->set($group, $groups);
            $config->save();
        }
    }

    public function addGroupPermission(string $group, string $perm)
    {
        if (Main::caching()){
            Functions::$cachedGroup[$group]["permissions"][] = $perm;
            foreach (Server::getInstance()->getOnlinePlayers() as $player){
                if ($this->getPlayerGroup($player) === $group){
                    Main::getInstance()->updatePermissions($player);
                }
            }
        }else{
            $config = $this->getGroupData();
            $all = $config->get($group);
            $all["permissions"][] = $perm;
            $config->set($group, $all);
            $config->save();
            foreach (Server::getInstance()->getOnlinePlayers() as $player){
                if ($this->getPlayerGroup($player) === $group){
                    Main::getInstance()->updatePermissions($player);
                }
            }
        }
    }

    public function removeGroupPermission(string $group, string $perm)
    {
        if (Main::caching()){
            $array = [];
            foreach (Functions::$cachedGroup[$group]["permissions"] as $id => $permission){
                if ($permission !== $perm){
                    $array[] = $permission;
                }
            }
            Functions::$cachedGroup[$group]["permissions"] = $array;
            foreach (Server::getInstance()->getOnlinePlayers() as $player){
                if ($this->getPlayerGroup($player) === $group){
                    Main::getInstance()->updatePermissions($player);
                }
            }
        }else{
            $config = $this->getGroupData();
            $all = $config->get($group);
            $permissions = $all["permissions"];
            $array = [];
            foreach ($permissions as $id => $permission){
                if ($permission !== $perm){
                    $array[] = $permission;
                }
            }
            $all["permissions"] = $array;
            $config->set($group, $all);
            $config->save();
            foreach (Server::getInstance()->getOnlinePlayers() as $player){
                if ($this->getPlayerGroup($player) === $group){
                    Main::getInstance()->updatePermissions($player);
                }
            }
        }
    }

    public function addPlayerPermission(Player|string $player, string $perm)
    {
        if ($player instanceof Player){
            if (Main::caching()){
                Functions::$cachedPlayers[$player->getName()]["permissions"][] = $perm;
                Main::getInstance()->updatePermissions($player);
            }else{
                $config = $this->getPlayersData();
                $all = $config->get($player->getName());
                $permissions = $all["permissions"];
                $all["permissions"][] = $perm;
                $config->set($player->getName(), $all);
                $config->save();
                Main::getInstance()->updatePermissions($player);
            }
        }else{
            if (Main::caching()){
                Functions::$cachedPlayers[$player]["permissions"][] = $perm;
            }else{
                $config = $this->getPlayersData();
                $all = $config->get($player);
                $permissions = $all["permissions"];
                $all["permissions"][] = $perm;
                $config->set($player, $all);
                $config->save();
            }
        }
    }

    public function removePlayerPermission(Player|string $player, string $perm)
    {
        if ($player instanceof Player){
            if (Main::caching()){
                $array = [];
                foreach (Functions::$cachedPlayers[$player->getName()]["permissions"] as $id => $permission){
                    if ($permission !== $perm){
                        $array[] = $permission;
                    }
                }
                Functions::$cachedPlayers[$player->getName()]["permissions"] = $array;
                Main::getInstance()->updatePermissions($player);
            }else{
                $config = $this->getPlayersData();
                $all = $config->get($player->getName());
                $permissions = $all["permissions"];
                $array = [];
                foreach ($permissions as $id => $permission){
                    if ($permission !== $perm){
                        $array[] = $permission;
                    }
                }
                $all["permissions"] = $array;
                $config->set($player->getName(), $all);
                $config->save();
            }
        }else{
            if (Main::caching()){
                $array = [];
                foreach (Functions::$cachedPlayers[$player]["permissions"] as $id => $permission){
                    if ($permission !== $perm){
                        $array[] = $permission;
                    }
                }
                Functions::$cachedPlayers[$player]["permissions"] = $array;
            }else{
                $config = $this->getPlayersData();
                $all = $config->get($player);
                $permissions = $all["permissions"];
                $array = [];
                foreach ($permissions as $id => $permission){
                    if ($permission !== $perm){
                        $array[] = $permission;
                    }
                }
                $all["permissions"] = $array;
                $config->set($player, $all);
                $config->save();
            }
        }
    }


    public function getGroupPermissions(string $group): array
    {
        if (Main::caching()){
            return Functions::$cachedGroup[$group]["permissions"];
        }else{
            $config = $this->getGroupData()->get($group);
            return $config["permissions"] !== [] ? $config["permissions"] : [];
        }
    }

    public function getPlayerPermissions(Player|string $player): array
    {
        if ($player instanceof Player) {
            if (Main::caching()) {
                return Functions::$cachedPlayers[$player->getName()]["permissions"];
            } else {
                $config = $this->getPlayersData()->get($player->getName());
                return $config["permissions"] === [] ? $config["permissions"] : [];
            }
        }else{
            if (Main::caching()) {
                return Functions::$cachedPlayers[$player]["permissions"];
            } else {
                $config = $this->getPlayersData()->get($player);
                return $config["permissions"] === [] ? $config["permissions"] : [];
            }
        }
    }

    public function getPermissions(Player $player): array
    {
        if ($this->getPlayerGroup($player) !== "") {
            $group = $this->getGroupPermissions($this->getPlayerGroup($player));
        }else{
            $group = [];
        }
        $perms = $this->getPlayerPermissions($player);
        return array_merge($group, $perms);
    }

    public function existGroupPermission(string $group, string $permission): bool
    {
        if (Main::caching()){
            return in_array($permission, Functions::$cachedGroup[$group]["permissions"]);
        }else{
            $config = $this->getGroupData()->get($group);
            return in_array($permission, $config["permissions"]);
        }
    }

    public function existPlayerPermission(Player|string $player, string $permission): bool
    {
        if ($player instanceof Player){
            if (Main::caching()){
                return in_array($permission, Functions::$cachedPlayers[$player->getName()]["permissions"]);
            }else{
                $config = $this->getPlayersData()->get($player->getName());
                return in_array($permission, $config["permissions"]);
            }
        }else{
            if (Main::caching()){
                return in_array($permission, Functions::$cachedPlayers[$player]["permissions"]);
            }else{
                $config = $this->getPlayersData()->get($player);
                return in_array($permission, $config["permissions"]);
            }
        }
    }

    public function updateGroupForPlayers(string $groupDelete)
    {
        if (Main::caching()){
            foreach (Functions::$cachedPlayers as $player => $value){
                $data = Functions::$cachedPlayers[$player];
                if ($data["group"] === $groupDelete){
                    $data["group"] = $this->getDefaultGroup();
                }
            }
        }else{
            $config = $this->getPlayersData();
            foreach ($this->getPlayersData() as $player => $value){
                $data = $this->getPlayersData()->get($player);
                if ($data["group"] === $groupDelete){
                    $data["group"] = $this->getDefaultGroup();
                    $config->set($player, $data);
                }
            }
            $config->save();
        }
    }

    /**
     * @throws provider\ProviderErrorException
     */
    public function updatePermissions(Player $player){
        $permissions = [];
        $provider = $this;
        foreach ($provider->getPermissions($player) as $permission){
            if ($permission === '*'){
                foreach (Server::getInstance()->getPluginManager()->getPermissions() as $perm){
                    $permissions[$perm->getName()] = true;
                }
            }else{
                $permissions[$permission] = true;
            }
        }
        $attachment = $this->getAttachment($player);
        $attachment->clearPermissions();
        $attachment->setPermissions($permissions);
    }

    public function getValidUUID(Player $player): ?string
    {
        $uuid = $player->getUniqueId();

        if ($uuid instanceof UUID){
            return $uuid->toString();
        }

        return null;
    }

    public function getAttachment(Player $player): PermissionAttachment{
        $uuid = $this->getValidUUID($player);

        if (!isset(Functions::$attachments[$uuid])){
            throw new RuntimeException($player->getName() . " has empty attachments.");
        }

        return Functions::$attachments[$uuid];
    }

}