<?php
include_once('./_common.php');

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/itemrecommend.php');
    return;
}

if (!$is_member)
    alert_close('회원만 메일을 발송할 수 있습니다.');

// 스팸을 발송할 수 없도록 세션에 아무값이나 저장하여 hidden 으로 넘겨서 다음 페이지에서 비교함
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);

$sql = " select it_name from {$g5['g5_shop_item_table']} where it_id='$it_id' ";
$it = sql_fetch($sql);
if (!$it['it_name'])
    alert_close("등록된 상품이 아닙니다.");

$g5['title'] =  $it['it_name'].' - 추천하기';
include_once(G5_PATH.'/head.sub.php');
?>

<!-- 상품 추천하기 시작 { -->
<div id="sit_rec_new" class="new_win">
    <h1 id="win_title"><?php echo $g5['title']; ?></h1>

    <form name="fitemrecommend" method="post" action="./itemrecommendmail.php" autocomplete="off" onsubmit="return fitemrecommend_check(this);">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <input type="hidden" name="it_id" value="<?php echo $it_id; ?>">

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <colgroup>
            <col class="grid_3">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="to_email">추천받는 분<br>E-mail<strong class="sound_only"> 필수</strong></label></th>
            <td><input type="text" name="to_email" id="to_email" required class="frm_input required" size="51"></td>
        </tr>
        <tr>
            <th scope="row"><label for="subject">제목<strong class="sound_only"> 필수</strong></label></th>
            <td><input type="text" name="subject" id="subject" required class="frm_input required" size="51"></td>
        </tr>
        <tr>
            <th scope="row"><label for="content">내용<strong class="sound_only"> 필수</strong></label></th>
            <td><textarea name="content" id="content" required class="required"></textarea></td>
        </tr>
        </tbody>
        </table>
    </div>

    <div class="win_btn">
        <input type="submit" id="btn_submit" value="보내기" class="btn_submit">
        <a href="javascript:window.close();">창닫기</a>
    </div>
    </form>

</div>

<script>
function fitemrecommend_check(f)
{
    return true;
}
</script>
<!-- } 상품 추천하기 끝 -->

<?php
include_once(G5_PATH.'/tail.sub.php');
?>