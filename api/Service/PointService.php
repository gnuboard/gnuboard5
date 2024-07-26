<?php

namespace API\Service;

use API\Database\Db;
use Exception;

class PointService
{
    public string $table;

    public function __construct()
    {
        global $g5;
        $this->setTable($g5['point_table']);
    }

    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    /**
     * 포인트 합계를 지급/차감 별로 계산
     * @param array $points
     * @return array
     */
    public function calculate_sum(array $points): array
    {
        $sum = ['negative' => 0, 'positive' => 0];

        foreach ($points as $point) {
            if ($point['po_point'] < 0) {
                $sum['negative'] += $point['po_point'];
            } else {
                $sum['positive'] += $point['po_point'];
            }
        }

        return $sum;
    }

    /**
     * 포인트 내역 총 개수 조회
     * @param string $mb_id 회원 아이디
     * @return int 포인트 내역 총 개수
     */
    public function fetchTotalPointCount(string $mb_id): int
    {
        $query = "SELECT count(*) FROM {$this->table} WHERE mb_id = :mb_id ORDER BY po_id DESC";

        $stmt = Db::getInstance()->run($query, ["mb_id" => $mb_id]);
        return $stmt->fetchColumn() ?? 0;
    }

    /**
     * 포인트 목록 조회
     * @param string $mb_id 회원 아이디
     * @param array $page_params 페이지 정보
     * @return mixed 포인트 목록 or false
     */
    public function fetchPoints(string $mb_id, array $page_params): mixed
    {
        $query = "SELECT * FROM {$this->table} WHERE mb_id = :mb_id ORDER BY po_id DESC LIMIT :offset, :per_page";

        $stmt = Db::getInstance()->run($query, [
            "mb_id" => $mb_id,
            "offset" => $page_params['offset'],
            "per_page" => $page_params['per_page']
        ]);

        return $stmt->fetchAll();
    }
}
