<?php
include_once('./_common.php');

if (G4_IS_MOBILE) {
    include_once(G4_MSHOP_PATH.'/item.php');
    return;
}

$it_id = escape_trim($_GET['it_id']);

include_once(G4_LIB_PATH.'/iteminfo.lib.php');

// 분류사용, 상품사용하는 상품의 정보를 얻음
$sql = " select a.*, b.ca_name, b.ca_use from {$g4['shop_item_table']} a, {$g4['shop_category_table']} b where a.it_id = '$it_id' and a.ca_id = b.ca_id ";
$it = sql_fetch($sql);
if (!$it['it_id'])
    alert('자료가 없습니다.');
if (!($it['ca_use'] && $it['it_use'])) {
    if (!$is_admin)
        alert('현재 판매가능한 상품이 아닙니다.');
}

// 분류 테이블에서 분류 상단, 하단 코드를 얻음
$sql = " select ca_include_head, ca_include_tail, ca_hp_cert_use, ca_adult_cert_use from {$g4['shop_category_table']} where ca_id = '{$it['ca_id']}' ";
$ca = sql_fetch($sql);

if(!$is_admin) {
    // 본인확인체크
    if($ca['ca_hp_cert_use'] && !$member['mb_hp_certify']) {
        if($is_member)
            alert('회원정보 수정에서 휴대폰 본인확인 후 이용해 주십시오.');
        else
            alert('휴대폰 본인확인된 로그인 회원만 이용할 수 있습니다.');
    }

    // 성인인증체크
    if($ca['ca_adult_cert_use'] && !$member['mb_adult']) {
        if($is_member)
            alert('휴대폰 본인확인으로 성인인증된 회원만 이용할 수 있습니다.\\n회원정보 수정에서 휴대폰 본인확인을 해주십시오.');
        else
            alert('휴대폰 본인확인으로 성인인증된 회원만 이용할 수 있습니다.');
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
    sql_query(" update {$g4['shop_item_table']} set it_hit = it_hit + 1 where it_id = '$it_id' "); // 1증가
    set_cookie("ck_it_id", $it_id, time() + 3600); // 1시간동안 저장
}

$g4['title'] = $it['it_name'].' &gt; '.$it['ca_name'];

// 분류 상단 코드가 있으면 출력하고 없으면 기본 상단 코드 출력
if ($ca['ca_include_head'])
    @include_once($ca['ca_include_head']);
else
    include_once('./_head.php');

// 분류 위치
// HOME > 1단계 > 2단계 ... > 6단계 분류
$ca_id = $it['ca_id'];
include G4_SHOP_PATH.'/navigation1.inc.php';

// 이 분류에 속한 하위분류 출력
include G4_SHOP_PATH.'/listcategory.inc.php';

if ($is_admin) {
    echo '<div class="sit_admin"><a href="'.G4_ADMIN_URL.'/shop_admin/itemform.php?w=u&amp;it_id='.$it_id.'" class="btn_admin">상품 관리</a></div>';
}
?>

<!-- 상품 상세보기 시작 { -->
<?php
// 상단 HTML
echo '<div id="sit_hhtml">'.stripslashes($it['it_head_html']).'</div>';

// 이전 상품보기
$sql = " select it_id, it_name from {$g4['shop_item_table']} where it_id > '$it_id' and SUBSTRING(ca_id,1,4) = '".substr($it['ca_id'],0,4)."' and it_use = '1' order by it_id asc limit 1 ";
$row = sql_fetch($sql);
if ($row['it_id']) {
    $prev_title = '이전상품<span class="sound_only"> '.$row['it_name'].'</span>';
    $prev_href = '<a href="./item.php?it_id='.$row['it_id'].'" class="btn01">';
    $prev_href2 = '</a>'.PHP_EOL;
} else {
    $prev_title = '';
    $prev_href = '';
    $prev_href2 = '';
}

// 다음 상품보기
$sql = " select it_id, it_name from {$g4['shop_item_table']} where it_id < '$it_id' and SUBSTRING(ca_id,1,4) = '".substr($it['ca_id'],0,4)."' and it_use = '1' order by it_id desc limit 1 ";
$row = sql_fetch($sql);
if ($row['it_id']) {
    $next_title = '다음 상품<span class="sound_only"> '.$row['it_name'].'</span>';
    $next_href = '<a href="./item.php?it_id='.$row['it_id'].'" class="btn01">';
    $next_href2 = '</a>'.PHP_EOL;
} else {
    $next_title = '';
    $next_href = '';
    $next_href2 = '';
}

// 관리자가 확인한 사용후기의 갯수를 얻음
$sql = " select count(*) as cnt from `{$g4['shop_item_use_table']}` where it_id = '{$it_id}' and is_confirm = '1' ";
$row = sql_fetch($sql);
$item_use_count = $row['cnt'];

// 상품문의의 갯수를 얻음
$sql = " select count(*) as cnt from `{$g4['shop_item_qa_table']}` where it_id = '{$it_id}' ";
$row = sql_fetch($sql);
$item_qa_count = $row['cnt'];

// 관련상품의 갯수를 얻음
$sql = " select count(*) as cnt from {$g4['shop_item_relation_table']} a left join {$g4['shop_item_table']} b on (a.it_id2=b.it_id and b.it_use='1') where a.it_id = '{$it['it_id']}' ";
$row = sql_fetch($sql);
$item_relation_count = $row['cnt'];

// 상품 선택옵션 갯수
$sql = " select count(*) as cnt from {$g4['shop_item_option_table']} where it_id = '{$it['it_id']}' and io_type = '0' and io_use = '1' ";
$row = sql_fetch($sql);
$opt_count = $row['cnt'];

// 상품 추가옵션 갯수
$sql = " select count(*) as cnt from {$g4['shop_item_option_table']} where it_id = '{$it['it_id']}' and io_type = '1' and io_use = '1' ";
$row = sql_fetch($sql);
$spl_count = $row['cnt'];

// 고객선호도 별점수
$star_score = get_star_image($it['it_id']);

// 선택 옵션
$option_1 = get_item_options($it['it_id'], $it['it_option_subject']);

// 추가 옵션
$option_2 = get_item_supply($it['it_id'], $it['it_supply_subject']);

if (G4_HTTPS_DOMAIN)
    $action_url = G4_HTTPS_DOMAIN.'/'.G4_SHOP_DIR.'/cartupdate.php';
else
    $action_url = './cartupdate.php';
?>

<script src="<?php echo G4_JS_URL; ?>/shop.js"></script>

<div id="sit">

    <!-- 상품 구입폼 시작 { -->
    <?php include_once(G4_SHOP_SKIN_PATH.'/item.form.skin.php'); ?>
    <!-- } 상품 구입폼 끝 -->

    <!-- 상품 상세정보 시작 { -->
    <?php include_once(G4_SHOP_SKIN_PATH.'/item.info.skin.php'); ?>
    <!-- } 상품 상세정보 끝 -->

</div>

<?php
// 하단 HTML
echo stripslashes($it['it_tail_html']);
?>
<!-- } 상품 상세보기 끝 -->

<?php
if ($ca['ca_include_tail'])
    @include_once($ca['ca_include_tail']);
else
    include_once('./_tail.php');
?>
