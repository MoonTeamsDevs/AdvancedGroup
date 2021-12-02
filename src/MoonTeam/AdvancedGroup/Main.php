<?php

namespace MoonTeam\AdvancedGroup;

use http\Exception\RuntimeException;
use MoonTeam\AdvancedGroup\commands\administrator\AddGPerm;
use MoonTeam\AdvancedGroup\commands\administrator\AddGroup;
use MoonTeam\AdvancedGroup\commands\administrator\AddPPerm;
use MoonTeam\AdvancedGroup\commands\administrator\Groups;
use MoonTeam\AdvancedGroup\commands\administrator\ListGPerms;
use MoonTeam\AdvancedGroup\commands\administrator\ListPPerms;
use MoonTeam\AdvancedGroup\commands\administrator\RemoveGPerm;
use MoonTeam\AdvancedGroup\commands\administrator\RemoveGroup;
use MoonTeam\AdvancedGroup\commands\administrator\RemovePPerm;
use MoonTeam\AdvancedGroup\commands\administrator\SetFormat;
use MoonTeam\AdvancedGroup\commands\administrator\SetGroup;
use MoonTeam\AdvancedGroup\listeners\PlayerListener;
use MoonTeam\AdvancedGroup\provider\JSONProvider;
use MoonTeam\AdvancedGroup\provider\MySQLProvider;
use MoonTeam\AdvancedGroup\provider\YAMLProvider;
use MoonTeam\AdvancedGroup\tasks\async\MySQLAsyncCache;
use MoonTeam\AdvancedGroup\tasks\async\MySQLAsyncCachePlayers;
use MoonTeam\AdvancedGroup\tasks\async\SetDefaultGroupTask;
use MoonTeam\AdvancedGroup\utils\Functions;
use pocketmine\permission\PermissionAttachment;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\uuid\UUID;

class Main extends PluginBase {

    public static self $instance;
    public static array $extensions = [];

    private $provider;

    public static function getInstance(): self{
        return self::$instance;
    }

    /**
     * @throws provider\ProviderErrorException
     */
    protected function onEnable(): void
    {
        self::$instance = $this;

        $provider = $this->getConfig()->get("provider");
        if ($provider === "mysql" || $provider === "sql"){
            if ($this->getConfig()->get("cache") !== true){
                $this->getLogger()->notice("It is better to activate the cache if you use mysql or sql as provider.");
            }
        }

        Functions::$PUBLIC_DATA = $this->getDataFolder();

        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener(), $this);

