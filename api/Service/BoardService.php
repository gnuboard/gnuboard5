<?php

namespace API\Service;

use API\Database\Db;
use Exception;

class BoardService
{
    public array $board;
    public string $write_table;

    public function setBoard(array $board): void
    {
        $this->board = $board;
        $this->setWriteTable($board['bo_table']);
    }

    public function setWriteTable(string $bo_table): void
    {
        global $g5;
        $this->write_table = $g5['write_prefix'] . $bo_table;
    }

    /**
     * 게시판 정보 조회
     */
    public function fetchBoardsByGroupId(string $gr_id): array
    {
        global $g5;

        $query = "SELECT * FROM {$g5['board_table']} WHERE gr_id = :gr_id";
        $stmt = Db::getInstance()->run($query, ['gr_id' => $gr_id]);
        return $stmt->fetchAll();
    }

    /**
     * 게시판 정보 조회
     */
    public function fetchBoardByTable(string $bo_table): array
    {
        global $g5;

        $query = "SELECT * FROM {$g5['board_table']} WHERE bo_table = :bo_table";
        $stmt = Db::getInstance()->run($query, ['bo_table' => $bo_table]);
        return $stmt->fetch();
    }

    /**
     * 게시판 글 총 레코드 수 조회
     */
    public function fetchTotalWriteCount(array $search_params): int
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
     * 게시판 정보 조회
     * @return array|bool
     */
    public function fetchWriteById(int $wr_id): mixed
    {
        $query = "SELECT * FROM {$this->write_table} WHERE wr_id = :wr_id";
        $stmt = Db::getInstance()->run($query, ['wr_id' => $wr_id]);

        return $stmt->fetch();
    }

    /**
     * 게시글의 원글/답글/댓글 조회
     */
    public function fetchWritesAndComments(int $wr_id): mixed
    {
        $query = "SELECT * FROM {$this->write_table} WHERE wr_parent = :wr_id order by wr_id";
        $stmt = Db::getInstance()->run($query, ['wr_id' => $wr_id]);

        return $stmt->fetchAll();
    }

    /**
     * 부모 게시글 정보 조회
     */
    public function fetchParentWriteByNumber(int $wr_num): array
    {
        $query = "SELECT * FROM {$this->write_table}
                    WHERE wr_num = :wr_num
                    AND wr_reply = ''
                    AND wr_is_comment = 0";
        $stmt = Db::getInstance()->run($query, ['wr_num' => $wr_num]);

        return $stmt->fetch();
    }

    /**
     * 게시글의 답글 조회
     */
    public function fetchReplyByWrite(array $write): mixed
    {
        $query = "SELECT * FROM {$this->write_table}
                    WHERE wr_reply LIKE :wr_reply
                    AND wr_id <> :wr_id
                    AND wr_num = :wr_num
                    AND wr_is_comment = 0";
        $stmt = Db::getInstance()->run($query, [
            'wr_reply' => $write['wr_reply'] . '%',
            'wr_id' => $write['wr_id'],
            'wr_num' => $write['wr_num'],
        ]);

        return $stmt->fetchAll();
    }

    /**
     * 게시글의 답글 조회
     */
    public function fetchReplyByComment(array $comment): mixed
    {
        $query = "SELECT * FROM {$this->write_table}
                    WHERE wr_comment_reply LIKE :wr_comment_reply
                    AND wr_id <> :wr_id
                    AND wr_parent = :wr_parent
                    AND wr_comment = :wr_comment
                    AND wr_is_comment = 1";
        $stmt = Db::getInstance()->run($query, [
            'wr_comment_reply' => $comment['wr_comment_reply'] . '%',
            'wr_id' => $comment['wr_id'],
            'wr_parent' => $comment['wr_parent'],
            'wr_comment' => $comment['wr_comment'],
        ]);

        return $stmt->fetchAll();
    }

    /**
     * 게시글의 댓글 목록 조회
     */
    public function fetchCommentsByWrite(array $write): mixed
    {
        $query = "SELECT * FROM {$this->write_table} WHERE wr_parent = :wr_id AND wr_is_comment = 1";
        $stmt = Db::getInstance()->run($query, ['wr_id' => $write['wr_id']]);

        return $stmt->fetchAll();
    }

