<?php

namespace MoonTeam\AdvancedGroup\provider;

use http\Exception\RuntimeException;
use MoonTeam\AdvancedGroup\Main;
use MoonTeam\AdvancedGroup\tasks\async\MySQLAsyncTask;
use MoonTeam\AdvancedGroup\utils\Functions;
use pocketmine\permission\PermissionAttachment;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\UUID;

class MySQLProvider implements Provider
{

    /**
     * @throws ProviderErrorException
     */
    public function __construct()
    {
        $this->tryConnect();
        $this->initTable();
    }

    /**
     * @throws ProviderErrorException
     */
    public function getData(): \mysqli
    {
        $database = Functions::$mysqli;
        if (!isset($database["host"])) {
            throw new ProviderErrorException("Please fill in the \"host\" field in the config.yml.");
        }
        if (!isset($database["username"])) {
            throw new ProviderErrorException("Please fill in the \"username\" field in the config.yml.");
        }
        if (!isset($database["password"])) {
            throw new ProviderErrorException("Please fill in the \"password\" field in the config.yml.");
        }
        if (!isset($database["database"])) {
            throw new ProviderErrorException("Please fill in the \"database\" field in the config.yml.");
        }
        if (!isset($database["port"])) {
            throw new ProviderErrorException("Please fill in the \"port\" field in the config.yml.");
        }
        return new \mysqli($database["host"], $database["username"], $database["password"], $database["database"], $database["port"]);
    }

    /**
     * @throws ProviderErrorException
     */
    public function tryConnect()
    {
        $this->getData();
        Main::getInstance()->getLogger()->notice("Successful connection to the MySQL database.");
    }

    /**
     * @throws ProviderErrorException
     */
    public function initTable(): void
    {
        $this->getData()->query("CREATE TABLE IF NOT EXISTS `groups` (`name` VARCHAR(55), `permissions` TEXT NOT NULL, `default` BOOLEAN, `format` TEXT NOT NULL, PRIMARY KEY(`name`))");
        $this->getData()->query("CREATE TABLE IF NOT EXISTS  `players` (`pseudo` VARCHAR(55), `group` VARCHAR(55), `permissions` TEXT NOT NULL, PRIMARY KEY(`pseudo`))");
    }

    public function addGroup(string $name, bool $default = false)
    {
        if (Main::caching()) {
            Functions::$cachedGroup[$name] = [
                "permissions" => [],
                "default" => $default,
                "format" => "??f[??7{group}??f] ??7{playerName} ??f-> ??7{msg}"
            ];
        }
        Server::getInstance()->getAsyncPool()->submitTask(new MySQLAsyncTask(Functions::$mysqli, "INSERT INTO `groups` VALUES ('$name', '', '" . (int)$default . "', '??f[??7{group}??f] ??7{playerName} ??f-> ??7{msg}')"));
    }

    /**
     * @throws ProviderErrorException
     */
    public function getGroups(): array
    {
        $result = [];
        if (Main::caching()) {
            foreach (Functions::$cachedGroup as $name => $value) {
                $result[] = $name;
            }
        } else {
            $query = $this->getData()->query("SELECT * FROM `groups`");
            foreach ($query->fetch_all() as $value) {
                $result[] = $value[0];
            }
        }
        return $result;
    }

    /**
     * @throws ProviderErrorException
     */
    public function removeGroup(string $name)
    {
        if (Main::caching()) {
            if (isset(Functions::$cachedGroup[$name])) {
                unset(Functions::$cachedGroup[$name]);
            }
            $this->getData()->query("DELETE FROM `groups` WHERE `name`='" . $name . "'");
        } else {
            $this->getData()->query("DELETE FROM `groups` WHERE `name`='" . $name . "'");
        }
    }

    /**
     * @throws ProviderErrorException
     */
    public function existGroup(string $name): bool
    {
        if (Main::caching()) {
            if (isset(Functions::$cachedGroup[$name])) {
                return true;
            } else {
                return false;
            }
        } else {
            $data = $this->getData()->query("SELECT * FROM `groups` WHERE `name`='" . $name . "'");
            return $data->num_rows > 0;
        }
    }

    /**
     * @throws ProviderErrorException
     */
    public function hasAccount(Player|string $player): bool
    {
        if ($player instanceof Player) {
            if (Main::caching()) {
                if (isset(Functions::$cachedPlayers[$player->getName()])){
                    return true;
                }else{
                    return false;
                }
            } else {
                $query = $this->getData()->query("SELECT * FROM `players` WHERE `pseudo`='" . $player->getName() . "'");
                return $query->num_rows > 0;
            }
        }else{
            if (Main::caching()) {
                if (isset(Functions::$cachedPlayers[$player])){
                    return true;
                }else{
                    return false;
                }
            } else {
                $query = $this->getData()->query("SELECT * FROM `players` WHERE `pseudo`='" . $player . "'");
                return $query->num_rows > 0;
            }
        }
    }

