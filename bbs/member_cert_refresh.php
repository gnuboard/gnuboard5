<?php
define('G5_CERT_IN_PROG', true);
include_once('./_common.php');

if(!$is_member) { alert("잘못된 접근입니다.", G5_URL); }

if (!empty($member['mb_certify']) && strlen($member['mb_dupinfo']) != 64) { // 본인인증 안된 계정이거나 ci로 인증된 계정일 경우
    alert("잘못된 접근입니다.", G5_URL);
}

if($config['cf_cert_use'] == 0) alert("본인인증을 이용 할 수 없습니다. 관리자에게 문의 하십시오.", G5_URL);

$g5['title'] = '본인인증을 다시 해주세요.';
include_once(G5_PATH.'/_head.php');

$action_url = G5_HTTPS_BBS_URL."/member_cert_refresh_update.php";
include_once($member_skin_path.'/member_cert_refresh.skin.php');

include_once(G5_PATH.'/_tail.php');