        $this->initProvider();
        if (self::caching()) {
            $this->initCache();
        }
        $this->initCommands();
        $this->initExtension();
    }

    protected function onDisable(): void
    {
        $this->getProvider()->saveGroupData(Functions::$cachedGroup);
        if (Main::caching()) {
            if (!empty(Server::getInstance()->getOnlinePlayers())) {
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    if ($player instanceof Player) {
                        $this->getProvider()->savePlayerData($player);
                        $this->unregisterPlayer($player);
                    }
                }
            }
        }else{
            if (!empty(Server::getInstance()->getOnlinePlayers())) {
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    if ($player instanceof Player) {
                        $this->unregisterPlayer($player);
                    }
                }
            }
        }
    }

    /**
     * @throws provider\ProviderErrorException
     */
    public function initProvider(){
        switch ($this->getConfig()->get("provider")){
            case "mysql":
                $database = $this->getConfig()->get("database");
                Functions::$mysqli = [
                    "host" => $database["host"],
                    "username" => $database["username"],
                    "password" => $database["password"],
                    "database" => $database["database"],
                    "port" => $database["port"]
                ];
                $this->provider = new MySQLProvider();
                $this->getServer()->getAsyncPool()->submitTask(new SetDefaultGroupTask(Functions::$mysqli));
                break;
            case "yaml":
                $this->provider = new YAMLProvider();
                break;
            case "json":
                $this->provider = new JSONProvider();
                break;
        }
    }

    public function getProvider(): JSONProvider|YAMLProvider|MySQLProvider{
        return $this->provider;
    }

    public static function caching(): bool{
        return Main::getInstance()->getConfig()->get("cache");
    }

    public function initCache(): void{
        $provider = $this->getProvider();
        if ($provider instanceof MySQLProvider){
            $database = new Config(Functions::$PUBLIC_DATA . "config.yml", Config::YAML);
            $database = $database->get("database");
            Server::getInstance()->getAsyncPool()->submitTask(new MySQLAsyncCache($database["host"], $database["username"], $database["password"], $database["database"], $database["port"]));
            Server::getInstance()->getAsyncPool()->submitTask(new MySQLAsyncCachePlayers($database["host"], $database["username"], $database["password"], $database["database"], $database["port"]));
        }
        if ($provider instanceof YAMLProvider || $provider instanceof JSONProvider){
            $array = [];
            foreach ($provider->getPlayersData()->getAll() as $player => $value){
                $data = $provider->getPlayersData()->get($player);
                $array[$player] = [
                    "permissions" => (!empty($data["permissions"]) ? $data["permissions"] : []),
                    "group" => $data["group"]
                ];
            }
            Functions::$cachedPlayers = $array;
            $array = [];
            foreach ($provider->getGroupData()->getAll() as $group => $value){
                $data = $provider->getGroupData()->get($group);
                $array[$group] = [
                    "permissions" => (!empty($data["permissions"]) ? $data["permissions"] : []),
                    "default" => $data["default"],
                    "format" => $data["format"]
                ];
            }
            Functions::$cachedGroup = $array;
        }
    }

    private function initCommands(){
        $this->getServer()->getCommandMap()->registerAll("AdvancedGroup", [
            new AddGroup("addgroup", "Allows you to add a new group.", "addgroup", []),
            new RemoveGroup("removegroup", "Allows you to delete a group.", "removegroup", []),
            new Groups("groups", "Allows you to see the existing groups.", "groups", []),
            new SetGroup("setgroup", "Allows you to define a player's group.", "setgroup", []),
            new SetFormat("setformat", "Allows you to redefine the format of a group.", "setformat", []),
            new AddGPerm("addgperm", "Allows you to add a permission to a group.", "addgperm", []),
            new AddPPerm("addpperm", "Allows you to add a permission to a player.", "addpperm", []),
            new ListGPerms("listgperms", "Allows you to see the list of permissions for a group.", "listgperm", []),
            new ListPPerms("listpperms", "Allows you to see the list of permissions for a player.", "listpperm", []),
            new RemoveGPerm("removegperm", "Allows you to remove a permission to a group.", "removegperm", ["rmgperm"]),
            new RemovePPerm("removepperm", "Allows you to remove a permission from a player.", "removepperm", ["rmpperm"])
        ]);
    }

    public function initExtension(): void{
        foreach ($this->getConfig()->get("extensions") as $name => $value){
            if ($value === true){
                $plugin = $this->getServer()->getPluginManager()->getPlugin($name);
                if (is_null($plugin)){
                    $this->getLogger()->error("The $name plugin was not found. Please download it.");
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }else{
                    $this->getLogger()->notice("The extension for the $name plugin has been activated.");
                }
            }
            Main::$extensions[$name] = $value;
        }
    }

    /** Thanks to PurePerm */
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

    /**
     * @throws provider\ProviderErrorException
     */
    public function registerPlayer(Player $player){
        $uuid = $this->getValidUUID($player);

        if (!isset(Functions::$attachments[$uuid])){
            $attachment = $player->addAttachment($this);
            Functions::$attachments[$uuid] = $attachment;
            $this->updatePermissions($player);
        }
    }

    public function isRegistered(Player $player): bool{
        $uuid = $this->getValidUUID($player);
        return isset(Functions::$attachments[$uuid]);
    }


    /**
     * @throws provider\ProviderErrorException
     */
    public function updatePermissions(Player $player){
        $permissions = [];
        $provider = $this->getProvider();
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

    public function unregisterPlayer(Player $player){
        $uuid = $this->getValidUUID($player);

        if ($uuid != null){
            if (isset(Functions::$attachments[$uuid])){
                $player->removeAttachment(Functions::$attachments[$uuid]);
            }
            unset(Functions::$attachments[$uuid]);
        }
    }

}