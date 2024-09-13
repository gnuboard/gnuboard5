<?php

namespace API\Service;

use API\Database\Db;


class BoardNewService
{
    public string $table;
    public string $board_table;
    public string $group_table;

    public function __construct()
    {
        global $g5;
        $this->table = $g5['board_new_table'];
        $this->board_table = $g5['board_table'];
        $this->group_table = $g5['group_table'];
    }

    /**
     * 최신글 총 레코드 수 조회
     * @param array $search_params 검색조건
     * @return int
     */
    public function fetchTotalCount(array $search_params): int
    {
        $sql_where = $this->getWhereBySearch($search_params, $search_values);

        $query = "SELECT COUNT(*) FROM {$this->table} a
                  JOIN {$this->board_table} b ON a.bo_table = b.bo_table
                  JOIN {$this->group_table} c ON b.gr_id = c.gr_id
                  WHERE b.bo_use_search = 1 AND {$sql_where}";

        $stmt = Db::getInstance()->run($query, $search_values);
        return $stmt->fetchColumn() ?: 0;
    }

    /**
     * 최신글 목록 조회
     * @param array $search_params 검색조건
     * @param array $page_params 페이징 정보
     * @return array
     */
    public function fetchBoardNews(array $search_params, array $page_params): array
    {
        $sql_where = $this->getWhereBySearch($search_params, $search_values);

        $query = "SELECT a.*, b.bo_subject, b.bo_mobile_subject, c.gr_subject, c.gr_id 
                  FROM {$this->table} a
                  JOIN {$this->board_table} b ON a.bo_table = b.bo_table
                  JOIN {$this->group_table} c ON b.gr_id = c.gr_id
                  WHERE b.bo_use_search = 1 AND {$sql_where}
                  ORDER BY a.bn_id DESC
                  LIMIT :offset, :per_page";

        $search_values['offset'] = $page_params['offset'];
        $search_values['per_page'] = $page_params['per_page'];

        $stmt = Db::getInstance()->run($query, $search_values);
        return $stmt->fetchAll();
    }

    /**
     * 최신글 상세 조회
     * @param int $bn_id 최신글 ID
     * @return array|false
     */
    public function fetchById(int $bn_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE bn_id = :bn_id";
        $stmt = Db::getInstance()->run($query, [':bn_id' => $bn_id]);
        return $stmt->fetch();
    }

    /**
     * 최신글 추가
     * @return string|false
     */
    public function insert(string $bo_table, int $wr_id, int $wr_parent, string $mb_id = '')
    {
        $data = [
            'bo_table' => $bo_table,
            'wr_id' => $wr_id,
            'wr_parent' => $wr_parent,
            'bn_datetime' => date('Y-m-d H:i:s'),
            'mb_id' => $mb_id
        ];
        return Db::getInstance()->insert($this->table, $data);
    }

    public function delete()
    {

    }

    /**
     * 최신글 삭제 (게시글)
     */
    public function deleteByWrite(string $bo_table, int $wr_id): void
    {
        Db::getInstance()->delete($this->table, ['bo_table' => $bo_table, 'wr_parent' => $wr_id]);
    }

    /**
     *  최신글 삭제 (댓글)
     */
    public function deleteByComment(string $bo_table, int $wr_id): void
    {
        Db::getInstance()->delete($this->table, ['bo_table' => $bo_table, 'wr_id' => $wr_id]);
    }

    /**
     * 검색 조건에 따라 WHERE 절과 바인딩 값을 생성하는 메서드
     * @param array $search_params 검색조건
     * @param array $search_values 바인딩할 값들을 저장할 배열 (참조로 전달)
     * @return string 생성된 WHERE 절
     */
    protected function getWhereBySearch(array $search_params, &$search_values): string
    {
        $where = [];
        $search_values = [];

        if (!empty($search_params['gr_id'])) {
            $where[] = 'b.gr_id = :gr_id';
            $search_values['gr_id'] = $search_params['gr_id'];
        }

        if (!empty($search_params['mb_id'])) {
            $where[] = 'a.mb_id = :mb_id';
            $search_values['mb_id'] = $search_params['mb_id'];
        }

        if (!empty($search_params['view'])) {
            if ($search_params['view'] === 'write') {
                $where[] = 'a.wr_id = wr_parent';
            } elseif ($search_params['view'] === 'comment') {
                $where[] = 'a.wr_id <> wr_parent';
            }
        }

        // 추가적인 검색 조건을 여기에 추가할 수 있습니다

        return !empty($where) ? implode(' AND ', $where) : '1';
    }
}
