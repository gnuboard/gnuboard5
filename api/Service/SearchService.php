<?php

namespace API\Service;

use API\Database\Db;
use API\v1\Model\Request\Board\BoardAllSearchRequest;

class SearchService
{
    public array $board;
    public string $table;

    private $config;

    private const CUT_CONTENT_LENGTH = 200;
    private MemberImageService $member_image_service;

    public function __construct(MemberImageService $member_image_service)
    {
        $this->member_image_service = $member_image_service;
        $this->config = ConfigService::getConfig();
    }

    /**
     * @param BoardAllSearchRequest $search_param
     * @param array $member
     * @return array
     */
    public function getSearchResults($search_param, $member)
    {
        $sfl = $search_param->sfl;
        $stx = $search_param->stx;
        $sop = $search_param->sop;
        $gr_id = $search_param->gr_id;
        $onetable = $search_param->onetable;
        $per_page = $search_param->per_page;

        if ($search_param->per_page === 0) {
            if ($search_param->is_mobile) {
                $per_page = $this->config['cf_mobile_page_rows'];
            } else {
                $per_page = $this->config['cf_write_pages'];
            }
        }

        $page = $search_param->page;

        // 검색어를 구분자로 나눈다. 여기서는 공백
        $search_keyword = explode(' ', strip_tags($stx));
        if (\count($search_keyword) > 1) {
            $search_keyword = \array_slice($search_keyword, 0, 2);
        }

        $searchable_board_info = $this->fetchSearchableBoardInfo($member, $gr_id, $onetable);
        $searchable_tables = $searchable_board_info['tables'];
        $searchable_levels = $searchable_board_info['read_level'];

        if (empty($searchable_tables) && empty($searchable_levels)) {
            return [];
        }

        [$search_condition, $search_condition_bind_param] = $this->generateSearchCondition($search_keyword, $sfl, $sop);
        $board_list = $this->fetchBoardList($search_condition, $search_condition_bind_param, $searchable_tables, $searchable_levels, $per_page);

        if ($board_list['total_count']) {
            $search_results = $this->fetchSearchResultsByPage($search_condition, $search_condition_bind_param, $board_list, $per_page, $page, $member);
        }

        return [
            'board_list' => $board_list['list'],
            'search_results' => $search_results ?? [],
            'total_count' => $board_list['total_count'],
            'total_page' => $board_list['total_page'],
            'page' => $page
        ];
    }


