<?php

namespace API\Service;

use API\Database\Db;

class GroupService
{
    public function __construct()
    {
    }

    public function fetchGroups(): array
    {
        global $g5;

        $query = "SELECT * FROM {$g5['group_table']}";
        $stmt = Db::getInstance()->run($query);

        return $stmt->fetchAll();
    }

    public function fetchGroup(string $gr_id): array
    {
        global $g5;

        if (!$gr_id) {
            return [];
        }

        $query = "SELECT * FROM {$g5['group_table']} WHERE gr_id = :gr_id";
        $stmt = Db::getInstance()->run($query, ['gr_id' => $gr_id]);
        return $stmt->fetch();
    }

    public function fetchGroupMember(string $gr_id, string $mb_id): array
    {
        global $g5;

        if (!$gr_id || !$mb_id) {
            return [];
        }

        $query = "SELECT * FROM {$g5['group_member_table']} WHERE gr_id = :gr_id AND mb_id = :mb_id";
        $stmt = Db::getInstance()->run($query, ['gr_id' => $gr_id, 'mb_id' => $mb_id]);
        return $stmt->fetch();
    }
}