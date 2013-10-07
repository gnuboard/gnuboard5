<?php
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

if (!$is_member) {
    alert_close("상품문의는 회원만 작성 가능합니다.");
}

// 상품문의의 내용에 쓸수 있는 최대 글자수 (한글은 영문3자)
$iq_question_max_length = 10000;

$w     = escape_trim($_REQUEST['w']);
$it_id = escape_trim($_REQUEST['it_id']);
$iq_id = escape_trim($_REQUEST['iq_id']);

if ($w == "u")
{
    $qa = sql_fetch(" select * from {$g5['g5_shop_item_qa_table']} where iq_id = '$iq_id' ");
    if (!$qa) {
        alert_close("상품문의 정보가 없습니다.");
    }

    $it_id    = $qa['it_id'];

    if (!$iq_admin && $qa['mb_id'] != $member['mb_id']) {
        alert_close("자신의 상품문의만 수정이 가능합니다.");
    }
}

include_once(G5_PATH.'/head.sub.php');

$itemqaform_skin = G5_MSHOP_SKIN_PATH.'/itemqaform.skin.php';

if(!file_exists($itemqaform_skin)) {
    echo str_replace(G5_PATH.'/', '', $itemqaform_skin).' 스킨 파일이 존재하지 않습니다.';
} else {
    include_once($itemqaform_skin);
}

include_once(G5_PATH.'/tail.sub.php');
?>