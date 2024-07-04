<?php

namespace API\Service;

use API\Database\Db;

class MemoService
{
    
    public function __construct()
    {
            
    }

    /**
     * 메모의 전체 카운트 수 조회
     * @param $meType
     * @param $mb_id
     * @return int
     */
    public function fetchTotalRecords($meType, $mb_id)
    {
        $memo_table = G5_TABLE_PREFIX . 'memo';
        if ($meType == 'recv') {
            // 그누보드 5 버전에서는 me_read_datetime 필드가 '0000-00-00 00:00:00' 으로 초기화 되어 있음
            // 0000 쓸지 0001 쓸지 플래그 필요함.
            $where = "me_recv_mb_id = :mb_id AND me_type = :me_type AND me_read_datetime = '0000-00-00 00:00:00'";
        } else {
            $where = "me_send_mb_id = :mb_id AND me_type = :me_type AND me_read_datetime = '0000-00-00 00:00:00'";
        }

        $query = "SELECT count(*) FROM $memo_table WHERE {$where}";
        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $mb_id,
            'me_type' => $meType,
        ]);
        return $stmt->fetchColumn();
    }

    public function fetchMemos($meType, $getAttribute, int $page, int $perPage)
    {
        if ($meType == 'recv') {
            $where = "me_recv_mb_id = :mb_id AND me_type = :me_type AND (me_read_datetime = '0000-00-00 00:00:00')";
        } else {
            $where = "me_send_mb_id = :mb_id AND me_type = :me_type AND (me_read_datetime = '0000-00-00 00:00:00')";
        }

        $memo_table = G5_TABLE_PREFIX . 'memo';
        $query = "SELECT * FROM $memo_table WHERE {$where} ORDER BY me_id DESC LIMIT :offset, :limit";
        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $getAttribute,
            'me_type' => $meType,
            'offset' => ($page - 1) * $perPage,
            'limit' => $perPage
        ]);

        return $stmt->fetchAll();
    }
}