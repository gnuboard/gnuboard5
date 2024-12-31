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
    
    private $queryString = ''; // 원본 쿼리문 저장
    
    private $boundValues = array(); // 바인딩된 값 저장
    
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
        
        $this->connection = ($connection == null) ? $g5['connect_db'] : $connection;
        
        if ($this->connection == null) {
            throw new MySQLNoConnectionException("Error: no MySQL connection is supplied");
        }
        
        $this->queryString = $query; // 쿼리문 저장
        $this->preparedQuery = mysqli_prepare($this->connection, $query);
        
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
        
        /*
        foreach ($args as $value) {
            if (is_string($value)) {
                $types .= "s";
            } elseif (is_int($value)) {
                $types .= "i";
            } elseif (is_double($value) || is_float($value)) {
                $types .= "d";
            } else {
                $types .= "s";
            }
            $valueToBind[] = $value;
        }
        */
        foreach ($args as $value) {
            if (is_int($value)) {
                $types .= "i";
            } elseif (is_float($value)) {
                $types .= "d";
            } elseif (is_numeric($value) && ctype_digit($value)) {
                // 숫자 문자열도 정수로 변환
                $value = (int) $value;
                $types .= "i";
            } elseif (is_numeric($value)) {
                // 숫자 문자열이지만 정수가 아닌 경우, float로 변환
                $value = (float) $value;
                $types .= "d";
            } else {
                $types .= "s";
            }
            $valueToBind[] = $value;
        }
        
        $this->boundValues = $valueToBind; // 바인딩된 값 저장
        
        $params = array_merge(array($this->preparedQuery, $types), $valueToBind);

        if (!call_user_func_array('mysqli_stmt_bind_param', $this->refValues($params))) {
            $bindCount = count($args);
            throw new MySQLQueryFailToBindException("Failed to bind ({$bindCount}) values to the query");
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
        if (!mysqli_stmt_execute($this->preparedQuery)) {
            
            // 에러 메시지와 SQLSTATE 에러 코드 가져오기
            $this->errorMessage = mysqli_stmt_error($this->preparedQuery);
            $this->errorCode = mysqli_stmt_errno($this->preparedQuery);
            $this->sqlState = mysqli_sqlstate($this->connection);
            
            /*
            throw new MySQLQueryFailToExecuteException(
                "Failed to execute the query",
                $this->sqlState,
                $this->errorCode,
                $this->errorMessage
            );
            */
            
            /*
            // 🔥 디버그 로그 출력 (원하는 방식으로 출력)
            echo "SQLSTATE 코드: {$this->sqlState}\n";
            echo "에러 코드: {$this->errorCode}\n";
            echo "에러 메시지: {$this->errorMessage}\n";
            */
            
            // throw new MySQLQueryFailToExecuteException("Failed to execute the query");
            
            // throw new MySQLQueryFailToExecuteException("Failed to execute the query: {$this->errorMessage} (SQLSTATE: {$this->sqlState}, Error Code: {$this->errorCode})");
            
            throw new Exception("Failed to execute the query: {$this->errorMessage} (SQLSTATE: {$this->sqlState}, Error Code: {$this->errorCode})");
        }
        
        // SELECT 쿼리의 경우 mysqli_result 객체를 반환
        $result = mysqli_stmt_get_result($this->preparedQuery);
        
        if ($result) {
            return $result; // SELECT 쿼리의 경우 mysqli_result 객체 반환
        } else {
            return true; // INSERT, UPDATE, DELETE 등 결과가 없는 쿼리의 경우 true 반환
        }
    }
    
    public function get_num_rows()
    {
        // https://www.php.net/manual/en/mysqli-stmt.num-rows.php
        /* store the result in an internal buffer */
        
        mysqli_stmt_store_result($this->preparedQuery);
        
        return mysqli_stmt_num_rows($this->preparedQuery);
    }
    
    /**
     * Return the result of the query executed
     * 
     * @return array
     */
    public function result($result)
    {
        $rows = array();
        
        if ($result) {
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
        
        try {
            $row = @mysqli_fetch_assoc($result);
        } catch (Exception $e) {
            $row = null;
        }

        return $row;
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
            // 값이 문자열이면 작은따옴표로 감싸기
            if (is_string($value)) {
                $value = "'" . addslashes($value) . "'";
            } elseif ($value === null) {
                $value = "NULL";
            }
            // ?를 바인딩된 값으로 하나씩 교체
            $query = preg_replace('/\?/', $value, $query, 1);
        }

        return $query;
    }
}

class G5MysqlCRUD
{
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
        
        if ($link === null) {
            $link = $g5['connect_db'];
        }
        
        if ($condition) {
            foreach ($condition as $index => $cond) {
                $condition[$index] = "({$cond})";
            }
            $conditionString = implode(" AND ", $condition);
        }

        $columnString = empty($columns) ? "*" : implode(",", $columns);
        
