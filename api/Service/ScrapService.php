<?php

namespace API\Service;

use API\Database\Db;

class ScrapService
{
    private string $table;

    public function __construct()
    {
        global $g5;

        $this->setTable($g5['scrap_table']);
    }

    /**
     * 스크랩 내역 총 개수 조회
     * @param string $mb_id 회원 아이디
     * @return int 스크랩 내역 총 개수
     */
    public function fetchTotalScrapCount(string $mb_id): int
    {
        $query = "SELECT count(*) FROM {$this->table} WHERE mb_id = :mb_id";

        $stmt = Db::getInstance()->run($query, ["mb_id" => $mb_id]);
        return $stmt->fetchColumn() ?? 0;
    }

    /**
     * 스크랩 목록 조회
     * @param string $mb_id 회원 아이디
     * @param array $page_params 페이지 정보
     * @return array|false
     */
    public function fetchScraps(string $mb_id, array $page_params)
    {
        $query = "SELECT * FROM {$this->table} WHERE mb_id = :mb_id ORDER BY ms_id DESC LIMIT :offset, :per_page";

        $stmt = Db::getInstance()->run($query, [
            "mb_id" => $mb_id,
            "offset" => $page_params['offset'],
            "per_page" => $page_params['per_page']
        ]);

        return $stmt->fetchAll();
    }

    /**
     * 스크랩 조회
     * @param int $ms_id 스크랩 아이디
     * @return array|false
     */
    public function fetchScrapById(int $ms_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE ms_id = :ms_id";

        $stmt = Db::getInstance()->run($query, ["ms_id" => $ms_id]);
        return $stmt->fetch();
    }

    /**
     * 스크랩 내역 존재 여부 조회
     * @param string $mb_id 회원 아이디
     * @param string $bo_table 게시판 테이블
     * @param int $wr_id 게시글 아이디
     * @return bool 스크랩 내역 존재 여부
     */
    public function existsScrap(string $mb_id, string $bo_table, int $wr_id): bool
    {
        $query = "SELECT count(*) FROM {$this->table} WHERE mb_id = :mb_id AND bo_table = :bo_table AND wr_id = :wr_id";

        $stmt = Db::getInstance()->run($query, [
            "mb_id" => $mb_id,
            "bo_table" => $bo_table,
            "wr_id" => $wr_id
        ]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * 스크랩 생성
     * @param string $mb_id 회원 아이디
     * @param string $bo_table 게시판 테이블
     * @param int $wr_id 게시글 아이디
     * @return void
     */
    public function createScrap(string $mb_id, string $bo_table, int $wr_id): void
    {
        $query = "INSERT INTO {$this->table} (mb_id, bo_table, wr_id, ms_datetime) VALUES (:mb_id, :bo_table, :wr_id, :ms_datetime)";

        Db::getInstance()->run($query, [
            "mb_id" => $mb_id,
            "bo_table" => $bo_table,
            "wr_id" => $wr_id,
            "ms_datetime" => date("Y-m-d H:i:s")
        ]);
    }

    /**
     * 스크랩 삭제 
     * @param int $ms_id 스크랩 아이디
     * @return void
     */
    public function deleteScrap(int $ms_id): void
    {
        Db::getInstance()->delete($this->table, ["ms_id" => $ms_id]);
    }

    public function setTable(string $table): void
    {
        $this->table = $table;
    }
}
