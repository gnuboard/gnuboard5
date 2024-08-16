<?php

namespace API\Service;

use API\Database\Db;

use function API\Service\Member\fetchMemberById;
use function API\Service\Member\updateMember;

/**
 * 포인트 합계를 지급/차감 별로 계산
 * @param array $points
 * @return array
 */
function calculate_sum(array $points): array
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
 * 포인트 지급
 * - common.lib.php > insert_point() 함수 참고
 * @param string $mb_id 회원 아이디
 * @param int $point 지급할 포인트
 * @param string $content 내용
 * @param string $rel_table 관련 테이블
 * @param string $rel_id 관련 아이디
 * @param string $rel_action 관련 액션
 * @param int $expire 만료일
 * @return bool
 */
function addPoint(
    string $mb_id,
    int $point,
    string $content = '',
    string $rel_table = '',
    string $rel_id = '',
    string $rel_action = '',
    int $expire = 0
) {
    $config = getConfig();
    if (
        !$config['cf_use_point']
        || $point === 0
        || $mb_id === ''
    ) {
        return false;
    }
    $member = fetchMemberById($mb_id);
    if (!$member) {
        return false;
    }
    if ($rel_table || $rel_id || $rel_action) {
        $exists_relation = fetchPointByRelation($mb_id, $rel_table, $rel_id, $rel_action);
        if ($exists_relation) {
            return false;
        }
    }

    $po_expire_date = '9999-12-31';
    if ($config['cf_point_term'] > 0) {
        if ($expire > 0) {
            $po_expire_date = date('Y-m-d', strtotime('+' . ($expire - 1) . ' days', G5_SERVER_TIME));
        } else {
            $po_expire_date = date('Y-m-d', strtotime('+' . ($config['cf_point_term'] - 1) . ' days', G5_SERVER_TIME));
        }
    }

    $po_expired = 0;
    if ($point < 0) {
        $po_expired = 1;
        $po_expire_date = G5_TIME_YMD;
    }

    $mb_point = calculateMemberPointSum($mb_id);
    $po_mb_point = $mb_point + $point;

    $data = [
        'mb_id' => $mb_id,
        'po_datetime' => date('Y-m-d H:i:s'),
        'po_content' => addslashes($content),
        'po_point' => $point,
        'po_mb_point' => $po_mb_point,
        'po_expired' => $po_expired,
        'po_expire_date' => $po_expire_date,
        'po_rel_table' => $rel_table,
        'po_rel_id' => $rel_id,
        'po_rel_action' => $rel_action
    ];
    insertPoint($data);

    if ($point < 0) {
        calculateUsePoint($mb_id, $point);
    }

    updateMember($mb_id, ['mb_point' => $po_mb_point]);

    return true;
}

/**
 * 회원의 전체 포인트 계산
 * - common.lib.php > get_point_sum() 함수 참고
 * @param string $mb_id 회원 아이디
 * @return int 포인트 합계
 */
function calculateMemberPointSum(string $mb_id)
{
    $config = getConfig();
    if ($config['cf_point_term'] > 0) {
        // 소멸포인트가 있으면 내역 추가
        $expire_point = fetchExpirePointSum($mb_id);
        if ($expire_point > 0) {
            $mb = fetchMemberById($mb_id);
            $point = $expire_point * (-1);
            $data = [
                'mb_id' => $mb_id,
                'po_datetime' => date('Y-m-d H:i:s'),
                'po_content' => '포인트 소멸',
                'po_point' => $point,
                'po_mb_point' => $mb['mb_point'] + $point,
                'po_expired' => 1,
                'po_expire_date' => date('Y-m-d'),
                'po_rel_table' => '@expire',
                'po_rel_id' => $mb_id,
                'po_rel_action' => 'expire' . '-' . uniqid('')
            ];
            insertPoint($data);

            // 240726 개선
            // - $expire_point가 양수이고 $point는 * -1 이므로
            // - insert_use_point는 무조건 실행됨
            calculateUsePoint($mb_id, $point);
        }

        updateExpiredPoints($mb_id);
    }

    // 포인트 합계
    return fetchMemberPointSum($mb_id);
}

/**
 * 아직 사용하지 않은 포인트 내역에 사용할 포인트를 기록
 * - common.lib.php > insert_use_point() 함수 참고
 * @param string $mb_id 회원 아이디
 * @param int $point 사용할 포인트
 * @param string $po_id 사용포인트에서 제외할 포인트 아이디
 * @return void
 */