    /**
     * 가장 최근 작성된 댓글의 날짜 조회
     */
    public function fetchWriteCommentLast(array $write): mixed
    {
        $query = "SELECT MAX(wr_datetime) as wr_last FROM {$this->write_table} WHERE wr_parent = :wr_parent";
        $stmt = Db::getInstance()->run($query, ['wr_parent' => $write['wr_parent']]);
        return $stmt->fetch();
    }

    /**
     * 게시글 작성 처리
     */
    public function createWriteData(object $data, array $member = [], array $parent_write = []): int
    {
        $min_wr_num = $this->fetchMinimumWriteNumber() - 1;
        $data->wr_num = $parent_write ? $parent_write['wr_num'] : $min_wr_num;
        $data->wr_parent = $parent_write['wr_id'] ?? 0;
        $data->wr_seo_title = exist_seo_title_recursive('bbs', generate_seo_title($data->wr_subject), $this->write_table);
        $data->mb_id = $member['mb_id'] ?? '';
        $data->wr_datetime = G5_TIME_YMDHIS;
        $data->wr_last = G5_TIME_YMDHIS;
        $data->wr_ip = $_SERVER['REMOTE_ADDR'];
        if ($parent_write) {
            $data->wr_reply = $this->setReplyCharacter($parent_write);
        }

        $insert_id = $this->insertWrite($data);
        return $insert_id;
    }

    /**
     * 게시글 정보를 데이터베이스에 등록
     */
    public function insertWrite(object $data): int
    {
        return Db::getInstance()->insert($this->write_table, (array)$data);
    }

    /**
     * 새글 테이블에 게시글 정보 등록
     */
    public function insertBoardNew(int $wr_id, int $wr_parent, string $mb_id): int
    {
        global $g5;

        $data = [
            'bo_table' => $this->board['bo_table'],
            'wr_id' => $wr_id,
            'wr_parent' => $wr_parent,
            'bn_datetime' => G5_TIME_YMDHIS,
            'mb_id' => $mb_id
        ];
        return Db::getInstance()->insert($g5['board_new_table'], $data);
    }

    /**
     * 게시글 수정 처리
     */
    public function updateWriteData(array $write, object $data): void
    {
        $data->wr_seo_title = exist_seo_title_recursive('bbs', generate_seo_title($data->wr_subject), $this->write_table, $write['wr_id']);
        $data->wr_last = G5_TIME_YMDHIS;

        $this->updateWrite($write['wr_id'], (array)$data);
    }

    /**
     * 게시판 데이터베이스 갱신
     */
    public function updateBoard(array $data)
    {
        global $g5;

        Db::getInstance()->update($g5['board_table'], ['bo_table' => $this->board['bo_table']], $data);
    }

    /**
     * 게시글 데이터베이스 갱신
     */
    public function updateWrite(int $wr_id, array $data): void
    {
        Db::getInstance()->update($this->write_table, ['wr_id' => $wr_id], $data);
    }

    /**
     * 게시판 게시글/댓글 갯수 갱신
     */
    public function updateWriteCount(int $count_writes = 0, int $count_comments = 0): void
    {
        global $g5;

        $query = "UPDATE {$g5['board_table']} 
                    SET bo_count_write = bo_count_write - :count_write, 
                        bo_count_comment = bo_count_comment - :count_comment 
                    WHERE bo_table = :bo_table";
        Db::getInstance()->run($query, [
            'count_write' => $count_writes,
            'count_comment' => $count_comments,
            'bo_table' => $this->board['bo_table']
        ]);
    }

    /**
     * 게시글 부모ID 갱신
     */
    public function updateWriteParentId(int $wr_id, int $parent_id): void
    {
        Db::getInstance()->update(
            $this->write_table,
            ['wr_id' => $wr_id],
            ['wr_parent' => $parent_id]
        );
    }

    /**
     * 게시글에 속한 댓글의 분류 갱신
     */
    public function updateCategoryByParentId(int $wr_id, string $ca_name): void
    {
        Db::getInstance()->update(
            $this->write_table,
            ['wr_parent' => $wr_id],
            ['ca_name' => $ca_name],
        );
    }

