<?php

namespace API\Service;

use API\Database\Db;

/**
 * @deprecated 함수 모음으로 변경
 */
class BoardGoodService
{
    public string $table;

    public function __construct()
    {
        $this->setTable();
    }

    public function setTable(): void
    {
        global $g5;
        $this->table = $g5['board_good_table'];
    }

    /**
     * 게시글 추천/비추천 정보 조회
     * @param string $mb_id 회원 ID
     * @param string $bo_table 게시판 테이블명
     * @param int $wr_id 게시글 아이디
     * @return array|false
     */
    public function fetchGoodByMember(string $mb_id, string $bo_table, int $wr_id)
    {
        $query = "SELECT *
                    FROM {$this->table}
                    WHERE bo_table = :bo_table
                    AND wr_id = :wr_id
                    AND mb_id = :mb_id";

        $stmt = Db::getInstance()->run($query, [
            'bo_table' => $bo_table,
            'wr_id' => $wr_id,
            'mb_id' => $mb_id
        ]);
        return $stmt->fetch();
    }

    /**
     * 게시글 추천/비추천 등록
     * @param string $mb_id 회원 ID
     * @param string $bo_table 게시판 테이블명
     * @param int $wr_id 게시글 아이디
     * @param string $bg_flag 추천/비추천 (good/nogood)
     * @return void
     */
    public function insertGood(string $mb_id, string $bo_table, int $wr_id, string $bg_flag)
    {
        Db::getInstance()->insert($this->table, [
            'bo_table' => $bo_table,
            'wr_id' => $wr_id,
            'mb_id' => $mb_id,
            'bg_flag' => $bg_flag,
            'bg_datetime' => date('Y-m-d H:i:s')
        ]);
    }
}
