<?php
include_once('./_common.php');

$w     = isset($_REQUEST['w']) ? preg_replace('/[^0-9a-z]/i', '', trim($_REQUEST['w'])) : '';
$it_id = isset($_REQUEST['it_id']) ? get_search_string(trim($_REQUEST['it_id'])) : '';
$is_id = isset($_REQUEST['is_id']) ? preg_replace('/[^0-9]/', '', trim($_REQUEST['is_id'])) : 0;
$use = array('is_subject'=>'', 'is_content'=>'');

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/itemuseform.php');
    return;
}

include_once(G5_EDITOR_LIB);

if (!$is_member) {
    alert_close("사용후기는 회원만 작성 가능합니다.");
}

// 상품정보체크
$row = get_shop_item($it_id, true);

if(! (isset($row['it_id']) && $row['it_id']))
    alert_close('상품정보가 존재하지 않습니다.');

if ($w == "") {
    $is_score = 5;

    // 사용후기 작성 설정에 따른 체크
    check_itemuse_write($it_id, $member['mb_id']);
} else if ($w == "u") {
    $use = sql_fetch(" select * from {$g5['g5_shop_item_use_table']} where is_id = '$is_id' ");
    if (!$use) {
        alert_close("사용후기 정보가 없습니다.");
    }

    $it_id    = $use['it_id'];
    $is_score = $use['is_score'];

    if (!$is_admin && $use['mb_id'] != $member['mb_id']) {
        alert_close("자신의 사용후기만 수정이 가능합니다.");
    }
}

include_once(G5_PATH.'/head.sub.php');

$is_dhtml_editor = false;
// 모바일에서는 DHTML 에디터 사용불가
if ($config['cf_editor'] && (!is_mobile() || defined('G5_IS_MOBILE_DHTML_USE') && G5_IS_MOBILE_DHTML_USE)) {
    $is_dhtml_editor = true;
}
$editor_html = editor_html('is_content', get_text(html_purifier($use['is_content']), 0), $is_dhtml_editor);
$editor_js = '';
$editor_js .= get_editor_js('is_content', $is_dhtml_editor);
$editor_js .= chk_editor_js('is_content', $is_dhtml_editor);

$itemuseform_skin = G5_SHOP_SKIN_PATH.'/itemuseform.skin.php';

if(!file_exists($itemuseform_skin)) {
    echo str_replace(G5_PATH.'/', '', $itemuseform_skin).' 스킨 파일이 존재하지 않습니다.';
} else {
    include_once($itemuseform_skin);
}

include_once(G5_PATH.'/tail.sub.php');