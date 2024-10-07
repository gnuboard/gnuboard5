<?php

namespace API\Service;

use API\Database\Db;

class BoardService
{
    public array $board;
    public string $table;
    private GroupService $group_service;
    private MemberService $member_service;

    public function __construct(GroupService $groupService, MemberService $memberService)
    {
        $this->group_service = $groupService;
        $this->member_service = $memberService;
        $this->setTable();
    }

    /**
     * 게시판 카테고리 목록 조회
     * @return array
     */
    public function getCategories(): array
    {
        if (!$this->board['bo_use_category'] || $this->board['bo_category_list'] === '') {
            return [];
        }
        return explode('|', $this->board['bo_category_list']);
    }

    public function isBoardAdmin($bo_table, $mb_id)
    {
        $board = $this->getBoard($bo_table);
        if (!$board) {
            return false;
        }
        return $board['bo_admin'] === $mb_id;
    }

    public function isBoardGroupAdmin($bo_table, $mb_id)
    {
        return $this->getGroupAdminByBoard($bo_table) === $mb_id;
    }

    public function isAdmin(string $admin_type, string $mb_id, string $bo_table)
    {
        $config = ConfigService::getConfig();
        switch ($admin_type) {
            case 'super':
                return is_super_admin($config, $mb_id);
            case 'group':
                return $this->getGroupAdminByBoard($bo_table) === $mb_id;
            case 'board':
                $board = $this->getBoard($bo_table);
                if (!$board) {
                    return false;
                }
                return $board['bo_admin'] === $mb_id;
            default:
                return false;
        }
    }

    /**
     * 관리자 정보 조회
     * @param string $admin_type
     * @param string $bo_table
     * @return array|false
     */
    public function getAdminInfo(string $admin_type, string $bo_table)
    {
        $config = ConfigService::getConfig();
        switch ($admin_type) {
            case 'super':
                return $config['cf_admin'];
            case 'group':
                if ($group_admin = $this->getGroupAdminByBoard($bo_table)) {
                    return $this->member_service->fetchMemberById($group_admin);
                }
                break;
            case 'board':
                if ($board_admin = $this->getBoardAdmin($bo_table)) {
                    return $this->member_service->fetchMemberById($board_admin);
                }
                break;
            default:
                return false;
        }
        return false;
    }

    /**
     * 게시판이 속하는 그룹의 그룹 관리자 조회
     * @param string $bo_table 게시판 테이블명
     * @return false|string
     */
    public function getGroupAdminByBoard($bo_table)
    {
        if (isset($this->board['bo_table']) && $this->board['bo_table'] === $bo_table) {
            return $this->group_service->fetchGroup($this->board['gr_id'])['gr_admin'];
        }

        $board = $this->getBoard($bo_table);
        if ($board) {
            return $this->group_service->fetchGroup($this->board['gr_id'])['gr_admin'];
        }

        return false;
    }

    /**
     * 게시판 관리자 조회
     * @param string $bo_table
     * @return false|string
     */
    public function getBoardAdmin($bo_table)
    {
        $board = $this->getBoard($bo_table);
        if ($board) {
            return $board['bo_admin'];
        }
        return false;
    }

    public function getBoard($bo_table)
    {
        static $cache = [];
        if (isset($cache[$bo_table])) {
            return $cache[$bo_table];
        }
        $board = $this->fetchBoard($bo_table);
        if ($board) {
            $cache[$bo_table] = $board;
            return $board;
        }
        return false;
    }

    /**
     * 게시판 공지글 ID 조회
     * 그누보드 5 의 board_notice 함수
     * @param $bo_notice
     * @param $wr_id
     * @param bool $is_insert
     * @return mixed|string
     */
    public function getBoardNoticeIds($bo_notice, $wr_id, $is_insert = false)
    {
        $notice_array = explode(',', trim($bo_notice));

        if ($is_insert && isset($notice_array[$wr_id])) {
            return $bo_notice;
        }

        $notice_array = array_merge(array($wr_id), $notice_array);
        $notice_array = array_unique($notice_array);
        if($notice_array === false) {
            return '';
        }
        
        foreach ($notice_array as $key => $value) {
            if (!trim($value)) {
                unset($notice_array[$key]);
            }
        }
        
        if (!$is_insert) {
            foreach ($notice_array as $key => $value) {
                if ((int)$value == (int)$wr_id) {
                    unset($notice_array[$key]);
                }
            }
        }
        return implode(',', $notice_array);
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
        $query = "SELECT * FROM {$this->table} WHERE gr_id = :gr_id ORDER BY bo_order";
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
        if (!$result) {
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
