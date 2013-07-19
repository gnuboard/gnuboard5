<?php
include_once('./_common.php');

if (G4_IS_MOBILE) {
    include_once(G4_MSHOP_PATH.'/itemqalist.php');
    return;
}

include_once(G4_LIB_PATH.'/thumb.lib.php');

$sfl = escape_trim($_REQUEST['sfl']);
$stx = escape_trim($_REQUEST['stx']);

$g4['title'] = '상품문의';
include_once('./_head.php');

$sql_common = " from `{$g4['shop_item_qa_table']}` a join `{$g4['shop_item_table']}` b on (a.it_id=b.it_id) ";
$sql_search = " where (1) ";

if(!$sfl)
    $sfl = 'b.it_name';

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "a.it_id" :
            $sql_search .= " ($sfl like '$stx%') ";
            break;
        case "a.iq_name" :
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
    $sst  = "a.iq_id";
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

<!-- 전체 상품 문의 목록 시작 { -->

<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<div id="sqa_sch">
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>">전체보기</a>
    <select name="sfl" required title="검색항목선택">
        <option value="">선택</option>
        <option value="b.it_name"    <?php echo get_selected($sfl, "b.it_name", true); ?>>상품명</option>
        <option value="a.it_id"      <?php echo get_selected($sfl, "a.it_id"); ?>>상품코드</option>
        <option value="a.iq_subject" <?php echo get_selected($sfl, "a.is_subject"); ?>>문의제목</option>
        <option value="a.iq_question"<?php echo get_selected($sfl, "a.iq_question"); ?>>문의내용</option>
        <option value="a.iq_name"    <?php echo get_selected($sfl, "a.it_id"); ?>>작성자명</option>
        <option value="a.mb_id"      <?php echo get_selected($sfl, "a.mb_id"); ?>>작성자아이디</option>
    </select>

    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" required class="frm_input">
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<div id="sqa">

    <!-- <p><?php echo $config['cf_title']; ?> 전체 상품문의 목록입니다.</p> -->

    <?php
    $sql = " select a.*, b.it_name
              $sql_common
              $sql_search
              $sql_order
              limit $from_record, $rows ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $num = $total_count - ($page - 1) * $rows - $i;
        $star = get_star($row['is_score']);

        $small_image = $row['it_id'];

        $it_href = G4_SHOP_URL.'/item.php?it_id='.$row['it_id'];

        $iq_question = get_view_thumbnail($row['iq_question'], 500);

        if ($row['iq_answer'])
        {
            $iq_answer = get_view_thumbnail($row['iq_answer'], 500);
            $iq_stats = '답변완료';
            $iq_style = 'sit_qaa_done';
            $is_answer = true;
        } else {
            $iq_stats = '답변전';
            $iq_style = 'sit_qaa_yet';
            $iq_answer = '답변이 등록되지 않았습니다.';
            $is_answer = false;
        }

        if ($i == 0) echo '<ol>';
    ?>
    <li>

        <div class="sqa_img">
            <a href="<?php echo $it_href; ?>">
                <?php echo get_it_image($small_image, 70, 70); ?>
                <span><?php echo $row['it_name']; ?></span>
            </a>
        </div>

        <section class="sqa_section">
            <h2><?php echo $row['iq_subject']; ?></h2>

            <dl class="sqa_dl">
                <dt>작성자</dt>
                <dd><?php echo $row['iq_name']; ?></dd>
                <dt>작성일</dt>
                <dd><?php echo substr($row['iq_time'],0,10); ?></dd>
                <dt>상태</dt>
                <dd class="<?php echo $iq_style; ?>"><?php echo $iq_stats; ?></dd>
            </dl>

            <div id="sqa_con_<?php echo $i; ?>" class="sqa_con" style="display:none;">
                <div class="sit_qa_qaq">
                    <strong>문의내용</strong><br>
                    <?php echo $iq_question; // 상품 문의 내용 ?>
                </div>
                <div class="sit_qa_qaa">
                    <strong>답변</strong><br>
                    <?php echo $iq_answer; ?>
                </div>
            </div>

            <div class="sqa_con_btn"><button class="sqa_con_<?php echo $i; ?>">보기</button></div>
        </section>

    </li>
    <?php }
    if ($i > 0) echo '</ol>';
    if ($i == 0) echo '<p id="sqa_empty">자료가 없습니다.</p>';
    ?>
</div>

<?php echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<script>
$(function(){
    // 사용후기 더보기
    $(".sqa_con_btn button").click(function(){
        var sqa_con_no = $(this).attr("class");
        $("#"+sqa_con_no).is(":hidden") ? $("#"+sqa_con_no).show() : $("#"+sqa_con_no).hide();
    });

    $(".sqa_con_btn button").toggle(function(){
        $(this).text("닫기");
    }, function(){
        $(this).text("보기");
    });
});
</script>
<!-- } 전체 상품 사용후기 목록 끝 -->

<?php
include_once('./_tail.php');
?>
