<?php

namespace API\Service;

use API\Database\Db;
use API\v1\Model\Response\Write\Comment;
use Exception;

/**
 * @deprecated 함수 모음으로 변경
 */
class CommentService
{
    public array $board;
    public string $write_table;

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
     * 게시글의 댓글목록 조회
     */
    public function getComments(int $wr_id): array
    {
        $fetch_comments = $this->fetchComments($wr_id);

        $comments = [];
        foreach ($fetch_comments as $comment) {
            $comments[] = new Comment($comment);
        }

        return $comments;
    }

    /**
     * 게시글의 댓글목록 조회 쿼리
     */
    public function fetchComments(int $wr_id): array
    {
        $query = "SELECT * FROM {$this->write_table}
                    WHERE wr_parent = :wr_id
                        AND wr_is_comment = 1
                    ORDER BY wr_comment, wr_comment_reply";
        $stmt = Db::getInstance()->run($query, ['wr_id' => $wr_id]);
        return $stmt->fetchAll();
    }

    public function updateCommentData(int $comment_id, object $data): void
    {
        $data->wr_last = G5_TIME_YMDHIS;
        $data->wr_ip = $_SERVER['REMOTE_ADDR'];

        $this->updateComment($comment_id, (array)$data);
    }

    /**
     * 댓글 작성 처리
     */
    public function createCommentData(array $write, object $data, array $member = [], array $parent_comment = []): int
    {
        $data->ca_name = $write['ca_name'];
        $data->wr_num = $write['wr_num'];
        $data->wr_parent = $write['wr_id'];
        $data->wr_is_comment = 1;
        $data->wr_datetime = G5_TIME_YMDHIS;
        $data->wr_ip = $_SERVER['REMOTE_ADDR'];

        if ($member) {
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
        Db::getInstance()->update($this->write_table, ['wr_id' => $comment_id], $data);
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