    /**
     * 게시판 정보에 게시글 수 갱신
     */
    public function incrementWriteCount(): void
    {
        global $g5;
        $query = "UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write + 1 WHERE bo_table = :bo_table";
        Db::getInstance()->run($query, ['bo_table' => $this->board['bo_table']]);
    }

    /**
     * 게시판 정보에 게시글 수 갱신
     */
    public function incrementCommentCount(): void
    {
        global $g5;
        $query = "UPDATE {$g5['board_table']} SET bo_count_comment = bo_count_comment + 1 WHERE bo_table = :bo_table";
        Db::getInstance()->run($query, ['bo_table' => $this->board['bo_table']]);
    }

    /**
     * 게시글 삭제
     */
    public function deleteWriteById(int $wr_id): void
    {
        Db::getInstance()->delete($this->write_table, ['wr_id' => $wr_id]);
    }

    /**
     * 부모 아이디로 게시글 삭제
     */
    public function deleteWriteByParentId(int $wr_id): void
    {
        Db::getInstance()->delete($this->write_table, ['wr_parent' => $wr_id]);
    }

    /**
     * 새글 테이블 정보 삭제
     */
    public function deleteBoardNew(int $wr_id): void
    {
        global $g5;

        Db::getInstance()->delete($g5['board_new_table'], ['bo_table' => $this->board['bo_table'], 'wr_parent' => $wr_id]);
    }

    /**
     * 게시글 목록의 최소 wr_num 조회
     */
    protected function fetchMinimumWriteNumber(): int
    {
        $query = "SELECT MIN(wr_num) AS min_wr_num FROM {$this->write_table}";
        $stmt = Db::getInstance()->run($query);
        $row = $stmt->fetch();
        return (int)$row['min_wr_num'];
    }

    /**
     * 게시글 목록 검색조회 조건 설정
     */
    protected function getWhereBySearch(array $query_params, &$params = []): string
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

        // 기존 코드에서의 개선점 
        // - 댓글 검색옵션이 없으면 게시글만 검색한다.
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
    protected function getWhereSearchPart(array $search_params, &$params = []): string
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
    protected function getSortOrder(array $search_params): array
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

    // Additional public methods for search part navigation
    /**
     * 게시판 카테고리 목록 조회
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

    /**
     * 게시글의 마지막 wr_reply 조회
     */
    public function fetchLastReply(array $write): string
    {
        $reply_len = strlen($write['wr_reply']) + 1;
        $order_func = $this->board['bo_reply_order'] ? 'MAX' : 'MIN';
        $values = [
            'reply_len1' => $reply_len,
            'reply_len2' => $reply_len,
            'wr_num' => $write['wr_num'],
        ];

        $query = "SELECT {$order_func}(SUBSTRING(wr_reply, :reply_len1, 1)) as reply 
                FROM {$this->write_table} 
                WHERE wr_num = :wr_num 
                AND SUBSTRING(wr_reply, :reply_len2, 1) <> ''";

        if ($write['wr_reply']) {
            $query .= " AND wr_reply LIKE :wr_reply";
            $values = array_merge($values, ['wr_reply' => $write['wr_reply'] . '%']);
        }
        $stmt = Db::getInstance()->run($query, $values);
        $row = $stmt->fetch();
        return $row['reply'] ?? '';
    }

    /**
     * 답변글 작성시 답변글 wr_reply 생성
     * - Exception 관련 코드를 Permission으로 이동하고 싶었으나 코드 중복이 발생하여 이동하지 않음
     */
    public function setReplyCharacter(array $write)
    {
        $last_reply = $this->fetchLastReply($write);

        if ($this->board['bo_reply_order']) {
            $begin_reply_char = 'A';
            $end_reply_char = 'Z';
            $reply_number = +1;
        } else {
            $begin_reply_char = 'Z';
            $end_reply_char = 'A';
            $reply_number = -1;
        }

        if (!$last_reply) {
            $reply_char = $begin_reply_char;
        } else if ($last_reply == $end_reply_char) {
            throw new Exception('더 이상 답변하실 수 없습니다. 답변은 26개 까지만 가능합니다.');
        } else {
            $reply_char = chr(ord($last_reply) + $reply_number);
        }

        return $write['wr_reply'] . $reply_char;
    }
}
