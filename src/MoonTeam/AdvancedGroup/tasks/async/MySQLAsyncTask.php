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

    public function onRun()
    {
        $database = $this->mysql;
        $db = new \mysqli($database["host"], $database["username"], $database["password"], $database["database"], $database["port"]);
        $db->query($this->query);
    }

    public function onCompletion(Server $server)
    {
        if ($this->call !== null){
            call_user_func($this->call, $this, $server);
        }
    }

}