function calculateUsePoint(string $mb_id, int $point, string $po_id = '')
{
    $use_point = abs($point);
    $points = fetchUnusedPoints($mb_id, $po_id);
    foreach ($points as $row) {
        $total_point = (int)$row['po_point'];
        $used_point = (int)$row['po_use_point'];

        $available_point = $total_point - $used_point;
        if ($available_point > $use_point) {
            updateUsePoint($row['po_id'], [
                'po_use_point' => 'po_use_point + ' . $use_point
            ]);
            break;
        } else {
            updateUsePoint($row['po_id'], [
                'po_use_point' => 'po_use_point + ' . $available_point,
                'po_expired' => 100
            ]);

            $use_point -= $available_point;
        }
    }
}

/**
 * 포인트 삭제 처리
 * - common.lib.php > delete_point() 함수 참고
 * @param string $mb_id 회원 아이디
 * @param string $rel_table 관련 테이블
 * @param string $rel_id 관련 아이디
 * @param string $rel_action 관련 액션
 * @return bool
 */
function removePoint(string $mb_id, string $rel_table, string $rel_id, string $rel_action): bool
{
    if (!($rel_table || $rel_id || $rel_action)) {
        return false;
    }

    // 포인트 내역이 없을 경우
    $row = fetchPointByRelation($mb_id, $rel_table, $rel_id, $rel_action);
    if (!(isset($row['po_id']) && $row['po_id'])) {
        return true;
    }

    // 사용 포인트 처리
    if (isset($row['po_point']) && (int)$row['po_point'] < 0) {
        $po_point = abs($row['po_point']);
        removeUsePoint($mb_id, $po_point);
    } elseif (isset($row['po_use_point']) && $row['po_use_point'] > 0) {
        calculateUsePoint($mb_id, $row['po_use_point'], $row['po_id']);
    }

    $result = deletePoint($mb_id, $rel_table, $rel_id, $rel_action);

    // 포인트 내역에 회원포인트 갱신
    updateHistoryForMember($mb_id, $row);

    // 회원의 포인트 내역의 합계를 구하고 업데이트
    $sum_point = calculateMemberPointSum($mb_id);
    updateMember($mb_id, ['mb_point' => $sum_point]);

    return $result;
}

/**
 * 사용포인트 삭제
 * - common.lib.php > delete_use_point() 함수 참고
 * @param string $mb_id 회원 아이디
 * @param int $point 삭제할 포인트
 * @return void
 */
function removeUsePoint(string $mb_id, int $point)
{
    $delete_point = abs($point);
    $points = fetchNonExpiredUsedPoints($mb_id);
    foreach ($points as $row) {
        $used_point = $row['po_use_point'];
        $po_expired = $row['po_expired'];
        $po_expire_date = $row['po_expire_date'];

        if (
            $po_expired == 100
            && ($po_expire_date === '9999-12-31' || $po_expire_date >= G5_TIME_YMD)
        ) {
            $po_expired = 0;
        }

        if ($used_point > $delete_point) {
            updateUsePoint($row['po_id'], [
                'po_use_point' => 'po_use_point - ' . $delete_point,
                'po_expired' => $po_expired
            ]);
            break;
        } else {
            updateUsePoint($row['po_id'], [
                'po_use_point' => '0',
                'po_expired' => $po_expired
            ]);

            $delete_point -= $used_point;
        }
    }
}

/**
 * 만료된 포인트 삭제
 * - common.lib.php > delete_expire_point() 함수 참고
 * @param string $mb_id 회원 아이디
 * @param int $point 삭제할 포인트
 * @return void
 */
function removeExpirePoint(string $mb_id, int $point)
{
    $config = getConfig();
    $delete_point = abs($point);
    $points = fetchExpiredUsedPoints($mb_id);
    foreach ($points as $row) {
        $used_point = $row['po_use_point'];
        $po_expired = '0';
        $po_expire_date = '9999-12-31';

        if ($config['cf_point_term'] > 0) {
            $po_expire_date = date('Y-m-d', strtotime('+' . ($config['cf_point_term'] - 1) . ' days', G5_SERVER_TIME));
        }

        if ($used_point > $delete_point) {
            updateUsePoint($row['po_id'], [
                'po_use_point' => 'po_use_point - ' . $delete_point,
                'po_expired' => $po_expired,
                'po_expire_date' => $po_expire_date
            ]);
            break;
        } else {
            updateUsePoint($row['po_id'], [
                'po_use_point' => '0',
                'po_expired' => $po_expired,
                'po_expire_date' => $po_expire_date
            ]);

            $delete_point -= $used_point;
        }
    }
}

// ========================================
// Database Queries
// ========================================

/**
 * 포인트 내역 총 개수 조회
 * @param string $mb_id 회원 아이디
 * @return int 포인트 내역 총 개수
 */
