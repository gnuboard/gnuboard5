<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>
    </div>
</div>

<hr>

<footer id="ft">
    <h1><?=$config['cf_title']?> 정보</h1>
    <?=popular('neo'); // 인기검색어 ?>
    <?=visit("neo"); // 방문자수 ?>
    <div id="ft_catch"><img src="<?=$g4['path']?>/img/ft_catch.jpg" alt="Sharing All Possibilities"></div>
    <div id="ft_copy">
        <p>Copyright &copy; <b>소유하신 도메인.</b> All rights reserved.</p>
    </div>
</footer>

<?
if (is_mobile()) {
    echo "<a href=\"{$g4['url']}/?mobile\">모바일 버전으로</a>";
}
?>

<script>
function fsearchbox_submit(f)
{
    if (f.stx.value.length < 2) {
        alert("검색어는 두글자 이상 입력하십시오.");
        f.stx.select();
        f.stx.focus();
        return false;
    }

    // 검색에 많은 부하가 걸리는 경우 이 주석을 제거하세요.
    var cnt = 0;
    for (var i=0; i<f.stx.value.length; i++) {
        if (f.stx.value.charAt(i) == ' ')
            cnt++;
    }

    if (cnt > 1) {
        alert("빠른 검색을 위하여 검색어에 공백은 한개만 입력할 수 있습니다.");
        f.stx.select();
        f.stx.focus();
        return false;
    }

    return true;
}
</script>

<?
include_once($g4['path'].'/tail.sub.php');
?>