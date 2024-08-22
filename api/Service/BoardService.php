<?php

namespace API\Service;

use API\Database\Db;

class BoardService
{
    public array $board;
    public string $table;

    public function __construct()
    {
        $this->setTable();
    }

    /**
     * 게시판 카테고리 목록 조회
     * @return array
     */
    public function getCategories(): array
    {
        if (
            !$this->board['bo_use_category']
            || $this->board['bo_category_list'] === ""
        ) {
            return [];
        }
        return explode("|", $this->board['bo_category_list']);
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 그룹별 게시판 목록 조회
     * @param string $gr_id 그룹 ID
     * @return array|false
     */
    public function fetchBoardsByGroupId(string $gr_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE gr_id = :gr_id";
        $stmt = Db::getInstance()->run($query, ['gr_id' => $gr_id]);
        return $stmt->fetchAll();
    }

    /**
     * 게시판 정보 조회
     * @param string $bo_table 게시판 테이블명
     * @return array|false
     */
    public function fetchBoard(string $bo_table)
    {
        $query = "SELECT * FROM {$this->table} WHERE bo_table = :bo_table";
        return Db::getInstance()->run($query, ['bo_table' => $bo_table])->fetch();
    }

    /**
     * 게시판테이블명 조회
     * @return array
     */
    public static function fetchBoardTables()
    {
        global $g5;
        $query = "SELECT bo_table FROM `{$g5['board_table']}` ";
        $result = Db::getInstance()->run($query)->fetchAll();
        if(!$result) {
            return [];
        }
        return $result;
    }

    /**
     * 게시판 정보 수정
     * @param array $data 수정할 데이터
     * @return int
     */
    public function updateBoard(array $data): int
    {
        return Db::getInstance()->update($this->table, $data, ['bo_table' => $this->board['bo_table']]);
    }

    /**
     * 게시글 갯수 1 증가
     * @return void
     */
    public function increaseWriteCount(): void
    {
        $query = "UPDATE {$this->table} SET bo_count_write = bo_count_write + 1 WHERE bo_table = :bo_table";
        Db::getInstance()->run($query, ['bo_table' => $this->board['bo_table']]);
    }

    /**
     * 댓글 수 1 증가
     * @return void
     */
    public function increaseCommentCount(): void
    {
        $query = "UPDATE {$this->table} SET bo_count_comment = bo_count_comment + 1 WHERE bo_table = :bo_table";
        Db::getInstance()->run($query, ['bo_table' => $this->board['bo_table']]);
    }

    /**
     * 게시글 및 댓글 수 차감
     * @param int $count_writes 차감할 게시글 수
     * @param int $count_comments 차감할 댓글 수
     * @return void
     */
    public function decreaseWriteAndCommentCount(int $count_writes = 0, int $count_comments = 0): void
    {
        $query = "UPDATE {$this->table} 
                    SET bo_count_write = bo_count_write - :count_write, 
                        bo_count_comment = bo_count_comment - :count_comment 
                    WHERE bo_table = :bo_table";

        Db::getInstance()->run($query, [
            'count_write' => $count_writes,
            'count_comment' => $count_comments,
            'bo_table' => $this->board['bo_table']
        ]);
    }

    // ========================================
    // Getters and Setters
    // ========================================

    public function setBoard(array $board): void
    {
        $this->board = $board;
    }

    public function setTable(): void
    {
        global $g5;
        $this->table = $g5['board_table'];
    }
}
