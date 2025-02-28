<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

/**
 * 
 * https://github.com/MadByAd/MPL-MySQL
 * The MySQL Query class is used for running MySQL queries
 * 
 * @author    MadByAd <adityaaw84@gmail.com>
 * @license   MIT License
 * @copyright Copyright (c) MadByAd 2024
 * 
 */

function get_pdo_connection() {
    global $g5;
    
    if (!(defined('G5_USE_DB_PDO') && G5_USE_DB_PDO)) {
        return $g5['connect_db'];
    }
    
    // $g5['connect_db']가 PDO인지 확인
    if (is_object($g5['connect_db']) && $g5['connect_db'] instanceof PDO) {
        // 이미 PDO라면 그대로 반환
        return $g5['connect_db'];
    }
    
    // $g5['connect_pdo_db']가 이미 존재하고 PDO 객체인지 확인
    if (isset($g5['connect_pdo_db']) && is_object($g5['connect_pdo_db']) && $g5['connect_pdo_db'] instanceof PDO) {
        // 기존 PDO 연결 재활용
        return $g5['connect_pdo_db'];
    }
    
    // PDO 연결 생성
    try {
        $dsn = "mysql:host=".G5_MYSQL_HOST.";dbname=".G5_MYSQL_DB.";charset=utf8";
        $g5['connect_pdo_db'] = new PDO($dsn, G5_MYSQL_USER, G5_MYSQL_PASSWORD);
        $g5['connect_pdo_db']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $g5['connect_pdo_db']->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // echo "Database connection established successfully.";
        
    } catch (PDOException $e) {
        // 연결 실패 시 에러 처리
        die("PDO Connection failed: " . $e->getMessage());
    }
}

class MySQLQueryFailToExecuteException extends Exception
{}

class MySQLQueryFailToBindException extends Exception
{}

class MySQLNoConnectionException extends Exception
{}

class G5MySQLQuery
{
    /**
     * Store the MySQLi connection
     * 
     * @var mysqli
     */
    private $connection = null;

    /**
     * Store the prepared query
     * 
     * @var mysqli_stmt
     */
    private $preparedQuery = null;
    
    private $boundValues = array(); // 바인딩된 값 저장
    
    private $queryString = ''; // 원본 쿼리문 저장
    private $errorMessage = '';
    private $errorCode = '';
    private $sqlState = '';
    
    /**
     * Construct a new query
     * 
     * @param string $query      The query
     * @param mysqli|null $connection The connection, if null then it will use
     *                                the default connection on the MySQL class
     * 
     * @throws MySQLNoConnectionException If no connection is supplied
     */
    public function __construct($query, $connection = null)
    {
        global $g5;
        
        /*
        if (class_exists('PDO') && is_object($g5['connect_db']) && $g5['connect_db'] instanceof PDO) {
        // PDO 사용 가능
        }
        */
        
        $this->connection = ($connection == null) ? get_pdo_connection() : $connection;
        
        if ($this->connection == null) {
            throw new MySQLNoConnectionException("Error: no MySQL connection is supplied");
        }
        
        $this->queryString = $query; // 쿼리문 저장
        
        if (defined('G5_USE_DB_PDO') && G5_USE_DB_PDO) {
            // PDO 모드
            $this->preparedQuery = $this->connection->prepare($query);
            if ($this->preparedQuery === false) {
                $errorInfo = $this->connection->errorInfo();
                $this->errorMessage = $errorInfo[2];
                $this->errorCode = $errorInfo[1];
                $this->sqlState = $errorInfo[0];
                throw new Exception($this->errorMessage);
            }
        } else {
            // MySQLi 모드
            $this->preparedQuery = mysqli_prepare($this->connection, $query);
        }
    }

