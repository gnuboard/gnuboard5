<?php

namespace API\Service;

use API\Database\Db;

class CurrentConnectService
{
    public function fetchTotalCount(bool $is_show_only_member)
    {
        $config = ConfigService::getConfig();

        $login_table = $GLOBALS['g5']['login_table'];
        $super_admin = $config['cf_admin'];

        $query = "SELECT count(*)
            FROM {$login_table} login
            WHERE login.mb_id <> '{$super_admin}'  AND 
                  login.lo_datetime > '" . date('Y-m-d H:i:s', G5_SERVER_TIME - (60 * $config['cf_login_minutes'])) . "'";
        if ($is_show_only_member) {
            $query .= " AND login.mb_id <> ''";
        }

        $query .= ' ORDER BY login.lo_datetime DESC';
        $stmt = Db::getInstance()->run($query);
        return $stmt->fetchColumn() ?: 0;
    }

    /**
     * @return array
     */
    public function fetchCurrentConnect(bool $is_show_only_member, int $page = 1)
    {
        $config = ConfigService::getConfig();
        $super_admin = $config['cf_admin'];
        $login_table = $GLOBALS['g5']['login_table'];
        $member_table = $GLOBALS['g5']['member_table'];

        $per_pages = $config['cf_page_rows'] ?: 10;
        $offset = ($page - 1) * $per_pages;

        $query = "SELECT login.mb_id, m.mb_nick, m.mb_name, m.mb_email, m.mb_homepage, m.mb_open, m.mb_point, login.lo_ip, login.lo_location, login.lo_url, login.lo_datetime
            FROM {$login_table} login LEFT JOIN {$member_table} m ON (login.mb_id = m.mb_id)
            WHERE login.mb_id <> '{$super_admin}' AND
             login.lo_datetime > '" . date('Y-m-d H:i:s', G5_SERVER_TIME - (60 * $config['cf_login_minutes'])) . "'";

        if ($is_show_only_member) {
            $query .= " AND login.mb_id <> ''";
        }

        $query .= " ORDER BY login.lo_datetime DESC LIMIT {$offset}, {$per_pages}";

        $stmt = Db::getInstance()->run($query);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * @param string $mb_id
     * @param array $data
     * @return void
     */
    public function createConnectInfo($mb_id, $data)
    {
        $login_table = $GLOBALS['g5']['login_table'];
        $query = "INSERT INTO {$login_table} (mb_id, lo_ip, lo_location, lo_url, lo_datetime) VALUES (:mb_id, :lo_ip, :lo_location, :lo_url, :lo_datetime)
            ON DUPLICATE KEY UPDATE lo_ip = :up_lo_ip, lo_location = :up_lo_location, lo_url = :up_lo_url, lo_datetime = :up_lo_datetime";
        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $mb_id,
            'lo_ip' => $_SERVER['REMOTE_ADDR'],
            'lo_location' => $data['lo_location'],
            'lo_url' => $data['lo_url'],
            'lo_datetime' => G5_TIME_YMDHIS,
            'up_lo_ip' => $_SERVER['REMOTE_ADDR'],
            'up_lo_location' => $data['lo_location'],
            'up_lo_url' => $data['lo_url'],
            'up_lo_datetime' => G5_TIME_YMDHIS
        ]);
        $stmt->execute();
    }
}