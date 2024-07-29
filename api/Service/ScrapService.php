<?php

namespace API\Service;

use API\Database\Db;

class ScrapService
{
    private string $table;

    public function __construct() {
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
     * @return mixed 스크랩 목록 or false
     */
    public function fetchScraps(string $mb_id, array $page_params): mixed
    {
        $query = "SELECT * FROM {$this->table} WHERE mb_id = :mb_id ORDER BY ms_id DESC LIMIT :offset, :per_page";

        $stmt = Db::getInstance()->run($query, [
            "mb_id" => $mb_id,
            "offset" => $page_params['offset'],
            "per_page" => $page_params['per_page']
        ]);

        return $stmt->fetchAll();
    }

    public function setTable(string $table): void
    {
        $this->table = $table;
    }
}
