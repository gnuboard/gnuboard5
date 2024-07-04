<?php

namespace API\Database;


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

            //mysql 0000 허용
            if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' || $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mariadb') {
                $this->pdo->exec("SET SESSION sql_mode = 'ALLOW_INVALID_DATES'");
            }
        } catch (PDOException $e) {
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

    /**
     * 쿼리를 실행하고 PDO 객체를 반환함.
     * @param string $query
     * @param array $params
     * @return \PDOStatement
     */
    public function run($query, $params = [])
    {
        if (!$params) {
            return $this->pdo->query($query);
        }

        $is_list = self::is_list($params);
        if ($is_list) {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        }

        $stmt = $this->pdo->prepare($query);
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindParam($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindParam($key, $value);
            }
        }

        $stmt->execute($params);
        if(G5_DEBUG) {
            $this->logging_last_stmt($stmt);
        }
        return $stmt;
    }

    /**
     * insert 쿼리
     * @param $table
     * @param array $data associative array
     * @return false|string
     */
    public function insert($table, array $data)
    {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $this->run("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})", array_values($data));

        return $this->pdo->lastInsertId();
    }

    /**
     * 업데이트 쿼리
     * @param string $table
     * @param array $data [column => value]
     * @param array $where [column => value]
     * @return int
     */
    public function update($table, array $data, $where)
    {
        $values = [];

        $fields = null;
        foreach ($data as $key => $value) {
            $key = '`' . trim($key, '`') . '`';
            $fields .= "$key = ?,";
            $values[] = $value;
        }
        $fields = rtrim($fields, ',');

        $whereCondition = null;
        $i = 0;
        foreach ($where as $key => $value) {
            $key = '`' . trim($key, '`') . '`';
            $whereCondition .= $i == 0 ? "$key = ?" : " AND $key = ?";
            $values[] = $value;
            $i++;
        }

        $query = "UPDATE $table SET $fields WHERE {$whereCondition}";
        return $this->run($query, $values)->rowCount();
    }

    /**
     * 삭제 쿼리
     * @param string $table
     * @param array $where [column => value]
     * @param ?int $limit
     * @return int
     */
    public function delete($table, $where, $limit = null)
    {
        $values = array_values($where);

        $whereCondition = null;
        $i = 0;
        foreach ($where as $key => $value) {
            $key = '`' . trim($key, '`') . '`';
            $whereCondition .= $i == 0 ? "$key = ?" : " AND $key = ?";
            $i++;
        }

        // limit 제한시
        if (is_numeric($limit)) {
            $limit = "LIMIT $limit";
        }

        $stmt = $this->run("DELETE FROM {$table} WHERE {$whereCondition} {$limit}", $values);
        return $stmt->rowCount();
    }

    /**
     * @param string $table
     * @param string $id_column
     * @param string $id_value
     * @return int
     */
    public function deleteById($table, $id_column, $id_value)
    {
        $stmt = $this->run("DELETE FROM {$table} WHERE {$id_column} = ?", [$id_value]);
        return $stmt->rowCount();
    }

    /**
     * id 컬럼 기준으로 여러 행을 지운다.
     * @param string $table
     * @param string $id_column
     * @param string $id_values
     * @return int 지워진 행 수
     */
    public function deleteByIds($table, $id_column, $id_values)
    {
        $stmt = $this->run("DELETE FROM {$table} WHERE {$id_column} IN ({$id_values})");
        return $stmt->rowCount();
    }

    /**
     * 테이블의 모든 데이터를 지운다.
     * @param string $table
     * @return int
     */
    public function deleteAll($table)
    {
        $stmt = $this->run("DELETE FROM {$table}");
        return $stmt->rowCount();
    }

    private static function is_list(array $array)
    {
        if ([] === $array || $array === array_values($array)) {
            return true;
        }

        $nextKey = -1;

        foreach ($array as $k => $v) {
            if ($k !== ++$nextKey) {
                return false;
            }
        }

        return true;
    }


    public function logging_last_stmt($stmt)
    {
        error_log($stmt->queryString);
        ob_start();
        $stmt->debugDumpParams();
        $paramInfo = ob_get_clean();
        //@todo app에서 관리하는 로깅으로 변경필요.
        error_log("Parameter info: \n" . $paramInfo);
    }


}