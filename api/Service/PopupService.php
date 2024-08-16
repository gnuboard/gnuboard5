<?php

namespace API\Service;

use API\Database\Db;

class PopupService
{


    /**
     * 팝업 데이터 가져오기.
     * @param string $device
     * @return array|false
     */
    public function fetch_popup($device)
    {
        $popup_table = $GLOBALS['g5']['new_win_table'];
        $query = "SELECT * FROM $popup_table WHERE nw_device = :device AND nw_begin_time <= NOW() AND nw_end_time >= NOW() ORDER BY nw_id DESC";
        $result = Db::getInstance()->run($query, ['device' => $device])->fetchAll();
        if (!$result) {
            return false;
        }
        return $result;
    }

    /**
     * 팝업 제외하기.
     * @param array $popups
     * @param string $except_ids
     * @return array
     */
    public function except_popup($popups, $except_ids)
    {
        if ($except_ids) {
            $except_id_array = explode(',', $except_ids);
            return array_filter($popups, function ($popup) use ($except_id_array) {
                return !in_array($popup['nw_id'], $except_id_array);
            });
        }
        return $popups;
    }
    
}