        /*
        $limit = isset($readSettings['limit']) ? $readSettings['limit'] : null;
        $offset = isset($readSettings['offset']) ? $readSettings['offset'] : null;
        $groupBy = isset($readSettings['groupBy']) ? $readSettings['groupBy'] : null;
        $orderBy = isset($readSettings['orderBy']) ? $readSettings['orderBy'] : null;
        */
        
        $limit = isset($readSettings['limit']) ? preg_replace('/[^0-9]/', '', $readSettings['limit']) : null;
        $offset = isset($readSettings['offset']) ? preg_replace('/[^0-9]/', '', $readSettings['offset']) : null;
        $groupBy = isset($readSettings['groupBy']) ? preg_replace('/[^a-z0-9_ \,]/i', '', $readSettings['groupBy']) : null;
        $orderBy = isset($readSettings['orderBy']) ? preg_replace('/[^a-z0-9_ \,]/i', '', $readSettings['orderBy']) : null;
        
        $orderType = isset($readSettings['orderType']) ? strtoupper($readSettings['orderType']) : "ASC";

        $query = "SELECT {$columnString} FROM {$table} ";
        
        if ($condition) {
            $query .= "WHERE {$conditionString} ";
        }
        
        if ($groupBy) {
            $query .= "GROUP BY {$groupBy} ";
        }
        
        if ($orderBy) {
            $query .= "ORDER BY {$orderBy} " . (in_array($orderType, array('ASC', 'A')) ? "ASC" : "DESC") . " ";
        }
        
        if ($limit) {
            $query .= "LIMIT ? ";
        }
        
        if ($offset) {
            $query .= "OFFSET ? ";
        }
        
        $queryObj = new G5MySQLQuery($query, $link);
        
        $valueToBind = array_merge($values, ($limit ? array($limit) : array()), ($offset ? array($offset) : array()));
        
        if (!empty($valueToBind)) {
            call_user_func_array(array($queryObj, 'bind'), $valueToBind);
        }
        
        // 실행된 쿼리 디버깅
        echo "실제 실행된 쿼리: " . $queryObj->getQuery() . "\n";
        
        // $result = $queryObj->execute();
        
        $result = sql_query($queryObj);
        