function fetchTotalPointCount(string $mb_id): int
{
    $point_table = $GLOBALS['g5']['point_table'];
    $query = "SELECT count(*) FROM {$point_table} WHERE mb_id = :mb_id ORDER BY po_id DESC";

    $stmt = Db::getInstance()->run($query, ["mb_id" => $mb_id]);
    return $stmt->fetchColumn() ?: 0;
}

/**
 * 포인트 목록 조회
 * @param string $mb_id 회원 아이디
 * @param array $page_params 페이지 정보
 * @return array|false
 */
function fetchPoints(string $mb_id, array $page_params)
{
    $point_table = $GLOBALS['g5']['point_table'];
    $query = "SELECT * FROM {$point_table} WHERE mb_id = :mb_id ORDER BY po_id DESC LIMIT :offset, :per_page";

    $stmt = Db::getInstance()->run($query, [
        "mb_id" => $mb_id,
        "offset" => $page_params['offset'],
        "per_page" => $page_params['per_page']
    ]);

    return $stmt->fetchAll();
}

/**
 * 관련 정보에 해당하는 포인트 내역 조회
 * @param string $mb_id 회원 아이디
 * @param string $rel_table 관련 테이블
 * @param string $rel_id 관련 아이디
 * @param string $rel_action 관련 액션
 * @return array|false
 */
function fetchPointByRelation(string $mb_id, string $rel_table, string $rel_id, string $rel_action)
{
    $point_table = $GLOBALS['g5']['point_table'];
    $query = "SELECT *
                    FROM {$point_table}
                    WHERE mb_id = :mb_id
                    AND po_rel_table = :po_rel_table
                    AND po_rel_id = :po_rel_id
                    AND po_rel_action = :po_rel_action";

    $stmt = Db::getInstance()->run($query, [
        'mb_id' => $mb_id,
        'po_rel_table' => $rel_table,
        'po_rel_id' => $rel_id,
        'po_rel_action' => $rel_action
    ]);

    return $stmt->fetch();
}

/**
 * 회원의 포인트 합계 조회
 * @param string $mb_id 회원 아이디
 * @return int 포인트 합계
 */
function fetchMemberPointSum(string $mb_id): int
{
    $point_table = $GLOBALS['g5']['point_table'];
    $query = "SELECT SUM(po_point) as sum_point
                    FROM {$point_table}
                    WHERE mb_id = :mb_id";

    $stmt = Db::getInstance()->run($query, ["mb_id" => $mb_id]);
    $row = $stmt->fetch();
    return $row['sum_point'] ?? 0;
}

/**
 * 만료일이 지난 포인트 조회
 * - common.lib.php > get_expire_point() 함수 참고
 * @param string $mb_id 회원 아이디
 * @return int 만료된 포인트 합계
 */
function fetchExpirePointSum(string $mb_id): int
{
    $config = getConfig();
    if ((int)$config['cf_point_term'] === 0) {
        return 0;
    }
    $point_table = $GLOBALS['g5']['point_table'];
    $query = "SELECT SUM(po_point - po_use_point) as sum_point
                    FROM {$point_table}
                    WHERE mb_id = :mb_id
                    AND po_expired = '0'
                    AND po_expire_date <> '9999-12-31'
                    AND po_expire_date < :po_expire_date";

    $stmt = Db::getInstance()->run($query, [
        'mb_id' => $mb_id,
        'po_expire_date' => date('Y-m-d')
    ]);

    $row = $stmt->fetch();
    return $row['sum_point'] ?? 0;
}

/**
 * 사용하지 않은 포인트 내역 조회
 * @param string $mb_id 회원 아이디
 * @param string $po_id 제외할 포인트 아이디
 * @return array
 */
function fetchUnusedPoints(string $mb_id, string $po_id = ''): array
{
    $config = getConfig();
    if ($config['cf_point_term']) {
        $order_by = "ORDER BY po_expire_date ASC, po_id ASC";
    } else {
        $order_by = "ORDER BY po_id ASC";
    }
    $point_table = $GLOBALS['g5']['point_table'];
    $query = "SELECT po_id, po_point, po_use_point
                    FROM {$point_table}
                    WHERE mb_id = :mb_id
                    AND po_id <> :po_id
                    AND po_expired = '0'
                    AND po_point > po_use_point
                    {$order_by}";

    $stmt = Db::getInstance()->run($query, [
        'mb_id' => $mb_id,
        'po_id' => $po_id
    ]);
    return $stmt->fetchAll() ?: [];
}

/**
 * 만료일이 지나지 않은 사용한 포인트 조회
 * @param string $mb_id 회원 아이디
 * @return array
 */
