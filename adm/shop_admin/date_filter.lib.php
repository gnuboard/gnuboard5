<?php
if (!defined('_GNUBOARD_')) exit;

// 날짜 없는 최초 조회와 잘못된 날짜 조회를 구분한다. 오류는 false로 반환한다.
function shop_admin_date_range($params, $default_to = '')
{
    if (!array_key_exists('fr_date', $params) && !array_key_exists('to_date', $params)) {
        return array('', $default_to);
    }

    foreach (array('fr_date', 'to_date') as $key) {
        if (!isset($params[$key]) || !is_string($params[$key]) || !preg_match('/\A[0-9]{8}\z/', $params[$key])) {
            return false;
        }
        $date = $params[$key];
        if (!checkdate((int) substr($date, 4, 2), (int) substr($date, 6, 2), (int) substr($date, 0, 4))) {
            return false;
        }
    }

    if ($params['fr_date'] > $params['to_date']) {
        return false;
    }

    return array($params['fr_date'], $params['to_date']);
}
