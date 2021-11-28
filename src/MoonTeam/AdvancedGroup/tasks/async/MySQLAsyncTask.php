<?php

namespace MoonTeam\AdvancedGroup\tasks\async;

use MoonTeam\AdvancedGroup\provider\MySQLProvider;
use MoonTeam\AdvancedGroup\utils\Functions;
use pocketmine\scheduler\AsyncTask;

class MySQLAsyncTask extends AsyncTask {

    private string $query;
    private array $mysql;

    public function __construct(array $mysql, string $query)
    {
        $this->mysql = $mysql;
        $this->query = $query;
    }

    public function onRun()
    {
        $database = $this->mysql;
        $db = new \mysqli($database["host"], $database["username"], $database["password"], $database["database"], $database["port"]);
        $db->query($this->query);
    }

}