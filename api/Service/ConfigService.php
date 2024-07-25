<?php

namespace API\Service;

use API\Database\Db;

class ConfigService
{
    private string $table;
    private array $config;

    public function __construct()
    {
        global $g5;
        $this->table = $g5['config_table'];
    }

    public function getConfig()
    {
        if (empty($this->config)) {
            $this->config = $this->fetchConfig();
        }

        return $this->config;
    }

    public function fetchConfig()
    {
        $stmt = Db::getInstance()->run("SELECT * FROM {$this->table}");

        return $stmt->fetch();
    }
}
