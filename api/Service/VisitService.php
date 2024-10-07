<?php

namespace API\Service;

use API\Database\Db;

class VisitService
{
    /**
     * 오늘, 어제, 최대, 전체 방문자 수를 반환합니다.
     * @return array
     * @todo cache
     * @todo 지난 10분간 캐시된 데이터를 반환합니다.
     */
    public function fetchCommonVisitCount()
    {
        global $g5;
        $db = Db::getInstance();
        // 오늘
        $query = "SELECT vs_count AS cnt FROM {$g5['visit_sum_table']} WHERE vs_date = '" . G5_TIME_YMD . "' ";
        $row = $db->run($query)->fetch();
        $vi_today = $row['cnt'] ?? 0;

        // 어제
        $query = "SELECT vs_count AS cnt FROM {$g5['visit_sum_table']} WHERE vs_date = DATE_SUB('" . G5_TIME_YMD . "', INTERVAL 1 DAY) ";
        $row = $db->run($query)->fetch();
        $vi_yesterday = $row['cnt'] ?? 0;

        // 최대
        $query = "SELECT max(vs_count) AS cnt FROM {$g5['visit_sum_table']} ";
        $row = $db->run($query)->fetch();
        $vi_max = $row['cnt'] ?? 0;

        // 전체
        $query = "SELECT sum(vs_count) AS total FROM {$g5['visit_sum_table']} ";
        $row = $db->run($query)->fetch();
        $vi_sum = $row['total'] ?? 0;

        return [
            'today' => $vi_today,
            'yesterday' => $vi_yesterday,
            'max' => $vi_max,
            'total' => $vi_sum
        ];
    }
}