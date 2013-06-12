<?php
include_once('./_common.php');
include_once(G4_LIB_PATH.'/thumb.lib.php');

$it_id = $_REQUEST['it_id'];

$itemuse_write = G4_BBS_URL.'/write.php?bo_table=itemuse&amp;wr_1='.$it_id;
$itemuse_board = G4_BBS_URL.'/board.php?bo_table=itemuse&amp;wr_1='.$it_id;

include_once(G4_PATH.'/head.sub.php');
?>

<a href="<?php echo $itemuse_board; ?>" target="_blank">더보기</a>

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
$sql_common = " from `{$g4['write_prefix']}itemuse` where wr_is_comment = 0 and wr_1 = '{$it_id}' ";

// 테이블의 전체 레코드수만 얻음
$sql = " select COUNT(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = 2;
$total_page  = ceil($total_count / $rows); // 전체 페이지 계산
if ($page == "") $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 레코드 구함

$sql = "select * $sql_common order by wr_num limit $from_record, $rows ";
$result = sql_query($sql);

for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $use_num     = $total_count - ($page - 1) * $rows - $i;
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
// 현재페이지, 총페이지수, 한페이지에 보여줄 행, URL
function itemuse_page($write_pages, $cur_page, $total_page, $url, $add="")
{
    $url = preg_replace('#&amp;page=[0-9]*(&amp;page=)$#', '$1', $url);

    $str = '';
    if ($cur_page > 1) {
        $str .= '<a href="'.$url.'1'.$add.'" class="pg_page pg_start" onclick="return false;">처음</a>'.PHP_EOL;
    }

    $start_page = ( ( (int)( ($cur_page - 1 ) / $write_pages ) ) * $write_pages ) + 1;
    $end_page = $start_page + $write_pages - 1;

    if ($end_page >= $total_page) $end_page = $total_page;

    if ($start_page > 1) $str .= '<a href="'.$url.($start_page-1).$add.'" class="pg_page pg_prev" onclick="return false;">이전</a>'.PHP_EOL;

    if ($total_page > 1) {
        for ($k=$start_page;$k<=$end_page;$k++) {
            if ($cur_page != $k)
                $str .= '<a href="'.$url.$k.$add.'" class="pg_page" onclick="return false;">'.$k.'</a><span class="sound_only">페이지</span>'.PHP_EOL;
            else
                $str .= '<span class="sound_only">열린</span><strong class="pg_current">'.$k.'</strong><span class="sound_only">페이지</span>'.PHP_EOL;
        }
    }

    if ($total_page > $end_page) $str .= '<a href="'.$url.($end_page+1).$add.'" class="pg_page pg_next">다음</a>'.PHP_EOL;

    if ($cur_page < $total_page) {
        $str .= '<a href="'.$url.$total_page.$add.'" class="pg_page pg_end" onclick="return false;">맨끝</a>'.PHP_EOL;
    }

    if ($str)
        return "<nav class=\"pg_wrap\"><span class=\"pg\">{$str}</span></nav>";
    else
        return "";
}

echo itemuse_page(10, $page, $total_page, "./itemuse.php?it_id=$it_id&amp;page=", "");
?>

<!-- <a href="javascript:itemusewin('it_id=<?php echo $it_id; ?>');">사용후기 쓰기<span class="sound_only"> 새 창</span></a> -->
<a href="<?php echo $itemuse_write; ?>" onclick="window.open(this.href); return false;">사용후기 쓰기<span class="sound_only"> 새 창</span></a>

<script>
$(function(){
    $(".use_href").click(function(){
        $(".use_div").hide();
        $("#use_div"+$(this).attr("target")).show();
    });

    $(".pg_page").click(function(){
        //alert($(this).attr("href"));
        $(top.document).find('#itemuse').load($(this).attr("href"));
    });
});
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>