    /**
     * @throws ProviderErrorException
     */
    public function createAccount(Player|string $player)
    {
        if ($player instanceof Player) {
            if (Main::caching()) {
                Functions::$cachedPlayers[$player->getName()] = [
                    "permissions" => [],
                    "group" => $this->getDefaultGroup()
                ];
            } else {
                $this->getData()->query("INSERT INTO `players` (`pseudo`, `group`, `permissions`) VALUES ('" . $player->getName() . "', '" . Functions::$defaultGroup . "', '')");
            }
        }else{
            if (Main::caching()) {
                Functions::$cachedPlayers[$player] = [
                    "permissions" => [],
                    "group" => $this->getDefaultGroup()
                ];
            } else {
                $this->getData()->query("INSERT INTO `players` (`pseudo`, `group`, `permissions`) VALUES ('" . $player . "', '" . Functions::$defaultGroup . "', '')");
            }
        }
    }

    /**
     * @throws ProviderErrorException
     */
    public function getDefaultGroup(): string
    {
        if (Main::caching()){
            $group = "";
            foreach (Functions::$cachedGroup as $name => $value){
                if ($value["default"] === true){
                    $group = $name;
                }
            }
            return $group;
        }else{
            $group = "";
            $query = $this->getData()->query("SELECT * FROM `groups`");
            foreach ($query->fetch_all() as $value){
                if ($value[2] === true){
                    $group = $value[0];
                }
            }
            return $group;
        }
    }

    public function savePlayerData(Player|string $player)
    {
        if ($player instanceof Player) {
            $data = Functions::$cachedPlayers[$player->getName()];
            Server::getInstance()->getAsyncPool()->submitTask(new MySQLAsyncTask(Functions::$mysqli, "UPDATE `players` SET `group`='" . $data["group"] . "', `permissions`='" . implode(":", $data["permissions"]) . "' WHERE `pseudo`='" . $player->getName() . "'"));
        }else{
            $data = Functions::$cachedPlayers[$player];
            Server::getInstance()->getAsyncPool()->submitTask(new MySQLAsyncTask(Functions::$mysqli, "UPDATE `players` SET `group`='" . $data["group"] . "', `permissions`='" . implode(":", $data["permissions"]) . "' WHERE `pseudo`='" . $player . "'"));
        }
    }

    public function saveGroupData(array $groups)
    {
        foreach ($groups as $data){
            Server::getInstance()->getAsyncPool()->submitTask(new MySQLAsyncTask(Functions::$mysqli, "UPDATE `groups` SET `permissions`='" . implode(":", $data["permissions"]) . "', `default`='" . (int)$data["default"] . "', `format`='" . $data["format"] . "' WHERE `name`='" . $data["name"] . "'"));
        }
    }

    /**
     * @throws ProviderErrorException
     */
    public function getPlayerGroup(Player|string $player)
    {
        if ($player instanceof Player){
            if (Main::caching()){
                return Functions::$cachedPlayers[$player->getName()]["group"];
            }else{
                $query = $this->getData()->query("SELECT * FROM `players` WHERE pseudo='" . $player->getName() . "'");
                return $query->fetch_array()["group"];
            }
        }else{
            if (Main::caching()){
                return Functions::$cachedPlayers[$player]["group"];
            }else{
                $query = $this->getData()->query("SELECT * FROM `players` WHERE pseudo='" . $player . "'");
                return $query->fetch_array()["group"];
            }
        }
    }

    /**
     * @throws ProviderErrorException
     */
    public function getFormatGroup(string $group)
    {
        if (Main::caching()){
            return Functions::$cachedGroup[$group]["format"];
        }else{
            $query = $this->getData()->query("SELECT * FROM `groups` WHERE `name`='" . $group . "'");
            return $query->fetch_array()["format"] ? $query->fetch_array()["format"] : "<{playerName}> {msg}";
        }
    }

    public function setGroup(Player|string $player, string $group)
    {
        if ($player instanceof Player) {
            if (Main::caching()) {
                Functions::$cachedPlayers[$player->getName()]["group"] = $group;
                $this->updatePermissions($player);
            } else {
                Server::getInstance()->getAsyncPool()->submitTask(new MySQLAsyncTask(Functions::$mysqli, "UPDATE `players` SET `group`='" . $group . "' WHERE pseudo='" . $player->getName() . "'", function (MySQLAsyncTask $asyncTask, Server $server) use ($player){
                    $this->updatePermissions($player);
                }));
            }
        }else{
            if (Main::caching()) {
                Functions::$cachedPlayers[$player]["group"] = $group;
            } else {
                Server::getInstance()->getAsyncPool()->submitTask(new MySQLAsyncTask(Functions::$mysqli, "UPDATE `players` SET `group`='" . $group . "' WHERE pseudo='" . $player . "'"));
            }
        }
    }

