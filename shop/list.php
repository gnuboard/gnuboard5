<?php
include_once('./_common.php');

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/list.php');
    return;
}

$sql = " select * from {$g5['g5_shop_category_table']} where ca_id = '$ca_id' and ca_use = '1'  ";
$ca = sql_fetch($sql);
if (!$ca['ca_id'])
    alert('등록된 분류가 없습니다.');

// 본인인증, 성인인증체크
if(!$is_admin) {
    $msg = shop_member_cert_check($ca_id, 'list');
    if($msg)
        alert($msg);
}

$g5['title'] = $ca['ca_name'].' 상품리스트';

if ($ca['ca_include_head'])
    @include_once($ca['ca_include_head']);
else
    include_once('./_head.php');

// 스킨을 지정했다면 지정한 스킨을 사용함 (스킨의 다양화)
//if ($skin) $ca[ca_skin] = $skin;

if ($is_admin)
    echo '<div class="sct_admin"><a href="'.G5_ADMIN_URL.'/shop_admin/categoryform.php?w=u&amp;ca_id='.$ca_id.'" class="btn_admin">분류 관리</a></div>';
?>

<script>
var itemlist_ca_id = "<?php echo $ca_id; ?>";
</script>
<script src="<?php echo G5_JS_URL; ?>/shop.list.js"></script>

<!-- 상품 목록 시작 { -->
<div id="sct">

    <?php
    $nav_ca_id = $ca_id;
    include G5_SHOP_SKIN_PATH.'/navigation.skin.php';

    // 상단 HTML
    echo '<div id="sct_hhtml">'.stripslashes($ca['ca_head_html']).'</div>';

    include G5_SHOP_SKIN_PATH.'/listcategory.skin.php';

    // 상품 출력순서가 있다면
    if ($sort != "")
        $order_by = $sort.' '.$sortodr.' , it_order, it_id desc';
    else
        $order_by = 'it_order, it_id desc';

    $error = '<p class="sct_noitem">등록된 상품이 없습니다.</p>';

    // 리스트 유형별로 출력
    $list_file = G5_SHOP_SKIN_PATH.'/'.$ca['ca_skin'];
    if (file_exists($list_file)) {

		echo '<div id="sct_sortlst">';
        include G5_SHOP_SKIN_PATH.'/list.sort.skin.php';

        // 상품 보기 타입 변경 버튼
        include G5_SHOP_SKIN_PATH.'/list.sub.skin.php';
		echo '</div>';

        // 총몇개 = 한줄에 몇개 * 몇줄
        $items = $ca['ca_list_mod'] * $ca['ca_list_row'];
        // 페이지가 없으면 첫 페이지 (1 페이지)
        if ($page == "") $page = 1;
        // 시작 레코드 구함
        $from_record = ($page - 1) * $items;

        $list = new item_list($ca['ca_skin'], $ca['ca_list_mod'], $ca['ca_list_row'], $ca['ca_img_width'], $ca['ca_img_height']);
        $list->set_category($ca['ca_id'], 1);
        $list->set_category($ca['ca_id'], 2);
        $list->set_category($ca['ca_id'], 3);
        $list->set_is_page(true);
        $list->set_order_by($order_by);
        $list->set_from_record($from_record);
        $list->set_view('it_img', true);
        $list->set_view('it_id', false);
        $list->set_view('it_name', true);
        $list->set_view('it_basic', true);
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
        echo '<div class="sct_nofile">'.$ca['ca_skin'].' 파일을 찾을 수 없습니다.<br>관리자에게 알려주시면 감사하겠습니다.</div>';
    }
    ?>

    <?php
    $qstr1 .= 'ca_id='.$ca_id;
    if($skin)
        $qstr1 .= '&amp;skin='.$skin;
    $qstr1 .='&amp;sort='.$sort.'&amp;sortodr='.$sortodr;
    echo get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['PHP_SELF'].'?'.$qstr1.'&amp;page=');
    ?>

    <?php
    // 하단 HTML
    echo '<div id="sct_thtml">'.stripslashes($ca['ca_tail_html']).'</div>';

?>
</div>
<!-- } 상품 목록 끝 -->

<?php
if ($ca['ca_include_tail'])
    @include_once($ca['ca_include_tail']);
else
    include_once('./_tail.php');

echo "\n<!-- {$ca['ca_skin']} -->\n";
?>
