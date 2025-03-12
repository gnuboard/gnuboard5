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
        $dsn = "mysql:host=".G5_MYSQL_HOST.";dbname=".G5_MYSQL_DB.";charset=".G5_DB_CHARSET;
        $g5['connect_pdo_db'] = new PDO($dsn, G5_MYSQL_USER, G5_MYSQL_PASSWORD);
        $g5['connect_pdo_db']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $g5['connect_pdo_db']->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $g5['connect_pdo_db']->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // 이스케이프 방지
        
        // echo "Database connection established successfully.";
        
    } catch (PDOException $e) {
        // 연결 실패 시 에러 처리
        die("PDO Connection failed: " . $e->getMessage());
    }
}

function get_pdo_insert_id($link=null) {
    
    if ($link === null) $link = get_pdo_connection();
    return sql_insert_id($link);
    
}

define('G5_IS_BIND_DEBUG', 0);

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
    
    private $isPostPage = 0;
    
    /**
     * Construct a new query
     * 
     * @param string $query      The query
     * @param mysqli|null $connection The connection, if null then it will use
     *                                the default connection on the MySQL class
     * 
     * @throws MySQLNoConnectionException If no connection is supplied
     */
    public function __construct($query, $connection = null, $not_prepared = null)
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
        
        $this->isPostPage = ($_SERVER['REQUEST_METHOD'] === 'POST') ? 1 : 0;
            
        $this->queryString = $query; // 쿼리문 저장
        
        if ($not_prepared) {
            return;
        }
        
        if (defined('G5_USE_DB_PDO') && G5_USE_DB_PDO) {
            // PDO 모드
            $this->preparedQuery = $this->connection->prepare($query);
            if ($this->preparedQuery === false) {
                $errorInfo = $this->connection->errorInfo();
                $this->errorMessage = $errorInfo[2];
                $this->errorCode = $errorInfo[1];
                $this->sqlState = $errorInfo[0];
                throw new Exception("PDO prepare failed: " . $this->errorMessage . " - Query: $query");
            }
        } else {
            // MySQLi 모드
            // $this->preparedQuery = mysqli_prepare($this->connection, $query);
            if (!($this->connection instanceof mysqli)) {
                throw new Exception("Invalid MySQLi connection: " . var_export($this->connection, true));
            }
            $this->preparedQuery = mysqli_prepare($this->connection, $query);
            if ($this->preparedQuery === false) {
                throw new Exception("MySQLi prepare failed: " . mysqli_error($this->connection) . " - Query: $query");
            }
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
        // LOCK TABLES는 바인딩 불필요
        if ($this->preparedQuery === null) {
            return; // 특수 쿼리는 바인딩 생략
        }
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
            // 백슬래시가 포함된 경우에만 제거
            if ($this->isPostPage && strpos($value, '\\') !== false) {
                // stripslashes 하는 이유는 common.php 에서 sql_escape_string 적용 때문에 하는것이다.(비효율적, 어쩔수가 없다;;)
                $value = stripslashes($value);
            }
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
            
            if ($this->preparedQuery === null) {
                // 특수 쿼리는 exec 사용
                return $this->connection->exec($this->queryString) !== false;
            }
            
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
            
            if ($this->preparedQuery === null) {
                // 특수 쿼리는 mysqli_query 사용
                return mysqli_query($this->connection, $this->queryString) !== false;
            }
            
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

        // 조인 포함 테이블 이름 검증
        if (!self::validateTableString($table)) {
            throw new Exception("Invalid table name or join syntax: $table");
        }
        
        $columnString = empty($columns) ? "*" : implode(",", $columns);
        
        $conditionString = self::buildConditionString($condition);

        $query = self::buildSelectQuery($table, $columnString, $conditionString, $readSettings);
        $queryObj = new G5MySQLQuery($query, $link);

        $valueToBind = self::prepareBindValues($values, $readSettings);
        if (!empty($valueToBind)) {
            call_user_func_array(array($queryObj, 'bind'), $valueToBind);
        }
        
        if (G5_IS_BIND_DEBUG) {
            // 실행된 쿼리 디버깅
            echo "<br>실제 실행된 쿼리: " . $queryObj->getQuery() . "\n";
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
        
        if (G5_IS_BIND_DEBUG) {
            // 실행된 쿼리 디버깅
            echo "실제 실행된 쿼리: " . $queryObj->getQuery() . "\n";
        }
        
        // $query->execute();
        
        return sql_query($queryObj);
    }
    
    /**
     * Update a column on a table
     */
    public static function update($table, $columns, $columnValues, $condition = array(), $conditionValues = array(), $link = null)
    {
        global $g5;
        if ($link === null) $link = get_pdo_connection();

        // 조인 포함 테이블 이름 검증
        if (!self::validateTableString($table)) {
            throw new Exception("Invalid table name or join syntax: $table");
        }
        
        // 컬럼 검증 및 처리
        /*
        $validatedColumns = array_map(function($col) {
            // 이미 완성된 SET 절 문자열인지 확인 (예: "column = CONCAT(...)"
            if (preg_match('/^[a-zA-Z0-9_]+\s*=\s*.+$/', $col)) {
                // 컬럼명 검증
                $colParts = explode('=', $col, 2);
                $colName = trim($colParts[0]);
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $colName)) {
                    throw new Exception("Invalid column name in SET clause: $colName");
                }
                return $col; // 그대로 사용
            }
            // 단순 컬럼명만 온 경우
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) {
                throw new Exception("Invalid column name: $col");
            }
            return "$col = ?";
        }, (array)$columns);
        */
        
        $validatedColumns = array_map(function($col) {
            if (preg_match('/^[a-zA-Z0-9_]+\s*=\s*.+$/', $col)) {
                $colParts = explode('=', $col, 2);
                $colName = trim($colParts[0]);
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $colName)) {
                    throw new Exception("Invalid column name in SET clause: $colName"); // $colName만 출력
                }
                return $col; // 그대로 사용
            }
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) {
                throw new Exception("Invalid column name: $col");
            }
            return "$col = ?";
        }, (array)$columns);
    
        $columnString = implode(",", $validatedColumns);
    
        $conditionString = self::buildConditionString($condition);

        $query = "UPDATE {$table} SET {$columnString} " . ($conditionString ? "WHERE {$conditionString}" : "");
        $queryObj = new G5MySQLQuery($query, $link);

        // $valuesToBind = array_merge($columnValues, $conditionValues);
        
        $valuesToBind = $columnValues; // $conditionValues는 이미 $columnValues에 포함됨
        
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

        // 조인 포함 테이블 이름 검증
        if (!self::validateTableString($table)) {
            throw new Exception("Invalid table name or join syntax: $table");
        }
    
        $conditionString = self::buildConditionString($condition);
        $query = "DELETE FROM {$table} WHERE {$conditionString}";
        $queryObj = new G5MySQLQuery($query, $connection);

        if (!empty($values)) {
            call_user_func_array(array($queryObj, 'bind'), $values);
        }
        return sql_query($queryObj);
    }
    
    // 테이블 문자열 검증 메서드
    private static function validateTableString($table)
    {
        // 단일 테이블 또는 기본 조인 구문 허용
        /*
        $pattern = '/^[a-zA-Z0-9_]+(?: [a-zA-Z0-9_]+)?(?:\s*(?:LEFT|RIGHT|INNER)?\s*JOIN\s*[a-zA-Z0-9_]+(?: [a-zA-Z0-9_]+)?\s*ON\s*\(\s*[a-zA-Z0-9_]+\.[a-zA-Z0-9_]+\s*=\s*[a-zA-Z0-9_]+\.[a-zA-Z0-9_]+\s*\))*$/i';
        return preg_match($pattern, $table) === 1;
        */
        
        // 조인 구문 허용: AS와 기본 JOIN 포함
        /*
        $pattern = '/^[a-zA-Z0-9_]+(?:\s+AS\s+[a-zA-Z0-9_]+)?(?:\s*(?:LEFT|RIGHT|INNER)?\s*JOIN\s*[a-zA-Z0-9_]+(?:\s+AS\s+[a-zA-Z0-9_]+)?\s*ON\s*\(\s*[a-zA-Z0-9_]+\.[a-zA-Z0-9_]+\s*=\s*[a-zA-Z0-9_]+\.[a-zA-Z0-9_]+\s*\))*$/i';
        $pattern = '/^[a-zA-Z0-9_]+(?:\s+(?:AS\s+)?[a-zA-Z0-9_]+)?(?:\s*(?:LEFT|RIGHT|INNER)?\s*JOIN\s*[a-zA-Z0-9_]+(?:\s+(?:AS\s+)?[a-zA-Z0-9_]+)?\s*ON\s*\(\s*[a-zA-Z0-9_]+\.[a-zA-Z0-9_]+\s*=\s*[a-zA-Z0-9_]+\.[a-zA-Z0-9_]+\s*\))*$/i';
        */
        $pattern = '/^[a-zA-Z0-9_]+(?:\s+(?:AS\s+)?[a-zA-Z0-9_]+)?(?:\s*(?:LEFT|RIGHT|INNER)?\s*JOIN\s*[a-zA-Z0-9_]+(?:\s+(?:AS\s+)?[a-zA-Z0-9_]+)?\s*ON\s*(?:\()?\s*[a-zA-Z0-9_]+\.[a-zA-Z0-9_]+\s*=\s*[a-zA-Z0-9_]+\.[a-zA-Z0-9_]+\s*(?:\))?)*$/i';

        return preg_match($pattern, $table) === 1;
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
    
    public static function buildConditionString($condition, $defaultLogic = 'AND')
    {
        if (empty($condition)) {
            return '';
        }
        
        $conditions = array();
        foreach ($condition as $cond) {
            // 기존 형식: 문자열 조건
            if (is_string($cond)) {
                if (preg_match('/^([a-zA-Z0-9_\.]+)\s*IN\s*\(\s*\?\s*(?:,\s*\?)*\s*\)$/i', $cond, $matches)) {
                    $column = $matches[1];
                    if (!in_array('IN', self::$allowedOperators)) {
                        throw new Exception("Invalid operator: IN");
                    }
                } elseif (!preg_match('/^([a-zA-Z0-9_\.]+)\s*(' . implode('|', array_diff(self::$allowedOperators, ['IN'])) . ')\s*\?$/i', $cond, $matches)) {
                    throw new Exception("Invalid condition format: $cond");
                } else {
                    $column = $matches[1];
                    $operator = strtoupper($matches[2]);
                    if (!in_array($operator, self::$allowedOperators)) {
                        throw new Exception("Invalid operator: $operator");
                    }
                }
                $conditions[] = "($cond)";
            } 
            // 새 형식: 배열 조건
            elseif (is_array($cond) && isset($cond['condition'])) {
                $conditions[] = '(' . $cond['condition'] . ')';
            } else {
                throw new Exception("Invalid condition format: " . print_r($cond, true));
            }
        }
        
        // 조건 결합
        $result = $conditions[0];
        for ($i = 1; $i < count($conditions); $i++) {
            // 배열 조건일 경우 logic 사용, 문자열 조건은 기본 AND
            $logic = (is_array($condition[$i]) && isset($condition[$i]['logic'])) ? strtoupper($condition[$i]['logic']) : $defaultLogic;
            if (!in_array($logic, ['AND', 'OR'])) {
                throw new Exception("Invalid logic operator: $logic");
            }
            $result .= " $logic " . $conditions[$i];
        }
        
        return $result;
    }
    
    private static function buildSelectQuery($table, $columnString, $conditionString, $readSettings)
    {
        $limit = isset($readSettings['limit']) ? preg_replace('/[^0-9]/', '', $readSettings['limit']) : null;
        $offset = isset($readSettings['offset']) ? preg_replace('/[^0-9]/', '', $readSettings['offset']) : null;
        
        $groupBy = isset($readSettings['groupby']) ? trim($readSettings['groupby']) : 
                   (isset($readSettings['groupBy']) ? trim($readSettings['groupBy']) : null);

        $orderBy = isset($readSettings['orderby']) ? trim($readSettings['orderby']) : 
                   (isset($readSettings['orderBy']) ? trim($readSettings['orderBy']) : null);

        $orderType = isset($readSettings['ordertype']) ? strtoupper($readSettings['ordertype']) : 
                     (isset($readSettings['orderType']) ? strtoupper($readSettings['orderType']) : "ASC");

        if ($groupBy && !preg_match('/^[a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)?(?:\s+(ASC|DESC))?(?:\s*,\s*[a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)?(?:\s+(ASC|DESC))?)*$/i', $groupBy)) {
            throw new Exception("Invalid GROUP BY: $groupBy");
        }
        if ($orderBy && !preg_match('/^[a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)?(?:\s+(ASC|DESC))?(?:\s*,\s*[a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)?(?:\s+(ASC|DESC))?)*$/i', $orderBy)) {
            throw new Exception("Invalid ORDER BY: $orderBy");
        }
        if (!in_array($orderType, ['ASC', 'DESC'])) {
            throw new Exception("Invalid order type: $orderType");
        }
        
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
}

function sql_bind_update($table, $updates, $conditions = array(), $link = null) {
    $setClauses = array();
    $updateValues = array();
    
    // $updates 처리
    foreach ($updates as $key => $value) {
        $setClause = sql_bind_update_map($key, $value);
        $setClauses[] = $setClause;
        
        if (is_array($value) && isset($value['function'])) {
            foreach ($value['args'] as $arg) {
                if (is_string($arg) && (strpos($arg, '$') === 0 || !preg_match('/^[a-zA-Z0-9_]+$/', $arg))) {
                    $updateValues[] = (strpos($arg, '$') === 0) ? substr($arg, 1) : $arg;
                }
            }
        } elseif (is_array($value) && isset($value['expression'])) {
            // expression은 바인딩 값 필요 없음
        } else {
            $updateValues[] = $value;
        }
    }
    
    $conditionArray = array_map('sql_bind_condition_map', array_keys($conditions), array_values($conditions));
    $conditionValues = array();
    foreach ($conditions as $k => $cond) {
        $value = sql_bind_condition_value($k, $cond); // $k와 $cond를 모두 전달
        if (is_array($value)) {
            $conditionValues = array_merge($conditionValues, $value);
        } else {
            $conditionValues[] = $value;
        }
    }
    
    //$allValues = array_merge($updateValues, $conditionValues);
    //return G5MysqlCRUD::update($table, $setClauses, $allValues, $conditionArray, $conditionValues, $link);
    
    $allValues = array_merge($updateValues, $conditionValues);
    // 디버깅용 출력 추가
    $conditionString = G5MysqlCRUD::buildConditionString($conditionArray);
    
    if (G5_IS_BIND_DEBUG) {
        $debugQuery = "UPDATE $table SET " . implode(", ", $setClauses) . ($conditionString ? " WHERE $conditionString" : "");
        echo "Debug Query: $debugQuery\n";
        echo "Debug Values: " . print_r($allValues, true) . "\n";
    }
    
    return G5MysqlCRUD::update($table, $setClauses, $allValues, $conditionArray, [], $link); // $conditionValues 중복 제거
    
    // return G5MysqlCRUD::update($table, $setClauses, $allValues, $conditionArray, $conditionValues, $link);
}

function sql_bind_update_map($key, $value = null)
{
    if (is_array($value) && isset($value['function'])) {
        $function = strtoupper($value['function']);
        $args = $value['args'];
        $argPlaceholders = array();
        foreach ($args as $arg) {
            if (!is_scalar($arg)) {
                throw new Exception("Invalid argument type in function args: " . print_r($arg, true));
            }
            if (is_string($arg) && strpos($arg, '$') === 0) {
                $argPlaceholders[] = '?';
            } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $arg)) {
                $argPlaceholders[] = '?';
            } else {
                $argPlaceholders[] = $arg;
            }
        }
        return "$key = $function(" . implode(', ', $argPlaceholders) . ")";
    } elseif (is_array($value) && isset($value['expression'])) {
        $expression = $value['expression'];
        if (!preg_match('/^[a-zA-Z0-9_]+\s*[+\-*\/]\s*[0-9]+$/', $expression)) {
            throw new Exception("Invalid expression format: $expression");
        }
        return "$key = $expression";
    }
    return "$key = ?";
}

