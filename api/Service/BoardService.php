<?php

namespace API\Service;

use API\Database\Db;
use API\v1\Model\Response\Write\File;

class BoardService
{
    public array $board;
    public string $write_table;

    public function __construct(array $board)
    {
        global $g5;

        $this->board = $board;
        $this->write_table = $g5['write_prefix'] . $board['bo_table'];
    }

    /**
     * 부모 게시글 정보 조회
     */
    public function fetchParentWrite(int $wr_num): array
    {
        $query = "SELECT mb_id FROM {$this->write_table}
                    WHERE wr_num = :wr_num
                    AND wr_reply = ''
                    AND wr_is_comment = 0";
        $query = "SELECT * FROM {$this->write_table} WHERE wr_id = :wr_id";
        $stmt = Db::getInstance()->run($query, ['wr_num' => $wr_num]);

        return $stmt->fetch();
    }

    /**
     * 게시판 카테고리 목록 조회
     */
    public function getCategories(): array
    {
        if (!$this->board['bo_use_category']
                || $this->board['bo_category_list'] === "") {
            return [];
        }
        return explode("|", $this->board['bo_category_list']);
    }

    /**
     * 게시판 글 총 레코드 수 조회
     */
    public function fetchTotalWritesRecords(array $search_params): int
    {
        // 검색조건 설정
        $sql_where = $this->getWhereBySearch($search_params, $search_values);

        // 검색단위 설정
        $sql_where .= $this->getWhereSearchPart($search_params, $search_values);

        $query = "SELECT count(*) FROM {$this->write_table} WHERE {$sql_where}";

        $stmt = Db::getInstance()->run($query, $search_values);
        return $stmt->fetchColumn();
    }

    /**
     * 공지 게시글 목록 조회
     */
    public function fetchNoticeWrites(): array
    {
        // 공지 게시글 ID를 배열로 변환
        $notice_string = trim($this->board['bo_notice']);
        $notice_ids = array_filter(array_map('trim', explode(',', $notice_string)));

        if (empty($notice_ids)) {
            return [];
        }

        // IN 조건을 사용하여 쿼리 작성
        $placeholders = implode(',', array_fill(0, count($notice_ids), '?'));
        $query = "SELECT * FROM {$this->write_table} WHERE wr_id IN ($placeholders) AND wr_option NOT LIKE '%secret%'";

        $stmt = Db::getInstance()->run($query, $notice_ids);

        return $stmt->fetchAll();
    }

    /**
     * 게시글 목록 조회
     */
    public function fetchWrites(array $search_params, array $page_params): array
    {
        // 검색조건 설정
        $sql_where = $this->getWhereBySearch($search_params, $search_values);
        
        // 검색단위 설정
        $sql_where .= $this->getWhereSearchPart($search_params, $search_values);

        // 정렬 설정
        list($sst, $sod) = $this->getSortOrder($search_params);
        $sql_order = $sst ? " ORDER BY {$sst} {$sod} " : "";

        // 쿼리 생성 및 실행
        $query = "SELECT * FROM {$this->write_table} WHERE {$sql_where} {$sql_order} LIMIT :offset, :per_page";
        $search_values[':offset'] = $page_params['offset'];
        $search_values[':per_page'] = $page_params['per_page'];

        $stmt = Db::getInstance()->run($query, $search_values);

        return $stmt->fetchAll();
    }

    /**
     * 게시글 목록 검색조회 조건 설정
     */
    public function getWhereBySearch(array $query_params, &$params = []): string
    {
        $category = $query_params['sca'];
        $keyword = $query_params['stx'];
        $field_string = $query_params['sfl'];
        $where_operator = $query_params['sod'];

        // 검색조건 배열 초기화
        $query_parts = [];

        // 카테고리
        if ($category) {
            $query_parts[] = "ca_name = :ca_name";
            $params[':ca_name'] = $category;
        }

        // Return early if search text is empty and not '0'
        if (empty($keyword) && $keyword !== '0') {
            $query_parts[] = "wr_is_comment = 0";
            return implode(' AND ', $query_parts);
        }

        // Split search text and fields
        $terms = explode(" ", $keyword);
        $tmp = explode(",", $field_string);
        $fields = array_map('trim', explode("||", $tmp[0]));
        $is_write = isset($tmp[1]) ? trim($tmp[1]) : "";

        $search_clauses = [];

        foreach ($terms as $i => $term) {
            insert_popular($fields, $term);

            $field_clauses = [];
            foreach ($fields as $field) {
                // SQL Injection prevention by whitelisting
                $field = preg_match("/^[\w\,\|]+$/", $field) ? strtolower($field) : "wr_subject";
                $param_key = ":{$field}_{$i}";
                switch ($field) {
                    case 'mb_id':
                    case 'wr_name':
                        $field_clauses[] = "$field = $param_key";
                        $params[$param_key] = $term;
                        break;
                    case 'wr_hit':
                    case 'wr_good':
                    case 'wr_nogood':
                        $field_clauses[] = "$field >= $param_key";
                        $params[$param_key] = $term;
                        break;
                    case 'wr_num':
                        $field_clauses[] = "$field = $param_key";
                        $params[$param_key] = -1 * $term;
                        break;
                    case 'wr_ip':
                    case 'wr_password':
                        $field_clauses[] = "1=0";
                        break;
                    default:
                        if (preg_match("/[a-zA-Z]/", $term)) {
                            $field_clauses[] = "INSTR(LOWER($field), LOWER($param_key))";
                        } else {
                            $field_clauses[] = "INSTR($field, $param_key)";
                        }
                        $params[$param_key] = $term;
                        break;
                }
            }
            $search_clauses[] = '(' . implode(' OR ', $field_clauses) . ')';
        }

        $query_parts[] = '(' . implode(" $where_operator ", $search_clauses) . ')';

        // TODO: 개선점 => 댓글 검색옵션이 없으면 게시글만 검색한다.
        /*
        if ($is_write === '1') {
            $query_parts[] = "wr_is_comment = '0'";
        } elseif ($is_write === '0') {
            $query_parts[] = "wr_is_comment = '1'";
        }
        */
        if ($is_write === '0') {
            $query_parts[] = "wr_is_comment = '1'";
        } else {
            $query_parts[] = "wr_is_comment = '0'";
        }

        return implode(' AND ', $query_parts);
    }

