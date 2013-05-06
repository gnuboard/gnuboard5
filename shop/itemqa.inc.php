<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<section id="sit_qna_list">
    <h3>등록된 상품문의</h3>

    <?php
    $sql_common = " from {$g4['shop_item_qa_table']} where it_id = '{$it['it_id']}' ";

    // 테이블의 전체 레코드수만 얻음
    $sql = " select COUNT(*) as cnt " . $sql_common;
    $row = sql_fetch($sql);
    $qa_total_count = $row['cnt'];

    $qa_total_page  = ceil($qa_total_count / $qa_page_rows); // 전체 페이지 계산
    if ($qa_page == "") $qa_page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
    $qa_from_record = ($qa_page - 1) * $qa_page_rows; // 시작 레코드 구함

    $sql = "select *
             $sql_common
             order by iq_id desc
             limit $qa_from_record, $qa_page_rows ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {

        $num = $qa_total_count - ($qa_page - 1) * $qa_page_rows - $i;

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

        if ($i == 0) echo '<ol id="sit_qna_ol">';
    ?>

    <li class="sit_qna_li">
        <button type="button" class="sit_qna_li_title" onclick="javascript:qa_menu('sit_qna_con_<?php echo $i; ?>')"><?php echo $num; ?>. <?php echo $iq_subject; ?></button>
        <dl class="sit_qna_dl">
            <dt>작성자</dt>
            <dd><?php echo $iq_name; ?></dd>
            <dt>작성일</dt>
            <dd><?php echo $iq_time; ?></dd>
            <dt>상태</dt>
            <dd><?php echo $iq_stats; ?></dd>
        </dl>

        <div id="sit_qna_con_<?php echo $i; ?>" class="sit_qna_con">
            <p class="sit_qna_qaq">
                <strong>문의내용</strong><br>
                <?php echo $iq_question; // 상품 문의 내용 ?>
            </p>
            <p class="sit_qna_qaa">
                <strong>답변</strong><br>
                <?php echo $iq_answer; ?>
            </p>

            <textarea id="tmp_iq_id<?php echo $i; ?>"><?php echo $row['iq_id']; ?></textarea>
            <textarea id="tmp_iq_name<?php echo $i; ?>"><?php echo $row['iq_name']; ?></textarea>
            <textarea id="tmp_iq_subject<?php echo $i; ?>"><?php echo $row['iq_subject']; ?></textarea>
            <textarea id="tmp_iq_question<?php echo $i; ?>"><?php echo $row['iq_question']; ?></textarea>

            <?php if ($row['mb_id'] == $member['mb_id'] && $iq_answer == 0) { ?>
            <div class="sit_qna_cmd">
                <button type="button" onclick="javascript:itemqa_update(<?php echo $i; ?>);" class="btn01">수정</button>
                <button type="button" onclick="javascript:itemqa_delete(fitemqa_password<?php echo $i; ?>, <?php echo $i; ?>);" class="btn01">삭제</button>
            </div>
            <?php } ?>
        </div>

        <div id="sit_qna_pw_<?php echo $i; ?>" class="sit_qna_pw">
            <form name="fitemqa_password<?php echo $i; ?>" method="post" action="./itemqaupdate.php" autocomplete="off">
            <input type="hidden" name="w" value="">
            <input type="hidden" name="iq_id" value="">
            <input type="hidden" name="it_id" value="<?php echo $it['it_id']; ?>">
            <span>삭제하시려면 글 작성 시 입력하신 패스워드를 입력해주세요.</span>
            <label for="iq_password_<?=$i?>">패스워드</label>
            <input type="password" name="iq_password" id="iq_password_<?=$i?>" required class="frm_input">
            <input type="submit" value="확인" class="btn_frmline">
            </form>
        </div>
    </li>

    <?php }

    if ($i >= 0) echo '</ol>';

    if (!$i) echo '<p class="sit_empty">상품문의가 없습니다.</p>';
    ?>
</section>

<div id="sit_qna_wbtn">
    <button type="button" id="iq_write" class="btn_submit" onclick="javascript:itemqa_insert();">상품문의 쓰기</button>
</div>

<section id="sit_qna_w">
    <h3>상품문의 작성</h3>

    <form name="fitemqa" method="post" onsubmit="return fitemqa_submit(this);" autocomplete="off">
    <input type="hidden" name="w" value="">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <input type="hidden" name="iq_id" value="">
    <input type="hidden" name="it_id" value="<?php echo $it['it_id']; ?>">
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <?php if (!$is_member) { ?>
    <tr>
        <th scope="row"><label for="iq_name">이름</label></th>
        <td><input type="text" name="iq_name" id="iq_name" required class="frm_input" maxlength="20" minlength="2"></td>
    </tr>
    <tr>
        <th scope="row"><label for="iq_password">패스워드</label></th>
        <td>
            <span class="frm_info">패스워드는 최소 3글자 이상 입력하십시오.</span>
            <input type="password" name="iq_password" id="iq_password" required class="frm_input" maxlength="20" minlength="3">
        </td>
    </tr>
    <?php } ?>
    <tr>
        <th scope="row"><label for="iq_subject">제목</label></th>
        <td><input type="text" name="iq_subject" id="iq_subject" required class="frm_input" size="71" maxlength="100"></td>
    </tr>
    <tr>
        <th scope="row"><label for="iq_question">내용</label></th>
        <td><textarea name="iq_question" id="iq_question" required></textarea></td>
    </tr>
    <tr>
        <th scope="row">자동등록방지</th>
        <td><?php echo $captcha_html; ?></td>
    </tr>
    </tbody>
    </table>

    <div class="btn_confirm">
        <input type="submit" value="작성완료" class="btn_submit">
    </div>
    </form>
</section>

<?php if ($qa_pages) get_paging(10, $qa_page, $qa_total_page, './item.php?it_id='.$it_id.'&amp;'.$qstr.'&amp;qa_page=', '#qa'); // 페이징 ?>

<script>
$(function() {
});

function fitemqa_submit(f)
{
<?php echo chk_captcha_js(); ?>

f.action = "itemqaupdate.php";
return true;
}

function itemqa_insert()
{
/*
if (!g4_is_member) {
    alert("로그인 하시기 바랍니다.");
    return;
}
*/

var f = document.fitemqa;
var id = document.getElementById('sit_qna_w');

id.style.display = 'block';

f.w.value = '';
f.iq_id.value = '';
if (!g4_is_member)
{
    f.iq_name.value = '';
    f.iq_name.readOnly = false;
    f.iq_password.value = '';
}
f.iq_subject.value = '';
f.iq_question.value = '';
}

function itemqa_update(idx)
{
var f = document.fitemqa;
var id = document.getElementById('sit_qna_w');

id.style.display = 'block';

f.w.value = 'u';
f.iq_id.value = document.getElementById('tmp_iq_id'+idx).value;
if (!g4_is_member)
{
    f.iq_name.value = document.getElementById('tmp_iq_name'+idx).value;
    f.iq_name.readOnly = true;
}
f.iq_subject.value = document.getElementById('tmp_iq_subject'+idx).value;
f.iq_question.value = document.getElementById('tmp_iq_question'+idx).value;
}

function itemqa_delete(f, idx)
{
var id = document.getElementById('sit_qna_w');

f.w.value = 'd';
f.iq_id.value = document.getElementById('tmp_iq_id'+idx).value;

if (g4_is_member)
{
    if (confirm("삭제하시겠습니까?"))
        f.submit();
}
else
{
    id.style.display = 'none';
    document.getElementById('itemqa_password'+idx).style.display = 'block';
}
}
</script>