function sql_bind_condition_map($key, $value = null)
{
    // 기존 키-값 쌍 형식 처리
    if (!is_array($key)) {
        if (is_array($value) && !empty($value)) {
            $operator = key($value);
            if (strtoupper($operator) === 'IN') {
                $placeholders = implode(', ', array_fill(0, count($value[$operator]), '?'));
                return array('condition' => "$key $operator ($placeholders)", 'logic' => 'AND');
            }
            return array('condition' => "$key $operator ?", 'logic' => 'AND');
        }
        return array('condition' => "$key = ?", 'logic' => 'AND');
    }
    
    // 새 형식 (연관 배열) 처리
    $cond = $key; // $key가 실제 조건 배열
    if (isset($cond['group'])) {
        $groupConditions = array_map('sql_bind_condition_map', array_keys($cond['group']), array_values($cond['group']));
        $groupLogic = isset($cond['groupLogic']) ? strtoupper($cond['groupLogic']) : 'AND';
        if (!in_array($groupLogic, ['AND', 'OR'])) {
            throw new Exception("Invalid group logic operator: $groupLogic");
        }
        return array(
            'condition' => G5MysqlCRUD::buildConditionString($groupConditions, $groupLogic),
            'logic' => isset($cond['logic']) ? strtoupper($cond['logic']) : 'AND'
        );
    }
    
    $column = $cond['column'];
    $value = $cond['value'];
    $operator = isset($cond['operator']) ? strtoupper($cond['operator']) : '=';
    $logic = isset($cond['logic']) ? strtoupper($cond['logic']) : 'AND';

    if (is_array($value) && $operator === 'IN') {
        $placeholders = implode(', ', array_fill(0, count($value), '?'));
        return array('condition' => "$column $operator ($placeholders)", 'logic' => $logic);
    }
    return array('condition' => "$column $operator ?", 'logic' => $logic);
}

