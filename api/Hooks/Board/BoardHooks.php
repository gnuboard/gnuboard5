<?php

namespace API\Hooks\Board;

use API\Database\Db;
use API\Service\BoardService;
use API\Service\CommentService;
use API\Service\ConfigService;
use API\Service\MailService;
use API\Service\MemberService;
use API\Service\WriteService;

class BoardHooks
{

    private BoardService $board_service;
    private MemberService $member_service;
    private CommentService $comment_service;
    private WriteService $write_service;

    public function __construct(
        BoardService $boardService,
        MemberService $memberService,
        CommentService $commentService,
        WriteService $write_service
    ) {
        $this->board_service = $boardService;
        $this->comment_service = $commentService;
        $this->member_service = $memberService;
        $this->write_service = $write_service;
    }

    /**
     * 옵션에 따라 댓글쓴이, 글쓴이, 관리자에게 메일을 발송
     * @param array $board
     * @param array $write
     * @param int $comment_id
     * @param $parent_comment
     * @return void
     */
    public function sendMailAfterComment(array $board, array $write, int $comment_id, $parent_comment)
    {
        $config = ConfigService::getConfig();
        if (!$config['cf_email_use'] || !$board['bo_use_email']) {
            return;
        }
        
        $wr_subject = get_text(stripslashes($write['wr_subject']));
        $html_type = 0;
        $wr_option = $write['wr_option'];
        if (str_contains($wr_option, 'html1')) {
            $html_type = 1;
        } else if (str_contains($wr_option, 'html2')) {
            $html_type = 2;
        }

        $wr_content = conv_content(conv_unescape_nl(stripslashes($write['wr_content'])), $html_type);
        $subject = "[{$config['cf_title']}] [{$board['bo_subject']}] 게시판에 댓글이 올라왔습니다.";

        // wr_subejct, name, content
        ob_start();
        include_once(G5_BBS_PATH . DIRECTORY_SEPARATOR . 'write_update_mail.php');
        $content = ob_get_clean();

        $replaced_content = run_replace('api_write_update_mail_content', $content);
        if ($replaced_content) {
            $content = $replaced_content;
        }
        
        // 보낼 이메일 주소들
        $to_emails = [];
        // 게시판관리자에게 보내는 메일
        if ($config['cf_email_wr_board_admin']) {
            if ($board_admin_info = $this->board_service->getAdminInfo('board', $board['bo_table'])) {
                $to_emails[] = $board_admin_info['mb_email'];
            }
        }

        // 게시판그룹관리자에게 보내는 메일
        if ($config['cf_email_wr_group_admin']) {
            if ($group_admin_info = $this->board_service->getAdminInfo('group', $board['bo_table'])) {
                $to_emails[] = $group_admin_info['mb_email'];
            }
        }
        // 최고관리자에게 보내는 메일
        if ($config['cf_email_wr_super_admin']) {
            $to_emails[] = $config['cf_admin_email'];
        }


        // 원글 글쓴이에게 보내는 메일
        if ($config['cf_email_wr_write'] && $write['mb_id']) {
            $to_emails[] = $write['wr_email'];
        }


        $comment = $this->comment_service->fetchComment($comment_id);

        // 댓글 쓴 모든이에게 메일 발송이 되어 있다면 (자신에게는 발송하지 않는다)
        if ($config['cf_email_wr_comment_all']) {
            global $g5;
            $write_table = $g5['write_prefix'] . $board['bo_table'];
            $query = " select distinct wr_email from {$write_table}
                        where wr_email
                        and wr_parent = ? ";
            $result = Db::getInstance()->run($query, [$write['wr_id']])->fetchAll();

            foreach ($result as $row) {
                if ($row['wr_email'] == $write['wr_email']) {
                    continue;
                }
                if ($row['wr_email'] == $comment['wr_email']) {
                    continue;
                }
                $to_emails[] = $row['wr_email'];
            }
        }

        // 중복된 메일주소 삭제
        $unique_email = array_unique($to_emails);
        $unique_email = run_replace('write_update_mail_list', array_values($unique_email), $board, $write);


        $mail = new MailService();
        foreach ($unique_email as $email) {
            $mail->send($write['wr_name'], $write['wr_email'], $email, $subject, $content, 1);
        }
    }

    /**
     * 글쓴이와 관리자 (최고관리자, 게시판 관리자, 게시판의 그룹관리자)에게 발송
     * @param array $board
     * @param array $group
     * @param int $wr_id
     * @param array $parent_write
     * @return void
     */
    public function sendMailAfterWrite($board, $group, $wr_id, $parent_write)
    {
        $config = ConfigService::getConfig();
        if (!$config['cf_email_use'] || !$board['bo_use_email']) {
            return;
        }

        $write = $this->write_service->fetchWrite($wr_id);
        if (!$write) {
            return;
        }

        $wr_subject = get_text(stripslashes($write['wr_subject']));
        $html_type = 0;
        $wr_option = $write['wr_option'];
        if (str_contains($wr_option, 'html1')) {
            $html_type = 1;
        } else if (str_contains($wr_option, 'html2')) {
            $html_type = 2;
        }

        $wr_content = conv_content(conv_unescape_nl(stripslashes($write['wr_content'])), $html_type);
        $subject = "[{$config['cf_title']}] [{$board['bo_subject']}] 게시판에 게시글이 올라왔습니다.";

        // wr_subejct, name, content
        ob_start();
        include_once(G5_BBS_PATH . DIRECTORY_SEPARATOR . 'write_update_mail.php');
        $content = ob_get_clean();

        $replaced_content = run_replace('api_write_update_mail_content', $content);
        if ($replaced_content) {
            $content = $replaced_content;
        }

        // 보낼 이메일 주소들
        $to_emails = [];
        // 게시판관리자에게 보내는 메일
        if ($config['cf_email_wr_board_admin']) {
            if ($board_admin_info = $this->board_service->getAdminInfo('board', $board['bo_table'])) {
                $to_emails[] = $board_admin_info['mb_email'];
            }
        }

        // 게시판그룹관리자에게 보내는 메일
        if ($config['cf_email_wr_group_admin']) {
            if ($group_admin_info = $this->board_service->getAdminInfo('group', $board['bo_table'])) {
                $to_emails[] = $group_admin_info['mb_email'];
            }
        }

        // 최고관리자에게 보내는 메일
        if ($config['cf_email_wr_super_admin']) {
            $to_emails[] = $config['cf_admin_email'];
        }

        // 글쓴이에게 보내는 메일
        if ($config['cf_email_wr_write'] && $write['mb_id']) {
            $write_member = $this->member_service->fetchMemberById($write['mb_id']);
            if ($write_member) {
                $write['wr_email'] = $write_member['mb_email'];
                $to_emails[] = $write_member['mb_email'];
            }
        }

        // 옵션에 메일받기가 체크되어 있고, 게시자의 메일이 있다면
        if (strpos($write['wr_option'], 'mail') !== false && $write['wr_email']) {
            $to_emails[] = $write['wr_email'];
        }

        // 중복된 메일주소 삭제
        $unique_email = array_unique($to_emails);
        $unique_email = run_replace('api_write_update_mail_list', array_values($unique_email), $board, $write);
        
        $mail = new MailService();
        foreach ($unique_email as $email) {
            $mail->send($write['wr_name'], $write['wr_email'], $email, $subject, $content, 1);
        }
    }

}