    public function setFormat(string $group, string $format)
    {
        if (Main::caching()){
            Functions::$cachedGroup[$group]["format"] = $format;
        }else{
            Server::getInstance()->getAsyncPool()->submitTask(new MySQLAsyncTask(Functions::$mysqli, "UPDATE `groups` SET format='" . $format . "' WHERE `name`='" . $group . "'"));
        }
    }

    /**
     * @throws ProviderErrorException
     */
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
            $query = $this->getData()->query("SELECT * FROM `groups` WHERE `name`='" . $group . "'");
            $permissions = $query->fetch_array()["permissions"];
            $explode = explode(":", $permissions);
            $explode[] = $perm;
            Server::getInstance()->getAsyncPool()->submitTask(new MySQLAsyncTask(Functions::$mysqli, "UPDATE `groups` SET `permissions`='" . implode(":", $explode) . "' WHERE `name`='" . $group . "'", function (MySQLAsyncTask $task, Server $server) use ($group){
                foreach (Server::getInstance()->getOnlinePlayers() as $player){
                    if ($this->getPlayerGroup($player) === $group){
                        $this->updatePermissions($player);
                    }
                }
            }));
        }
    }

    /**
     * @throws ProviderErrorException
     */
    public function addPlayerPermission(Player|string $player, string $perm)
    {
        if ($player instanceof Player){
            if (Main::caching()){
                Functions::$cachedPlayers[$player->getName()]["permissions"][] = $perm;
                Main::getInstance()->updatePermissions($player);
            }else{
                $query = $this->getData()->query("SELECT * FROM `players` WHERE `pseudo`='" . $player->getName() . "'");
                $permissions = $query->fetch_array()["permissions"];
                $explode = explode(":", $permissions);
                $explode[] = $perm;
                Server::getInstance()->getAsyncPool()->submitTask(new MySQLAsyncTask(Functions::$mysqli, "UPDATE `players` SET `permissions`='" . implode(":", $explode) . "' WHERE `pseudo`='" . $player->getName() . "'", function (MySQLAsyncTask $asyncTask, Server $server) use ($player){
                    $this->updatePermissions($player);
                }));
            }
        }else{
            if (Main::caching()){
                Functions::$cachedPlayers[$player]["permissions"][] = $perm;
            }else{
                $query = $this->getData()->query("SELECT * FROM `players` WHERE `pseudo`='" . $player . "'");
                $permissions = $query->fetch_array()["permissions"];
                $explode = explode(":", $permissions);
                $explode[] = $perm;
                Server::getInstance()->getAsyncPool()->submitTask(new MySQLAsyncTask(Functions::$mysqli, "UPDATE `players` SET `permissions`='" . implode(":", $explode) . "' WHERE `pseudo`='" . $player . "'"));
            }
        }
    }

    /**
     * @throws ProviderErrorException
     */
    public function getGroupPermissions(string $group): array
    {
        if (Main::caching()){
            return Functions::$cachedGroup[$group]["permissions"];
        }else{
            $query = $this->getData()->query("SELECT * FROM `groups` WHERE `name`='" . $group . "'");
            return $query->fetch_array()["permissions"] === "" ? explode(":", $query->fetch_array()["permissions"]) : [];
        }
    }

    /**
     * @throws ProviderErrorException
     */
    public function getPlayerPermissions(string|Player $player): array
    {
        if ($player instanceof Player) {
            if (Main::caching()) {
                return Functions::$cachedPlayers[$player->getName()]["permissions"];
            } else {
                $query = $this->getData()->query("SELECT * FROM `players` WHERE `pseudo`='" . $player->getName() . "'");
                return $query->fetch_array()["permissions"] ? explode(":", $query->fetch_array()["permissions"]) : [];
            }
        }else{
            if (Main::caching()) {
                return Functions::$cachedPlayers[$player]["permissions"];
            } else {
                $query = $this->getData()->query("SELECT * FROM `players` WHERE `pseudo`='" . $player . "'");
                return $query->fetch_array()["permissions"] ? explode(":", $query->fetch_array()["permissions"]) : [];
            }
        }
    }

    /**
     * @throws ProviderErrorException
     */
    public function getPermissions(Player $player): array
    {
        $group = $this->getGroupPermissions($this->getPlayerGroup($player));
        $perms = $this->getPlayerPermissions($player);
        return array_merge($group, $perms);
    }

    /**
     * @throws ProviderErrorException
     */
    public function existGroupPermission(string $group, string $permission): bool
    {
        if (Main::caching()){
            return in_array($permission, Functions::$cachedGroup[$group]["permissions"]);
        }else{
            $query = $this->getData()->query("SELECT * FROM `groups` WHERE `name`='" . $group . "'");
            return in_array($permission, explode(":", $query->fetch_array()["permissions"]));
        }
    }

    /**
     * @throws ProviderErrorException
     */
    public function existPlayerPermission(Player|string $player, string $permission): bool
    {
        if ($player instanceof Player){
            if (Main::caching()){
                return in_array($permission, Functions::$cachedPlayers[$player->getName()]["permissions"]);
            }else{
                $query = $this->getData()->query("SELECT * FROM `players` WHERE `pseudo`='" . $player->getName() . "'");
                return in_array($permission, explode(":", $query->fetch_array()["permissions"]));
            }
        }else{
            if (Main::caching()){
                return in_array($permission, Functions::$cachedPlayers[$player]["permissions"]);
            }else{
                $query = $this->getData()->query("SELECT * FROM `players` WHERE `pseudo`='" . $player . "'");
                return in_array($permission, explode(":", $query->fetch_array()["permissions"]));
            }
        }
    }

    /**
     * @throws ProviderErrorException
     */
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
            $query = $this->getData()->query("SELECT * FROM `groups` WHERE `name`='" . $group . "'");
            $permissions = $query->fetch_array()["permissions"];
            $explode = explode(":", $permissions);
            $array = [];
            foreach ($explode as $id => $permission){
                if ($permission !== $perm){
                    $array[] = $permission;
                }
            }
            Server::getInstance()->getAsyncPool()->submitTask(new MySQLAsyncTask(Functions::$mysqli, "UPDATE `groups` SET `permissions`='" . implode(":", $array) . "' WHERE `name`='" . $group . "'", function (MySQLAsyncTask $task, Server $server) use ($group){
                foreach (Server::getInstance()->getOnlinePlayers() as $player){
                    if ($this->getPlayerGroup($player) === $group){
                        Main::getInstance()->updatePermissions($player);
                    }
                }
            }));
        }
    }

    /**
     * @throws ProviderErrorException
     */
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
                $query = $this->getData()->query("SELECT * FROM `players` WHERE `pseudo`='" . $player->getName() . "'");
                $permissions = $query->fetch_array()["permissions"];
                $explode = explode(":", $permissions);
                $array = [];
                foreach ($explode as $id => $permission){
                    if ($permission !== $perm){
                        $array[] = $permission;
                    }
                }
                Server::getInstance()->getAsyncPool()->submitTask(new MySQLAsyncTask(Functions::$mysqli, "UPDATE `players` SET `permissions`='" . implode(":", $array) . "' WHERE `pseudo`='" . $player->getName() . "'", function (MySQLAsyncTask $asyncTask, Server $server) use ($player){
                    $this->updatePermissions($player);
                }));
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
                $query = $this->getData()->query("SELECT * FROM `players` WHERE `pseudo`='" . $player . "'");
                $permissions = $query->fetch_array()["permissions"];
                $explode = explode(":", $permissions);
                $array = [];
                foreach ($explode as $id => $permission){
                    if ($permission !== $perm){
                        $array[] = $permission;
                    }
                }
                Server::getInstance()->getAsyncPool()->submitTask(new MySQLAsyncTask(Functions::$mysqli, "UPDATE `players` SET `permissions`='" . implode(":", $array) . "' WHERE `pseudo`='" . $player . "'"));
            }
        }
    }

    /**
     * @throws ProviderErrorException
     */
    public function updateGroupForPlayers(string $groupDelete)
    {
        if (Main::caching()) {
            foreach (Functions::$cachedPlayers as $player => $value) {
                $data = Functions::$cachedPlayers[$player];
                if ($data["group"] === $groupDelete) {
                    $data["group"] = $this->getDefaultGroup();
                }
            }
        } else {
            $query = $this->getData()->query("SELECT * FROM `players`");
            foreach ($query->fetch_all() as $value) {
                if ($value[1] === $groupDelete) {
                    Server::getInstance()->getAsyncPool()->submitTask(new MySQLAsyncTask(Functions::$mysqli, "UPDATE `players` SET `group`='" . $this->getDefaultGroup() . "'"));
                }
            }
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
                foreach (PermissionManager::getInstance()->getPermissions() as $perm){
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