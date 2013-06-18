<?php
include_once('./_common.php');
include_once(G4_LIB_PATH.'/thumbnail.lib.php');

$it_id = $_REQUEST['it_id'];

$itemuse_form = "./itemuseform.php?it_id=".$it_id;
$itemuse_list = "./itemuselist.php";

//include_once(G4_PATH.'/head.sub.php');
?>

<section id="sit_use_list">
    <h3>등록된 사용후기</h3>

    <?php
    /*
        여분필드 용도
        wr_1 : 상품코드
        wr_2 : 상품명
        wr_3 : 평점 1~5
        wr_4 : 관리자확인
    */
    //$sql_common = " from `{$g4['write_prefix']}itemuse` where wr_is_comment = 0 and wr_1 = '{$it['it_id']}' and wr_4 = '1' ";
    $sql_common = " from `{$g4['shop_item_use_table']}` where it_id = '{$it_id}' and is_confirm = '1' ";

    // 테이블의 전체 레코드수만 얻음
    $sql = " select COUNT(*) as cnt " . $sql_common;
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];

    $rows = 5;
    $total_page  = ceil($total_count / $rows); // 전체 페이지 계산
    if ($page == "") $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
    $from_record = ($page - 1) * $rows; // 시작 레코드 구함

    $sql = "select * $sql_common order by is_id desc limit $from_record, $rows ";
    $result = sql_query($sql);

    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $is_num     = $total_count - ($page - 1) * $rows - $i;
        $is_star    = get_star($row['is_score']);
        $is_name    = get_text($row['is_name']);
        $is_subject = conv_subject($row['is_subject'],50,"…");
        //$is_content = ($row['wr_content']);
        $is_content = get_view_thumbnail($row['is_content'], 300);
        $is_time    = substr($row['is_time'], 2, 8);
        $is_href    = './itemuselist.php?bo_table=itemuse&amp;wr_id='.$row['wr_id'];

        // http://stackoverflow.com/questions/6967081/show-hide-multiple-divs-with-jquery?answertab=votes#tab-top

        if ($i == 0) echo '<ol id="sit_use_ol">';
    ?>

        <li class="sit_use_li">
            <button type="button" class="sit_use_li_title" onclick="javascript:qa_menu('sit_use_con_<?php echo $i; ?>')"><b><?php echo $is_num; ?>.</b> <?php echo $is_subject; ?></button>
            <dl class="sit_use_dl">
                <dt>작성자</dt>
                <dd><?php echo $is_name; ?></dd>
                <dt>작성일</dt>
                <dd><?php echo $is_time; ?></dd>
                <dt>선호도<dt>
                <dd class="sit_use_star"><img src="<?php echo G4_URL; ?>/img/shop/s_star<?php echo $is_star; ?>.png" alt="별<?php echo $is_star; ?>개"></dd>
            </dl>

            <div id="sit_use_con_<?php echo $i; ?>" class="sit_use_con">
                <p>
                    <?php echo $is_content; // 사용후기 내용 ?>
                </p>
            </div>
        </li>

    <?php }

    if ($i >= 0) echo '</ol>';

    if (!$i) echo '<p class="sit_empty">사용후기가 없습니다.</p>';
    ?>
</section>

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

echo itemuse_page($config['cf_write_pages'], $page, $total_page, "./itemuse.php?it_id=$it_id&amp;page=", "");
?>

<div id="sit_use_wbtn">
    <!-- <a href="javascript:itemusewin('it_id=<?php echo $it_id; ?>');">사용후기 쓰기<span class="sound_only"> 새 창</span></a> -->
    <a href="<?php echo $itemuse_form; ?>" id="itemuse_form" class="btn02">사용후기 쓰기<span class="sound_only"> 새 창</span></a>
    <a href="<?php echo $itemuse_list; ?>" id="itemuse_list" class="btn01">더보기</a>
</div>

<script>
$(function(){
    $("#itemuse_form").click(function(){
        window.open(this.href, "itemuse_form", "width=800,height=550");
        return false;
    });

    $(".use_href").click(function(){
        var $content = $("#use_div"+$(this).attr("target"));
        $(".use_div").each(function(index, value){
            if ($(this).get(0) == $content.get(0)) { // 객체의 비교시 .get(0) 를 사용한다.
                $(this).is(":hidden") ? $(this).show() : $(this).hide();
            } else {
                $(this).hide();
            }
        });
    });

    $(".pg_page").click(function(){
        //alert($(this).attr("href"));
        $(top.document).find('#itemuse').load($(this).attr("href"));
    });
});
</script>

<?php
//include_once(G4_PATH.'/tail.sub.php');
?>