    /**
     * 검색 단위 설정
     */
    public function getWhereSearchPart(array $search_params, &$params = []): string
    {
        if (!$search_params['is_search']) {
            return "";
        }

        $spt = $search_params['spt'];
        $search_part = $search_params['search_part'];

        $params[':min_wr_num'] = $spt;
        $params[':max_wr_num'] = $spt + $search_part;

        return " AND (wr_num BETWEEN :min_wr_num AND :max_wr_num)";
    }

    /**
     * 정렬 조건 설정
     */
    private function getSortOrder(array $search_params): array
    {
        $sst = $search_params['sst'] ?? null;
        $sod = $search_params['sod'] ?? '';

        if (!$sst) {
            if ($this->board['bo_sort_field']) {
                $sst = $this->board['bo_sort_field'];
            } else {
                $sst  = "wr_num, wr_reply";
                $sod = "";
            }
        } else {
            $board_sort_fields = get_board_sort_fields($this->board, 1);
            if (!$sod && array_key_exists($sst, $board_sort_fields)) {
                $sst = $board_sort_fields[$sst];
            } else {
                $sst = preg_match("/^(wr_datetime|wr_hit|wr_good|wr_nogood)$/i", $sst) ? $sst : "";
            }
        }

        if (!$sst) {
            $sst  = "wr_num, wr_reply";
        }

        return [$sst, $sod];
    }

    /**
     * 최소 wr_num 조회
     */
    public function getMinimumWriteNumber(): int
    {
        $query = "SELECT MIN(wr_num) AS min_wr_num FROM {$this->write_table}";
        $stmt = Db::getInstance()->run($query);
        $row = $stmt->fetch();
        return (int)$row['min_wr_num'];
    }

    /**
     * 검색 단위 > 이전위치 조회
     */
    public function getPrevSearchPart(array $search_params): int
    {
        if (!$search_params['is_search']) {
            return 0;
        }
        $min_spt = $search_params['min_spt'];
        $prev_spt = $search_params['spt'] - $search_params['search_part'];
        if (isset($min_spt) && $prev_spt < $min_spt) {
            return 0;
        }
        return $search_params['spt'] - $search_params['search_part'];
    }

    /**
     * 검색 단위 > 다음위치 조회
     */
    public function getNextSearchPart(array $search_params): int
    {
        if (!$search_params['is_search']) {
            return 0;
        }
        $next_spt = $search_params['spt'] + $search_params['search_part'];
        if ($next_spt > 0) {
            return 0;
        }
        return $search_params['spt'] + $search_params['search_part'];
    }

    public function fetchWriteFiles(int $wr_id): array
    {
        global $g5;

        $query = "SELECT * FROM {$g5['board_file_table']} WHERE bo_table = :bo_table AND wr_id = :wr_id ORDER BY bf_no";
        $stmt = Db::getInstance()->run($query, ['bo_table' => $this->board['bo_table'], 'wr_id' => $wr_id]);

        return $stmt->fetchAll();
    }

    public function getWriteFiles(int $wr_id, string $type)
    {
        $fetch_files = $this->fetchWriteFiles($wr_id);
        
        $images = [];
        $files = [];
        foreach ($fetch_files as $file) {
            if (preg_match("/\.(gif|jpg|jpeg|png|webp)$/i", $file['bf_file'])) {
                $images[] = new File($file);
            } else {
                $files[] = new File($file);
            }
        }
        return $type === 'image' ? $images : $files;
    }
}