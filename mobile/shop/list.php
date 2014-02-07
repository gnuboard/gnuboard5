<?php
include_once('./_common.php');

$sql = " select *
           from {$g5['g5_shop_category_table']}
          where ca_id = '$ca_id'
            and ca_use = '1'  ";
$ca = sql_fetch($sql);
if (!$ca['ca_id'])
    alert('등록된 분류가 없습니다.', G5_SHOP_URL);

// 본인인증, 성인인증체크
if(!$is_admin) {
    $msg = shop_member_cert_check($ca_id, 'list');
    if($msg)
        alert($msg, G5_SHOP_URL);
}

$g5['title'] = $ca['ca_name'].' 상품리스트';

include_once(G5_MSHOP_PATH.'/_head.php');

// 스킨을 지정했다면 지정한 스킨을 사용함 (스킨의 다양화)
//if ($skin) $ca[ca_skin] = $skin;
?>

<div id="sct">

    <?php
    $nav_ca_id = $ca_id;
    include G5_MSHOP_SKIN_PATH.'/navigation.skin.php';

    // 상단 HTML
    echo '<div id="sct_hhtml">'.stripslashes($ca['ca_mobile_head_html']).'</div>';

    // 상품 출력순서가 있다면
    if ($sort != "")
        $order_by = $sort.' '.$sortodr.' , it_order, it_id desc';
    else
        $order_by = 'it_order, it_id desc';

    $error = '<p class="sct_noitem">등록된 상품이 없습니다.</p>';

    // 리스트 유형별로 출력
    $list_file = G5_MSHOP_SKIN_PATH.'/'.$ca['ca_mobile_skin'];
    if (file_exists($list_file)) {
        include G5_MSHOP_SKIN_PATH.'/list.sort.skin.php';

        // 총몇개
        $items = $ca['ca_mobile_list_mod'];
        // 페이지가 없으면 첫 페이지 (1 페이지)
        if ($page == "") $page = 1;
        // 시작 레코드 구함
        $from_record = ($page - 1) * $items;

        $list = new item_list($ca['ca_mobile_skin'], $ca['ca_mobile_list_mod'], 1, $ca['ca_mobile_img_width'], $ca['ca_mobile_img_height']);
        $list->set_category($ca['ca_id'], 1);
        $list->set_category($ca['ca_id'], 2);
        $list->set_category($ca['ca_id'], 3);
        $list->set_is_page(true);
        $list->set_mobile(true);
        $list->set_order_by($order_by);
        $list->set_from_record($from_record);
        $list->set_view('it_img', true);
        $list->set_view('it_id', false);
        $list->set_view('it_name', true);
        $list->set_view('it_cust_price', true);
        $list->set_view('it_price', true);
        $list->set_view('it_icon', true);
        $list->set_view('sns', true);
        echo $list->run();

        // where 된 전체 상품수
        $total_count = $list->total_count;
        // 전체 페이지 계산
        $total_page  = ceil($total_count / $items);
    }
    else
    {
        echo '<div class="sct_nofile">'.$ca['ca_mobile_skin'].' 파일을 찾을 수 없습니다.<br>관리자에게 알려주시면 감사하겠습니다.</div>';
    }
    ?>

    <?php
    $qstr1 .= 'ca_id='.$ca_id;
    if($skin)
        $qstr1 .= '&amp;skin='.$skin;
    $qstr1 .='&amp;sort='.$sort.'&amp;sortodr='.$sortodr;
    echo get_paging($config['cf_mobile_pages'], $page, $total_page, $_SERVER['PHP_SELF'].'?'.$qstr1.'&amp;page=');
    ?>

    <?php
    // 하단 HTML
    echo '<div id="sct_thtml">'.stripslashes($ca['ca_mobile_tail_html']).'</div>';
?>
</div>

<?php
include_once(G5_MSHOP_PATH.'/_tail.php');

echo "\n<!-- {$ca['ca_mobile_skin']} -->\n";
?>
