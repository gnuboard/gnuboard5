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

    public function fetchGroups(): array
    {
        $query = "SELECT * FROM {$this->group_table}";
        $stmt = Db::getInstance()->run($query);

        return $stmt->fetchAll();
    }

    public function fetchGroup(string $gr_id): array
    {
        if (!$gr_id) {
            return [];
        }

        $query = "SELECT * FROM {$this->group_table} WHERE gr_id = :gr_id";
        $stmt = Db::getInstance()->run($query, ['gr_id' => $gr_id]);

        return $stmt->fetch() ?: [];
    }

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
