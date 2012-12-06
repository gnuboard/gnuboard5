<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<h1>쪽지보내기</h1>

<ul>
    <li><a href="./memo.php?kind=recv">받은쪽지</a></li>
    <li><a href="./memo.php?kind=send">보낸쪽지</a></li>
    <li><a href="./memo_form.php">쪽지보내기</a></li>
</ul>

<form name="fmemoform" method="post" onsubmit="return fmemoform_submit(this);" autocomplete="off">
<table>
<caption>쪽지쓰기</caption>
<tbody>
<tr>
    <th scope="row"><label for="me_recv_mb_id">받는 회원아이디</label></th>
    <td>
        <input type="text" id="me_recv_mb_id" name="me_recv_mb_id" required value="<?=$me_recv_mb_id?>">
        여러 회원에게 보낼때는 컴마(,)로 구분하세요.
    </td>
</tr>
<tr>
    <th scope="row"><label for="me_memo">내용</label></th>
    <td><textarea id="me_memo" name="me_memo" required><?=$content?></textarea></td>
</tr>
</tbody>
</table>

<fieldset>
    <legend>자동등록방지</legend>
    <img id="kcaptcha_image" />
    <input type="text" name="wr_key" required>
    왼쪽의 글자를 입력하세요.
</fieldset>

<div class="btn_confirm">
    <input type="submit" id="btn_submit" value="보내기">
    <a href="javascript:window.close();">창닫기</a>
</div>
</form>

<script src="<?=$g4[path]?>/js/md5.js"></script>
<script src="<?="$g4[path]/js/jquery.kcaptcha.js"?>"></script>
<script>
with (document.fmemoform) {
    if (me_recv_mb_id.value == "")
        me_recv_mb_id.focus();
    else
        me_memo.focus();
}

function fmemoform_submit(f)
{
    if (!check_kcaptcha(f.wr_key)) {
        return false;
    }

    document.getElementById("btn_submit").disabled = true;

    f.action = "./memo_form_update.php";
    return true;
}
</script>
