<?php

namespace API\Service;

use API\Database\Db;


class MemberService
{
    public function fetchMemberById(string $mb_id)
    {
        global $g5;

        $query = "SELECT * FROM {$g5['member_table']} WHERE mb_id = :mb_id";

        $stmt = Db::getInstance()->run($query, ["mb_id" => $mb_id]);

        return $stmt->fetch();
    }

    public function insertMember(object $data)
    {
        global $g5;

        $data->mb_datetime = date('Y-m-d H:i:s', G5_SERVER_TIME);

        $insert_id = Db::getInstance()->insert($g5['member_table'], (array)$data);

        return $insert_id;
    }

    public function updateMember(string $mb_id, array $data): int
    {
        global $g5;

        $update_count = Db::getInstance()->update(
            $g5['member_table'],
            ["mb_id" => $mb_id],
            $data
        );

        return $update_count;
    }
}