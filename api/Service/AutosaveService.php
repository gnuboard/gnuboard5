<?php

namespace API\Service;

use API\Database\Db;

/**
 * @deprecated 함수 모음으로 변경
 */
class AutosaveService
{
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

        $query = "SELECT count(*) FROM $autosave_table WHERE mb_id = :mb_id";
        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $mb_id
        ]);

        $total_records = $stmt->fetchColumn();
        if ($total_records === false) {
            return false;
        }

        $total_pages = ceil($total_records / $per_page);

        return [
            'autosaves' => $data,
            'total_records' => $total_records ?: 0,
            'total_pages' => $total_pages ?: 0
        ];
    }

    /**
     * 임시저장된 글 조회
     * @param $mb_id
     * @param $as_id
     * @return array|false
     */
    public function fetch_autosave($mb_id, $as_id)
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
     * 임시저장된 글 갯수 조회
     * @param string $mb_id
     * @return int 없으면 0
     */
    public function get_count($mb_id)
    {
        $autosave_table = $GLOBALS['g5']['autosave_table'];
        $query = "SELECT count(*) FROM {$autosave_table} WHERE mb_id = :mb_id";
        $result = Db::getInstance()->run($query, [
            'mb_id' => $mb_id
        ])->fetch();
        return $result[0] ?? 0;
    }
}
