<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$delete_str = "";
if ($w == 'x') $delete_str = "댓";
if ($w == 'u') $g4['title'] = $delete_str."글 수정";
else if ($w == 'd' || $w == 'x') $g4['title'] = $delete_str."글 삭제";
else $g4['title'] = $g4['title'];
?>

<div id="pw_confirm">
    <h1><?=$g4['title']?></h1>
    <p>
        <? if ($w == 'u') {?>
        <strong>작성자만 글을 수정할 수 있습니다.</strong>
        작성자 본인이라면, 글 작성시 입력한 패스워드를 입력하여 글을 수정할 수 있습니다.
        <? } else if ($w == 'd' || $w == 'x') { ?>
        <strong>작성자만 글을 삭제할 수 있습니다.</strong>
        작성자 본인이라면, 글 작성시 입력한 패스워드를 입력하여 글을 삭제할 수 있습니다.
        <? } else { ?>
        <strong>비밀글 기능으로 보호된 글입니다.</strong>
        작성자와 관리자만 열람하실 수 있습니다. 본인이라면 패스워드를 입력하세요.
        <? } ?>
    </p>

    <form name="fboardpassword" method="post" action="<? echo $action; ?>">
    <input type="hidden" name="w" value="<?=$w?>">
    <input type="hidden" name="bo_table" value="<?=$bo_table?>">
    <input type="hidden" name="wr_id" value="<?=$wr_id?>">
    <input type="hidden" name="comment_id" value="<?=$comment_id?>">
    <input type="hidden" name="sfl" value="<?=$sfl?>">
    <input type="hidden" name="stx" value="<?=$stx?>">
    <input type="hidden" name="page" value="<?=$page?>">

    <fieldset>
        <label for="password_wr_password">패스워드<strong class="sound_only">필수</strong></label>
        <input type="password" id="password_wr_password" name="wr_password" class="fs_input required" maxLength="20" size="15" required>
        <input type="submit" class="fs_submit" value="확인">
    </fieldset>
    </form>

    <div class="btn_confirm">
        <a href="<?=$_SERVER['HTTP_REFERER']?>">돌아가기</a>
    </div>

</div>

<script>
document.fboardpassword.wr_password.focus();
</script>