function fetchNonExpiredUsedPoints(string $mb_id): array
{
    $config = getConfig();
    if ($config['cf_point_term']) {
        $order_by = "ORDER BY po_expire_date ASC, po_id ASC";
    } else {
        $order_by = "ORDER BY po_id ASC";
    }
    $point_table = $GLOBALS['g5']['point_table'];
    $query = "SELECT po_id, po_point, po_use_point, po_expire_date
                    FROM {$point_table}
                    WHERE mb_id = :mb_id
                    AND po_expired <> '1'
                    AND po_use_point > 0
                    {$order_by}";

    $stmt = Db::getInstance()->run($query, [
        'mb_id' => $mb_id,
    ]);
    return $stmt->fetchAll() ?: [];
}

/**
 * 만료되고 사용한 포인트 조회
 * @param string $mb_id 회원 아이디
 * @return array
 */
function fetchExpiredUsedPoints(string $mb_id): array
{
    $point_table = $GLOBALS['g5']['point_table'];
    $query = "SELECT po_id, po_point, po_use_point, po_expire_date
                    FROM {$point_table}
                    WHERE mb_id = :mb_id
                    AND po_expired = '1'
                    AND po_point >= 0
                    AND po_use_point > 0
                    ORDER BY po_expire_date DESC, po_id DESC";

    $stmt = Db::getInstance()->run($query, [
        'mb_id' => $mb_id,
    ]);
    return $stmt->fetchAll() ?: [];
}

/**
 * 포인트 내역 추가
 * @param array $data 추가할 데이터
 * @return int 추가된 포인트 아이디
 */
function insertPoint(array $data): int
{
    $point_table = $GLOBALS['g5']['point_table'];
    $insert_id = Db::getInstance()->insert($point_table, $data);

    return (int)$insert_id;
}

/**
 * 사용하지 않은 포인트 내역 업데이트
 * @param int $po_id 포인트 아이디
 * @param array $fields 업데이트할 필드
 * @return void
 */
function updateUsePoint(int $po_id, array $fields): void
{
    $set_clause = [];
    $params = [];
    foreach ($fields as $field => $value) {
        $set_clause[] = "{$field} = :{$field}";
        $params[$field] = $value;
    }
    $set_clause = implode(', ', $set_clause);
    $point_table = $GLOBALS['g5']['point_table'];
    $query = "UPDATE {$point_table} SET {$set_clause} WHERE po_id = :po_id";

    Db::getInstance()->run($query, array_merge($params, ['po_id' => $po_id]));
}

/**
 * 유효기간이 지난 포인트 만료처리
 * @param string $mb_id 회원 아이디
 * @return int 만료된 포인트 개수
 */
function updateExpiredPoints(string $mb_id): int
{
    $point_table = $GLOBALS['g5']['point_table'];
    $query = "UPDATE {$point_table}
                    SET po_expired = '1'
                    WHERE mb_id = :mb_id
                    AND po_expired <> '1'
                    AND po_expire_date <> '9999-12-31'
                    AND po_expire_date < :po_expire_date";
    $stmt = Db::getInstance()->run($query, [
        'mb_id' => $mb_id,
        'po_expire_date' => date('Y-m-d')
    ]);

    return $stmt->rowCount();
}

/**
 * 포인트 목록에 회원 포인트 갱신
 * - 전달된 이력보다 나중에 발생한 이력을 갱신
 * @param string $mb_id 회원 아이디
 * @param array $row 포인트 내역
 */
function updateHistoryForMember(string $mb_id, array $row): void
{
    if (isset($row['po_point'])) {
        $point_table = $GLOBALS['g5']['point_table'];
        $query = "UPDATE {$point_table}
                    SET po_mb_point = po_mb_point - :po_point
                    WHERE mb_id = :mb_id
                    AND po_id > :po_id";

        Db::getInstance()->run($query, [
            ':po_point' => $row['po_point'],
            ':mb_id' => $mb_id,
            ':po_id' => $row['po_id'],
        ]);
    }
}

/**
 * 포인트 내역 삭제
 * @param string $mb_id 회원 아이디
 * @param string $rel_table 관련 테이블
 * @param string $rel_id 관련 아이디
 * @param string $rel_action 관련 액션
 * @return bool
 */
function deletePoint(string $mb_id, string $rel_table, string $rel_id, string $rel_action): bool
{
    $point_table = $GLOBALS['g5']['point_table'];
    $row_count = Db::getInstance()->delete($point_table, [
        'mb_id' => $mb_id,
        'po_rel_table' => $rel_table,
        'po_rel_id' => $rel_id,
        'po_rel_action' => $rel_action
    ]);

    return $row_count > 0;
}
