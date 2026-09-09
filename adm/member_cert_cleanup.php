<?php
$sub_menu = '200100';
require_once './_common.php';

check_demo();
auth_check_menu($auth, $sub_menu, 'w');
auth_check_menu($auth, $sub_menu, 'd');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alert('올바른 방법으로 이용해 주십시오.');
}

check_admin_token();
admin_referer_check();

$mb_id = isset($_POST['mb_id']) && is_string($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
$mb = get_member($mb_id);
if (empty($mb['mb_id'])) {
    alert('존재하지 않는 회원자료입니다.');
}

if ($is_admin !== 'super' && ($mb['mb_level'] >= $member['mb_level'] || is_admin($mb['mb_id']) === 'super')) {
    alert('자신보다 권한이 높거나 같은 회원의 본인확인 정보는 삭제할 수 없습니다.');
}

if (!admin_clear_member_certification($mb['mb_id'])) {
    alert('본인확인 정보를 모두 삭제하지 못했습니다. DB 상태를 확인한 뒤 다시 시도해 주십시오.');
}

alert('본인확인 정보와 인증 내역을 삭제했습니다.', './member_form.php?w=u&mb_id='.urlencode($mb['mb_id']));
