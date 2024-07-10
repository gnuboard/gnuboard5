<?php

namespace API\Service\Poll;

use API\Database\Db;


/**
 * 최신 투표 1건 조회
 * @return array|null
 */
function get_latest_poll()
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
 * 투표 조회
 * @param int $po_id
 * @return array|null
 */
function get_poll($po_id)
{
    $poll_table = $GLOBALS['g5']['poll_table'];
    $query = "SELECT * FROM  $poll_table WHERE po_id = :po_id";
    $result = Db::getInstance()->run($query, ['po_id' => $po_id])->fetch();
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
function vote_poll($po_id, $item_id, $ip)
{
    $poll_table = $GLOBALS['g5']['poll_table'];
    $poll_target_id = "po_poll{$item_id}";
    $poll_item_count = "po_cnt{$item_id}";
    $db = Db::getInstance();
    $poll = $db->run("SELECT * FROM $poll_table WHERE po_id = :po_id", ['po_id' => $po_id])->fetch();
    if (!isset($poll[$poll_target_id])) {
        return false;
    }

    if ($poll['po_use'] == 0) {
        return false;
    }

    $affect_rows = Db::getInstance()->update($poll_table, [
        'po_id' => $po_id,
    ], [
        $poll_item_count => $poll[$poll_item_count] + 1,
        'po_ip' => $ip,
    ]);

    return $affect_rows > 0;
}


/**
 * @param $poll
 * @return bool
 * @todo 멤버/guest 체크 필요
 * 투표 여부 확인
 */
function check_already_vote($poll)
{
    //@todo 
    //if guest
    $poll_ips = explode(',', trim($poll['po_ips']));
    if (in_array($ip = $_SERVER['REMOTE_ADDR'], $poll_ips)) {
        return true;
    }
    $member = [];
    //if member
    $ids = explode(',', trim($poll['mb_ids']));
    if (in_array($member['mb_id'], $ids)) {
        return true;
    }
    return false;
}


/**
 * 투표 기타의견 추가
 * @param $po_id
 * @param $poll_etc
 * @return void
 */
function add_etc_poll($po_id, $poll_etc)
{
    $poll_etc_table = $GLOBALS['g5']['poll_etc_table'];
    $db = Db::getInstance();
    $db->update($poll_etc_table, ['po_id' => $po_id], [
        'pc_name' => $poll_etc['pc_name'],
        'pc_idea' => $poll_etc['pc_idea'],
        'mb_id' => $poll_etc['mb_id'] ?? '',
    ]);
}

function delete_etc_poll($po_id, $pc_id)
{
    $poll_etc_table = $GLOBALS['g5']['poll_etc_table'];
    $result = Db::getInstance()->delete($poll_etc_table, ['pc_id' => $pc_id, 'po_id' => $po_id, 'mb_id']);
    return $result > 0;
}

function check_auth_etc_poll($pc_id, $mb_id)
{
    $poll_etc_table = $GLOBALS['g5']['poll_etc_table'];
    $result = Db::getInstance()->run("SELECT * FROM $poll_etc_table WHERE pc_id = :pc_id AND mb_id = :mb_id", ['pc_id' => $pc_id, 'mb_id' => $mb_id])->fetch();
    return isset($result['pc_id']);
}