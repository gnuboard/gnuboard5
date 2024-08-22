<?php

namespace API\Service;

use API\Database\Db;
use API\v1\Model\Response\Write\Comment;
use Exception;


class CommentService
{
    public array $board;
    public string $write_table;
    private BoardPermission $board_permission;
    private MemberImageService $image_service;

    public function __construct(BoardPermission $board_permission, MemberImageService $image_service)
    {
        $this->image_service = $image_service;
        $this->board_permission = $board_permission;
    }

    public function setBoard(array $board): void
    {
        $this->board = $board;
        $this->setWriteTable($board['bo_table']);
    }

    public function setWriteTable(string $bo_table): void
    {
        global $g5;
        $this->write_table = $g5['write_prefix'] . $bo_table;
    }

    /**
     * 댓글 1개 조회
     * @param int $wr_id
     * @param string $mb_id
     * @param string|null $password 비밀글 비밀번호
     * @return array
     */
    public function getComment(int $wr_id, string $mb_id, ?string $password): array
    {
        $fetch_comments = [$this->fetchComment($wr_id)];
        $result = [];
        foreach ($fetch_comments as $comment) {
            $comment['save_content'] = $comment['wr_content'];
            $comment['mb_icon_path'] = $this->image_service->getMemberImagePath($comment['mb_id'], 'icon');
            $comment['mb_image_path'] = $this->image_service->getMemberImagePath($comment['mb_id'], 'image');
            $can_read_comment = $this->board_permission->canReadSecretComment($mb_id, $comment, $password);
            // 수정,삭제는 읽기 권한과 동일함.
            $comment['is_del'] = $can_read_comment;
            $comment['is_edit'] = $can_read_comment;
            // 읽기 권한확인
            if (!$can_read_comment) {
                $empty_comment = array_map(function () {
                    return '';
                }, $comment);
                $secret_comment = [
                    'wr_id' => $comment['wr_id'],
                    'wr_parent' => $comment['wr_parent'],
                    'wr_comment_reply' => $comment['wr_comment_reply'],
                    'wr_option' => $comment['wr_option'],
                    'is_secret' => true,
                    'is_secret_content' => true,
                    'save_content' => '비밀글입니다.'
                ];
                $comment = array_merge($empty_comment, $secret_comment);
            }
            // 비밀글 여부 표시
            if(strpos($comment['wr_option'], 'secret') !== false) {
                $comment['is_secret'] = true;
                $comment['is_secret_content'] = true;
            }

            $result[] = new Comment($comment);
        }

        return $result;
    }

    /**
     *  댓글 1개 조회 쿼리
     * @param int $wr_id
     * @return array
     */
    public function fetchComment(int $wr_id): array
    {
        $query = "SELECT * FROM `{$this->write_table}`
                    WHERE wr_id = :wr_id
                        AND wr_is_comment = 1";
        return Db::getInstance()->run($query, [
            'wr_id' => $wr_id,
        ])->fetch();
    }
    
    /**
     * 게시글의 댓글목록 조회
     */
    public function getComments(int $wr_id, string $mb_id, $page, $per_page): array
    {
        $fetch_comments = $this->fetchComments($wr_id, $page, $per_page);
        $result = [];
        foreach ($fetch_comments as $comment) {
            $comment['save_content'] = $comment['wr_content'];
            $comment['mb_icon_path'] = $this->image_service->getMemberImagePath($comment['mb_id'], 'icon');
            $comment['mb_image_path'] = $this->image_service->getMemberImagePath($comment['mb_id'], 'image');
            $can_read_comment = $this->board_permission->canReadSecretComment($mb_id, $comment);
            // 수정,삭제는 읽기 권한과 동일함.
            $comment['is_del'] = $can_read_comment;
            $comment['is_edit'] = $can_read_comment;
            if (!$can_read_comment) {
                $empty_comment = array_map(function () {
                    return '';
                }, $comment);
                $secret_comment = [
                    'wr_id' => $comment['wr_id'],
                    'wr_parent' => $comment['wr_parent'],
                    'wr_comment_reply' => $comment['wr_comment_reply'],
                    'wr_option' => $comment['wr_option'],
                    'is_secret' => true,
                    'is_secret_content' => true,
                    'save_content' => '비밀글입니다.'
                ];
                $comment = array_merge($empty_comment, $secret_comment);
            }

            $result[] = new Comment($comment);
        }

        return $result;
    }

    /**
     * 게시글의 댓글목록 조회 쿼리
     */
    public function fetchComments(int $wr_id, $page, $per_page): array
    {
        $offset = ($page - 1) * $per_page;
        $limit = $per_page;
        $query = "SELECT * FROM {$this->write_table}
                    WHERE wr_parent = :wr_id
                        AND wr_is_comment = 1
                    
                    ORDER BY wr_comment, wr_comment_reply
                    LIMIT :offset, :limit
                    ";
        return Db::getInstance()->run($query, [
            'wr_id' => $wr_id,
            'offset' => $offset,
            'limit' => $limit
        ])->fetchAll();
    }

