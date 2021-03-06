<?php

namespace MoonTeam\AdvancedGroup\tasks\async;

use MoonTeam\AdvancedGroup\provider\MySQLProvider;
use MoonTeam\AdvancedGroup\utils\Functions;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class MySQLAsyncTask extends AsyncTask {

    private string $query;
    private array $mysql;
    private $call;

    public function __construct(array $mysql, string $query, callable $call = null)
    {
        $this->mysql = $mysql;
        $this->query = $query;
        $this->call = $call;
    }

    public function onRun(): void
    {
        $database = $this->mysql;
        $db = new \mysqli($database["host"], $database["username"], $database["password"], $database["database"], $database["port"]);
        $query = $db->query($this->query);
        if (is_null($query)){
            $this->cancelRun();
        }
        $query->close();
    }

    public function onCompletion(): void
    {
        if ($this->call !== null){
            call_user_func($this->call, $this, Server::getInstance());
        }
    }

}