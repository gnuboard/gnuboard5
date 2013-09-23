<?php
include_once('./_common.php');

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/item.php');
    return;
}

$it_id = escape_trim($_GET['it_id']);

include_once(G5_LIB_PATH.'/iteminfo.lib.php');

// 분류사용, 상품사용하는 상품의 정보를 얻음
$sql = " select a.*, b.ca_name, b.ca_use from {$g5['g5_shop_item_table']} a, {$g5['g5_shop_category_table']} b where a.it_id = '$it_id' and a.ca_id = b.ca_id ";
$it = sql_fetch($sql);
if (!$it['it_id'])
    alert('자료가 없습니다.');
if (!($it['ca_use'] && $it['it_use'])) {
    if (!$is_admin)
        alert('현재 판매가능한 상품이 아닙니다.');
}

// 분류 테이블에서 분류 상단, 하단 코드를 얻음
$sql = " select ca_include_head, ca_include_tail, ca_cert_use, ca_adult_use from {$g5['g5_shop_category_table']} where ca_id = '{$it['ca_id']}' ";
$ca = sql_fetch($sql);

if(!$is_admin) {
    // 본인확인체크
    if($ca['ca_cert_use'] && !$member['mb_certify']) {
        if($is_member)
            alert('회원정보 수정에서 본인확인 후 이용해 주십시오.');
        else
            alert('본인확인된 로그인 회원만 이용할 수 있습니다.');
    }

    // 성인인증체크
    if($ca['ca_adult_use'] && !$member['mb_adult']) {
        if($is_member)
            alert('본인확인으로 성인인증된 회원만 이용할 수 있습니다.\\n회원정보 수정에서 본인확인을 해주십시오.');
        else
            alert('본인확인으로 성인인증된 회원만 이용할 수 있습니다.');
    }
}

// 오늘 본 상품 저장 시작
// tv 는 today view 약자
$saved = false;
$tv_idx = (int)get_session("ss_tv_idx");
if ($tv_idx > 0) {
    for ($i=1; $i<=$tv_idx; $i++) {
        if (get_session("ss_tv[$i]") == $it_id) {
            $saved = true;
            break;
        }
    }
}

if (!$saved) {
    $tv_idx++;
    set_session("ss_tv_idx", $tv_idx);
    set_session("ss_tv[$tv_idx]", $it_id);
}
// 오늘 본 상품 저장 끝

// 조회수 증가
if ($_COOKIE['ck_it_id'] != $it_id) {
    sql_query(" update {$g5['g5_shop_item_table']} set it_hit = it_hit + 1 where it_id = '$it_id' "); // 1증가
    set_cookie("ck_it_id", $it_id, time() + 3600); // 1시간동안 저장
}

$g5['title'] = $it['it_name'].' &gt; '.$it['ca_name'];

// 분류 상단 코드가 있으면 출력하고 없으면 기본 상단 코드 출력
if ($ca['ca_include_head'])
    @include_once($ca['ca_include_head']);
else
    include_once('./_head.php');

// 분류 위치
// HOME > 1단계 > 2단계 ... > 6단계 분류
$ca_id = $it['ca_id'];
include G5_SHOP_SKIN_PATH.'/navigation.skin.php';

// 이 분류에 속한 하위분류 출력
include G5_SHOP_SKIN_PATH.'/listcategory.skin.php';

if ($is_admin) {
    echo '<div class="sit_admin"><a href="'.G5_ADMIN_URL.'/shop_admin/itemform.php?w=u&amp;it_id='.$it_id.'" class="btn_admin">상품 관리</a></div>';
}
?>

<!-- 상품 상세보기 시작 { -->
<?php
// 상단 HTML
echo '<div id="sit_hhtml">'.stripslashes($it['it_head_html']).'</div>';

// 보안서버경로
if (G5_HTTPS_DOMAIN)
    $action_url = G5_HTTPS_DOMAIN.'/'.G5_SHOP_DIR.'/cartupdate.php';
else
    $action_url = './cartupdate.php';
?>

<script src="<?php echo G5_JS_URL; ?>/shop.js"></script>

<div id="sit">

    <?php
    // 상품 구입폼
    include_once(G5_SHOP_SKIN_PATH.'/item.form.skin.php');
    ?>

    <?php
    // 상품 상세정보
    include_once(G5_SHOP_SKIN_PATH.'/item.info.skin.php');
    ?>

</div>

<?php
// 하단 HTML
echo stripslashes($it['it_tail_html']);
?>

<?php
if ($ca['ca_include_tail'])
    @include_once($ca['ca_include_tail']);
else
    include_once('./_tail.php');
?>