    /**
     * Bind values to the query (using this method will prevent SQL injection)
     * 
     * @param mixed ...$values The values
     * 
     * @return void
     * 
     * @throws MySQLQueryFailToBindException if failed to bind the values
     */
    public function bind()
    {
        $args = func_get_args();
        $types = '';
        $valueToBind = array();
        
        foreach ($args as $value) {
            if (is_array($value)) {
                foreach ($value as $subValue) {
                    $this->addBindTypeAndValue($subValue, $types, $valueToBind);
                }
            } else {
                $this->addBindTypeAndValue($value, $types, $valueToBind);
            }
        }
        
        $this->boundValues = $valueToBind;

        if (defined('G5_USE_DB_PDO') && G5_USE_DB_PDO) {
            // PDO 바인딩
            foreach ($valueToBind as $index => $value) {
                $paramType = $this->getPDOParamType($value);
                $this->preparedQuery->bindValue($index + 1, $value, $paramType);
            }
        } else {
            // MySQLi 바인딩
            $params = array_merge(array($this->preparedQuery, $types), $valueToBind);
            if (!call_user_func_array('mysqli_stmt_bind_param', $this->refValues($params))) {
                throw new MySQLQueryFailToBindException("Failed to bind (" . count($args) . ") values to the query");
            }
        }
        
    }

    private function addBindTypeAndValue($value, &$types, &$valueToBind)
    {
        if (is_int($value)) {
            $types .= "i";
        } elseif (is_float($value)) {
            $types .= "d";
        } elseif (is_numeric($value) && ctype_digit($value)) {
            $value = preg_replace('/[^0-9]/', '', $value);
            $types .= (strlen($value) > 1 && preg_match('/^0.+/', $value)) ? "s" : "i";
        } elseif (is_numeric($value)) {
            $value = (float) $value;
            $types .= "d";
        } else {
            $types .= "s";
        }
        $valueToBind[] = $value;
    }
    /*
    private function getPDOParamType($value)
    {
        if (is_int($value)) {
            return PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            return PDO::PARAM_BOOL;
        } elseif ($value === null) {
            return PDO::PARAM_NULL;
        } else {
            return PDO::PARAM_STR;
        }
    }
    */
    
    private function getPDOParamType($value)
    {
        if (is_int($value)) {
            return PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            return PDO::PARAM_BOOL;
        } elseif (is_null($value)) { // null 비교는 === 대신 is_null 사용
            return PDO::PARAM_NULL;
        } elseif (is_float($value)) {
            return PDO::PARAM_STR; // 실수는 문자열로 처리 (MariaDB 호환)
        } elseif (is_numeric($value)) {
            // 숫자형 문자열 ("1", "123.45") 처리
            if (ctype_digit($value) && !preg_match('/^0[0-9]+$/', $value)) {
                return PDO::PARAM_INT; // "123" -> 정수
            }
            return PDO::PARAM_STR; // "123.45", "0123" -> 문자열
        } else {
            return PDO::PARAM_STR;
        }
    }

    /**
     * Execute the query
     * 
     * @return void
     * 
     * @throws MySQLQueryFailToExecuteException if failed to execute the query
     */
    public function execute()
    {
        if (defined('G5_USE_DB_PDO') && G5_USE_DB_PDO) {
            // PDO 모드
            if ($this->preparedQuery === false) {
                $errorInfo = $this->connection->errorInfo();
                $this->errorMessage = $errorInfo[2] ?: "Failed to prepare the statement";
                $this->errorCode = $errorInfo[1] ?: "N/A";
                $this->sqlState = $errorInfo[0] ?: "N/A";
                throw new Exception($this->errorMessage);
            }

            if (!$this->preparedQuery->execute()) {
                $errorInfo = $this->preparedQuery->errorInfo();
                $this->errorMessage = $errorInfo[2] ?: "Unknown error occurred";
                $this->errorCode = $errorInfo[1] ?: "N/A";
                $this->sqlState = $errorInfo[0] ?: "N/A";
                throw new Exception($this->errorMessage);
            }

            return $this->preparedQuery;
            
            // LOCK TABLES는 결과를 반환하지 않으므로 true 반환
            // return true; // PDOStatement 대신 true로 통일
        
        } else {
            // MySQLi 모드
            if ($this->preparedQuery === false || !($this->preparedQuery instanceof mysqli_stmt)) {
                $this->errorMessage = mysqli_error($this->connection) ?: "Failed to prepare the statement";
                $this->errorCode = mysqli_errno($this->connection) ?: "N/A";
                $this->sqlState = mysqli_sqlstate($this->connection) ?: "N/A";
                throw new Exception($this->errorMessage);
            }

            if (!mysqli_stmt_execute($this->preparedQuery)) {
                $stmtErrorMessage = mysqli_stmt_error($this->preparedQuery);
                $stmtErrorCode = mysqli_stmt_errno($this->preparedQuery);
                $stmtSqlState = mysqli_stmt_sqlstate($this->preparedQuery);

                $connErrorMessage = mysqli_error($this->connection);
                $connErrorCode = mysqli_errno($this->connection);
                $connSqlState = mysqli_sqlstate($this->connection);

                $this->errorMessage = $stmtErrorMessage ?: $connErrorMessage ?: "Unknown error occurred";
                $this->errorCode = $stmtErrorCode ?: $connErrorCode ?: "N/A";
                $this->sqlState = $stmtSqlState ?: $connSqlState ?: "N/A";
                throw new Exception($this->errorMessage);
            }

            $result = mysqli_stmt_get_result($this->preparedQuery);
            return $result ? $result : true;
        }
    }
    
