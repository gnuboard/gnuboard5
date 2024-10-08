<?php

namespace API\Service;

use API\Database\Db;


class MemoService
{
    private MemberService $member_service;

    public function __construct(MemberService $member_service)
    {
        $this->member_service = $member_service;
    }

    /**
     * 회원 모든 쪽지를 조회한다
     * @param string $me_type
     * @param string $mb_id
     * @param int $page
     * @param int $per_page
     * @return array
     */
    public function getMemos(string $me_type, string $mb_id, int $page, int $per_page)
    {
        $result = $this->fetchMemos($me_type, $mb_id, $page, $per_page);
        foreach ($result as &$memo) {
            $memo['me_is_read'] = $memo['me_read_datetime'] !== '0000-00-00 00:00:00';
        }
        return $result;
    }

    /**
     * 메모의 전체 카운트 수 조회
     * @param string $me_type
     * @param string $mb_id
     * @return int
     */
    public function fetchTotalCount($me_type, $mb_id)
    {
        $memo_table = $GLOBALS['g5']['memo_table'];
        if ($me_type === 'recv') {
            $where = 'me_recv_mb_id = :mb_id AND me_type = :me_type AND me_read_datetime = :me_read_datetime';
        } else {
            $where = 'me_send_mb_id = :mb_id AND me_type = :me_type AND me_read_datetime = :me_read_datetime';
        }

        $query = "SELECT count(*) FROM $memo_table WHERE {$where}";
        // 그누보드 5 에서는 me_read_datetime 컬럼이 '0000-00-00 00:00:00' 으로 초기화 되어 있음
        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $mb_id,
            'me_type' => $me_type,
            'me_read_datetime' => '0000-00-00 00:00:00'
        ]);
        return $stmt->fetchColumn() ?: 0;
    }

    /**
     * 읽기여부 관계없이 회원의 모든 쪽지를 조회한다.
     * @param string $me_type
     * @param string $mb_id
     * @param int $page
     * @param int $per_page
     * @return array
     */
    public function fetchMemos(string $me_type, string $mb_id, int $page, int $per_page)
    {
        if ($me_type === 'recv') {
            $where = 'me_recv_mb_id = :mb_id AND me_type = :me_type';
        } else {
            $where = 'me_send_mb_id = :mb_id AND me_type = :me_type';
        }

        $memo_table = $GLOBALS['g5']['memo_table'];
        $query = "SELECT * FROM $memo_table WHERE {$where} ORDER BY me_id DESC LIMIT :offset, :limit";
        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $mb_id,
            'me_type' => $me_type,
            'offset' => ($page - 1) * $per_page,
            'limit' => $per_page
        ]);

        $result = $stmt->fetchAll();
        if (empty($result)) {
            return [];
        }
        return $result;
    }

    /**
     * 쪽지 보내기 가능한 맴버 조회
     * @param string $receiver_ids
     * @return array ['available_ids' => [], 'not_available_ids' => []]
     */
    public function getReceiveMembers(string $receiver_ids)
    {
        $member_table = $GLOBALS['g5']['member_table'];
        $send_target_ids = explode(',', $receiver_ids);
        $where_in_placeholder = Db::makeWhereInPlaceHolder($send_target_ids);
        $member_info_open_query = "SELECT mb_id, mb_open, mb_leave_date FROM {$member_table} WHERE mb_id IN ({$where_in_placeholder}) AND mb_open = 1";
        $stmt = Db::getInstance()->run($member_info_open_query, $send_target_ids);
        $result = $stmt->fetchAll();

        $available_ids = [];
        foreach ($result as $row) {
            if (isset($row['mb_open']) && $row['mb_open'] == 1 && empty($row['mb_leave_date'])) {
                $available_ids[] = $row['mb_id'];
            }
        }

        $not_available_ids = array_diff($send_target_ids, $available_ids);

        return [
            'available_ids' => $available_ids,
            'not_available_ids' => $not_available_ids
        ];
    }

    /**
     * 쪽지 전송
     * @param string $mb_id
     * @param string $receiver_ids
     * @param string $content
     * @return array int[] 전송된 쪽지의 테이블 번호
     * @throws \Exception 회원 정보가 없습니다.
     * @throws \Exception 쪽지를 전송할 회원이 없습니다.
     */
    public function sendMemo($mb_id, $receiver_ids, $content, $ip)
    {
        $result = $this->getReceiveMembers($receiver_ids);
        $member_result = $this->member_service->fetchMemberById($mb_id);
        if (!isset($member_result['mb_no'])) {
            throw new \Exception('회원 정보가 없습니다.', 400);
        }
        $mb_no = $member_result['mb_no'];

        if (empty($result['available_ids'])) {
            throw new \Exception('쪽지를 전송할 회원이 없습니다.', 400);
        }

        $receiver_id_list = $result['available_ids'];
        if (count($result['not_available_ids']) !== 0 && (count($result['not_available_ids']) === count($receiver_id_list))) {
            throw new \Exception(' 존재(또는 정보공개)하지 않는 회원이거나 탈퇴/차단된 회원입니다."', 400);
        }

        $memo_table = $GLOBALS['g5']['memo_table'];
        $send_memo_ids = [];
        foreach ($result['available_ids'] as $receiver_id) {
            // 받는 회원
            $last_insert_id = Db::getInstance()->insert($memo_table, [
                'me_recv_mb_id' => $receiver_id,
                'me_send_mb_id' => $mb_id,
                'me_send_datetime' => G5_TIME_YMDHIS,
                'me_memo' => $content,
                'me_type' => 'recv',
                'me_send_id' => $mb_no,
                'me_send_ip' => $ip
            ]);

            if ($last_insert_id) {
                // 보낸 회원기록
                Db::getInstance()->insert($memo_table, [
                    'me_recv_mb_id' => $receiver_id,
                    'me_send_mb_id' => $mb_id,
                    'me_send_datetime' => G5_TIME_YMDHIS,
                    'me_memo' => $content,
                    'me_type' => 'send',
                    'me_send_id' => $mb_no,
                    'me_send_ip' => $ip
                ]);

                $send_memo_ids[] = $last_insert_id;
            }
        }

        return $send_memo_ids;
    }

    /**
     * 1건의 쪽지를 조회한다.
     * @param int $memo_id
     * @param string $member_id
     * @return array
     * @throws \Exception 읽기 권한이 없습니다.
     */
    public function fetchMemo($memo_id, $member_id)
    {
        $memo_table = $GLOBALS['g5']['memo_table'];
        $query = "SELECT * FROM $memo_table WHERE me_id = :me_id";
        $memo = Db::getInstance()->run($query, ['me_id' => $memo_id])->fetch();
        if (isset($memo['me_recv_mb_id']) && $memo['me_recv_mb_id'] !== $member_id) {
            throw new \Exception('권한이 없습니다.', 403);
        }

        return $memo;
    }

    /**
     * 쪽지를 읽음 표시한다.
     * @param int $memo_id
     * @return bool
     */
    public function checkRead($memo_id)
    {
        $memo_table = $GLOBALS['g5']['memo_table'];
        $row_count = Db::getInstance()->update($memo_table, ['me_read_datetime' => G5_TIME_YMDHIS], ['me_id' => $memo_id]);
        return $row_count > 0;
    }

    /**
     *
     * @param int $memo_id
     * @param string $mb_id
     * @return int
     * @throws \Exception 권한이 없습니다.
     */
    public function deleteMemo(int $memo_id, string $mb_id)
    {
        $memo_table = $GLOBALS['g5']['memo_table'];
        $query = "SELECT * FROM $memo_table WHERE me_id = :me_id";
        $memo = Db::getInstance()->run($query, ['me_id' => $memo_id])->fetch();
        if (isset($memo['me_recv_mb_id']) && $memo['me_recv_mb_id'] !== $mb_id) {
            throw new \Exception('권한이 없습니다.', 403);
        }

        return Db::getInstance()->delete($memo_table, ['me_id' => $memo_id]);
    }

    /**
     * 메모 알림을 삭제합니다.
     * @param int $memo_id
     * @return void
     * @throws \Exception 권한이 없습니다.
     */
    public function deleteMemoCall(int $memo_id)
    {
        $memo_table = $GLOBALS['g5']['memo_table'];
        $query = "SELECT * FROM $memo_table WHERE me_id = :me_id";
        $memo = Db::getInstance()->run($query, ['me_id' => $memo_id])->fetch();
        if (!isset($memo['me_recv_mb_id'])) {
            throw new \Exception('해당 쪽지가 없습니다.', 404);
        }

        //reset mb_memo_call
        $member_table = $GLOBALS['g5']['member_table'];
        Db::getInstance()->update(
            $member_table,
            [
                'mb_memo_call' => ''
            ],
            [
                'mb_id' => $memo['me_recv_mb_id'],
                'mb_memo_call' => $memo['me_send_mb_id']
            ]
        );

        $this->decreseNotReadMemoCount($memo['me_recv_mb_id']);
    }

    /**
     * 읽지 않은 쪽지 갯수 업데이트
     * @param string $receiver_mb_id 받는 회원 아이디
     * @return int
     */
    public function updateNotReadMemoCount(string $receiver_mb_id)
    {
        $not_read_memo_count = $this->fetchNotReadMemoCount($receiver_mb_id);
        $member_table = $GLOBALS['g5']['member_table'];
        return Db::getInstance()->update($member_table, ['mb_memo_cnt' => $not_read_memo_count], ['mb_id' => $receiver_mb_id]);
    }

    /**
     * 읽지 않은 쪽지 갯수 감소
     * @param string $receiver_mb_id
     * @return int|void
     */
    public function decreseNotReadMemoCount(string $receiver_mb_id)
    {
        $not_read_memo_count = $this->fetchNotReadMemoCount($receiver_mb_id);
        if ($not_read_memo_count === 0) {
            return;
        }

        $member_table = $GLOBALS['g5']['member_table'];
        return Db::getInstance()->update($member_table, ['mb_memo_cnt' => $not_read_memo_count - 1], ['mb_id' => $receiver_mb_id]);
    }

    /**
     * 읽지 않은 쪽지 갯수 조회
     * @param string $mb_id
     * @return int
     */
    public function fetchNotReadMemoCount(string $mb_id)
    {
        $memo_table = $GLOBALS['g5']['memo_table'];
        $result = Db::getInstance()->run(
            "SELECT count(*) as cnt FROM $memo_table 
                       WHERE me_recv_mb_id = :mb_id
                         AND me_type = 'recv'
                         AND me_read_datetime = '0000-00-00 00:00:00'",
            ['mb_id' => $mb_id]
        )->fetch();
        return $result['cnt'] ?: 0;
    }

}
