<?php

namespace SIR\Database;


use PDO;
use PDOException;

/**
 * Class Db
 * PDO Wrapper
 */
class Db
{
    private static $instance = null;

    /**
     * @var ?PDO PDO 객체
     */
    private $pdo;

    private function __construct()
    {
        require_once __DIR__ . '/../../data/dbconfig.php';
        $db_settings = [
            'driver' => 'mysql', // @todo
            'host' => G5_MYSQL_HOST,
            'dbname' => G5_MYSQL_DB,
            'user' => G5_MYSQL_USER,
            'password' => G5_MYSQL_PASSWORD
        ];

        try {
            $this->pdo = new PDO(
                "{$db_settings['driver']}:host={$db_settings['host']};dbname={$db_settings['dbname']}",
                $db_settings['user'],
                $db_settings['password'],

                // PHP 7.1 이상이므로 에뮬레이팅 필요없음
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            //@todo 앱 전역 핸들러에서 처리
            error_log("Connection failed: " . $e->getMessage());
            throw new DbConnectException("Database connection failed", -1);
        }
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Db();
        }
        return self::$instance;
    }


    /**
     * PDO 객체를 반환함.
     * @return PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }
    

    public function __destruct()
    {
        $this->pdo = null;
    }


}