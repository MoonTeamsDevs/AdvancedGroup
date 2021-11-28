<?php

namespace MoonTeam\AdvancedGroup\tasks\async;

use MongoDB\BSON\Unserializable;
use MoonTeam\AdvancedGroup\provider\MySQLProvider;
use MoonTeam\AdvancedGroup\utils\Functions;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class MySQLAsyncCache extends AsyncTask {

    public array $cache = [];
    public static $callable1;
    private $host;
    private $username;
    private $password;
    private $database;
    private $port;

    public function __construct(string $host, string $username, string $password, string $database, int $port)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
    }

    public function onRun()
    {
        $cache = [];
        $mysqli = new \mysqli($this->host, $this->username, $this->password, $this->database, $this->port);
        $query = $mysqli->query("SELECT * FROM `groups`");
        foreach ($query->fetch_all() as $value){
            $cache[$value[0]] = [
                "permissions" => (!empty($value[1]) ? explode(":", $value[1]) : []),
                "default" => (bool)$value[2],
                "format" => $value[3],
                "name" => $value[0]
            ];
            if ($value[2] === true){
                Functions::$defaultGroup = $value[0];
            }
        }
        $this->setResult($cache);
    }

    public function onCompletion(Server $server)
    {
        Functions::$cachedGroup = $this->getResult();
    }

}