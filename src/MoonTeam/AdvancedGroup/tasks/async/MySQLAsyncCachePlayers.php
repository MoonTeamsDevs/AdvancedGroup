<?php

namespace MoonTeam\AdvancedGroup\tasks\async;

use MoonTeam\AdvancedGroup\utils\Functions;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class MySQLAsyncCachePlayers extends AsyncTask {

    public array $cache = [];
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
        $query = $mysqli->query("SELECT * FROM `players`");
        if (!is_null($query)) {
            foreach ($query->fetch_all() as $value) {
                $cache[$value[0]] = [
                    "group" => $value[1],
                    "permissions" => (!empty($value[2]) ? explode(":", $value[2]) : [])
                ];
            }
            $query->close();
            $this->setResult($cache);
        }else{
            $this->cancelRun();
        }
    }

    public function onCompletion(Server $server)
    {
        Functions::$cachedPlayers = $this->getResult();
    }

}