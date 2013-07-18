<?php
include_once('./_common.php');
include_once(G4_LIB_PATH.'/thumbnail.lib.php');

//$it_id = $_REQUEST['it_id'];

$itemqa_list = "./itemqalist.php";
$itemqa_form = "./itemqaform.php?it_id=".$it_id;
$itemqa_formupdate = "./itemqaformupdate.php?it_id=".$it_id;

$thumbnail_width = 500;
?>

<!-- 상품문의 목록 시작 { -->
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

        $hash = md5($row['iq_id'].$row['iq_time'].$row['iq_ip']);

        // http://stackoverflow.com/questions/6967081/show-hide-multiple-divs-with-jquery?answertab=votes#tab-top

        $iq_stats = '';
        $iq_style = '';
        $iq_answer = '';
        if ($row['iq_answer'])
        {
            $iq_answer = get_view_thumbnail($row['iq_answer'], $thumbnail_width);
            $iq_stats = '답변완료';
            $iq_style = 'sit_qaa_done';
            $is_answer = true;
        } else {
            $iq_stats = '답변전';
            $iq_style = 'sit_qaa_yet';
            $iq_answer = '답변이 등록되지 않았습니다.';
            $is_answer = false;
        }

        if ($i == 0) echo '<ol id="sit_qa_ol">';
    ?>

        <li class="sit_qa_li">
            <button type="button" class="sit_qa_li_title" onclick="javascript:qa_menu('sit_qa_con_<?php echo $i; ?>')"><b><?php echo $iq_num; ?>.</b> <?php echo $iq_subject; ?></button>
            <dl class="sit_qa_dl">
                <dt>작성자</dt>
                <dd><?php echo $iq_name; ?></dd>
                <dt>작성일</dt>
                <dd><?php echo $iq_time; ?></dd>
                <dt>상태</dt>
                <dd class="<?php echo $iq_style; ?>"><?php echo $iq_stats; ?></dd>
            </dl>

            <div id="sit_qa_con_<?php echo $i; ?>" class="sit_qa_con">
                <div class="sit_qa_p">
                    <div class="sit_qa_qaq">
                        <strong>문의내용</strong><br>
                        <?php echo $iq_question; // 상품 문의 내용 ?>
                    </div>
                    <div class="sit_qa_qaa">
                        <strong>답변</strong><br>
                        <?php echo $iq_answer; ?>
                    </div>
                </div>

                <?php if ($is_admin || ($row['mb_id'] == $member['mb_id'] && !$is_answer)) { ?>
                <div class="sit_qa_cmd">
                    <a href="<?php echo $itemqa_form."&amp;iq_id={$row['iq_id']}&amp;w=u"; ?>" class="itemqa_form btn01" onclick="return false;">수정</a>
                    <a href="<?php echo $itemqa_formupdate."&amp;iq_id={$row['iq_id']}&amp;w=d&amp;hash={$hash}"; ?>" class="itemqa_delete btn01">삭제</a>
                    <!-- <button type="button" onclick="javascript:itemqa_update(<?php echo $i; ?>);" class="btn01">수정</button>
                    <button type="button" onclick="javascript:itemqa_delete(fitemqa_password<?php echo $i; ?>, <?php echo $i; ?>);" class="btn01">삭제</button> -->
                </div>
                <?php } ?>
            </div>
        </li>

    <?php }

    if ($i >= 0) echo '</ol>';

    if (!$i) echo '<p class="sit_empty">상품문의가 없습니다.</p>';
    ?>
</section>

<?php
echo itemqa_page($config['cf_mobile_pages'], $page, $total_page, "./itemqa.php?it_id=$it_id&amp;page=", "");
?>

<div id="sit_qa_wbtn">
    <!-- <a href="javascript:itemqawin('it_id=<?php echo $it_id; ?>');">상품문의 쓰기<span class="sound_only"> 새 창</span></a> -->
    <a href="<?php echo $itemqa_form; ?>" class="btn02 itemqa_form">상품문의 쓰기<span class="sound_only"> 새 창</span></a>
    <a href="<?php echo $itemqa_list; ?>" id="itemqa_list" class="btn01">더보기</a>
</div>

<script>
$(function(){
    $(".itemqa_form").click(function(){
        window.open(this.href, "itemqa_form", "width=800,height=500,scrollbars=1");
        return false;
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

    $(".qa_page").click(function(){
        $("#itemqa").load($(this).attr("href"));
        return false;
    });
});
</script>
<!-- } 상품문의 목록 끝 -->