    public function fetchTotalRecords(int $wr_id)
    {
        $query = "SELECT count(*) FROM `{$this->write_table}`
                    WHERE wr_parent = :wr_id
                        AND wr_is_comment = 1
                    ORDER BY wr_comment, wr_comment_reply";
        $stmt = Db::getInstance()->run($query, [
            'wr_id' => $wr_id
        ]);
        return $stmt->fetchColumn();
    }

    public function updateCommentData(int $comment_id, object $data): void
    {
        $data->wr_last = G5_TIME_YMDHIS;
        $data->wr_ip = $_SERVER['REMOTE_ADDR'];

        $this->updateComment($comment_id, (array)$data);
    }

    /**
     * 댓글 정보를 데이터베이스에 등록
     * @param array $write
     * @param object $data 댓글의 입력 데이터
     * @param array $member
     * @param array $parent_comment
     * @return int
     * @throws Exception
     */
    public function createCommentData(array $write, object $data, array $member = [], array $parent_comment = []): int
    {
        $is_guest = $member['mb_id'] === '';
        $data->ca_name = $write['ca_name'];
        $data->wr_num = $write['wr_num'];
        $data->wr_parent = $write['wr_id'];
        $data->wr_is_comment = 1;
        $data->wr_datetime = G5_TIME_YMDHIS;
        $data->wr_ip = $_SERVER['REMOTE_ADDR'];

        if (!$is_guest) {
            $data->mb_id = $member['mb_id'];
            $data->wr_name = $member['mb_name'];
            $data->wr_email = $member['mb_email'];
            $data->wr_homepage = $member['mb_homepage'];
        }

        if ($parent_comment) {
            $data->wr_comment = $parent_comment['wr_comment'];
            $data->wr_comment_reply = $this->setReplyCharacter($parent_comment);
        } else {
            $data->wr_comment = $this->fetchMaxComment($write['wr_id']) + 1;
            $data->wr_comment_reply = '';
        }

        $insert_id = $this->insertComment($data);
        return $insert_id;
    }

    /**
     * 댓글 정보를 데이터베이스에 업데이트
     */
    public function updateComment(int $comment_id, array $data): void
    {
        Db::getInstance()->update($this->write_table, $data, ['wr_id' => $comment_id]);
    }
    

    /**
     * 댓글 정보를 데이터베이스에 등록
     * @return string|false
     */
    public function insertComment(object $data): string
    {
        return Db::getInstance()->insert($this->write_table, (array)$data);
    }

    /**
     * 대댓글 wr_comment_reply 생성
     * - Exception 관련 코드를 Permission으로 이동하고 싶었으나 코드 중복이 발생하여 이동하지 않음
     */
    public function setReplyCharacter(array $parent_comment): string
    {
        $last_reply = $this->fetchLastReply($parent_comment);

        if ($this->board['bo_reply_order']) {
            $begin_reply_char = 'A';
            $end_reply_char = 'Z';
            $reply_number = +1;
        } else {
            $begin_reply_char = 'Z';
            $end_reply_char = 'A';
            $reply_number = -1;
        }

        if (!$last_reply) {
            $reply_char = $begin_reply_char;
        } else if ($last_reply == $end_reply_char) {
            throw new Exception('더 이상 대댓글을 작성하실 수 없습니다. 대댓글은 26개 까지만 가능합니다.');
        } else {
            $reply_char = chr(ord($last_reply) + $reply_number);
        }

        return $parent_comment['wr_comment_reply'] . $reply_char;
    }

    /**
     * 댓글의 마지막 wr_reply 조회
     */
    public function fetchLastReply(array $parent): string
    {
        $reply_len = strlen($parent['wr_comment_reply']) + 1;
        $order_func = $this->board['bo_reply_order'] ? 'MAX' : 'MIN';
        $values = [
            'reply_len1' => $reply_len,
            'reply_len2' => $reply_len,
            'wr_parent' => $parent['wr_parent'],
            'wr_comment' => $parent['wr_comment']
        ];

        $query = "SELECT {$order_func}(SUBSTRING(wr_comment_reply, :reply_len1, 1)) as reply 
                FROM {$this->write_table} 
                WHERE wr_parent = :wr_parent
                AND wr_comment = :wr_comment 
                AND SUBSTRING(wr_comment_reply, :reply_len2, 1) <> ''";

        if ($parent['wr_comment_reply']) {
            $query .= " AND wr_comment_reply LIKE :wr_comment_reply";
            $values = array_merge($values, ['wr_comment_reply' => $parent['wr_comment_reply'] . '%']);
        }

        $stmt = Db::getInstance()->run($query, $values);
        $row = $stmt->fetch();
        return $row['reply'] ?? '';
    }

    /**
     * 댓글의 최대 wr_comment 조회
     */
    public function fetchMaxComment(int $wr_id): int
    {
        $query = "SELECT MAX(wr_comment) as max_comment FROM {$this->write_table} WHERE wr_parent = :wr_id and wr_is_comment = 1";
        $stmt = Db::getInstance()->run($query, ['wr_id' => $wr_id]);
        $row = $stmt->fetch();
        return $row['max_comment'] ?? 0;
    }

    /**
     * 댓글 삭제
     */
    public function deleteCommentById(int $wr_id): void
    {
        Db::getInstance()->delete($this->write_table, ['wr_id' => $wr_id]);
    }
}
