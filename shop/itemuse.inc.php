<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$itemuse_write = G4_BBS_URL.'/write.php?bo_table=itemuse&amp;wr_1='.$it_id;
$itemuse_board = G4_BBS_URL.'/board.php?bo_table=itemuse&amp;wr_1='.$it_id;

include_once(G4_LIB_PATH.'/thumb.lib.php');
?>

<a href="<?php echo $itemuse_board; ?>">더보기</a>

<table id="sit_ps_tbl">
<thead>
<tr>
    <th>번호</th>
    <th>제목</th>
    <th>작성자</th>
    <th>작성일</th>
    <th>평점</th>
</tr>
</thead>
<?php
/*
    여분필드 용도 
    wr_1 : 상품코드
    wr_2 : 상품명
    wr_3 : 평점 1~5
    wr_4 : 관리자확인
*/
//$sql_common = " from `{$g4['write_prefix']}itemuse` where wr_is_comment = 0 and wr_1 = '{$it['it_id']}' and wr_4 = '1' ";
$sql_common = " from `{$g4['write_prefix']}itemuse` where wr_is_comment = 0 and wr_1 = '{$it['it_id']}' ";

// 테이블의 전체 레코드수만 얻음
$sql = " select COUNT(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$use_total_count = $row['cnt'];

$use_total_page  = ceil($use_total_count / $use_page_rows); // 전체 페이지 계산
if ($use_page == "") $use_page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$use_from_record = ($use_page - 1) * $use_page_rows; // 시작 레코드 구함

$sql = "select * $sql_common order by wr_num limit $use_from_record, $use_page_rows ";
$result = sql_query($sql);

for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $use_num     = $use_total_count - ($use_page - 1) * $use_page_rows - $i;
    $use_star    = get_star($row['wr_3']);
    $use_name    = get_text($row['wr_name']);
    $use_subject = conv_subject($row['wr_subject'],50,"…");
    $use_content = $row['wr_content'];
    $use_time    = substr($row['wr_datetime'], 2, 8);
    $use_href    = G4_BBS_URL.'/board.php?bo_table=itemuse&amp;wr_id='.$row['wr_id'];

    // http://stackoverflow.com/questions/6967081/show-hide-multiple-divs-with-jquery?answertab=votes#tab-top
?>

<tr>
    <td><?php echo $use_num; ?><span class="sound_only">번</span></td>
    <td>
        <a href="<?php echo $use_href; ?>" class="use_href" onclick="return false;" target="<?php echo $i; ?>"><?php echo $use_subject; ?></a>
        <div id="use_div<?php echo $i; ?>" class="use_div" style="display:none;">
            <?php echo $use_content; ?>
        </div>
    </td>
    <td><?php echo $use_name; ?></td>
    <td><?php echo $use_time; ?></td>
    <td><img src="<?php echo G4_URL; ?>/img/shop/s_star<?php echo $use_star; ?>.png" alt="별<?php echo $use_star; ?>개"></td>
</tr>

<?php
}

if (!$i) {
    echo '<tr><td class="empty_class">등록된 사용후기가 없습니다.</td></tr>';
}
?>
</table>

<?php
if ($use_pages) {
    $use_pages = get_paging(10, $use_page, $use_total_page, "./item.php?it_id=$it_id&amp;$qstr&amp;use_page=", "#use");
}
?>

<!-- <a href="javascript:itemusewin('it_id=<?php echo $it_id; ?>');">사용후기 쓰기<span class="sound_only"> 새 창</span></a> -->
<a href="<?php echo $itemuse_write; ?>" onclick="window.open(this.href); return false;">사용후기 쓰기<span class="sound_only"> 새 창</span></a>

<script>
$(function(){
    $(".use_href").click(function(){
        $(".use_div").hide();
        $("#use_div"+$(this).attr("target")).show();
    });
});
</script>
