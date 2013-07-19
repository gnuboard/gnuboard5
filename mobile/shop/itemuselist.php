<?php
include_once('./_common.php');
include_once(G4_LIB_PATH.'/thumb.lib.php');

$sfl = escape_trim($_REQUEST['sfl']);
$stx = escape_trim($_REQUEST['stx']);

$g4['title'] = '사용후기';
include_once(G4_MSHOP_PATH.'/_head.php');

$sql_common = " from `{$g4['shop_item_use_table']}` a join `{$g4['shop_item_table']}` b on (a.it_id=b.it_id) ";
$sql_search = " where a.is_confirm = '1' ";

if(!$sfl)
    $sfl = 'b.it_name';

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "a.it_id" :
            $sql_search .= " ($sfl like '$stx%') ";
            break;
        case "a.is_name" :
        case "a.mb_id" :
            $sql_search .= " ($sfl = '$stx') ";
            break;
        default :
            $sql_search .= " ($sfl like '%$stx%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst  = "a.is_id";
    $sod = "desc";
}
$sql_order = " order by $sst $sod ";

$sql = " select count(*) as cnt
         $sql_common
         $sql_search
         $sql_order ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
?>

<!-- 전체 상품 사용후기 목록 시작 { -->
<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<div id="sps_sch">
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>">전체보기</a>
    <label for="sfl" class="sound_only">검색항목</label>
    <select name="sfl" id="sfl" required>
        <option value="">선택</option>
        <option value="b.it_name"   <?php echo get_selected($sfl, "b.it_name"); ?>>상품명</option>
        <option value="a.it_id"     <?php echo get_selected($sfl, "a.it_id"); ?>>상품코드</option>
        <option value="a.is_subject"<?php echo get_selected($sfl, "a.is_subject"); ?>>후기제목</option>
        <option value="a.is_content"<?php echo get_selected($sfl, "a.is_content"); ?>>후기내용</option>
        <option value="a.is_name"   <?php echo get_selected($sfl, "a.is_name"); ?>>작성자명</option>
        <option value="a.mb_id"     <?php echo get_selected($sfl, "a.mb_id"); ?>>작성자아이디</option>
    </select>

    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" required class="frm_input">
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<div id="sps">

    <!-- <p><?php echo $config['cf_title']; ?> 전체 사용후기 목록입니다.</p> -->

    <?php
    $sql = " select *
              $sql_common
              $sql_search
              $sql_order
              limit $from_record, $rows ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $num = $total_count - ($page - 1) * $rows - $i;
        $star = get_star($row['is_score']);

        $is_content = get_view_thumbnail($row['is_content'], 500);
        $small_image = $row['it_id'];

        $row2 = sql_fetch(" select it_name from {$g4['shop_item_table']} where it_id = '{$row['it_id']}' ");
        $it_href = G4_SHOP_URL."/item.php?it_id={$row['it_id']}";

        if ($i == 0) echo '<ol>';
    ?>
    <li>

        <div class="sps_img">
            <a href="<?php echo $it_href; ?>">
                <?php echo get_it_image($small_image, 70, 70); ?>
                <span><?php echo $row2['it_name']; ?></span>
            </a>
        </div>

        <section class="sps_section">
            <h2><?php echo $row['is_subject']; ?></h2>

            <dl class="sps_dl">
                <dt>작성자</dt>
                <dd><?php echo $row['is_name']; ?></dd>
                <dt>작성일</dt>
                <dd><?php echo substr($row['is_time'],0,10); ?></dd>
                <dt>평가점수</dt>
                <dd><img src="<?php echo G4_URL; ?>/img/shop/s_star<?php echo $star; ?>.png" alt="별<?php echo $star; ?>개"></dd>
            </dl>

            <div id="sps_con_<?php echo $i; ?>" style="display:none;">
                <?php echo $is_content; // 사용후기 내용 ?>
            </div>

            <div class="sps_con_btn"><button class="sps_con_<?php echo $i; ?>">보기</button></div>
        </section>

    </li>
    <?php }
    if ($i > 0) echo '</ol>';
    if ($i == 0) echo '<p id="sps_empty">자료가 없습니다.</p>';
    ?>
</div>

<?php echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<script>
$(function(){
    // 사용후기 더보기
    $(".sps_con_btn button").click(function(){
        var sps_con_no = $(this).attr("class");
        $("#"+sps_con_no).is(":hidden") ? $("#"+sps_con_no).show() : $("#"+sps_con_no).hide();
    });

    $(".sps_con_btn button").toggle(function(){
        $(this).text("닫기");
    }, function(){
        $(this).text("보기");
    });
});
</script>
<!-- } 전체 상품 사용후기 목록 끝 -->

<?php
include_once(G4_MSHOP_PATH.'/_tail.php');
?>
