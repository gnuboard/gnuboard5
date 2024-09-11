<?php

namespace API\Service;

use API\Database\Db;

class ConfigService
{
    private static ?array $config = null;

    public static function getConfig()
    {
        if (self::$config === null) {
            self::$config = self::fetchConfig();
        }

        return self::$config;
    }

    /**
     * @return mixed
     */

    public static function fetchConfig()
    {
        global $g5;
        return Db::getInstance()->run("SELECT * FROM `{$g5['config_table']}`")->fetch();
    }
}
