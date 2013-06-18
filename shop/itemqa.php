<?php
include_once('./_common.php');
include_once(G4_LIB_PATH.'/thumbnail.lib.php');

$it_id = $_REQUEST['it_id'];

$itemqa_list = "./itemqalist.php";
$itemqa_form = "./itemqaform.php?it_id=".$it_id;
$itemqa_formupdate = "./itemqaformupdate.php?it_id=".$it_id;

$thumbnail_width = 500;
?>

<section id="sit_qa_list">
    <h3>등록된 상품문의</h3>

    <?php
    $sql_common = " from `{$g4['shop_item_qa_table']}` where it_id = '{$it_id}' ";

    // 테이블의 전체 레코드수만 얻음
    $sql = " select COUNT(*) as cnt " . $sql_common;
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];

    $rows = 5;
    $total_page  = ceil($total_count / $rows); // 전체 페이지 계산
    if ($page == "") $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
    $from_record = ($page - 1) * $rows; // 시작 레코드 구함

    $sql = "select * $sql_common order by iq_id desc limit $from_record, $rows ";
    $result = sql_query($sql);

    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $iq_num     = $total_count - ($page - 1) * $rows - $i;
        $iq_star    = get_star($row['iq_score']);
        $iq_name    = get_text($row['iq_name']);
        $iq_subject = conv_subject($row['iq_subject'],50,"…");
        $iq_question = get_view_thumbnail($row['iq_question'], $thumbnail_width);
        $iq_time    = substr($row['iq_time'], 2, 8);
        $iq_href    = './itemqalist.php?bo_table=itemqa&amp;wr_id='.$row['wr_id'];

        $hash = md5($row['iq_id'].$row['iq_time'].$row['iq_ip']);

        // http://stackoverflow.com/questions/6967081/show-hide-multiple-divs-with-jquery?answertab=votes#tab-top

        $iq_stats = '';
        $iq_answer = '';
        if ($row['iq_answer'])
        {
            $iq_answer = get_view_thumbnail($row['iq_answer'], $thumbnail_width);
            $iq_stats = '답변완료';
            $is_answer = true;
        } else {
            $iq_stats = '답변전';
            $iq_answer = '답변이 등록되지 않았습니다.';
            $is_answer = false;
        }

        if ($i == 0) echo '<ol id="sit_qa_ol">';
    ?>

        <li class="sit_qa_li">
            <button type="button" class="sit_qa_li_title" onclick="javascript:qa_menu('sit_qa_con_<?php echo $i; ?>')"><b><?php echo $num; ?>.</b> <?php echo $iq_subject; ?></button>
            <dl class="sit_qa_dl">
                <dt>작성자</dt>
                <dd><?php echo $iq_name; ?></dd>
                <dt>작성일</dt>
                <dd><?php echo $iq_time; ?></dd>
                <dt>상태</dt>
                <dd><?php echo $iq_stats; ?></dd>
            </dl>

            <div id="sit_qa_con_<?php echo $i; ?>" class="sit_qa_con">
                <p class="sit_qa_qaq">
                    <strong>문의내용</strong><br>
                    <?php echo $iq_question; // 상품 문의 내용 ?>
                </p>

                <?php if ($is_admin || ($row['mb_id'] == $member['mb_id'] && !$is_answer)) { ?>
                <div class="sit_qa_cmd">
                    <a href="<?php echo $itemqa_form."&amp;iq_id={$row['iq_id']}&amp;w=u"; ?>" class="itemqa_form" onclick="return false;">수정</a>
                    <a href="<?php echo $itemqa_formupdate."&amp;iq_id={$row['iq_id']}&amp;w=d&amp;hash={$hash}"; ?>" class="itemqa_delete">삭제</a>
                    <!-- <button type="button" onclick="javascript:itemqa_update(<?php echo $i; ?>);" class="btn01">수정</button>
                    <button type="button" onclick="javascript:itemqa_delete(fitemqa_password<?php echo $i; ?>, <?php echo $i; ?>);" class="btn01">삭제</button> -->
                </div>
                <?php } ?>

                <p class="sit_qa_qaa">
                    <strong>답변</strong><br>
                    <?php echo $iq_answer; ?>
                </p>
            </div>
            </div>
        </li>

    <?php }

    if ($i >= 0) echo '</ol>';

    if (!$i) echo '<p class="sit_empty">상품문의가 없습니다.</p>';
    ?>
</section>

<?php
// 현재페이지, 총페이지수, 한페이지에 보여줄 행, URL
function itemqa_page($write_pages, $cur_page, $total_page, $url, $add="")
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

echo itemqa_page(10, $page, $total_page, "./itemqa.php?it_id=$it_id&amp;page=", "");
?>

<div id="sit_qa_wbtn">
    <!-- <a href="javascript:itemqawin('it_id=<?php echo $it_id; ?>');">상품문의 쓰기<span class="sound_only"> 새 창</span></a> -->
    <a href="<?php echo $itemqa_form; ?>" onclick="return false;" class="btn02 itemqa_form">상품문의 쓰기<span class="sound_only"> 새 창</span></a>
    <a href="<?php echo $itemqa_list; ?>" id="itemqa_list" class="btn01">더보기</a>
</div>

<script>
$(function(){
    $(".itemqa_form").click(function(){
        window.open(this.href, "itemqa_form", "width=800,height=500"); 
    });

    $(".itemqa_delete").click(function(){
        return confirm("정말 삭제 하시겠습니까?\n\n삭제후에는 되돌릴수 없습니다.");
    });

    $(".qa_href").click(function(){
        var $content = $("#qa_div"+$(this).attr("target"));
        $(".qa_div").each(function(index, value){
            if ($(this).get(0) == $content.get(0)) { // 객체의 비교시 .get(0) 를 사용한다.
                $(this).is(":hidden") ? $(this).show() : $(this).hide();
            } else {
                $(this).hide();
            }
        });
    });

    $(".pg_page").click(function(){
        //alert($(this).attr("href"));
        $(top.document).find('#itemqa').load($(this).attr("href"));
    });
});
</script>
