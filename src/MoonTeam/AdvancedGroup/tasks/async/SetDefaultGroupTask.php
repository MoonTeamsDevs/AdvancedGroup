<?php

namespace MoonTeam\AdvancedGroup\tasks\async;

use MoonTeam\AdvancedGroup\utils\Functions;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class SetDefaultGroupTask extends AsyncTask {

    private array $mysql;

    public function __construct(array $mysql)
    {
        $this->mysql = $mysql;
    }

    public function onRun(): void
    {
        $database = $this->mysql;
        $db = new \mysqli($database["host"], $database["username"], $database["password"], $database["database"], $database["port"]);
        $query = $db->query("SELECT * FROM `groups`");
        if (!is_null($query)) {
            foreach ($query->fetch_all() as $value) {
                if ((bool)$value[2] === true) {
                    $this->setResult($value[0]);
                }
            }
            $query->close();
        }else{
            $this->cancelRun();
        }
    }

    public function onCompletion(): void
    {
        Functions::$defaultGroup = $this->getResult();
    }

}