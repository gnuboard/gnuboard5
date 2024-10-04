<?php

namespace API\Hooks\Member;

use API\Service\MailService;

class MemberHooks
{

    /**
     * 임시비밀번호 메일 발송
     * @param array $config 환경설정
     * @param array $member 회원정보
     * @param string $mb_nonce 인증용 난수
     * @param string $change_password 변경될 비밀번호
     * @return void
     */
    public function sendMailResetPassword(array $config, array $member, string $mb_nonce, string $change_password)
    {
        // 인증 링크 생성
        $href = G5_BBS_URL . '/password_lost_certify.php?mb_no=' . $member['mb_no'] . '&amp;mb_nonce=' . $mb_nonce;

        $subject = "[{$config['cf_title']}  요청하신 회원정보 찾기 안내 메일입니다.";

        $content = '<div style="margin:30px auto;width:600px;border:10px solid #f7f7f7">';
        $content .= '<div style="border:1px solid #dedede">';
        $content .= '<h1 style="padding:30px 30px 0;background:#f7f7f7;color:#555;font-size:1.4em">';
        $content .= '회원정보 찾기 안내';
        $content .= '</h1>';
        $content .= '<span style="display:block;padding:10px 30px 30px;background:#f7f7f7;text-align:right">';
        $content .= '<a href="' . G5_URL . '" target="_blank">' . $config['cf_title'] . '</a>';
        $content .= '</span>';
        $content .= '<p style="margin:20px 0 0;padding:30px 30px 30px;border-bottom:1px solid #eee;line-height:1.7em">';
        $content .= addslashes($member['mb_name']) . ' (' . addslashes($member['mb_nick']) . ')' . ' 회원님은 ' . G5_TIME_YMDHIS . ' 에 회원정보 찾기 요청을 하셨습니다.<br>';
        $content .= '저희 사이트는 관리자라도 회원님의 비밀번호를 알 수 없기 때문에, 비밀번호를 알려드리는 대신 새로운 비밀번호를 생성하여 안내 해드리고 있습니다.<br>';
        $content .= '아래에서 변경될 비밀번호를 확인하신 후, <span style="color:#ff3061"><strong>비밀번호 변경</strong> 링크를 클릭 하십시오.</span><br>';
        $content .= '비밀번호가 변경되었다는 인증 메세지가 출력되면, 홈페이지에서 회원아이디와 변경된 비밀번호를 입력하시고 로그인 하십시오.<br>';
        $content .= '로그인 후에는 정보수정 메뉴에서 새로운 비밀번호로 변경해 주십시오.';
        $content .= '</p>';
        $content .= '<p style="margin:0;padding:30px 30px 30px;border-bottom:1px solid #eee;line-height:1.7em">';
        $content .= '<span style="display:inline-block;width:100px">회원아이디</span> ' . $member['mb_id'] . '<br>';
        $content .= '<span style="display:inline-block;width:100px">변경될 비밀번호</span> <strong style="color:#ff3061">' . $change_password . '</strong>';
        $content .= '</p>';
        $content .= '<a href="' . $href . '" target="_blank" style="display:block;padding:30px 0;background:#484848;color:#fff;text-decoration:none;text-align:center">비밀번호 변경</a>';
        $content .= '</div>';
        $content .= '</div>';

        $mail = new MailService();
        $mail->send($config['cf_admin_email_name'], $config['cf_admin_email'], $member['mb_email'], $subject, $content, 1);
    }
}