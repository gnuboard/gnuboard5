<?php
include_once('./_common.php');

$ev_id = isset($_GET['ev_id']) ? (int) $_GET['ev_id'] : 0;
$skin = isset($_GET['skin']) ? clean_xss_tags($_GET['skin'], 1, 1) : '';
$ca_id = isset($_GET['ca_id']) ? clean_xss_tags($_GET['ca_id'], 1, 1) : '';

// 상품 리스트에서 다른 필드로 정렬을 하려면 아래의 배열 코드에서 해당 필드를 추가하세요.
if( isset($sort) && ! in_array($sort, array('it_name', 'it_sum_qty', 'it_price', 'it_use_avg', 'it_use_cnt', 'it_update_time')) ){
    $sort='';
}

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/event.php');
    return;
}

$sql = " select * from {$g5['g5_shop_event_table']}
          where ev_id = '$ev_id'
            and ev_use = 1 ";
$ev = sql_fetch($sql);
if (! (isset($ev['ev_id']) && $ev['ev_id']))
    alert('등록된 이벤트가 없습니다.');

$g5['title'] = $ev['ev_subject'];
include_once('./_head.php');

if ($is_admin)
    echo '<div class="sev_admin"><a href="'.G5_ADMIN_URL.'/shop_admin/itemeventform.php?w=u&amp;ev_id='.$ev['ev_id'].'" class="btn_admin btn"><span class="sound_only">이벤트 관리</span><i class="fa fa-cog fa-spin fa-fw"></i></a></div>';
?>

<script>
var itemlist_ca_id = "<?php echo $ev_id; ?>";
</script>
<script src="<?php echo G5_JS_URL; ?>/shop.list.js"></script>

<!-- 이벤트 시작 { -->
<?php
$himg = G5_DATA_PATH.'/event/'.$ev_id.'_h';
if (file_exists($himg))
    echo '<div id="sev_himg" class="sev_img"><img src="'.G5_DATA_URL.'/event/'.$ev_id.'_h" alt=""></div>';

// 상단 HTML
echo '<div id="sev_hhtml">'.conv_content($ev['ev_head_html'], 1).'</div>';

// 상품 출력순서가 있다면
if ($sort != "")
    $order_by = $sort.' '.$sortodr.' , b.it_order, b.it_id desc';
else
    $order_by = 'b.it_order, b.it_id desc';

if ($skin) {
    $skin = preg_replace(array('#\.+(\/|\\\)#', '#[\'\"]#'), array('', ''), $skin);
    $ev['ev_skin'] = $skin;
}

define('G5_SHOP_CSS_URL', G5_SHOP_SKIN_URL);

// 리스트 유형별로 출력
$list_file = G5_SHOP_SKIN_PATH."/{$ev['ev_skin']}";
if (file_exists($list_file))
{
    
    echo '<div id="sct_sortlst">';
    include G5_SHOP_SKIN_PATH.'/list.sort.skin.php';

    // 상품 보기 타입 변경 버튼
    include G5_SHOP_SKIN_PATH.'/list.sub.skin.php';

    echo '</div>';

    // 총몇개 = 한줄에 몇개 * 몇줄
    $items = $ev['ev_list_mod'] * $ev['ev_list_row'];
    // 페이지가 없으면 첫 페이지 (1 페이지)
    if ($page < 1) $page = 1;
    // 시작 레코드 구함
    $from_record = ($page - 1) * $items;

    $list = new item_list(G5_SHOP_SKIN_PATH.'/'.$ev['ev_skin'], $ev['ev_list_mod'], $ev['ev_list_row'], $ev['ev_img_width'], $ev['ev_img_height']);
    $list->set_event($ev['ev_id']);
    $list->set_is_page(true);
    $list->set_order_by($order_by);
    $list->set_from_record($from_record);
    $list->set_view('it_img', true);
    $list->set_view('it_id', false);
    $list->set_view('it_name', true);
    $list->set_view('it_cust_price', false);
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
    echo '<div align="center">'.$ev['ev_skin'].' 파일을 찾을 수 없습니다.<br>관리자에게 알려주시면 감사하겠습니다.</div>';
}
?>

<?php
$qstr .= 'skin='.$skin.'&amp;ev_id='.$ev_id.'&amp;sort='.$sort.'&amp;sortodr='.$sortodr;
echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page=");
?>

<?php
// 하단 HTML
echo '<div id="sev_thtml">'.conv_content($ev['ev_tail_html'], 1).'</div>';

$timg = G5_DATA_PATH.'/event/'.$ev_id.'_t';
if (file_exists($timg))
    echo '<div id="sev_timg" class="sev_img"><img src="'.G5_DATA_URL.'/event/'.$ev_id.'_t" alt=""></div>';
?>
<!-- } 이벤트 끝 -->

<?php
include_once('./_tail.php');