    public function sql_error_print($e) {
        
        // 에러 정보 설정
        $errorMessage = $e->getMessage();
        $errorCode = $e->getCode();
        $sqlState = 'N/A'; // G5MySQLQuery에서 SQLSTATE를 별도로 제공하지 않으면 기본값
        $queryString = method_exists($this, 'getQuery') ? $this->getQuery() : 'Unknown Query';

        // G5MySQLQuery에서 직접 에러 정보를 가져오기 위해 추가 확인
        if (property_exists($this, 'errorMessage') && property_exists($this, 'errorCode') && property_exists($this, 'sqlState')) {
            $errorMessage = $this->errorMessage ?: $errorMessage;
            $errorCode = $this->errorCode ?: $errorCode;
            $sqlState = $this->sqlState ?: $sqlState;
        }
        
        // 호출 스택에서 에러 호출 지점 찾기
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $callerFile = $_SERVER['SCRIPT_NAME']; // 기본값
        $callerLine = 'N/A'; // 기본값

        foreach ($stack as $trace) {
            if (isset($trace['function']) && $trace['function'] === 'sql_bind_select_fetch') {
                $callerFile = $trace['file'] ?? $_SERVER['SCRIPT_NAME'];
                $callerLine = $trace['line'] ?? 'N/A';
                break;
            }
        }
        
        $errorOutput = "Query: " . $queryString . "\n" .
                        "SQLSTATE: " . $sqlState . "\n" .
                        "Error Code: " . $errorCode . "\n" .
                        "Error Message: " . $errorMessage . "\n" .
                        "File: " . $callerFile . "\n" .
                        "Line: " . $callerLine . "\n" .
                        "Executed Query: " . $queryString;

        $errorOutput .= "\nException in " . $e->getFile() . " on line " . $e->getLine();

        echo $errorOutput;
    }
    
    public function get_num_rows()
    {
        if (defined('G5_USE_DB_PDO') && G5_USE_DB_PDO) {
            return $this->preparedQuery->rowCount();
        } else {
            // https://www.php.net/manual/en/mysqli-stmt.num-rows.php
            /* store the result in an internal buffer */
            
            mysqli_stmt_store_result($this->preparedQuery);
            
            return mysqli_stmt_num_rows($this->preparedQuery);
        }
    }
    
