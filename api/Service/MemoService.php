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
     * @param string $me_type
     * @param string $mb_id
     * @return int
     */
    public function fetch_total_records($me_type, $mb_id)
    {
        $memo_table = $GLOBALS['g5']['memo_table'];
        if ($me_type == 'recv') {
            $where = "me_recv_mb_id = :mb_id AND me_type = :me_type AND me_read_datetime = :me_read_datetime";
        } else {
            $where = "me_send_mb_id = :mb_id AND me_type = :me_type AND me_read_datetime = :me_read_datetime";
        }

        $query = "SELECT count(*) FROM $memo_table WHERE {$where}";
        // 그누보드 5 에서는 me_read_datetime 컬럼이 '0000-00-00 00:00:00' 으로 초기화 되어 있음
        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $mb_id,
            'me_type' => $me_type,
            'me_read_datetime' => '0000-00-00 00:00:00'
        ]);
        return $stmt->fetchColumn();
    }

    /**
     * @param string $me_type
     * @param string $mb_id
     * @param int $page
     * @param int $per_page
     * @return array
     */
    public function fetch_memos(string $me_type, string $mb_id, int $page, int $per_page)
    {
        if ($me_type == 'recv') {
            $where = "me_recv_mb_id = :mb_id AND me_type = :me_type AND me_read_datetime = :me_read_datetime";
        } else {
            $where = "me_send_mb_id = :mb_id AND me_type = :me_type AND me_read_datetime = :me_read_datetime";
        }

        $memo_table = $GLOBALS['g5']['memo_table'];
        $query = "SELECT * FROM $memo_table WHERE {$where} ORDER BY me_id DESC LIMIT :offset, :limit";
        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $mb_id,
            'me_type' => $me_type,
            'me_read_datetime' => '0000-00-00 00:00:00',
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
    public function get_recive_members(string $receiver_ids)
    {
        $member_table = $GLOBALS['g5']['member_table'];
        $send_target_ids = explode(',', $receiver_ids);
        $where_in_placeholder = Db::makeWhereInPlaceHolder($send_target_ids);
        $member_Info_open_query = "SELECT mb_id, mb_open, mb_leave_date FROM {$member_table} WHERE mb_id IN ({$where_in_placeholder}) AND mb_open = 1";
        $stmt = Db::getInstance()->run($member_Info_open_query, $send_target_ids);
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
     * @param string $mb_id
     * @param string $receiver_ids
     * @param string $content
     * @return bool|string[] ['error' => '쪽지를 전송할 회원이 없습니다.', code => 400]
     */
    public function send_memo($mb_id, $receiver_ids, $content)
    {
        $result = $this->get_recive_members($receiver_ids);

        $member_result = get_member($mb_id, 'mb_no');
        if (!isset($member_result['mb_no'])) {
            return ['error' => '회원 정보가 없습니다.', 'code' => 400];
        }
        $mb_no = $member_result['mb_no'];

        if (empty($result['available_ids'])) {
            return ['error' => '쪽지를 전송할 회원이 없습니다.', 'code' => 400];
        }

        $receiver_id_list = $result['available_ids'];
        if (count($result['not_available_ids']) !== 0 && (count($result['not_available_ids']) == count($receiver_id_list))) {
            return ['error' => ' 존재(또는 정보공개)하지 않는 회원이거나 탈퇴/차단된 회원입니다."', 'code' => 400];
        }

        $memo_table = $GLOBALS['g5']['memo_table'];

        foreach ($result['available_ids'] as $receiver_id) {
            $last_insert_id = Db::getInstance()->insert($memo_table, [
                'me_recv_mb_id' => $receiver_id,
                'me_send_mb_id' => $mb_id,
                'me_send_datetime' => G5_TIME_YMDHIS,
                'me_memo' => $content,
                'me_type' => 'recv',
                'me_send_id' => $mb_no,
                'me_send_ip' => $this->request->getServerParams()['REMOTE_ADDR']
            ]);

            if ($last_insert_id) {
                Db::getInstance()->insert($memo_table, [
                    'me_recv_mb_id' => $receiver_id,
                    'me_send_mb_id' => $mb_id,
                    'me_send_datetime' => G5_TIME_YMDHIS,
                    'me_memo' => $content,
                    'me_type' => 'send',
                    'me_send_id' => $mb_no,
                    'me_send_ip' => $this->request->getServerParams()['REMOTE_ADDR']
                ]);
            }
        }

        return true;
    }

    /**
     * 1건의 쪽지를 조회한다.
     * @param int $memo_id
     * @param string $member_id
     * @return array
     */
    public function fetch_memo($memo_id, $member_id)
    {
        $memo_table = $GLOBALS['g5']['memo_table'];
        $query = "SELECT * FROM $memo_table WHERE me_id = :me_id";
        $memo = Db::getInstance()->run($query, ['me_id' => $memo_id])->fetch();
        if (isset($memo['me_recv_mb_id']) && $memo['me_recv_mb_id'] !== $member_id) {
            return ['error' => '권한이 없습니다.', 'code' => 403];
        }

        return $memo;
    }

    /**
     * 쪽지를 읽음 표시한다.
     * @param int $memo_id
     * @return bool
     */
    public function read_check($memo_id)
    {
        $memo_table = $GLOBALS['g5']['memo_table'];
        $row_count = Db::getInstance()->update($memo_table, ['me_id' => $memo_id], ['me_read_datetime' => G5_TIME_YMDHIS]);
        return $row_count > 0;
    }

    /**
     *
     * @param int $memo_id
     * @param string $mb_id
     * @return array|int ['error' => '권한이 없습니다.', 'code' => 403] , 삭제된 row 수
     */
    public function delete_memo(int $memo_id, string $mb_id)
    {
        $memo_table = $GLOBALS['g5']['memo_table'];
        $query = "SELECT * FROM $memo_table WHERE me_id = :me_id";
        $memo = Db::getInstance()->run($query, ['me_id' => $memo_id])->fetch();
        if (isset($memo['me_recv_mb_id']) && $memo['me_recv_mb_id'] !== $mb_id) {
            return ['error' => '권한이 없습니다.', 'code' => 403];
        }

        return Db::getInstance()->delete($memo_table, ['me_id' => $memo_id]);
    }

    /**
     * 메모 알림을 삭제합니다.
     * @param int $memo_id
     * @return array|void ['error' => '권한이 없습니다.', 'code' => 403]
     */
    public function delete_memo_call(int $memo_id)
    {
        $memoTable = $GLOBALS['g5']['memo_table'];
        $query = "SELECT * FROM $memoTable WHERE me_id = :me_id";
        $memo = Db::getInstance()->run($query, ['me_id' => $memo_id])->fetch();
        if (!isset($memo['me_recv_mb_id'])) {
            return ['error' => '해당 쪽지가 없습니다.', 'code' => 404];
        }

        if ($memo['me_read_datetime'] == '0000-00-00 00:00:00') {
            return;
        }

        //reset mb_memo_call
        $member_table = $GLOBALS['g5']['member_table'];
        Db::getInstance()->update($member_table,
            [
                'mb_id' => $memo['me_recv_mb_id'],
                'mb_memo_call' => $memo['me_send_mb_id']
            ],
            [
                'mb_memo_call' => ''
            ]
        );

        $this->update_not_read_memo_count($memo['me_recv_mb_id']);
    }

    /**
     * 안읽은 쪽지 수 업데이트
     * @param string $receiver_mb_id 받는 회원 아이디
     */
    public function update_not_read_memo_count(string $receiver_mb_id): int
    {
        $not_read_memo_count = $this->not_read_memo_count($receiver_mb_id);
        $member_table = $GLOBALS['g5']['member_table'];
        return Db::getInstance()->update($member_table, ['mb_id' => $receiver_mb_id], ['mb_memo_cnt' => $not_read_memo_count]);
    }

    /**
     * 읽지 않은 쪽지 수 조회
     * @param string $mb_id
     * @return int
     */
    public function not_read_memo_count($mb_id)
    {
        $memo_table = $GLOBALS['g5']['memo_table'];
        $result = Db::getInstance()->run("SELECT count(*) as cnt FROM $memo_table 
                       WHERE me_recv_mb_id = :mb_id  
                         AND me_type = 'recv'
                         AND me_read_datetime = '0000-00-00 00:00:00'",
            ['mb_id' => $mb_id])->fetch();
        return $result['cnt'] ?? 0;
    }


}