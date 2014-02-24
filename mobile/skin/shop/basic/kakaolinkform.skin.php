<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_MSHOP_SKIN_URL.'/style.css">', 0);
?>

<script src="<?php echo G5_JS_URL; ?>/kakao.link.js"></script>

<div id="kakao_message" class="new_win">
    <h1 id="win_title">카카오톡으로 보내기</h1>

        <form name="fkakao" class="win_desc" onsubmit="return kakaolink_send(this);">
        <div id="kakao_title"><?php echo $title; ?></div>
        <div class="tbl_frm01">
            <label for="message">메세지</label>
            <textarea id="message" name="message"></textarea>
        </div>
        <div class="win_btn">
            <input type="submit" value="보내기" class="btn_submit">
            <button type="button" onclick="window.close();">취소</button>
        </div>
        </form>
</div>

<script>
// 카카오톡 링크 보내기
function kakaolink_send(f)
{
    var msg = f.message.value;
    if(!msg) {
        alert("메세지를 입력해 주세요");
        return false;
    }

    /*
    msg, url, appid, appname은 실제 서비스에서 사용하는 정보로 업데이트되어야 합니다.
    */
    kakao.link("talk").send({
        msg : msg,
        url : "<?php echo $url; ?>",
        appid : "<?php echo $_SERVER['HTTP_HOST']; ?>",
        appver : "2.0",
        appname : "<?php echo $config['cf_title']; ?>",
        type : "link"
    });

    return false;
    //window.close();
}
</script>