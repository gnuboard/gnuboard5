<?php
include_once("./_common.php");

if (!$is_member)
    alert_close('회원만 메일을 발송할 수 있습니다.');

// 스팸을 발송할 수 없도록 세션에 아무값이나 저장하여 hidden 으로 넘겨서 다음 페이지에서 비교함
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);

$sql = " select it_name from {$g4['shop_item_table']} where it_id='$it_id' ";
$it = sql_fetch($sql);
if (!$it['it_name'])
    alert_close("등록된 상품이 아닙니다.");

$g4['title'] =  $it['it_name'].' - 추천하기';
define('_SHOP_', true);
include_once(G4_PATH.'/head.sub.php');
?>

<div id="sit_rec_new" class="new_win">
    <h1><?php echo $g4['title']; ?></h1>

    <div class="cbox">
        <form name="fitemrecommend" method="post" action="./itemrecommendmail.php" autocomplete="off" onsubmit="return fitemrecommend_check(this);">
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        <input type="hidden" name="it_id" value="<?php echo $it_id; ?>">
        <table class="frm_tbl">
        <colgroup>
            <col class="grid_3">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="to_email">추천받는 분 E-mail</label></th>
            <td><input type="text" name="to_email" id="to_email" required class="frm_input" size="51"></td>
        </tr>
        <tr>
            <th scope="row"><label for="subject">제목</label></th>
            <td><input type="text" name="subject" id="subject" required class="frm_input" size="51"></td>
        </tr>
        <tr>
            <th scope="row"><label for="content">내용</label></th>
            <td><textarea name="content" id="content" required></textarea></td>
        </tr>
        </tbody>
        </table>

        <div class="btn_win">
            <input type="submit" id="btn_submit" value="보내기" class="btn_submit">
            <a href="javascript:window.close();">창닫기</a>
        </div>
        </form>
    </div>

</div>

<script>
function fitemrecommend_check(f)
{
    return true;
}
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>