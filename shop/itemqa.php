<<<<<<< HEAD
<?php
include_once('./_common.php');
include_once(G4_LIB_PATH.'/thumbnail.lib.php');

$it_id = $_REQUEST['it_id'];

$itemqa_list = "./itemqalist.php";
$itemqa_form = "./itemqaform.php?it_id=".$it_id;
$itemqa_formupdate = "./itemqaformupdate.php?it_id=".$it_id;

include_once(G4_PATH.'/head.sub.php');
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
        $iq_question = get_view_thumbnail($row['iq_question'], 300);
        $iq_time    = substr($row['iq_time'], 2, 8);
        $iq_href    = './itemqalist.php?bo_table=itemqa&amp;wr_id='.$row['wr_id'];

        $hash = md5($row['iq_id'].$row['iq_time'].$row['iq_ip']);

        // http://stackoverflow.com/questions/6967081/show-hide-multiple-divs-with-jquery?answertab=votes#tab-top

        $iq_stats = '';
        $iq_answer = '';
        $iq_flag = 0;
        if ($row['iq_answer'])
        {
            $iq_answer = get_view_thumbnail($row['iq_answer'], 300);
            $iq_stats = '답변완료';
        } else {
            $iq_stats = '답변전';
            $iq_answer = '답변이 등록되지 않았습니다.';
            $iq_flag = 1;
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
                <p class="sit_qa_qaa">
                    <strong>답변</strong><br>
                    <?php echo $iq_answer; ?>
                </p>

                <?php if ($row['mb_id'] == $member['mb_id'] && $iq_answer == 0) { ?>
                <div class="sit_qa_cmd">
                    <a href="<?php echo $itemqa_form."&amp;iq_id={$row['iq_id']}&amp;w=u"; ?>" class="itemqa_form" onclick="return false;">수정</a>
                    <a href="<?php echo $itemqa_formupdate."&amp;iq_id={$row['iq_id']}&amp;w=d&amp;hash={$hash}"; ?>" class="itemqa_delete" onclick="return false;">삭제</a>
                    <!-- <button type="button" onclick="javascript:itemqa_update(<?php echo $i; ?>);" class="btn01">수정</button>
                    <button type="button" onclick="javascript:itemqa_delete(fitemqa_password<?php echo $i; ?>, <?php echo $i; ?>);" class="btn01">삭제</button> -->
                </div>
                <?php } ?>
            </div>
            </div>
        </li>


        <li class="sit_qa_li">
            <button type="button" class="sit_qa_li_title" onclick="javascript:qa_menu('sit_qa_con_<?php echo $i; ?>')"><b><?php echo $iq_num; ?>.</b> <?php echo $iq_subject; ?></button>
            <dl class="sit_qa_dl">
                <dt>작성자</dt>
                <dd><?php echo $iq_name; ?></dd>
                <dt>작성일</dt>
                <dd><?php echo $iq_time; ?></dd>
            </dl>

            <div id="sit_qa_con_<?php echo $i; ?>" class="sit_qa_con">
                <p>
                    <?php echo $iq_question; // 상품문의 질문 ?>
                    <?php echo $iq_answer; // 상품문의 답변 ?>
                    <a href="<?php echo $itemqa_form."&amp;iq_id={$row['iq_id']}&amp;w=u"; ?>" class="itemqa_form" onclick="return false;">수정</a>
                    <a href="<?php echo "./itemqaformupdate.php?w=d&amp;it_id={$row['it_id']}&amp;iq_id={$row['iq_id']}&amp;hash={$hash}"; ?>">삭제</a>
                </p>
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
        window.open(this.href, "itemqa_form", "width=800,height=550"); 
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

<?php
include_once(G4_PATH.'/tail.sub.php');
?>
=======
<?php
include_once('./_common.php');

$it_id = $_REQUEST['it_id'];
?>

<section id="sit_qa_list">
    <h3>등록된 상품문의</h3>

    <?php
    $sql_common = " from {$g4['shop_item_qa_table']} where it_id = '$it_id' ";

    // 테이블의 전체 레코드수만 얻음
    $sql = " select COUNT(*) as cnt " . $sql_common;
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];

    $rows = 5;
    $total_page  = ceil($total_count / $rows); // 전체 페이지 계산
    if ($page == "") $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
    $from_record = ($page - 1) * $rows; // 시작 레코드 구함

    $sql = "select *
             $sql_common
             order by iq_id desc
             limit $from_record, $rows ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {

        $num = $total_count - ($page - 1) * $rows - $i;

        $iq_name  = get_text($row['iq_name']);
        $iq_subject  = conv_subject($row['iq_subject'],50, '…');
        $iq_question = conv_content($row['iq_question'],0);
        $iq_answer   = conv_content($row['iq_answer'],0);

        $iq_time = substr($row['iq_time'], 2, 14);

        //$qa = "<img src='$g4[shop_img_path]/icon_poll_q.gif' border=0>";
        //if ($row[iq_answer]) $qa .= "<img src='$g4[shop_img_path]/icon_answer.gif' border=0>";
        //$qa = "$qa";

        $iq_stats = '';
        $iq_answer = '';
        $iq_flag = 0;
        if ($row['iq_answer'])
        {
            $iq_answer = conv_content($row['iq_answer'],0);
            $iq_stats = '답변완료';
        } else {
            $iq_stats = '답변전';
            $iq_answer = '답변이 등록되지 않았습니다.';
            $iq_flag = 1;
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
                <p class="sit_qa_qaa">
                    <strong>답변</strong><br>
                    <?php echo $iq_answer; ?>
                </p>

                <textarea id="tmp_iq_id<?php echo $i; ?>"><?php echo $row['iq_id']; ?></textarea>
                <textarea id="tmp_iq_name<?php echo $i; ?>"><?php echo $row['iq_name']; ?></textarea>
                <textarea id="tmp_iq_subject<?php echo $i; ?>"><?php echo $row['iq_subject']; ?></textarea>
                <textarea id="tmp_iq_question<?php echo $i; ?>"><?php echo $row['iq_question']; ?></textarea>

                <?php if ($row['mb_id'] == $member['mb_id'] && $iq_answer == 0) { ?>
                <div class="sit_qa_cmd">
                    <a href="./itemqaform.php?w=u&amp;it_id=<?php echo $it_id; ?>&amp;iq_id=<?php echo $row['iq_id']; ?>" class="itemqa_mod btn01">수정</a>
                    <a href="./itemqaformupdate.php?w=d&amp;it_id=<?php echo $it_id; ?>&amp;iq_id=<?php echo $row['iq_id']; ?>" class="itemqa_del btn01">삭제</a>
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
// 현재페이지, 총페이지수, 한페이지에 보여줄 행, URL
function itemqa_page($write_pages, $cur_page, $total_page, $url, $add="")
{
    $url = preg_replace('#&amp;page=[0-9]*(&amp;page=)$#', '$1', $url);

    $str = '';
    if ($cur_page > 1) {
        $str .= '<a href="'.$url.'1'.$add.'" class="qa_page qa_start" onclick="return false;">처음</a>'.PHP_EOL;
    }

    $start_page = ( ( (int)( ($cur_page - 1 ) / $write_pages ) ) * $write_pages ) + 1;
    $end_page = $start_page + $write_pages - 1;

    if ($end_page >= $total_page) $end_page = $total_page;

    if ($start_page > 1) $str .= '<a href="'.$url.($start_page-1).$add.'" class="qa_page qa_prev" onclick="return false;">이전</a>'.PHP_EOL;

    if ($total_page > 1) {
        for ($k=$start_page;$k<=$end_page;$k++) {
            if ($cur_page != $k)
                $str .= '<a href="'.$url.$k.$add.'" class="qa_page" onclick="return false;">'.$k.'</a><span class="sound_only">페이지</span>'.PHP_EOL;
            else
                $str .= '<span class="sound_only">열린</span><strong class="qa_current">'.$k.'</strong><span class="sound_only">페이지</span>'.PHP_EOL;
        }
    }

    if ($total_page > $end_page) $str .= '<a href="'.$url.($end_page+1).$add.'" class="qa_page qa_next">다음</a>'.PHP_EOL;

    if ($cur_page < $total_page) {
        $str .= '<a href="'.$url.$total_page.$add.'" class="qa_page qa_end" onclick="return false;">맨끝</a>'.PHP_EOL;
    }

    if ($str)
        return "<nav class=\"qa_wrap\"><span class=\"qa\">{$str}</span></nav>";
    else
        return "";
}

echo itemqa_page($config['cf_write_pages'], $page, $total_page, "./itemqa.php?it_id=$it_id&amp;page=", "");
?>

<div id="sit_qa_wbtn">
    <a href="./itemqaform.php?it_id=<?php echo $it_id; ?>" id="itemqa_form" class="btn02">상품문의 쓰기</a>
</div>

<script>
$(function(){
    $("#itemqa_form").click(function(){
        window.open(this.href, "itemqa_form", "width=800,height=550");
        return false;
    });

    $(".itemqa_mod").live("click", function() {
        window.open(this.href, "itemqa_form", "width=800,height=550");
        return false;
    });

    $(".itemqa_del").live("click", function() {
        if(!confirm("상품문의를 삭제하시겠습니까?"))
            return false;

        <?php if($is_member) { ?>
        document.location.href = this.href;
        <?php } else { ?>
        var iq_pass_frm = "<div id=\"iq_password_frm\">";
        iq_pass_frm += "<form name=\"fitemqapass\" method=\"post\" action=\""+this.href+"\">";
        iq_pass_frm += "<label for=\"iq_password\">비밀번호</label>";
        iq_pass_frm += "<input type=\"password\" name=\"iq_password\" id=\"iq_password\" size=\"20\">";
        iq_pass_frm += "<input type=\"submit\" value=\"확인\">";
        iq_pass_frm += "</form>";
        iq_pass_frm += "</div>";

        $("#iq_password_frm").remove();
        $(this).after(iq_pass_frm);
        return false;
        <?php } ?>
    });

    $("form[name=fitemqapass]").live("submit", function() {
        var pass = trim($("input[name=iq_password]").val());
        if(pass == "") {
            alert("비밀번호를 입력해 주십시오.");
            return false;
        }

        return true;
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
        //alert($(this).attr("href"));
        $(top.document).find('#itemqa').load($(this).attr("href"));
    });
});
</script>
>>>>>>> 8ba2a84198461168008549042bbfc2d01e738d03
