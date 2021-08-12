<?php
include_once('./_common.php');

$it_id = isset($_REQUEST['it_id']) ? safe_replace_regex($_REQUEST['it_id'], 'it_id') : '';

if (!$is_member)
    alert_close('회원만 메일을 발송할 수 있습니다.');

// 스팸을 발송할 수 없도록 세션에 아무값이나 저장하여 hidden 으로 넘겨서 다음 페이지에서 비교함
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);

$it = get_shop_item($it_id, true);
if (! (isset($it['it_name']) && $it['it_name']))
    alert_close("등록된 상품이 아닙니다.");

$g5['title'] =  $it['it_name'].' - 추천하기';
include_once(G5_PATH.'/head.sub.php');
?>

<div id="sit_rec_new" class="new_win">
    <h1 id="win_title"><?php echo $g5['title']; ?></h1>

    <form name="fitemrecommend" method="post" action="./itemrecommendmail.php" autocomplete="off" onsubmit="return fitemrecommend_check(this);">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <input type="hidden" name="it_id" value="<?php echo $it_id; ?>">

    <div class="new_win_con form_01">
        <ul>
            <li>
                <label for="to_email" class="sound_only">추천받는 분 E-mail</label>
                <input type="email" name="to_email" id="to_email" required class="frm_input full_input" placeholder="추천받는 분 E-mail">
            </li>
            <li>
                <label for="subject" class="sound_only">제목</label>
                <input type="text" name="subject" id="subject" required class="frm_input full_input" placeholder="제목">
            </li>
            <li>
                <label for="content" class="sound_only">내용</label>
                <textarea name="content" id="content" required  placeholder="내용"></textarea>
            </li>
        </ul>

    </div>
    <div class="win_btn">
        <input type="submit" id="btn_submit" value="보내기" class="btn_submit">
        <a href="javascript:window.close();" class="btn_close">창닫기</a>
    </div>

    </form>

</div>

<script>
function fitemrecommend_check(f)
{
    return true;
}
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');