    /**
     * Return the result of the query executed
     * 
     * @return array
     */
    public function result($result)
    {
        $rows = array();
        if (defined('G5_USE_DB_PDO') && G5_USE_DB_PDO) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $rows[] = $row;
            }
        } else {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }
    
    // 한행만 리턴
    public function result_fetch($result)
    {
        if (!$result) return array();
        
        if (defined('G5_USE_DB_PDO') && G5_USE_DB_PDO) {
            return $result->fetch(PDO::FETCH_ASSOC) ?: array();
        } else {
            return @mysqli_fetch_assoc($result) ?: array();
        }
        
        /*
        try {
            $row = @mysqli_fetch_assoc($result);
        } catch (Exception $e) {
            $row = null;
        }

        return $row;
        */
    }
    
    /**
     * Utility function to pass references for call_user_func_array
     * 
     * @param array $arr The array to be passed as references
     * 
     * @return array
     */
    private function refValues($arr)
    {
        $refs = array();
        foreach ($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }
        return $refs;
    }
    
    /**
     * 디버그용으로 바인딩된 쿼리를 출력하는 메서드
     * 
     * @return string 실행된 쿼리문
     */
    public function getQuery()
    {
        $query = $this->queryString;
        $values = $this->boundValues;

        foreach ($values as $value) {
            /*
            // 값이 문자열이면 작은따옴표로 감싸기
            if (is_string($value)) {
                $value = "'" . addslashes($value) . "'";
            } elseif ($value === null) {
                $value = "NULL";
            }
            */
            if ($value === null) {
                $value = "NULL";
            } elseif (is_numeric($value)) {
                if (ctype_digit($value) && strlen($value) > 1 && preg_match('/^0.+/', $value)) {
                    $value = "'" . addslashes($value) . "'";
                }
            } elseif (is_string($value)) {
                $value = "'" . addslashes($value) . "'";
            } else { 
                //if ((is_int($value) || is_float($value)) {
                //}
            }
            // ?를 바인딩된 값으로 하나씩 교체
            $query = preg_replace('/\?/', $value, $query, 1);
        }

        return $query;
    }
}

class G5MysqlCRUD
{
    
    private static $allowedOperators = array('=', '>', '<', '>=', '<=', '!=', 'LIKE', 'IN');
    
    /**
     * Read / Get a data from a table
     * 
     * @param string $table
     * @param array  $columns
     * @param array  $condition
     * @param array  $values
     * @param array  $readSettings
     * @param mixed  $link
     * 
     * @return array The readed / selected rows
     */
    public static function read($table, $columns = array(), $condition = array(), $values = array(), $readSettings = array(), $link = null, $is_fetches = 0)
    {
        global $g5;
        if ($link === null) $link = get_pdo_connection();

        // 테이블 이름 검증 (간단한 정규식)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new Exception("Invalid table name: $table");
        }
        
        // $columnString = empty($columns) ? "*" : implode(",", $columns);
        
