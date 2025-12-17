<?php
include_once('./_common.php');

if (!$is_member) {
    alert('회원 전용 서비스 입니다.', G5_BBS_URL . '/login.php?url=' . urlencode($url));
}

$post_ct_chk = (isset($_POST['chk_ci_id']) && is_array($_POST['chk_ci_id'])) ? $_POST['chk_ci_id'] : array();
$post_ci_ids = (isset($_POST['ci_id']) && is_array($_POST['ci_id'])) ? $_POST['ci_id'] : array();
$fldcnt = count($post_ci_ids);

$delete_ci_ids = array();

if ($w == 'd') {
    if (isset($_GET['ci_id'])) {
        $delete_ci_ids[] = (int) $_GET['ci_id'];
    }
}

for ($i = 0; $i < $fldcnt; $i++) {

    $ct_chk = isset($post_ct_chk[$i]) ? 1 : 0;

    $ci_id = ($ct_chk && isset($post_ci_ids[$i])) ? (int) $post_ci_ids[$i] : 0;

    if (!$ci_id) continue;

    $delete_ci_ids[] = $ci_id;
}


foreach ($delete_ci_ids as $ci_id) {

    if (empty($ci_id)) continue;

    $sql = "SELECT * 
            FROM {$g5['g5_subscription_mb_cardinfo_table']} 
            WHERE ci_id = '" . $ci_id . "' 
            AND mb_id = '" . $member['mb_id'] . "'";
    $row = sql_fetch($sql);

    if (isset($row['ci_id']) && $row['ci_id']) {

        $pg_service = $row['pg_service'];
        $od_test = $row['od_test'];
        $card_mask_number = $row['card_mask_number'];

        $sql = "DELETE FROM {$g5['g5_subscription_mb_cardinfo_table']} 
                WHERE mb_id = '" . $member['mb_id'] . "' 
                AND pg_service = '$pg_service' 
                AND od_test = '$od_test' 
                AND card_mask_number = '$card_mask_number'";
        // sql_query($sql);
    }
}

exit;

if ($w == "d") {
    $wi_id = isset($_GET['wi_id']) ? (int) $_GET['wi_id'] : 0;

    $sql = " select mb_id from {$g5['g5_shop_wish_table']} where wi_id = '$wi_id' ";
    $row = sql_fetch($sql);

    if ($row['mb_id'] != $member['mb_id'])
        alert('위시리시트 상품을 삭제할 권한이 없습니다.');

    $sql = " delete from {$g5['g5_shop_wish_table']}
              where wi_id = '$wi_id'
                and mb_id = '{$member['mb_id']}' ";
    sql_query($sql);
} else {
    $it_id = isset($_REQUEST['it_id']) ? $_REQUEST['it_id'] : null;

    if (is_array($it_id))
        $it_id = $_REQUEST['it_id'][0];

    $it_id = safe_replace_regex($it_id, 'it_id');

    if (!$it_id)
        alert('상품코드가 올바르지 않습니다.', G5_SHOP_URL);

    // 본인인증, 성인인증체크
    if (!$is_admin) {
        $msg = shop_member_cert_check($it_id, 'item');
        if ($msg)
            alert($msg, G5_SHOP_URL);
    }

    // 상품정보 체크
    $row = get_shop_item($it_id, true);

    if (! (isset($row['it_id']) && $row['it_id']))
        alert('상품정보가 존재하지 않습니다.', G5_SHOP_URL);

    $sql = " select wi_id from {$g5['g5_shop_wish_table']}
              where mb_id = '{$member['mb_id']}' and it_id = '$it_id' ";
    $row = sql_fetch($sql);

    if (! (isset($row['wi_id']) && $row['wi_id'])) { // 없다면 등록
        $sql = " insert {$g5['g5_shop_wish_table']}
                    set mb_id = '{$member['mb_id']}',
                        it_id = '$it_id',
                        wi_time = '" . G5_TIME_YMDHIS . "',
                        wi_ip = '" . $_SERVER['REMOTE_ADDR'] . "' ";
        sql_query($sql);
    }
}

goto_url('./wishlist.php');
