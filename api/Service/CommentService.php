<?php

namespace API\Service;

use API\Database\Db;
use API\v1\Model\Response\Write\Comment;

class CommentService
{
    public array $board;
    public string $write_table;

    public function __construct(array $board)
    {
        global $g5;

        $this->board = $board;
        $this->write_table = $g5['write_prefix'] . $board['bo_table'];
    }

    public function getComments(int $wr_id): array
    {
        $fetch_comments = $this->fetchComments($wr_id);

        $comments = [];
        foreach ($fetch_comments as $comment) {
            $comments[] = new Comment($comment);
        }

        return $comments;
    }

    public function fetchComments(int $wr_id): array
    {
        $query = "SELECT * FROM {$this->write_table}
                    WHERE wr_parent = :wr_id
                        AND wr_is_comment = 1
                    ORDER BY wr_comment, wr_comment_reply";
        $stmt = Db::getInstance()->run($query, ['wr_id' => $wr_id]);
        return $stmt->fetchAll();
    }
}