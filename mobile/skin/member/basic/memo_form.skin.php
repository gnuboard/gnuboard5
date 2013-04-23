<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<link rel="stylesheet" href="<?=$member_skin_url?>/style.css">

<div id="memo_write" class="new_win">
    <h1>쪽지보내기</h1>

    <ul class="new_win_ul">
        <li><a href="./memo.php?kind=recv">받은쪽지</a></li>
        <li><a href="./memo.php?kind=send">보낸쪽지</a></li>
        <li><a href="./memo_form.php">쪽지쓰기</a></li>
    </ul>

    <form name="fmemoform" action="./memo_form_update.php" onsubmit="return fmemoform_submit(this);" method="post" autocomplete="off">
    <div class="cbox">
        <table class="frm_tbl">
        <caption>쪽지쓰기</caption>
        <tbody>
        <tr>
            <th scope="row"><label for="me_recv_mb_id">받는 회원아이디<strong class="sound_only">필수</strong></label></th>
            <td>
                <input type="text" name="me_recv_mb_id" value="<?=$me_recv_mb_id?>" id="me_recv_mb_id" required class="frm_input required">
                <span class="frm_info">여러 회원에게 보낼때는 컴마(,)로 구분하세요.</span>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="me_memo">내용</label></th>
            <td><textarea name="me_memo" id="me_memo" required><?=$content?></textarea></td>
        </tr>
        <tr>
            <th scope="row">자동등록방지</th>
            <td>
                <?=captcha_html();?>
            </td>
        </tr>
        </tbody>
        </table>
    </div>

    <div class="btn_win">
        <p>
            작성하신 쪽지를 발송하시려면 <strong>보내기</strong> 버튼을, 작성을 취소하고 창을 닫으시려면 <strong>창닫기</strong> 버튼을 누르세요.
        </p>
        <input type="submit" value="보내기" id="btn_submit" class="btn_submit">
        <button type="button" class="btn_cancel" onclick="javascript:window.close();">창닫기</button>
    </div>
    </form>
</div>

<script>
function fmemoform_submit(f)
{
    <? echo chk_captcha_js(); ?>

    return true;
}
</script>
