<?php

namespace API\Service;

use API\Database\Db;


class GroupService
{
    private string $group_table;
    private string $group_member_table;

    public function __construct()
    {
        $this->group_table = $GLOBALS['g5']['group_table'];
        $this->group_member_table = $GLOBALS['g5']['group_member_table'];
    }

    /**
     * 게시판그룹 목록 조회
     * @return array|false
     */
    public function fetchGroups()
    {
        $query = "SELECT * FROM {$this->group_table} ORDER BY gr_id";
        $stmt = Db::getInstance()->run($query);

        return $stmt->fetchAll();
    }

    /**
     * 게시판그룹 조회
     * @param string $gr_id 그룹 ID
     * @return array
     */
    public function fetchGroup(string $gr_id): array
    {
        if (!$gr_id) {
            return [];
        }

        $query = "SELECT * FROM {$this->group_table} WHERE gr_id = :gr_id";
        $stmt = Db::getInstance()->run($query, ['gr_id' => $gr_id]);

        return $stmt->fetch() ?: [];
    }

    /**
     * 게시판그룹 회원 조회
     * @param string $gr_id 그룹 ID
     * @param string $mb_id 회원 ID
     * @return array
     */
    public function fetchGroupMember(string $gr_id, string $mb_id): array
    {
        if (!$gr_id || !$mb_id) {
            return [];
        }

        $query = "SELECT * FROM {$this->group_member_table} WHERE gr_id = :gr_id AND mb_id = :mb_id";
        $stmt = Db::getInstance()->run($query, ['gr_id' => $gr_id, 'mb_id' => $mb_id]);
        return $stmt->fetch();
    }
}