        // 컬럼 검증
        $columnString = empty($columns) ? "*" : implode(",", array_map(function($col) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) {
                throw new Exception("Invalid column name: $col");
            }
            return $col;
        }, (array)$columns));
        
        $conditionString = self::buildConditionString($condition);

        $query = self::buildSelectQuery($table, $columnString, $conditionString, $readSettings);
        $queryObj = new G5MySQLQuery($query, $link);

        $valueToBind = self::prepareBindValues($values, $readSettings);
        if (!empty($valueToBind)) {
            call_user_func_array(array($queryObj, 'bind'), $valueToBind);
        }

        $result = sql_query($queryObj);

        if ($is_fetches === 1) {
            return $queryObj->result_fetch($result);
        } elseif ($is_fetches === 2) {
            return $queryObj->result($result);
        }
        return $result;
    }
    
    /**
     * Create / insert a new data into a table
     * 
     * @param string $table      The table name
     * @param array  $columns    The columns to be inserted with data
     * @param array  $values     The values for the columns
     * @param mixed  $connection The mysqli connection if null then it will use the default connection
     * 
     * @return void
     */
    public static function insert($table, $columns, $values, $updateColumns = array(), $link = null)
    {
        global $g5;
        if ($link === null) $link = get_pdo_connection();

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new Exception("Invalid table name: $table");
        }
        
        $columns = array_map(function($col) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) {
                throw new Exception("Invalid column name: $col");
            }
            return $col;
        }, (array)$columns);
        
        list($valuePlaceholders, $bindValues) = self::prepareInsertValues($values);
        $columnString = implode(",", $columns);
        $valueString = implode(",", $valuePlaceholders);

        $updateString = self::buildUpdateString($updateColumns, $bindValues);
        $query = "INSERT INTO {$table} ({$columnString}) VALUES ({$valueString}){$updateString}";
        $queryObj = new G5MySQLQuery($query, $link);

        if (!empty($bindValues)) {
            call_user_func_array(array($queryObj, 'bind'), $bindValues);
        }
        return sql_query($queryObj);
    }
    
    /**
     * Update a column on a table
     */
    public static function update($table, $columns, $columnValues, $condition = array(), $conditionValues = array(), $link = null)
    {
        global $g5;
        if ($link === null) $link = get_pdo_connection();

        // 테이블 이름 검증
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new Exception("Invalid table name: $table");
        }
        
        // 컬럼 검증 (업데이트할 컬럼)
        $validatedColumns = array_map(function($col) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) {
                throw new Exception("Invalid column name: $col");
            }
            return "$col = ?";
        }, (array)$columns);
        $columnString = implode(",", $validatedColumns);
    
        $conditionString = self::buildConditionString($condition);

        $query = "UPDATE {$table} SET {$columnString} " . ($conditionString ? "WHERE {$conditionString}" : "");
        $queryObj = new G5MySQLQuery($query, $link);

        $valuesToBind = array_merge($columnValues, $conditionValues);
        if (!empty($valuesToBind)) {
            call_user_func_array(array($queryObj, 'bind'), $valuesToBind);
        }
        return sql_query($queryObj);
    }
    
    /**
     * Delete a data from a table
     */
    public static function delete($table, $condition = array(), $values = array(), $connection = null)
    {
        global $g5;
        if ($connection === null) $connection = get_pdo_connection();

        // 테이블 이름 검증
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new Exception("Invalid table name: $table");
        }
    
        $conditionString = self::buildConditionString($condition);
        $query = "DELETE FROM {$table} " . ($conditionString ? "WHERE {$conditionString}" : "");
        $queryObj = new G5MySQLQuery($query, $connection);

        if (!empty($values)) {
            call_user_func_array(array($queryObj, 'bind'), $values);
        }
        return sql_query($queryObj);
    }
    
    /*
    private static function buildConditionString($condition)
    {
        if ($condition) {
            foreach ($condition as $index => $cond) {
                $condition[$index] = "({$cond})";
            }
            return implode(" AND ", $condition);
        }
        return '';
    }
    */
    
    private static function buildConditionString($condition)
    {
        if ($condition) {
            foreach ($condition as $index => $cond) {
                // 연산자와 컬럼 검증
                if (preg_match('/^([a-zA-Z0-9_]+)\s*([=><!]+|LIKE|IN)\s*\?$/i', $cond, $matches)) {
                    $column = $matches[1];
                    $operator = strtoupper($matches[2]);
                    if (!in_array($operator, self::$allowedOperators)) {
                        throw new Exception("Invalid column or operator in condition: $cond");
                    }
                } else {
                    throw new Exception("Invalid condition format: $cond");
                }
                $condition[$index] = "($cond)";
            }
            return implode(" AND ", $condition);
        }
        return '';
    }
    
    private static function buildSelectQuery($table, $columnString, $conditionString, $readSettings)
    {
        $limit = isset($readSettings['limit']) ? preg_replace('/[^0-9]/', '', $readSettings['limit']) : null;
        $offset = isset($readSettings['offset']) ? preg_replace('/[^0-9]/', '', $readSettings['offset']) : null;
        $groupBy = isset($readSettings['groupBy']) ? preg_replace('/[^a-z0-9_ \,\.]/i', '', $readSettings['groupBy']) : null;
        $orderBy = isset($readSettings['orderBy']) ? preg_replace('/[^a-z0-9_ \,\.]/i', '', $readSettings['orderBy']) : null;
        $orderType = isset($readSettings['orderType']) ? strtoupper($readSettings['orderType']) : "ASC";

        $query = "SELECT {$columnString} FROM {$table} ";
        if ($conditionString) $query .= "WHERE {$conditionString} ";
        if ($groupBy) $query .= "GROUP BY {$groupBy} ";
        if ($orderBy) $query .= "ORDER BY {$orderBy} " . (in_array($orderType, array('ASC', 'A')) ? "ASC" : "DESC") . " ";
        if ($limit) $query .= "LIMIT ? ";
        if ($offset) $query .= "OFFSET ? ";
        return $query;
    }
    
    private static function prepareBindValues($values, $readSettings)
    {
        $limit = isset($readSettings['limit']) ? preg_replace('/[^0-9]/', '', $readSettings['limit']) : null;
        $offset = isset($readSettings['offset']) ? preg_replace('/[^0-9]/', '', $readSettings['offset']) : null;
        return array_merge($values, ($limit ? array($limit) : array()), ($offset ? array($offset) : array()));
    }

    private static function prepareInsertValues($values)
    {
        $valuePlaceholders = array();
        $bindValues = array();
        foreach ($values as $value) {
            if (is_array($value) && isset($value['subquery'])) {
                $valuePlaceholders[] = $value['subquery'];
            } else {
                $valuePlaceholders[] = '?';
                $bindValues[] = $value;
            }
        }
        return array($valuePlaceholders, $bindValues);
    }

    private static function buildUpdateString($updateColumns, &$bindValues)
    {
        $updatePlaceholder = array();
        foreach ($updateColumns as $column => $value) {
            if ($value instanceof RawSQL) {
                $updatePlaceholder[] = "{$column} = {$value->getSQL()}";
            } else {
                $updatePlaceholder[] = "{$column} = ?";
                $bindValues[] = $value;
            }
        }
        return !empty($updatePlaceholder) ? " ON DUPLICATE KEY UPDATE " . implode(", ", $updatePlaceholder) : "";
    }
    
    public static function updateWithTemplate($query, $values, $link = null)
    {
        if ($link === null) $link = get_pdo_connection();
        
        $queryObj = new G5MySQLQuery($query, $link);
        if (!empty($values)) {
            call_user_func_array(array($queryObj, 'bind'), $values);
        }
        return sql_query($queryObj);
    }
}

