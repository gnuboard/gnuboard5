<?php

namespace API\Service;

use API\Database\Db;


class AutosaveService
{

    /**
     * 임시저장된 글 목록 페이지로 조회
     * @param string $mb_id
     * @param int $page
     * @param int $per_page
     * @return array ['autosaves' => [], 'total_records' => 0, 'total_pages' => 0]
     */
    public function getAutosaves(string $mb_id, int $page, int $per_page)
    {
        $autosaves = $this->fetch_autosaves($mb_id, $page, $per_page);
        $total_records = $this->fetchTotalAutosaves($mb_id);
        $total_pages = ceil($total_records / $per_page);

        return [
            'autosaves' => $autosaves ?: [],
            'total_records' => $total_records ?: 0,
            'total_pages' => $total_pages ?: 0
        ];
    }

    /**
     * 임시저장된 글 갯수 조회
     * @param string $mb_id
     * @return int 없으면 0
     */
    public function getCount($mb_id)
    {
        return $this->fetchCount($mb_id);
    }

    /**
     * @param int $mb_id
     * @return int
     */
    public function fetchTotalAutosaves(int $mb_id)
    {
        $autosave_table = $GLOBALS['g5']['autosave_table'];
        $query = "SELECT count(*) FROM $autosave_table WHERE mb_id = :mb_id";
        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $mb_id
        ]);

        $total_records = $stmt->fetchColumn();
        return $total_records ?: 0;
    }

    /**
     * 회원 아이디로 임시저장된 글 목록 조회
     * @param string $mb_id
     * @param int $page
     * @param int $per_page
     * @return array|false
     */
    public function fetch_autosaves(string $mb_id, int $page, int $per_page)
    {
        $autosave_table = $GLOBALS['g5']['autosave_table'];
        $query = "SELECT * FROM $autosave_table WHERE mb_id = :mb_id ORDER BY as_id DESC LIMIT :offset, :limit";
        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $mb_id,
            'offset' => ($page - 1) * $per_page,
            'limit' => $per_page
        ]);
        $data = $stmt->fetchAll();
        if (!$data) {
            return false;
        }
        return $data;
    }

    /**
     * 임시저장된 글 조회
     * @param $mb_id
     * @param $as_id
     * @return array|false
     */
    public function fetchAutosave($mb_id, $as_id)
    {
        $autosave_table = $GLOBALS['g5']['autosave_table'];
        $query = "SELECT * FROM $autosave_table WHERE mb_id = :mb_id AND as_id = :as_id";
        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $mb_id,
            'as_id' => $as_id
        ]);
        return $stmt->fetch();
    }

    /**
     * 임시저장된 글 갯수 DB 조회
     * @param string $mb_id
     * @return int 없으면 0
     */
    public function fetchCount($mb_id)
    {
        $autosave_table = $GLOBALS['g5']['autosave_table'];
        $query = "SELECT count(*) FROM {$autosave_table} WHERE mb_id = :mb_id";
        $result = Db::getInstance()->run($query, [
            'mb_id' => $mb_id
        ])->fetch();
        return $result[0] ?? 0;
    }
}
