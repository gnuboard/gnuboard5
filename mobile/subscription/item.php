<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 이전 상품보기
$sql = " select it_id, it_name from {$g5['g5_shop_item_table']}
          where it_id > '$it_id'
            and SUBSTRING(ca_id,1,4) = '".substr($it['ca_id'],0,4)."'
            and it_use = '1'
          order by it_id asc
          limit 1 ";
$row = sql_fetch($sql);
if (isset($row['it_id']) && $row['it_id']) {
    $prev_title = '이전상품 <span>'.$row['it_name'].'</span>';
    $prev_href = '<a href="'.subscription_item_url($row['it_id']).'" id="siblings_prev">';
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
if (isset($row['it_id']) && $row['it_id']) {
    $next_title = '다음상품 <span>'.$row['it_name'].'</span>';
    $next_href = '<a href="'.subscription_item_url($row['it_id']).'" id="siblings_next">';
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

if ($default['de_mobile_rel_list_use']) {
    // 관련상품의 개수를 얻음
    $sql = " select count(*) as cnt
               from {$g5['g5_shop_item_relation_table']} a
               left join {$g5['g5_shop_item_table']} b on (a.it_id2=b.it_id)
              where a.it_id = '{$it['it_id']}' and b.it_use='1' ";
    $row = sql_fetch($sql);
    $item_relation_count = $row['cnt'];
}

// 상품품절체크
if(G5_SOLDOUT_CHECK)
    $is_soldout = subscription_is_soldout($it['it_id'], true);

// 주문가능체크
$is_orderable = true;
if(!$it['it_use'] || $it['it_tel_inq'] || $is_soldout)
    $is_orderable = false;

if($is_orderable) {
    if(defined('G5_THEME_USE_OPTIONS_TRTD') && G5_THEME_USE_OPTIONS_TRTD){
        $option_item = get_item_options($it['it_id'], $it['it_option_subject'], '');
        $supply_item = get_item_supply($it['it_id'], $it['it_supply_subject'], '');
    } else {
        // 선택 옵션 ( 기존의 tr td 태그로 가져오려면 'div' 를 '' 로 바꾸거나 또는 지워주세요 )
        $option_item = get_item_options($it['it_id'], $it['it_option_subject'], 'div');

        // 추가 옵션 ( 기존의 tr td 태그로 가져오려면 'div' 를 '' 로 바꾸거나 또는 지워주세요 )
        $supply_item = get_item_supply($it['it_id'], $it['it_supply_subject'], 'div');
    }

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
$skin_dir = G5_MSUBSCRIPTION_SKIN_PATH;
$ca_dir_check = true;

if($it['it_mobile_skin']) {
    if(preg_match('#^theme/(.+)$#', $it['it_mobile_skin'], $match))
        $skin_dir = G5_THEME_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$match[1];
    else
        $skin_dir = G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$it['it_mobile_skin'];

    if(is_dir($skin_dir)) {
        $form_skin_file = $skin_dir.'/item.form.skin.php';

        if(is_file($form_skin_file))
            $ca_dir_check = false;
    }
}

if($ca_dir_check) {
    if($ca['ca_mobile_skin_dir']) {
        if(preg_match('#^theme/(.+)$#', $ca['ca_mobile_skin_dir'], $match))
            $skin_dir = G5_THEME_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$match[1];
        else
            $skin_dir = G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$ca['ca_mobile_skin_dir'];

        if(is_dir($skin_dir)) {
            $form_skin_file = $skin_dir.'/item.form.skin.php';

            if(!is_file($form_skin_file))
                $skin_dir = G5_SUBSCRIPTION_SKIN_PATH;
        } else {
            $skin_dir = G5_SUBSCRIPTION_SKIN_PATH;
        }
    }
}

define('G5_SHOP_CSS_URL', str_replace(G5_PATH, G5_URL, $skin_dir));
define('G5_SUBSCRIPTION_CSS_URL', str_replace(G5_PATH, G5_URL, $skin_dir));

$g5['title'] = $it['it_name'].' &gt; '.$it['ca_name'];
$naverpay_button_js = '';

include_once(G5_MSUBSCRIPTION_PATH.'/_head.php');

// 상단 HTML
echo run_replace('subscription_it_mobile_head_html', '<div id="sit_hhtml">'.conv_content($it['it_mobile_head_html'], 1).'</div>', $it);
?>

<?php if($is_orderable) { ?>
<script src="<?php echo G5_JS_URL; ?>/shop.js?ver=<?php echo G5_JS_VER; ?>"></script>
<?php } ?>

<?php
if (G5_HTTPS_DOMAIN)
    $action_url = G5_HTTPS_DOMAIN.'/'.G5_SUBSCRIPTION_DIR.'/cartupdate.php';
else
    $action_url = G5_SUBSCRIPTION_URL.'/cartupdate.php';
?>

<div id="sit">

    <?php
    // 상품 구입폼
    include_once($skin_dir.'/item.form.skin.php');
    ?>

</div>

<?php
// 하단 HTML
echo run_replace('subscription_it_mobile_tail_html', conv_content($it['it_mobile_tail_html'], 1), $it);

include_once(G5_MSUBSCRIPTION_PATH.'/_tail.php');