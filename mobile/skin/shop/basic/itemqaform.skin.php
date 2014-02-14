<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_MSHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 상품문의 쓰기 시작 { -->
<div id="sit_qa_write" class="new_win">
    <h1 id="win_title">상품문의 쓰기</h1>

    <form name="fitemqa" method="post" action="./itemqaformupdate.php" onsubmit="return fitemqa_submit(this);" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="it_id" value="<?php echo $it_id; ?>">
    <input type="hidden" name="iq_id" value="<?php echo $iq_id; ?>">
    <input type="hidden" name="is_mobile_shop" value="1">

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <colgroup>
            <col class="grid_2">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">옵션</th>
            <td>
                <input type="checkbox" name="iq_secret" value="1" <?php echo $chk_secret; ?>>
                <label for="iq_secret">비밀글</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="iq_email">이메일</label></th>
            <td><input type="email" name="iq_email" value="<?php echo $qa['iq_email']; ?>" class="frm_input" size="30"> 이메일을 입력하시면 답변 등록 시 답변이 이메일로 전송됩니다.</td>
        </tr>
        <tr>
            <th scope="row"><label for="iq_hp">휴대폰</label></th>
            <td><input type="text" name="iq_hp" value="<?php echo $qa['iq_hp']; ?>" class="frm_input" size="20"> 휴대폰번호를 입력하시면 답변 등록 시 답변등록 알림이 SMS로 전송됩니다.</td>
        </tr>
        <tr>
            <th scope="row"><label for="iq_subject">제목</label></th>
            <td><input type="text" name="iq_subject" value="<?php echo get_text($qa['iq_subject']); ?>" id="iq_subject" required class="required frm_input" minlength="2" maxlength="250"></td>
        </tr>
        <tr>
            <th scope="row"><label for="iq_question">질문</label></th>
            <td><?php echo $editor_html; ?></td>
        </tr>
        </tbody>
        </table>
    </div>

    <div class="win_btn">
        <input type="submit" value="작성완료" class="btn_submit">
        <button type="button" onclick="self.close();">닫기</button>
    </div>
    </form>
</div>

<script type="text/javascript">
function fitemqa_submit(f)
{
    <?php echo $editor_js; ?>

    return true;
}
</script>
<!-- } 상품문의 쓰기 끝 -->