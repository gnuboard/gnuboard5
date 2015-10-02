<?php
include_once('./_common.php');

if (!$member['mb_id'])
    alert_close('회원만 이용하실 수 있습니다.');

if (!$member['mb_open'] && $is_admin != 'super' && $member['mb_id'] != $mb_id)
    alert_close('자신의 정보를 공개하지 않으면 다른분의 정보를 조회할 수 없습니다.\\n\\n정보공개 설정은 회원정보수정에서 하실 수 있습니다.');

$mb = get_member($mb_id);
if (!$mb['mb_id'])
    alert_close('회원정보가 존재하지 않습니다.\\n\\n탈퇴하였을 수 있습니다.');

if (!$mb['mb_open'] && $is_admin != 'super' && $member['mb_id'] != $mb_id)
    alert_close('정보공개를 하지 않았습니다.');

$g5['title'] = $mb['mb_nick'].'님의 자기소개';
include_once(G5_PATH.'/head.sub.php');

$mb_nick = get_sideview($mb['mb_id'], get_text($mb['mb_nick']), $mb['mb_email'], $mb['mb_homepage'], $mb['mb_open']);

// 회원가입후 몇일째인지? + 1 은 당일을 포함한다는 뜻
$sql = " select (TO_DAYS('".G5_TIME_YMDHIS."') - TO_DAYS('{$mb['mb_datetime']}') + 1) as days ";
$row = sql_fetch($sql);
$mb_reg_after = $row['days'];

$mb_homepage = set_http(get_text(clean_xss_tags($mb['mb_homepage'])));
$mb_profile = $mb['mb_profile'] ? conv_content($mb['mb_profile'],0) : '소개 내용이 없습니다.';

include_once($member_skin_path.'/profile.skin.php');

include_once(G5_PATH.'/tail.sub.php');
?>