function sql_bind_condition_value($key, $value = null)
{
    // 기존 키-값 쌍 형식 처리
    if (!is_array($key)) {
        if (is_array($value) && !empty($value)) {
            $operator = key($value);
            if (strtoupper($operator) === 'IN') {
                return $value[$operator];
            }
            return $value[$operator];
        }
        return $value;
    }
    
    // 새 형식 처리
    $cond = $key;
    if (isset($cond['group'])) {
        $values = array();
        foreach ($cond['group'] as $k => $subCond) {
            $subValue = sql_bind_condition_value($k, $subCond);
            if (is_array($subValue)) {
                $values = array_merge($values, $subValue);
            } else {
                $values[] = $subValue;
            }
        }
        return $values;
    }
    
    $value = $cond['value'];
    if (is_array($value) && strtoupper(key($value)) === 'IN') {
        return $value['IN'];
    }
    return $value;
}

function sql_bind_select_join($query, $values = array(), $link = null, $is_fetch = 0)
{
    if ($link === null) $link = get_pdo_connection();
    $queryObj = new G5MySQLQuery($query, $link);
    if (!empty($values)) {
        call_user_func_array(array($queryObj, 'bind'), $values);
    }
    $result = sql_query($queryObj);
    
    if ($is_fetch === 1) {
        return $queryObj->result_fetch($result);
    } elseif ($is_fetch === 2) {
        return $queryObj->result($result);
    }
    return $result;
}