function sql_bind_insert($table, $inserts, $updateColumns = array(), $link = null){
    
    $columns = array_keys($inserts);
    $values = array_values($inserts);
    
    return G5MysqlCRUD::insert($table, $columns, $values, $updateColumns, $link);
    
    /*
    try {
        $result = G5MysqlCRUD::insert($table, $columns, $values, $link);
    } catch (MySQLQueryFailToExecuteException $e) {
        // 에러 정보 출력
        // error_log("SQLSTATE: " . $e->getSQLState());
        // error_log("Error Code: " . $e->getErrorCode());
        // error_log("Error Message: " . $e->getErrorMessage());
        // echo "쿼리 실행 중 오류가 발생했습니다. 관리자에게 문의하세요.";
    }
    */
}

function sql_bind_update($table, $updates, $conditions = array(), $link = null) {
    
    $columns = array_map('sql_bind_update_map', array_keys($updates));
    $columnValues = array_values($updates);
    $condition = array_map('sql_bind_condition_map', array_keys($conditions), array_values($conditions));
    $conditionValues = array_map('sql_bind_condition_value', array_values($conditions));
    return G5MysqlCRUD::update($table, $columns, $columnValues, $condition, $conditionValues, $link);

}

function sql_bind_update_map($item) {
    return "$item = ?";
}

function sql_bind_condition_map($item, $value)
{
    if (is_array($value)) {
        $operator = key($value);
        if (strtoupper($operator) === 'IN') {
            $placeholders = implode(', ', array_fill(0, count($value[$operator]), '?'));
            return "$item $operator ($placeholders)";
        }
        return "$item $operator ?";
    }
    return "$item = ?";
}

function sql_bind_condition_value($value)
{
    if (is_array($value)) {
        if (strtoupper(key($value)) === 'IN') {
            return $value['IN']; // 배열 반환
        }
        return current($value);
    }
    return $value;
}

