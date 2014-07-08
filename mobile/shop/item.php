<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/iteminfo.lib.php');

$it_id = trim($_GET['it_id']);

// 분류사용, 상품사용하는 상품의 정보를 얻음
$sql = " select a.*,
                b.ca_name,
                b.ca_use
           from {$g5['g5_shop_item_table']} a,
                {$g5['g5_shop_category_table']} b
          where a.it_id = '$it_id'
            and a.ca_id = b.ca_id ";
$it = sql_fetch($sql);
if (!$it['it_id'])
    alert('자료가 없습니다.');
if (!($it['ca_use'] && $it['it_use'])) {
    if (!$is_admin)
        alert('판매가능한 상품이 아닙니다.');
}

// 분류 테이블에서 분류 상단, 하단 코드를 얻음
$sql = " select ca_mobile_skin_dir, ca_include_head, ca_include_tail, ca_cert_use, ca_adult_use
           from {$g5['g5_shop_category_table']}
          where ca_id = '{$it['ca_id']}' ";
$ca = sql_fetch($sql);

// 본인인증, 성인인증체크
if(!$is_admin) {
    $msg = shop_member_cert_check($it_id, 'item');
    if($msg)
        alert($msg, G5_SHOP_URL);
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
if (get_cookie('ck_it_id') != $it_id) {
    sql_query(" update {$g5['g5_shop_item_table']} set it_hit = it_hit + 1 where it_id = '$it_id' "); // 1증가
    set_cookie("ck_it_id", $it_id, time() + 3600); // 1시간동안 저장
}

// 이전 상품보기
$sql = " select it_id, it_name from {$g5['g5_shop_item_table']}
          where it_id > '$it_id'
            and SUBSTRING(ca_id,1,4) = '".substr($it['ca_id'],0,4)."'
            and it_use = '1'
          order by it_id asc
          limit 1 ";
$row = sql_fetch($sql);
if ($row['it_id']) {
    $prev_title = '이전상품 <span>'.$row['it_name'].'</span>';
    $prev_href = '<a href="'.G5_SHOP_URL.'/item.php?it_id='.$row['it_id'].'" id="siblings_prev">';
    $prev_href2 = '</a>';
} else {
    $prev_title = '';
    $prev_href = '';
    $prev_href2 = '';
}

// 다음 상품보기
$sql = " select it_id, it_name from {$g5['g5_shop_item_table']}
          where it_id < '$it_id'
            and SUBSTRING(ca_id,1,4) = '".substr($it['ca_id'],0,4)."'
            and it_use = '1'
          order by it_id desc
          limit 1 ";
$row = sql_fetch($sql);
if ($row['it_id']) {
    $next_title = '다음 상품 <span>'.$row['it_name'].'</span>';
    $next_href = '<a href="'.G5_SHOP_URL.'/item.php?it_id='.$row['it_id'].'" id="siblings_next">';
    $next_href2 = '</a>';
} else {
    $next_title = '';
    $next_href = '';
    $next_href2 = '';
}

// 관리자가 확인한 사용후기의 개수를 얻음
$sql = " select count(*) as cnt from `{$g5['g5_shop_item_use_table']}` where it_id = '{$it_id}' and is_confirm = '1' ";
$row = sql_fetch($sql);
$item_use_count = $row['cnt'];

// 상품문의의 개수를 얻음
$sql = " select count(*) as cnt from `{$g5['g5_shop_item_qa_table']}` where it_id = '{$it_id}' ";
$row = sql_fetch($sql);
$item_qa_count = $row['cnt'];

if ($default['de_rel_list_use']) {
    // 관련상품의 개수를 얻음
    $sql = " select count(*) as cnt
               from {$g5['g5_shop_item_relation_table']} a
               left join {$g5['g5_shop_item_table']} b on (a.it_id2=b.it_id and b.it_use='1')
              where a.it_id = '{$it['it_id']}' ";
    $row = sql_fetch($sql);
    $item_relation_count = $row['cnt'];
}

// 상품품절체크
$is_soldout = is_soldout($it['it_id']);

// 주문가능체크
$is_orderable = true;
if(!$it['it_use'] || $it['it_tel_inq'] || $is_soldout)
    $is_orderable = false;

if($is_orderable) {
    // 선택 옵션
    $option_item = get_item_options($it['it_id'], $it['it_option_subject']);

    // 추가 옵션
    $supply_item = get_item_supply($it['it_id'], $it['it_supply_subject']);

    // 상품 선택옵션 수
    $option_count = 0;
    if($it['it_option_subject']) {
        $temp = explode(',', $it['it_option_subject']);
        $option_count = count($temp);
    }

    // 상품 추가옵션 수
    $supply_count = 0;
    if($it['it_supply_subject']) {
        $temp = explode(',', $it['it_supply_subject']);
        $supply_count = count($temp);
    }
}

// 스킨경로
$skin_dir = G5_MSHOP_SKIN_PATH;
$ca_dir_check = true;

if($it['it_mobile_skin']) {
    $skin_dir = G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$it['it_mobile_skin'];

    if(is_dir($skin_dir)) {
        $form_skin_file = $skin_dir.'/item.form.skin.php';

        if(is_file($form_skin_file))
            $ca_dir_check = false;
    }
}

if($ca_dir_check) {
    if($ca['ca_mobile_skin_dir']) {
        $skin_dir = G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$ca['ca_mobile_skin_dir'];

        if(is_dir($skin_dir)) {
            $form_skin_file = $skin_dir.'/item.form.skin.php';

            if(!is_file($form_skin_file))
                $skin_dir = G5_MSHOP_SKIN_PATH;
        } else {
            $skin_dir = G5_MSHOP_SKIN_PATH;
        }
    }
}

define('G5_SHOP_CSS_URL', str_replace(G5_PATH, G5_URL, $skin_dir));

$g5['title'] = $it['it_name'].' &gt; '.$it['ca_name'];

include_once(G5_MSHOP_PATH.'/_head.php');

// 분류 위치
// HOME > 1단계 > 2단계 ... > 5단계 분류
$ca_id = $it['ca_id'];
$nav_skin = $skin_dir.'/navigation.skin.php';
if(!is_file($nav_skin))
    $nav_skin = G5_MSHOP_SKIN_PATH.'/navigation.skin.php';
include $nav_skin;

// 상단 HTML
echo '<div id="sit_hhtml">'.stripslashes($it['it_mobile_head_html']).'</div>';
?>

<?php if($is_orderable) { ?>
<script src="<?php echo G5_JS_URL; ?>/shop.js"></script>
<?php } ?>

<?php
if (G5_HTTPS_DOMAIN)
    $action_url = G5_HTTPS_DOMAIN.'/'.G5_SHOP_DIR.'/cartupdate.php';
else
    $action_url = G5_SHOP_URL.'/cartupdate.php';
?>

<div id="sit">

    <?php
    // 상품 구입폼
    include_once($skin_dir.'/item.form.skin.php');
    ?>

</div>

<?php
// 하단 HTML
echo stripslashes($it['it_mobile_tail_html']);

include_once(G5_MSHOP_PATH.'/_tail.php');
?>