function sql_bind_select($table, $columns, $conditions = array(), $readSettings = array(), $link = null, $is_fetch = 0) {
    // 조건을 키-값 쌍으로 전달받으므로 array_map에 key와 value를 함께 사용
    $conditionArray = array_map('sql_bind_condition_map', array_keys($conditions), array_values($conditions));
    $values = array();
    foreach ($conditions as $k => $cond) {
        $value = sql_bind_condition_value($k, $cond);
        if (is_array($value)) {
            $values = array_merge($values, $value);
        } else {
            $values[] = $value;
        }
    }
    if (!is_array($columns)) $columns = explode(',', $columns);
    
    $conditionString = G5MysqlCRUD::buildConditionString($conditionArray);
    return G5MysqlCRUD::read($table, $columns, $conditionArray, $values, $readSettings, $link, $is_fetch);
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
    
    $queryObj = new G5MySQLQuery($query, $link, 1);
    return sql_query($queryObj);
}

function sql_bind_unlock($link = null) {
    global $g5;
    if (!$link) $link = get_pdo_connection();
    
    $queryObj = new G5MySQLQuery("UNLOCK TABLES", $link, 1);
    return sql_query($queryObj);
}

function sql_bind_delete($table, $conditions = array(), $link = null){
    
    $condition = array_map('sql_bind_condition_map', array_keys($conditions), array_values($conditions));
    $values = array_map('sql_bind_condition_value', array_values($conditions));
    
    /*
    echo "Query: DELETE FROM $table WHERE " . implode(" AND ", $condition) . "\n";
    echo "Values: " . print_r($values, true) . "\n";
    exit;
    */
    
    return G5MysqlCRUD::delete($table, $condition, $values, $link);
    
}