        if ($is_fetches === 1) {
            
            // 한행
            return $queryObj->result_fetch($result);
        } else if ($is_fetches === 2){
            
            // 여러행
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
    public static function insert($table, $columns, $values, $link = null)
    {
        global $g5;
        
        if ($link === null) {
            $link = $g5['connect_db'];
        }
        
        $valuePlaceholders = array();
        $bindValues = array();

        foreach ($values as $value) {
            // 서브쿼리 감지 (괄호와 SELECT 키워드 검사)
            if (is_string($value) && 
                (strpos($value, '(') !== false || stripos($value, 'SELECT') !== false || stripos($value, 'IFNULL') !== false)) {
                $valuePlaceholders[] = $value; // 서브쿼리는 그대로 삽입
            } else {
                $valuePlaceholders[] = '?'; // 일반 값은 바인딩
                $bindValues[] = $value;
            }
        }

        // 컬럼과 값 설정
        $columnString = implode(",", $columns);
        $valueString = implode(",", $valuePlaceholders);

        // SQL 쿼리 준비
        $query = new G5MySQLQuery("INSERT INTO {$table} ({$columnString}) VALUES ({$valueString})");
        
        // 안전하게 일반 값만 바인딩
        if (!empty($bindValues)) {
            call_user_func_array(array($query, 'bind'), $bindValues);
        }
        
        /*
        $valueParameter = array();

        foreach ($columns as $u) {
            $valueParameter[] = "?"; 
        }

        $columnString = implode(",", $columns);
        $valueParameter = implode(",", $valueParameter);

        $query = new G5MySQLQuery("INSERT INTO {$table} ({$columnString}) VALUES ({$valueParameter})");
        
        call_user_func_array(array($query, 'bind'), $values);
        */
        
        // 실행된 쿼리 디버깅
        echo "실제 실행된 쿼리: " . $query->getQuery() . "\n";
        
        // $query->execute();
        
        return sql_query($query);
        
    }
    
    /**
     * Update a column on a table
     */
    public static function update($table, $columns, $columnValues, $condition = array(), $conditionValues = array(), $link = null)
    {
        global $g5;
        
        if ($link === null) {
            $link = $g5['connect_db'];
        }
        
        if ($condition) {
            foreach ($condition as $index => $cond) {
                $condition[$index] = "({$cond})";
            }
            $conditionString = implode(" AND ", $condition);
        }

        $columnString = implode(",", $columns);
        
        $query = "UPDATE {$table} SET {$columnString} ";
        
        if ($condition) {
            $query .= "WHERE {$conditionString}";
        }

        $queryObj = new G5MySQLQuery($query, $link);
        
        $valuesToBind = array_merge($columnValues, $conditionValues);
        call_user_func_array(array($queryObj, 'bind'), $valuesToBind);
        
        // 실행된 쿼리 디버깅
        // echo "실제 실행된 쿼리: " . $queryObj->getQuery() . "\n";
        
        // $queryObj->execute();
        
        return sql_query($queryObj);
    }
    
    /**
     * Delete a data from a table
     */
    public static function delete($table, $condition = array(), $values = array(), $connection = null)
    {
        global $g5;
        
        if ($link === null) {
            $link = $g5['connect_db'];
        }
        
        if ($condition) {
            foreach ($condition as $index => $cond) {
                $condition[$index] = "({$cond})";
            }
            $conditionString = implode(" AND ", $condition);
        }
        
        $query = "DELETE FROM {$table} ";
        
        if ($condition) {
            $query .= "WHERE {$conditionString}";
        }

        $queryObj = new G5MySQLQuery($query, $connection);
        
        if (!empty($values)) {
            call_user_func_array(array($queryObj, 'bind'), $values);
        }
        
        // 실행된 쿼리 디버깅
        echo "실제 실행된 쿼리: " . $queryObj->getQuery() . "\n";
        
        // $queryObj->execute();
        
        return sql_query($queryObj);
    }
}

function sql_bind_insert($table, $inserts, $link = null){
    
    $columns = array_keys($inserts);
    $values = array_values($inserts);
    
    return G5MysqlCRUD::insert($table, $columns, $values, $link);
    
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

function sql_bind_update($table, $updates, $conditions = array(), $link = null){
    
    $columns_array = array_keys($updates);
    
    $columns = array_map(function($item) {
        return "$item = ?";
    }, $columns_array);
    
    $columnValues = array_values($updates);
    
    $condition_array = array_keys($conditions);
    
    $condition = array_map(function($item) {
        return "$item = ?";
    }, $condition_array);
    
    $conditionValues = array_values($conditions);
    
    return G5MysqlCRUD::update($table, $columns, $columnValues, $condition, $conditionValues, $link);

}

/*
function sql_bind_select($table, $columns, $conditions = array(), $readSettings = array(), $link = null, $is_fetch = 0){
    
    $condition_array = array_keys($conditions);
    
    $condition = array_map(function($item) {
        return "$item = ?";
    }, $condition_array);
    
    $values = array_values($conditions);
    
    if (! is_array($columns)) {
        $columns = explode(',', $columns);
    }
    
    return G5MysqlCRUD::read($table, $columns, $condition, $values, $readSettings, $link, $is_fetch);
    
}
*/

function sql_bind_select($table, $columns, $conditions = array(), $readSettings = array(), $link = null, $is_fetch = 0) {
    
    // 조건의 키 배열 가져오기
    $condition_array = array_keys($conditions);
    
    // 조건에 연산자를 적용하기 위해 배열 구조 변경
    $condition = array_map(function($item) use ($conditions) {
        // 조건 값이 배열인지 확인 (예: ['>' => 100] 형식)
        if (is_array($conditions[$item])) {
            $operator = key($conditions[$item]); // 연산자 가져오기 (예: >, <, =, !=)
            return "$item $operator ?";
        } else {
            return "$item = ?";
        }
    }, $condition_array);
    
    // 조건에 사용할 값만 추출 (연산자를 제외한 실제 값만 가져오기)
    $values = array_map(function($value) {
        if (is_array($value)) {
            return current($value); // 연산자와 값 중 값만 추출
        }
        return $value;
    }, array_values($conditions));
    
    // 컬럼이 문자열로 전달되었을 경우 배열로 변환
    if (!is_array($columns)) {
        $columns = explode(',', $columns);
    }
    
    // G5MysqlCRUD 클래스의 read 메서드 호출
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

function sql_bind_delete($table, $conditions = array(), $link = null){
    
    // 조건의 키 배열 가져오기
    $condition_array = array_keys($conditions);
    
    // 조건에 연산자를 적용하기 위해 배열 구조 변경
    $condition = array_map(function($item) use ($conditions) {
        // 조건 값이 배열인지 확인 (예: ['>' => 100] 형식)
        if (is_array($conditions[$item])) {
            $operator = key($conditions[$item]); // 연산자 가져오기 (예: >, <, =, !=)
            return "$item $operator ?";
        } else {
            return "$item = ?";
        }
    }, $condition_array);
    
    // 조건에 사용할 값만 추출 (연산자를 제외한 실제 값만 가져오기)
    $values = array_map(function($value) {
        if (is_array($value)) {
            return current($value); // 연산자와 값 중 값만 추출
        }
        return $value;
    }, array_values($conditions));
    
    /*
    $condition_array = array_keys($conditions);
    
    $condition = array_map(function($item) {
        return "$item = ?";
    }, $condition_array);
    
    $values = array_values($conditions);
    */
    
    return G5MysqlCRUD::delete($table, $condition, $values, $link);
    
}