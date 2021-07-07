<?php
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

if (!$is_member) {
    alert_close("상품문의는 회원만 작성 가능합니다.");
}

$w     = isset($_REQUEST['w']) ? preg_replace('/[^0-9a-z]/i', '', trim($_REQUEST['w'])) : '';
$it_id = isset($_REQUEST['it_id']) ? get_search_string(trim($_REQUEST['it_id'])) : '';
$iq_id = isset($_REQUEST['iq_id']) ? preg_replace('/[^0-9]/', '', trim($_REQUEST['iq_id'])) : 0;

// 상품정보체크
$row = get_shop_item($it_id, true);
if(! (isset($row['it_id']) && $row['it_id']))
    alert_close('상품정보가 존재하지 않습니다.');

$chk_secret = '';

if($w == '') {
    $qa['iq_email'] = $member['mb_email'];
    $qa['iq_hp'] = $member['mb_hp'];
}

if ($w == "u")
{
    $qa = sql_fetch(" select * from {$g5['g5_shop_item_qa_table']} where iq_id = '$iq_id' ");
    if (!$qa) {
        alert_close("상품문의 정보가 없습니다.");
    }

    $it_id    = $qa['it_id'];

    if (!$is_admin && $qa['mb_id'] != $member['mb_id']) {
        alert_close("자신의 상품문의만 수정이 가능합니다.");
    }

    if($qa['iq_secret'])
        $chk_secret = 'checked="checked"';
}

include_once(G5_PATH.'/head.sub.php');

$is_dhtml_editor = false;
// 모바일에서는 DHTML 에디터 사용불가
if ($config['cf_editor'] && (!is_mobile() || defined('G5_IS_MOBILE_DHTML_USE') && G5_IS_MOBILE_DHTML_USE)) {
    $is_dhtml_editor = true;
}
$editor_html = editor_html('iq_question', get_text(html_purifier($qa['iq_question']), 0), $is_dhtml_editor);
$editor_js = '';
$editor_js .= get_editor_js('iq_question', $is_dhtml_editor);
$editor_js .= chk_editor_js('iq_question', $is_dhtml_editor);

$itemqaform_skin = G5_MSHOP_SKIN_PATH.'/itemqaform.skin.php';

if(!file_exists($itemqaform_skin)) {
    echo str_replace(G5_PATH.'/', '', $itemqaform_skin).' 스킨 파일이 존재하지 않습니다.';
} else {
    include_once($itemqaform_skin);
}

include_once(G5_PATH.'/tail.sub.php');