<?php

namespace API\Service;

use API\Database\Db;
use Slim\Psr7\Request;

class MemoService
{

    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * 메모의 전체 카운트 수 조회
     * @param $meType
     * @param $mb_id
     * @return int
     */
    public function fetchTotalRecords($meType, $mb_id)
    {
        $memo_table = G5_TABLE_PREFIX . 'memo';
        if ($meType == 'recv') {
            // 그누보드 5 버전에서는 me_read_datetime 필드가 '0000-00-00 00:00:00' 으로 초기화 되어 있음
            $where = "me_recv_mb_id = :mb_id AND me_type = :me_type AND me_read_datetime = '0000-00-00 00:00:00'";
        } else {
            $where = "me_send_mb_id = :mb_id AND me_type = :me_type AND me_read_datetime = '0000-00-00 00:00:00'";
        }

        $query = "SELECT count(*) FROM $memo_table WHERE {$where}";
        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $mb_id,
            'me_type' => $meType,
        ]);
        return $stmt->fetchColumn();
    }

    public function fetchMemos($meType, $getAttribute, int $page, int $perPage)
    {
        if ($meType == 'recv') {
            $where = "me_recv_mb_id = :mb_id AND me_type = :me_type AND (me_read_datetime = '0000-00-00 00:00:00')";
        } else {
            $where = "me_send_mb_id = :mb_id AND me_type = :me_type AND (me_read_datetime = '0000-00-00 00:00:00')";
        }

        $memo_table = G5_TABLE_PREFIX . 'memo';
        $query = "SELECT * FROM $memo_table WHERE {$where} ORDER BY me_id DESC LIMIT :offset, :limit";
        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $getAttribute,
            'me_type' => $meType,
            'offset' => ($page - 1) * $perPage,
            'limit' => $perPage
        ]);

        return $stmt->fetchAll();
    }

    /**
     * 쪽지 보내기 가능한 맴버 조회
     * @param $receiverIds
     * @return array ['availableIds' => [], 'notAvailableIds' => []]
     */
    public function getReciveMembers($receiverIds)
    {
        $g5 = $GLOBALS['g5'];
        $targetIdArray = explode(',', $receiverIds);

        $whereInplaceholder = Db::makeWhereInPlaceHolder($targetIdArray);
        $memberInfoOpenQuery = "SELECT mb_id, mb_id_open FROM {$g5['member_table']} WHERE mb_id IN ({$whereInplaceholder}) AND mb_memo_open = 1";
        $stmt = Db::getInstance()->run($memberInfoOpenQuery, $targetIdArray);
        $result = $stmt->fetchAll();

        $availableIds = [];
        foreach ($result as $row) {
            if ($row['mb_id_open'] == 1 && $row['mb_leave_date'] && $row['mb_leave_date'] <= date("Ymd", G5_SERVER_TIME)) {
                $availableIds[] = $row['mb_id'];
            }
        }

        $notAvailableIds = array_diff($receiverIds, $availableIds);

        return [
            'availableIds' => $availableIds,
            'notAvailableIds' => $notAvailableIds
        ];
    }

    /**
     * @param $mb_id
     * @param $reciverIds
     * @param $content
     * @return bool|string[] ['error' => '쪽지를 전송할 회원이 없습니다.', code => 400]
     */
    public function sendMemo($mb_id, $reciverIds, $content)
    {
        $result = $this->getReciveMembers($reciverIds);

        if (empty($result['availableIds'])) {
            return ['error' => '쪽지를 전송할 회원이 없습니다.', 'code' => 400];
        }

        $reciverIdArray = explode(',', $result['availableIds']);
        if (count($result['notAvailableIds']) !== 0 && (count($result['notAvailableIds']) == count($reciverIdArray))) {
            return ['error' => ' 존재(또는 정보공개)하지 않는 회원이거나 탈퇴/차단된 회원입니다."', 'code' => 400];
        }

        $memo_table = $GLOBALS['g5']['memo_table'];

        foreach ($result['availableIds'] as $reciverId) {
            $lastInsertId = Db::getInstance()->insert($memo_table, [
                'me_recv_mb_id' => $mb_id,
                'me_send_mb_id' => $reciverId,
                'me_send_datetime' => G5_TIME_YMDHIS,
                'me_memo' => $content,
                'me_type' => 'recv',
                'me_ip' => $this->request->getServerParams()['REMOTE_ADDR']
            ]);

            if ($lastInsertId) {
                Db::getInstance()->insert($memo_table, [
                    'me_recv_mb_id' => $reciverId,
                    'me_send_mb_id' => $mb_id,
                    'me_send_datetime' => G5_TIME_YMDHIS,
                    'me_memo' => $content,
                    'me_type' => 'send',
                    'me_ip' => $this->request->getServerParams()['REMOTE_ADDR']
                ]);
            }
        }

        return true;
    }

    /**
     * 1건의 쪽지를 조회한다.
     * @param $memoId
     * @param $memberId
     * @return array
     */
    public function fetchMemo($memoId, $memberId)
    {
        $memo_table = G5_TABLE_PREFIX . 'memo';
        $query = "SELECT * FROM $memo_table WHERE me_id = :me_id";
        $memo = Db::getInstance()->run($query, ['me_id' => $memoId])->fetch();
        if (isset($memo['me_recv_mb_id']) && $memo['me_recv_mb_id'] !== $memberId) {
            return ['error' => '권한이 없습니다.', 'code' => 403];
        }

        Db::getInstance()->update($memo_table, ['me_id' => $memoId], ['me_read_datetime' => G5_TIME_YMDHIS]);
        return $memo;
    }

    /**
     *
     * @param int $memoId
     * @param string $memberId
     * @return array|int ['error' => '권한이 없습니다.', 'code' => 403] , 삭제된 row 수
     */
    public function deleteMemo($memoId, $memberId)
    {
        $memo_table = G5_TABLE_PREFIX . 'memo';
        $query = "SELECT * FROM $memo_table WHERE me_id = :me_id";
        $memo = Db::getInstance()->run($query, ['me_id' => $memoId])->fetch();
        if (isset($memo['me_recv_mb_id']) && $memo['me_recv_mb_id'] !== $memberId) {
            return ['error' => '권한이 없습니다.', 'code' => 403];
        }

        return Db::getInstance()->delete($memo_table, ['me_id' => $memoId]);
    }

    /**
     * 메모 알림을 삭제합니다.
     * @param $memoId
     * @return void
     */
    public function deleteMemoCall($memoId)
    {
        $memoTable = $GLOBALS['g5']['memo_table'];
        $query = "SELECT * FROM $memoTable WHERE me_id = :me_id";
        $memo = Db::getInstance()->run($query, ['me_id' => $memoId])->fetch();
        if ($memo['me_read_datetime'] == '0000-00-00 00:00:00') {
            return;
        }

        //reset mb_memo_call
        Db::getInstance()->update($memoTable,
            [
                'mb_id' => $memo['me_recv_mb_id'],
                'mb_memo_call' => $memo['me_send_mb_id']
            ],
            [
                'mb_memo_call' => ''
            ]
        );
    }


}