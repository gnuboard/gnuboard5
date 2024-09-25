<?php

namespace API\Hooks\Alarm;

use API\Service\AlarmService;

class AlarmHooks
{

    public function __construct(AlarmService $alarm_service)
    {
        $this->alarm_service = $alarm_service;
    }

    public function sender_after_comment($board, $write, $comment_id, $parent_comment)
    {
        global $app;

        /**
         * @var \API\Service\CommentService $comment_serivce
         */
        $comment_serivce = $app->getContainer()->get('API\Service\CommentService');
        $comment = $comment_serivce->fetchComment($comment_id);
        if (!$comment['mb_id']) {
            return false;
        }

        /**
         * @var \API\Service\MemberService $member_service
         */
        $member_service = $app->getContainer()->get('API\Service\MemberService');
        $member = $member_service->fetchMemberById($comment['mb_id']);
        $title = $write['wr_subject'] . '에 댓글이 달렸습니다.';
        $body = ($member['mb_nick'] ?? '') . '님이 댓글을 달았습니다.';
        $fcm_tokens = $this->alarm_service->fetchFcmToken($comment['mb_id']);
        $order = $comment_serivce->fetchCommentOrder($write['wr_id'], $comment['wr_id']);

        foreach ($fcm_tokens as $fcm_token) {
            $data = $this->alarm_service->createMessage(['token', $fcm_token['ft_token']], $title, $body);
            $data = $this->alarm_service->addData($data,
                [
                    'alarm_type' => 'comment',
                    'bo_table' => $board['bo_table'],
                    'wr_id' => (string)$write['wr_id'],
                    'comment_id' => (string)$comment_id,
                    'order' => (string)$order
                ]);
            $this->alarm_service->sendMessage($data);
        }
    }
}