    /**
     * 검색가능한 게시판과 조건 조합으로 각 게시판의 결과 쿼리를 수행
     * @param $search_condition
     * @param $search_condition_bind_param
     * @param $search_board_list
     * @param $per_page
     * @param $page
     * @param $member
     * @return array 검색결과
     */
    public function fetchSearchResultsByPage($search_condition, $search_condition_bind_param, $search_board_list, $per_page, $page, $member)
    {
        global $g5;

        $search_result = [];
        $from_record = ($page - 1) * $per_page;
        $total_rows = 0;

        // UNION ALL 쿼리
        $union_query = '';
        $params = [];
        foreach ($search_board_list['list'] as $board) {
            $table = $board['table'];
            $tmp_write_table = $g5['write_prefix'] . $table;

            if ($union_query) {
                $union_query .= ' UNION ALL ';
            }
            $union_query .= "
                SELECT 
                    ? AS bo_table,
                    ? AS bo_read_level,
                    wr_id, wr_parent, wr_subject, wr_content, wr_option, wr_datetime, 
                    mb_id, wr_name, wr_email, wr_homepage, wr_is_comment 
                FROM {$tmp_write_table} 
                WHERE {$search_condition}
            ";
            $params[] = $table;
            $params[] = $board['read_level'];
            foreach ($search_condition_bind_param as $value) {
                $params[] = $value;
            }
        }

        $search_query = "
        SELECT * FROM (
            {$union_query}
        ) AS union_search
        ORDER BY wr_datetime DESC 
        LIMIT ?, ?";

        $params[] = $from_record;
        $params[] = $per_page;

        $stmt = Db::getInstance()->run($search_query, $params);

        while ($row = $stmt->fetch()) {
            $table = $row['bo_table'];
            $read_level = $row['bo_read_level'];

            $table_search_result = [
                'wr_subject' => $row['wr_subject'],
                'wr_content' => $row['wr_content'],
                'wr_option' => $row['wr_option']
            ];

            $table_search_result['type'] = 'write';

            if ($row['wr_is_comment']) {
                // 댓글의 원글
                $write_parent_query = "SELECT wr_subject, wr_option FROM {$g5['write_prefix']}{$table} WHERE wr_id = :wr_parent";
                $parent_result = Db::getInstance()->run($write_parent_query, ['wr_parent' => $row['wr_parent']])->fetch();
                $table_search_result['type'] = 'comment';
                $table_search_result['wr_subject'] = $parent_result['wr_subject'];
                $table_search_result['wr_parent_option'] = $parent_result['wr_option'];
                $table_search_result['wr_parent'] = $row['wr_parent'];

                // 댓글의 순서
                $comment_order_query = "SELECT wr_id FROM {$g5['write_prefix']}{$table} WHERE wr_parent = :wr_parent AND wr_is_comment = 1 ORDER BY wr_comment, wr_comment_reply";
                $comment_order_result = Db::getInstance()->run($comment_order_query, ['wr_parent' => $row['wr_parent']])->fetchAll();
                $comment_order = 1;
                foreach ($comment_order_result as $comment) {
                    if ($comment['wr_id'] === $row['wr_id']) {
                        break;
                    }
                    ++$comment_order;
                }
                $table_search_result['comment_order'] = $comment_order;
            }

            if (str_contains($table_search_result['wr_option'], 'secret')) {
                $table_search_result['wr_content'] = '[비밀글 입니다.]';
            }

            $subject = get_text($table_search_result['wr_subject']);
            if ($read_level <= $member['mb_level']) {
                $content = strip_tags($table_search_result['wr_content']);
                $content = get_text($content, 1);
                $content = str_replace('&nbsp;', '', $content);
                $content = cut_str($content, self::CUT_CONTENT_LENGTH, "…");
            } else {
                $content = '';
            }

            $table_search_result['wr_subject'] = $subject;
            $table_search_result['wr_content'] = $content;
            $table_search_result['wr_name'] = $row['wr_name'];
            $table_search_result['wr_datetime'] = $row['wr_datetime'];
            $table_search_result['wr_id'] = $row['wr_id'];
            $table_search_result['bo_table'] = $row['bo_table'];
            $table_search_result['mb_icon_path'] = $this->member_image_service->getMemberImagePath($row['mb_id'], 'icon');
            $table_search_result['mb_image_path'] = $this->member_image_service->getMemberImagePath($row['mb_id'], 'image');
            $search_result[] = $table_search_result;
            $total_rows++;

            if ($total_rows >= $per_page) {
                break;
            }
        }

        return $search_result;
    }


