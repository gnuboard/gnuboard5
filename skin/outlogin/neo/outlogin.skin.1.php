<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if ($g4['https_url']) {
    $outlogin_url = $_GET['url'];
    if ($outlogin_url) {
        if (preg_match("/^\.\.\//", $outlogin_url)) {
            $outlogin_url = urlencode($g4[url]."/".preg_replace("/^\.\.\//", "", $outlogin_url));
        }
        else {
            $purl = parse_url($g4[url]);
            if ($purl[path]) {
                $path = urlencode($purl[path]);
                $urlencode = preg_replace("/".$path."/", "", $urlencode);
            }
            $outlogin_url = $g4[url].$urlencode;
        }
    }
    else {
        $outlogin_url = $g4[url];
    }
}
else {
    $outlogin_url = $urlencode;
}


if ($g4['https_url'])
    $action_url = "{$g4['https_url']}/$g4[bbs]/login_check.php";
else
    $action_url = "{$g4['bbs_path']}/login_check.php";
?>

<section id="ol_before" class="outlogin">
<h2>사이트 멤버쉽</h2>
<!-- 로그인 전 외부로그인 시작 -->
    <form name="fhead" method="post" action="<?=$action_url?>" onsubmit="return fhead_submit(this);" autocomplete="off">
    <fieldset>
        <legend>로그인</legend>
        <input type="hidden" name="url" value="<?=$outlogin_url?>">
        <label for="ol_mb_id" id="ol_mb_id_label">아이디</label>
        <input type="text" id="ol_mb_id" name="mb_id" maxlength="20" required>
        <label for="ol_mb_password" id="ol_mb_password_label">패스워드</label>
        <input type="password" id="ol_mb_password" name="mb_password" maxlength="20">
        <input type="checkbox" id="auto_login" name="auto_login" value="1" onclick="if (this.checked) { if (confirm('자동로그인을 사용하시면 다음부터 회원아이디와 패스워드를 입력하실 필요가 없습니다.\n\n\공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?')) { this.checked = true; } else { this.checked = false; } }">
        <label for="auto_login" id="auto_login_label">ID저장</label>
        <input type="submit" id="ol_submit" value="로그인">
        <ul>
            <li><a href="javascript:win_password_lost();">아이디찾기</a></li>
            <li><a href="<?=$g4['bbs_path']?>/register.php">회원가입</a></li>
        </ul>
    </fieldset>
    </form>
</section>

<script>
function fhead_submit(f)
{
    return true;
}
</script>
<!-- 로그인 전 외부로그인 끝 -->