/*
// 조건 문자열 생성 함수 (PHP 5.2 호환)
function build_condition_string($item, $conditions)
{
    if (is_array($conditions[$item])) {
        $operator = key($conditions[$item]);
        if (strtoupper($operator) === 'IN') {
            $placeholders = implode(', ', array_fill(0, count($conditions[$item][$operator]), '?'));
            return "$item $operator ($placeholders)";
        }
        return "$item $operator ?";
    }
    return "$item = ?";
}

function sql_bind_select($table, $columns, $conditions = array(), $readSettings = array(), $link = null, $is_fetch = 0) {
    
    // 조건의 키 배열 가져오기
    $condition_array = array_keys($conditions);

    // 조건에 연산자를 적용하기 위해 배열 구조 변경 (PHP 5.2용 일반 함수)
    $condition = array_map('build_condition_string', $condition_array, array_fill(0, count($condition_array), $conditions));

    // 조건에 사용할 값만 추출 (IN 연산자 처리 포함)
    $values = array();
    foreach ($conditions as $key => $value) {
        if (is_array($value) && strtoupper(key($value)) === 'IN') {
            // IN 연산자의 경우 배열 값을 펼침
            $values = array_merge($values, $value['IN']);
        } else {
            // 기타 연산자는 단일 값 추가
            $values[] = is_array($value) ? current($value) : $value;
        }
    }

    // 컬럼이 문자열로 전달되었을 경우 배열로 변환
    if (!is_array($columns)) {
        $columns = explode(',', $columns);
    }

    // G5MysqlCRUD 클래스의 read 메서드 호출
    return G5MysqlCRUD::read($table, $columns, $condition, $values, $readSettings, $link, $is_fetch);
}
*/

function sql_bind_select($table, $columns, $conditions = array(), $readSettings = array(), $link = null, $is_fetch = 0) {
    $condition = array_map('sql_bind_condition_map', array_keys($conditions), array_values($conditions));
    $values = array();
    foreach ($conditions as $key => $value) {
        if (is_array($value) && strtoupper(key($value)) === 'IN') {
            $values = array_merge($values, $value['IN']);
        } else {
            $values[] = sql_bind_condition_value($value);
        }
    }
    if (!is_array($columns)) $columns = explode(',', $columns);
    return G5MysqlCRUD::read($table, $columns, $condition, $values, $readSettings, $link, $is_fetch);
}

// 한 행만 리턴 (sql_fetch 와 같은 역할)
function sql_bind_select_fetch($table, $columns, $conditions = array(), $readSettings = array(), $link = null){
    
    return sql_bind_select($table, $columns, $conditions, $readSettings, $link, 1);
    
}

// 결과값에서 mysqli_fetch_assoc 하여 여러 행을 리턴
function sql_bind_select_array($table, $columns, $conditions = array(), $readSettings = array(), $link = null){
    
    return sql_bind_select($table, $columns, $conditions, $readSettings, $link, 2);
    
}

function sql_bind_lock($tables, $lock_type = 'WRITE', $link = null) {
    global $g5;
    if (!$link) $link = get_pdo_connection();

    $tables = (array) $tables; // 단일 또는 배열로 처리
    $tableClauses = array();
    foreach ($tables as $table) {
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        if (!empty($table)) {
            $tableClauses[] = "{$table} {$lock_type}";
        }
    }
    if (empty($tableClauses)) {
        throw new Exception("No valid table names provided");
    }

    $query = "LOCK TABLES " . implode(", ", $tableClauses);
    $queryObj = new G5MySQLQuery($query, $link);
    return sql_query($queryObj);
}

function sql_bind_unlock($link = null) {
    global $g5;
    if (!$link) $link = get_pdo_connection();
    
    $queryObj = new G5MySQLQuery("UNLOCK TABLES", $link);
    return sql_query($queryObj);
}

function sql_bind_delete($table, $conditions = array(), $link = null){
    
    $condition = array_map('sql_bind_condition_map', array_keys($conditions), array_values($conditions));
    $values = array_map('sql_bind_condition_value', array_values($conditions));
    return G5MysqlCRUD::delete($table, $condition, $values, $link);
    
}