    /**
     * 검색 가능한 게시판을 찾는다
     * 게시판관리자에서 검색체크, 그룹설정 게시판에서 회원의 그룹 소속여부, 게시판 보기 레벨을 확인
     * @param $member
     * @param $gr_id
     * @param $onetable
     * @return array[]
     */
    public function fetchSearchableBoardInfo($member, $gr_id = null, $onetable = null)
    {
        global $g5;

        $query = "SELECT b.gr_id, b.bo_table, b.bo_read_level, g.gr_use_access, g.gr_admin
            FROM {$g5['board_table']} b
            LEFT JOIN {$g5['group_table']} g ON b.gr_id = g.gr_id
            WHERE b.bo_use_search = 1 AND b.bo_list_level <= :mb_level";
        $params = ['mb_level' => ($member['mb_level'] ?? 1)];

        if ($gr_id) {
            $query .= ' AND g.gr_id = :gr_id';
            $params['gr_id'] = $gr_id;
        }

        if ($onetable) {
            $query .= ' AND bo_table = :onetable';
            $params['onetable'] = $onetable;
        }
        $query .= ' ORDER BY bo_order, g.gr_id, bo_table;';

        $stmt = Db::getInstance()->run($query, $params);

        $searchable_tables = [];
        $searchable_levels = [];
        $search_tables_result = $stmt->fetchAll();

        if (is_super_admin($this->config, $member['mb_id'])) {
            foreach ($search_tables_result as $search_table) {
                $searchable_tables[] = $search_table['bo_table'];
                $searchable_levels[] = $search_table['bo_read_level'];
            }

            return [
                'tables' => $searchable_tables,
                'read_level' => $searchable_levels
            ];
        }

        foreach ($search_tables_result as $search_table) {
            // 그룹 접근 제한이 있는 경우
            if ($search_table['gr_use_access']) {
                // 그룹 관리자가 아닌 경우
                if (!($search_table['gr_admin'] && $search_table['gr_admin'] === $member['mb_id'])) {
                    // 비회원인 경우
                    if ($member['mb_id'] == '') {
                        continue;
                    }

                    // 그룹에 속해있지 않은 경우
                    $group_member_query = "SELECT COUNT(*) as cnt FROM {$g5['group_member_table']} 
                             WHERE gr_id = :gr_id AND mb_id = :mb_id AND mb_id <> ''";
                    $group_member_stmt = Db::getInstance()->run($group_member_query, ['gr_id' => $search_table['gr_id'], 'mb_id' => $member['mb_id'] ?? '']);
                    $group_member_count = $group_member_stmt->fetch();
                    if (!isset($group_member_count['cnt']) || !$group_member_count['cnt']) {
                        continue;
                    }
                }
            }

            $searchable_tables[] = $search_table['bo_table'];
            $searchable_levels[] = $search_table['bo_read_level'];
        }

        return [
            'tables' => $searchable_tables,
            'read_level' => $searchable_levels
        ];
    }

    /**
     * 검색 조건에 맞는 where 절 생성
     * @param $search_words
     * @param $sfl
     * @param $sop
     * @return array [search_query, bind_param]
     */
    private function generateSearchCondition($search_words, $sfl, $sop)
    {
        $bind_param = [];
        $search_query = '(';
        $operator1 = '';

        foreach ($search_words as $word) {
            if (trim($word) === '') {
                continue;
            }
            $search_str = $word;
            $search_query .= $operator1 . '(';
            $operator2 = '';
            $fields = explode('||', trim($sfl));
            foreach ($fields as $field) {
                $search_query .= $operator2;
                switch ($field) {
                    case 'mb_id':
                    case 'wr_name':
                        $search_query .= "$field = ?";
                        $bind_param[] = $search_str;
                        break;
                    case 'wr_subject':
                    case 'wr_content':
                        if (preg_match('/[a-zA-Z]/', $search_str)) {
                            $field = strtolower($field);
                            $search_str = strtolower($search_str);
                        }
                        $search_query .= "INSTR({$field}, ?)";
                        $bind_param[] = $search_str;
                        break;
                    default:
                        $search_query .= '1=0';
                        break;
                }
                $operator2 = ' OR ';
            }
            $search_query .= ')';
            $operator1 = " {$sop} ";
        }
        $search_query .= ')';

        return [$search_query, $bind_param];
    }

    /**
     * 검색된 게시판에서 검색된 게시글 수를 가져온다
     * @param $search_query
     * @param $search_query_bind_param
     * @param $searchable_tables
     * @param $searchable_levels
     * @param $per_page
     * @return array [list, total_count, total_page]
     */
    public function fetchBoardList($search_query, $search_query_bind_param, $searchable_tables, $searchable_levels, $per_page)
    {
        $board_list = [];
        $total_count = 0;
        foreach ($searchable_tables as $index => $table) {
            $write_table = $GLOBALS['g5']['write_prefix'] . $table;
            $query = "SELECT COUNT(*) as cnt FROM `{$write_table}`";
            if ($search_query) {
                $query .= " WHERE {$search_query}";
            }

            $row = Db::getInstance()->run($query, $search_query_bind_param)->fetch();

            $total_count += $row['cnt'];
            if ($row['cnt']) {
                $board_list[] = [
                    'table' => $table,
                    'read_level' => $searchable_levels[$index],
                    'count' => $total_count
                ];
            }
        }

        return [
            'list' => $board_list,
            'total_count' => $total_count,
            'total_page' => ceil($total_count / $per_page ?: 1)
        ];
    }
}
