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
    public function getCount(string $mb_id)
    {
        return $this->fetchCount($mb_id);
    }

    /**
     * @param string $mb_id
     * @return int
     */
    public function fetchTotalAutosaves(string $mb_id)
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
     * @param string $mb_id
     * @param int $as_id
     * @return array|false
     */
    public function fetchAutosave(string $mb_id, int $as_id)
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
     * 임시저장 하기
     * @param array $data
     * @return false|string
     */
    public function createAutosave(array $data)
    {
        $autosave_table = $GLOBALS['g5']['autosave_table'];
        $result = Db::getInstance()->insert($autosave_table, [
            'mb_id' => $data['mb_id'],
            'as_uid' => $data['as_uid'],
            'as_subject' => $data['as_subject'],
            'as_content' => $data['as_content'],
            'as_datetime' => G5_TIME_YMDHIS,
        ]);
        
        return $result;
    }

    /**
     * 임시저장된 글 갯수 DB 조회
     * @param string $mb_id
     * @return int 없으면 0
     */
    public function fetchCount(string $mb_id)
    {
        $autosave_table = $GLOBALS['g5']['autosave_table'];
        $query = "SELECT count(*) FROM {$autosave_table} WHERE mb_id = :mb_id";
        $result = Db::getInstance()->run($query, [
            'mb_id' => $mb_id
        ])->fetchColumn();
        return $result;
    }

    /**
     * 임시저장된 글 삭제
     * @param string $mb_id
     * @param int $as_id
     * @return int
     */
    public function deleteAutosave(string $mb_id, int $as_id)
    {
        $autosave_table = $GLOBALS['g5']['autosave_table'];
        $result = Db::getInstance()->delete($autosave_table, [
            'as_id' => $as_id,
            'mb_id' => $mb_id
        ]);

        return $result;
    }
}
