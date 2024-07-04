<?php

namespace API\Service;

use SIR\Database\Db;

class MemoService
{
    
    public function __construct()
    {
            
    }
    
    
    public function fetch_total_records($meType, $mb_id) : int
    {
        
        $memo_table = G5_TABLE_PREFIX . 'memo';
        if ($meType == 'recv') {
            // 그누보드 5 버전에서는 me_read_datetime 필드가 '0000-00-00 00:00:00' 으로 초기화 되어 있음
            $where = "me_recv_mb_id = :mb_id AND me_read_datetime = '0000-00-00 00:00:00'";
        } else {
            $where = "me_send_mb_id = :mb_id AND me_read_datetime = '0000-00-00 00:00:00'";
        }

        $query = "SELECT count(*) FROM $memo_table WHERE {$where} ";
        $result = Db::getInstance()->getPdo()->prepare($query);
        $result->execute(['mb_id' => $mb_id]);
        
        return $result->fetch(\PDO::FETCH_ASSOC);
    }

    public function fetch_memos($meType, $getAttribute, int $page, int $perPage)
    {
        if ($meType == 'recv') {
            $where = "me_recv_mb_id = :mb_id AND (me_read_datetime = '0000-00-00 00:00:00' OR me_read_datetime = '0001-01-01 00:00:00')";
        } else {
            $where = "me_send_mb_id = :mb_id AND (me_read_datetime = '0000-00-00 00:00:00' OR me_read_datetime = '0001-01-01 00:00:00')";
        }
        
        $memo_table = G5_TABLE_PREFIX . 'memo';
        $query = "SELECT * FROM $memo_table WHERE {$where} ORDER BY me_id DESC LIMIT :offset, :limit";
        $stmt = Db::getInstance()->getPdo()->prepare($query);
        $stmt->execute([
            'mb_id' => $getAttribute,
            'offset' => ($page - 1) * $perPage,
            'limit' => $perPage
        ]);
        
        return $stmt->fetchAll();
    }
}