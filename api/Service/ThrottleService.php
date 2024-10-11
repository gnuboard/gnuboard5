<?php

namespace API\Service;

use API\Database\Db;

/**
 * JWT 엑세스 토큰당 요청 제한 서비스
 * 글쓰기 등 토큰당 요청 제한(도배 방지 등)이 필요한 서비스에서 사용
 * 관리자 환경설정에서 설정한 시간동안 토큰당 한번의 요청만 허용
 * ** ip 당 요청제한은 WAF 나 웹서버 설정을 이용하세요.
 */
class ThrottleService
{
    private $limit_seconds;

    public function __construct()
    {
        $config = ConfigService::getConfig();
        $this->limit_seconds = $config['cf_delay_sec'];

        if (($GLOBALS['g5']['throttle_table'] ?? '') === '') {
            $this->createThrottleTable();
        }
    }

    public function createThrottleTable()
    {
        $throttle_table = $GLOBALS['g5']['throttle_table'] ?? G5_TABLE_PREFIX . 'throttle';

        //th_scope: 적용범위 글쓰기면 'write' 댓글은 'comment' 등
        $query = "CREATE TABLE IF NOT EXISTS {$throttle_table} (
            `th_token_hash` varchar(80) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT '엑세스 토큰 해시', 
            `th_scope` varchar(50) NOT NULL COMMENT '적용 범위',
            `th_created_at` DATETIME NOT NULL COMMENT '추가된 시각',
            `th_last_access_at` DATETIME NOT NULL COMMENT '마지막 접근시각',
            PRIMARY KEY (`th_token_hash`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='토큰당 시간기준 요청제한 테이블'";

        Db::getInstance()->getPdo()->exec($query);
    }

    /**
     * 만료된 데이터 삭제
     * @return void
     */
    public function removeExpires()
    {
        $remove_time = date('Y-m-d H:i:s', time() - $this->limit_seconds);
        $throttle_table = $GLOBALS['g5']['throttle_table'] ?? G5_TABLE_PREFIX . 'throttle';
        $query = "DELETE FROM {$throttle_table} WHERE th_last_access_at < :th_last_access_at";
        $stmt = Db::getInstance()->getPdo()->prepare($query);
        $stmt->execute(['th_last_access_at' => $remove_time]);
    }

    /**
     * 토큰 추가 또는 갱신
     * @param string $token_hash sha256 해시된 토큰
     * @param string $scope
     * @return void
     */
    public function upsertToken($token_hash, $scope)
    {
        $throttle_table = $GLOBALS['g5']['throttle_table'] ?? G5_TABLE_PREFIX . 'throttle';
        $query = "INSERT INTO {$throttle_table} 
            (th_token_hash, th_scope, th_created_at, th_last_access_at) 
            VALUES (:th_token_hash, :th_scope, :th_created_at, :th_last_access_at) ON DUPLICATE KEY UPDATE th_last_access_at = :up_th_last_access_at";
        $stmt = Db::getInstance()->getPdo()->prepare($query);
        $stmt->execute([
            'th_token_hash' => $token_hash,
            'th_scope' => $scope,
            'th_created_at' => G5_TIME_YMDHIS,
            'th_last_access_at' => G5_TIME_YMDHIS,
            'up_th_last_access_at' => G5_TIME_YMDHIS
        ]);
    }
    
    public function useThrottle()
    {
        return $this->limit_seconds <= 0 ? false : true;
    }

    /**
     * 토큰당 요청 제한 걸렸는지 확인
     * @param string $token_hash
     * @return bool
     */
    public function isThrottled($token_hash, $type)
    {
        $this->removeExpires();
        $last_time = $this->fetchLastAccessTime($token_hash, $type);
        // 마지막 접근시각이 있고 제한시간 내에 접근했다면
        if ($last_time && G5_TIME_YMDHIS - $last_time < $this->limit_seconds) {
            return true;
        }
        return false;
    }

    /**
     * 토큰당 마지막 접근시각 조회
     * @param string $token_hash
     * @param string $type
     * @return int|false
     */
    public function fetchLastAccessTime($token_hash, $type)
    {
        $throttle_table = $GLOBALS['g5']['throttle_table'] ?? G5_TABLE_PREFIX . 'throttle';
        $query = "SELECT th_last_access_at FROM {$throttle_table} WHERE th_token_hash = :th_token_hash AND th_scope = :th_scope";
        $stmt = Db::getInstance()->run($query, ['th_token_hash' => $token_hash, 'th_scope' => $type]);
        return $stmt->fetchColumn();
    }
}