<?php

namespace API\Service;

use API\Database\Db;

class PollService
{

    /**
     * 투표 조회
     * @todo cache 추가
     * @param int $po_id
     * @return array|null
     */
    public function get_poll($po_id)
    {
        $poll_table = $GLOBALS['g5']['poll_table'];
        $query = "SELECT * FROM  $poll_table WHERE po_id = :po_id";
        static $cache = [];
        if (!isset($cache[$po_id])) {
            $poll = Db::getInstance()->run($query, ['po_id' => $po_id])->fetch();
            if (!isset($poll['po_id'])) {
                return null;
            }
            $cache[$po_id] = $poll;
        }

        return $cache[$po_id];
    }

    public function get_latest_poll()
    {
        $poll_table = $GLOBALS['g5']['poll_table'];
        $query = "SELECT * FROM  $poll_table WHERE po_use = 1 ORDER BY po_date DESC LIMIT 1";
        $result = Db::getInstance()->run($query)->fetch();
        if (!isset($result['po_id'])) {
            return null;
        }

        return $result;
    }

    /**
     * 투표하기
     * @param int $po_id
     * @param int $item_id
     * @param string $ip
     * @return bool
     */
    public function vote_poll($po_id, $item_id, $ip)
    {
        $poll_table = $GLOBALS['g5']['poll_table'];
        $poll_target_id = "po_poll{$item_id}";
        $db = Db::getInstance();
        $poll = $db->run("SELECT * FROM $poll_table WHERE po_id = :po_id", ['po_id' => $po_id])->fetch();
        if (!isset($poll[$poll_target_id])) {
            return false;
        }

        if ($poll['po_use'] == 0) {
            return false;
        }

        $poll_item_index = "po_cnt{$item_id}";
        $affect_rows = $db->update($poll_table, [
            'po_id' => $po_id,
        ], [
            $poll_item_index => $poll[$poll_item_index] + 1,
            'po_ip' => $ip,
        ]);

        return $affect_rows > 0;
    }

    /**
     * @param $poll
     * @param $member
     * @param $ip
     * @return bool
     * 투표 여부 확인
     */
    public function check_already_vote($poll, $member, $ip)
    {
        //member
        if (isset($member['mb_id'])) {
            $ids = explode(',', trim($poll['mb_ids']));
            if (in_array($member['mb_id'], $ids)) {
                return true;
            }
        }

        //guest
        $poll_ips = explode(',', trim($poll['po_ips']));
        if (in_array($ip, $poll_ips)) {
            return true;
        }
        return false;
    }

    /**
     * 투표 기타의견 추가
     * @param $po_id
     * @param $pc_name
     * @param $pc_idea
     * @param string $mb_id 회원아이디 비회원은 공백문자
     * @return bool
     */
    public function add_etc_poll($po_id, $pc_name, $pc_idea, $mb_id)
    {
        $poll_etc_table = $GLOBALS['g5']['poll_etc_table'];
        $db = Db::getInstance();

        //poll etc 테이블 auto increment 설정안되어있어서 아래와 같이 해야함.
        $poll_etc = $db->run("SELECT * FROM  {$poll_etc_table} ORDER BY pc_id DESC LIMIT 1")->fetch();
        if ($poll_etc === false) {
            return false;
        }

        if ($poll_etc) {
            $pc_id = $poll_etc['pc_id'] + 1;
        } else {
            $pc_id = 1;
        }

        $query_result = $db->insert($poll_etc_table, [
            'po_id' => $po_id,
            'pc_id' => $pc_id,
            'pc_name' => $pc_name,
            'pc_idea' => $pc_idea,
            'mb_id' => $mb_id,
            'pc_datetime' => G5_TIME_YMDHIS
        ]);

        return $query_result;
    }

    /**
     * @param $po_id
     * @param $pc_id
     * @return bool
     */
    public function delete_etc_poll($po_id, $pc_id)
    {
        $poll_etc_table = $GLOBALS['g5']['poll_etc_table'];
        $result = Db::getInstance()->delete($poll_etc_table, ['pc_id' => $pc_id, 'po_id' => $po_id, 'mb_id']);
        return $result > 0;
    }

    /**
     * @param $pc_id
     * @param $mb_id
     * @return bool
     */
    public function check_auth_etc_poll($pc_id, $mb_id)
    {
        $poll_etc_table = $GLOBALS['g5']['poll_etc_table'];
        $result = Db::getInstance()->run("SELECT * FROM $poll_etc_table WHERE pc_id = :pc_id AND mb_id = :mb_id", ['pc_id' => $pc_id, 'mb_id' => $mb_id])->fetch();
        return isset($result['pc_id']);
    }
}