<?php

namespace API\Service;

use API\Database\Db;

class ConfigService
{
    private static array $config;

    

    public static function getConfig()
    {
        if (empty(self::$config)) {
            self::$config = self::fetchConfig();
        }

        return self::$config;
    }

    /**
     * @TODO cache
     * @return mixed
     */

    public static function fetchConfig()
    {
        global $g5;
        return Db::getInstance()->run("SELECT * FROM `{$g5['config_table']}`")->fetch();
    }
}
