<?php

namespace API\Service;

use API\Database\Db;

class PopularSearch
{

    /**
     * 인기 검색어 조회
     * @param int $count_limit 가져올 갯수
     * @param int $days 현재에서 며칠전 데이터인지 지정 e.g.) 7 (7일전)
     * @return array|false
     */
    public function get_keywords($days, $count_limit)
    {
        $popular_search_table = $GLOBALS['g5']['popular_table'];
        $query = "SELECT *, COUNT({$popular_search_table}.pp_word) as count FROM {$popular_search_table}
            WHERE pp_date BETWEEN :before_date AND :now  
            GROUP BY pp_word
            ORDER BY pp_id DESC LIMIT :count_limit";

        $before_date = date('Y-m-d', strtotime("-{$days} days"));
        $now = G5_TIME_YMD;

        $stmt = Db::getInstance()->run($query, [
            'before_date' => $before_date,
            'now' => $now,
            'count_limit' => $count_limit
        ]);
        return $stmt->fetchAll();
    }

    /**
     * 인기 키워드 추가
     * @throw \PDOException 쿼리 실패시
     * @param $keyword
     * @return bool
     */
    public function add_keyword($keyword)
    {
        $popular_search_table = $GLOBALS['g5']['popular_table'];
        $query = "INSERT INTO {$popular_search_table} (pp_word, pp_date, pp_ip) VALUES (:pp_word, :pp_date, :pp_ip)";
        try {
            $stmt = Db::getInstance()->run($query, [
                'pp_word' => $keyword,
                'pp_date' => G5_TIME_YMD,
                'pp_ip' => $_SERVER['REMOTE_ADDR']
            ]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            $error = Db::getInstance()->getPdo()->errorInfo();
            // 1062: MYSQL duplicate key 오류번호
            if (isset($error[1]) && $error[1] === 1062) {
                return true;
            }
            throw $e;
        }
    }

}
