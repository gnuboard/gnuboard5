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


